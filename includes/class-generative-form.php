<?php
namespace Contactum;

/**
 * AI form generation — turns a plain-text description into a saved
 * Contactum form via the OpenAI API, using the site owner's own API key
 * (entered under Contactum → Settings → AI Form Generator, never hardcoded).
 *
 * Architecturally this is the same problem as importing a form from another
 * plugin: turn an external field list into a saved form. It reuses
 * FieldBuilder (shared with the CF7/GF/WPForms/etc. importers) to build
 * field arrays, rather than duplicating that ~250-line switch statement.
 */
class GenerativeForm {

    const OPTION_KEY = '_contactum_ai_form_generator_details';
    const API_URL     = 'https://api.openai.com/v1/chat/completions';
    const MODEL        = 'gpt-4o-mini';

    public function __construct() {
        add_action( 'wp_ajax_contactum_generate_ai_form', [ $this, 'generate' ] );
    }

    /**
     * Field types the AI is allowed to use — kept in sync with what
     * FieldBuilder::field() actually supports.
     *
     * @return array
     */
    private function allowed_types() {
        return [
            'text', 'email', 'textarea', 'select', 'multiselect', 'date',
            'number', 'url', 'checkbox', 'radio', 'hidden', 'section_break',
            'html', 'toc', 'file', 'name',
        ];
    }

    public function get_api_key() {
        $settings = get_option( self::OPTION_KEY, [] );
        return ! empty( $settings['api_key'] ) ? $settings['api_key'] : '';
    }

    public function is_configured() {
        return (bool) $this->get_api_key();
    }

    // ── AJAX entry point ─────────────────────────────────────────────────────

    public function generate() {
        check_ajax_referer( 'contactum-form-builder-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized operation', 'contactum' ) ] );
        }

        $api_key = $this->get_api_key();
        if ( ! $api_key ) {
            wp_send_json_error( [
                'message' => __( 'Add your OpenAI API key under Settings → AI Form Generator first.', 'contactum' ),
            ] );
        }

        $prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
        $extra  = isset( $_POST['additional_prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['additional_prompt'] ) ) : '';

        if ( ! $prompt ) {
            wp_send_json_error( [ 'message' => __( 'Please describe the form you want to create.', 'contactum' ) ] );
        }

        if ( mb_strlen( $prompt ) > 2000 || mb_strlen( $extra ) > 1000 ) {
            wp_send_json_error( [ 'message' => __( 'Please keep the description shorter and try again.', 'contactum' ) ] );
        }

        $rate_error = $this->check_rate_limit();
        if ( $rate_error ) {
            wp_send_json_error( [ 'message' => $rate_error ] );
        }

        $ai_form = $this->request_openai( $api_key, $prompt, $extra );

        if ( is_wp_error( $ai_form ) ) {
            wp_send_json_error( [ 'message' => $ai_form->get_error_message() ] );
        }

        $form_id = $this->save_form( $ai_form );

        if ( is_wp_error( $form_id ) ) {
            wp_send_json_error( [ 'message' => $form_id->get_error_message() ] );
        }

        $this->bump_rate_limit();

        wp_send_json_success( [
            'form_id' => $form_id,
            'message' => __( 'Form generated successfully.', 'contactum' ),
        ] );
    }

    // ── Rate limiting ─────────────────────────────────────────────────────────
    // The site owner's own key pays per call here (unlike a centrally-proxied
    // service), so a simple per-user daily cap guards against runaway cost
    // from repeated clicking or a compromised admin account.

    private function rate_limit_key() {
        return 'contactum_ai_gen_count_' . get_current_user_id();
    }

    private function check_rate_limit() {
        $limit = (int) apply_filters( 'contactum_ai_generation_daily_limit', 20 );
        $count = (int) get_transient( $this->rate_limit_key() );

        if ( $count >= $limit ) {
            return sprintf(
                /* translators: %d: max generations allowed per day */
                __( "You've reached today's limit of %d AI form generations. Try again tomorrow.", 'contactum' ),
                $limit
            );
        }

        return '';
    }

    private function bump_rate_limit() {
        $key   = $this->rate_limit_key();
        $count = (int) get_transient( $key );
        set_transient( $key, $count + 1, DAY_IN_SECONDS );
    }

    // ── OpenAI request ───────────────────────────────────────────────────────

    private function request_openai( $api_key, $prompt, $extra ) {
        $user_prompt = 'Create a form for: ' . $prompt;

        if ( $extra ) {
            $user_prompt .= "\nAlso include fields for: " . $extra;
        }

        $body = [
            'model'           => apply_filters( 'contactum_ai_model', self::MODEL ),
            'messages'        => [
                [ 'role' => 'system', 'content' => $this->get_system_prompt() ],
                [ 'role' => 'user', 'content' => $user_prompt ],
            ],
            'temperature'     => 0.4,
            'response_format' => [ 'type' => 'json_object' ],
        ];

        $response = wp_remote_post( self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 60,
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( 200 !== $code ) {
            $message = isset( $data['error']['message'] )
                ? $data['error']['message']
                : __( 'OpenAI request failed. Please check your API key and try again.', 'contactum' );
            return new \WP_Error( 'openai_error', $message );
        }

        $content = isset( $data['choices'][0]['message']['content'] ) ? $data['choices'][0]['message']['content'] : '';

        // Defensive: strip a ```json fence if the model added one despite
        // response_format: json_object already asking for raw JSON.
        if ( preg_match( '/```json(.*?)```/s', $content, $matches ) ) {
            $content = trim( $matches[1] );
        }

        $decoded = json_decode( $content, true );

        if ( JSON_ERROR_NONE !== json_last_error() || empty( $decoded['fields'] ) || ! is_array( $decoded['fields'] ) ) {
            return new \WP_Error( 'invalid_response', __( 'Could not understand the AI response. Please try rephrasing your description.', 'contactum' ) );
        }

        return $decoded;
    }

    private function get_system_prompt() {
        $allowed = implode( ', ', $this->allowed_types() );

        return "You are a form-schema generator for a WordPress form builder. Return ONLY a JSON object — no markdown, no explanation, no code fences.\n\n"
            . "Schema:\n"
            . "{\n"
            . "  \"title\": \"short form title\",\n"
            . "  \"fields\": [\n"
            . "    { \"type\": \"text\", \"label\": \"...\", \"name\": \"field_name\", \"required\": true, \"placeholder\": \"...\", \"options\": [\"...\"] }\n"
            . "  ]\n"
            . "}\n\n"
            . "Rules:\n"
            . "- \"type\" must be exactly one of: {$allowed}. Never invent a type outside this list — pick the closest match instead.\n"
            . "- Only \"select\", \"multiselect\", \"radio\", and \"checkbox\" use \"options\" (an array of plain strings).\n"
            . "- \"name\" must be lowercase snake_case, unique within the form, no spaces or punctuation.\n"
            . "- Set \"required\" explicitly (true or false) for every field based on what the prompt implies.\n"
            . "- Include one \"email\" field unless the prompt clearly describes a form that shouldn't collect contact info.\n"
            . "- Only include fields the prompt actually asked for — don't pad the form with unrelated fields.";
    }

    // ── Save as a Contactum form ─────────────────────────────────────────────

    private function save_form( array $ai_form ) {
        $allowed = $this->allowed_types();
        $fields  = [];

        foreach ( $ai_form['fields'] as $index => $ai_field ) {
            if ( ! is_array( $ai_field ) ) {
                continue;
            }

            $type = isset( $ai_field['type'] ) ? sanitize_key( $ai_field['type'] ) : '';

            // Validate against Contactum's own allowed set rather than
            // trusting the model directly — an unrecognised type is
            // skipped, not guessed at, so a hallucinated field never
            // breaks the saved form.
            if ( ! in_array( $type, $allowed, true ) ) {
                continue;
            }

            $label = ! empty( $ai_field['label'] ) ? sanitize_text_field( $ai_field['label'] ) : '';

            $name = ! empty( $ai_field['name'] ) ? sanitize_key( $ai_field['name'] ) : sanitize_key( $label );
            if ( ! $name ) {
                $name = $type . '_' . $index;
            }

            $options = [];
            if ( ! empty( $ai_field['options'] ) && is_array( $ai_field['options'] ) ) {
                foreach ( $ai_field['options'] as $option ) {
                    if ( is_string( $option ) && '' !== $option ) {
                        $clean             = sanitize_text_field( $option );
                        $options[ $clean ] = $clean;
                    }
                }
            }

            $built = FieldBuilder::field( $type, [
                'required'    => ! empty( $ai_field['required'] ) ? 'yes' : 'no',
                'label'       => $label ? $label : ucfirst( str_replace( '_', ' ', $name ) ),
                'name'        => $name,
                'placeholder' => ! empty( $ai_field['placeholder'] ) ? sanitize_text_field( $ai_field['placeholder'] ) : '',
                'help'        => ! empty( $ai_field['help'] ) ? sanitize_text_field( $ai_field['help'] ) : '',
                'options'     => $options,
            ] );

            if ( $built ) {
                $fields[] = $built;
            }
        }

        if ( ! $fields ) {
            return new \WP_Error( 'empty_form', __( 'The AI didn\'t return any usable fields. Please try again with a more specific description.', 'contactum' ) );
        }

        $title = ! empty( $ai_form['title'] ) ? sanitize_text_field( $ai_form['title'] ) : __( 'AI Generated Form', 'contactum' );

        $form_id = wp_insert_post( [
            'post_title'  => $title,
            'post_type'   => 'contactum_forms',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ] );

        if ( is_wp_error( $form_id ) ) {
            return $form_id;
        }

        foreach ( $fields as $menu_order => $field ) {
            wp_insert_post( [
                'post_type'    => 'contactum_input',
                'post_status'  => 'publish',
                'post_content' => maybe_serialize( $field ),
                'post_parent'  => $form_id,
                'menu_order'   => $menu_order,
            ] );
        }

        update_post_meta( $form_id, 'wpuf_form_settings', FieldBuilder::default_form_settings() );

        return $form_id;
    }
}
