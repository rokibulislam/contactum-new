<?php
namespace Contactum\Integrations;

class CleanTalkIntegration extends Contactum_Integration {

    const API_ENDPOINT = 'https://moderate.cleantalk.org/api2.0';
    const API_TIMEOUT  = 8;

    public $option_key = 'contactum_cleantalk';

    public function __construct() {
        $this->id          = 'cleantalk';
        $this->title       = __( 'CleanTalk Spam Protection', 'contactum' );
        $this->description = __( 'Cloud-based spam filter. Checks every form submission against the CleanTalk database — no CAPTCHA needed.', 'contactum' );
        $this->icon        = 'dashicons-shield-alt';

        parent::__construct();

        add_filter( 'contactum_check_spam', [ $this, 'check_spam' ], 10, 4 );
        add_action( 'wp_ajax_contactum_cleantalk_verify_key', [ $this, 'verify_key_ajax' ] );
    }

    // ── Integration defaults (per-form) ──────────────────────────────────────

    public function getIntegrationDefaults() {
        return [
            'enabled'      => false,
            'email_field'  => '',   // field name to read submitter email from
            'name_field'   => '',   // optional: field name to read submitter name from
            'block_action' => 'block', // 'block' | 'flag' (flag = save but mark as spam)
        ];
    }

    public function get_integration_settings() {
        return [
            [
                'name'        => 'enabled',
                'label'       => __( 'Enable CleanTalk', 'contactum' ),
                'type'        => 'checkbox-single',
                'placeholder' => __( 'Enable spam check for this form', 'contactum' ),
            ],
            [
                'name'        => 'email_field',
                'label'       => __( 'Email Field Name', 'contactum' ),
                'type'        => 'text',
                'placeholder' => __( 'e.g. email  (blank = auto-detect)', 'contactum' ),
                'help'        => __( 'Leave blank to use the first email-type field automatically.', 'contactum' ),
            ],
            [
                'name'        => 'name_field',
                'label'       => __( 'Name Field Name', 'contactum' ),
                'type'        => 'text',
                'placeholder' => __( 'e.g. name  (optional)', 'contactum' ),
            ],
        ];
    }

    // ── Settings-page section ────────────────────────────────────────────────

    public function get_settings_section() {
        return [
            'id'        => 'cleantalk',
            'title'     => '',
            'name'      => __( 'CleanTalk Spam Protection', 'contactum' ),
            'icon'      => 'dashicons-shield-alt',
            'component' => 'CleanTalkSettings',
        ];
    }

    // ── Global settings fields ────────────────────────────────────────────────

    public function get_settings_fields() {
        return [
            'valid_message'   => __( 'API key is valid. CleanTalk spam protection is active.', 'contactum' ),
            'invalid_message' => __( 'API key is invalid.', 'contactum' ),
            'hide_on_valid'   => false,
            'fields'          => [
                [
                    'name'        => 'access_key',
                    'label'       => __( 'Access Key', 'contactum' ),
                    'type'        => 'text',
                    'placeholder' => __( 'Your CleanTalk access key', 'contactum' ),
                    'help'        => __( 'Get your free key at cleantalk.org.', 'contactum' ),
                ],
            ],
        ];
    }

    // ── Global settings storage ───────────────────────────────────────────────

    public function getGlobalSettings() {
        $saved = get_option( $this->option_key, [] );
        return wp_parse_args( $saved, [
            'access_key' => '',
            'status'     => false,
        ] );
    }

    public function saveGlobalSettings( $data, $settings_key ) {
        $key = isset( $data['access_key'] ) ? sanitize_text_field( $data['access_key'] ) : '';

        $settings = [
            'access_key' => $key,
            'status'     => false,
        ];

        if ( ! empty( $key ) ) {
            $valid            = $this->validate_key( $key );
            $settings['status'] = $valid;
        }

        update_option( $this->option_key, $settings, 'no' );
        wp_send_json_success( $settings );
    }

    // ── AJAX: verify key on demand ────────────────────────────────────────────

    public function verify_key_ajax() {
        check_ajax_referer( 'contactum-form-builder-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'contactum' ) );
        }

        $key = isset( $_POST['access_key'] ) ? sanitize_text_field( wp_unslash( $_POST['access_key'] ) ) : '';

        if ( empty( $key ) ) {
            wp_send_json_error( __( 'Please enter an access key.', 'contactum' ) );
        }

        $valid = $this->validate_key( $key );
        if ( $valid ) {
            wp_send_json_success( __( 'Access key is valid.', 'contactum' ) );
        } else {
            wp_send_json_error( __( 'Access key is invalid. Check your CleanTalk dashboard.', 'contactum' ) );
        }
    }

    // ── Spam check filter ────────────────────────────────────────────────────

    /**
     * Hooked to `contactum_check_spam`.
     *
     * Returns a non-empty errors array to reject the submission.
     * Always fails open: if the API is unreachable we let the submission through
     * rather than blocking legitimate users.
     *
     * @param  array  $errors        Existing errors from earlier filters.
     * @param  int    $form_id
     * @param  array  $entry_fields  Sanitised field values keyed by field name.
     * @param  array  $post_data     wp_unslash'd $_POST.
     * @return array
     */
    public function check_spam( $errors, $form_id, $entry_fields, $post_data ) {
        // Pass through if a prior filter already blocked this.
        if ( ! empty( $errors ) ) {
            return $errors;
        }

        $integration = contactum_is_integration_active( $form_id, $this->id );
        if ( false === $integration ) {
            return $errors;
        }

        $global = $this->getGlobalSettings();
        $key    = $global['access_key'] ?? '';

        if ( empty( $key ) ) {
            return $errors;
        }

        // ── Resolve email ────────────────────────────────────────────────────
        $email_field = ! empty( $integration->integration->email_field )
            ? $integration->integration->email_field
            : $this->auto_detect_email_field( $form_id );

        $email = ( $email_field && isset( $entry_fields[ $email_field ] ) )
            ? sanitize_email( $entry_fields[ $email_field ] )
            : '';

        // ── Resolve name ─────────────────────────────────────────────────────
        $name_field = ! empty( $integration->integration->name_field )
            ? $integration->integration->name_field
            : '';

        $name = ( $name_field && isset( $entry_fields[ $name_field ] ) )
            ? sanitize_text_field( $entry_fields[ $name_field ] )
            : '';

        // ── Submit time (bot detection) ───────────────────────────────────────
        $form_start  = isset( $post_data['_ctm_form_start'] ) ? absint( $post_data['_ctm_form_start'] ) : 0;
        $submit_time = $form_start > 0 ? max( 0, time() - $form_start ) : 0;

        // ── Call CleanTalk API ────────────────────────────────────────────────
        $result = $this->call_api( $key, [
            'method_name'     => 'check_message',
            'sender_email'    => $email,
            'sender_ip'       => contactum_get_client_ip(),
            'sender_nickname' => $name,
            'submit_time'     => $submit_time,
            'js_on'           => 1,
            'agent'           => 'contactum-' . CONTACTUM_VERSION,
        ] );

        // Fail open: API error → let submission through.
        if ( null === $result ) {
            return $errors;
        }

        if ( isset( $result['allow'] ) && 0 === (int) $result['allow'] ) {
            $message = isset( $result['comment'] )
                ? sanitize_text_field( $result['comment'] )
                : __( 'Your submission was identified as spam. Please try again.', 'contactum' );

            $errors['cleantalk_spam'] = $message;
        }

        return $errors;
    }

    // ── API helpers ──────────────────────────────────────────────────────────

    /**
     * Call the CleanTalk REST API.
     *
     * @param  string $auth_key
     * @param  array  $params
     * @return array|null  Decoded response body, or null on transport error.
     */
    private function call_api( $auth_key, array $params ) {
        $body = array_merge( $params, [ 'auth_key' => $auth_key ] );

        $response = wp_remote_post( self::API_ENDPOINT, [
            'body'      => $body,
            'timeout'   => self::API_TIMEOUT,
            'blocking'  => true,
            'sslverify' => true,
        ] );

        if ( is_wp_error( $response ) ) {
            // Log silently — never expose raw errors to submitters.
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[Contactum CleanTalk] API error: ' . $response->get_error_message() );
            }
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! is_array( $data ) ) {
            return null;
        }

        return $data;
    }

    /**
     * Lightweight key validation: send a minimal request and check the response.
     *
     * @param  string $key
     * @return bool
     */
    private function validate_key( $key ) {
        $result = $this->call_api( $key, [
            'method_name'  => 'check_message',
            'sender_email' => 'test@example.com',
            'sender_ip'    => '127.0.0.1',
            'submit_time'  => 60,
            'js_on'        => 1,
            'agent'        => 'contactum-' . CONTACTUM_VERSION,
        ] );

        // CleanTalk returns `error_message` containing "Invalid auth_key" for bad keys.
        if ( null === $result ) {
            return false;
        }

        if ( ! empty( $result['error_message'] ) && stripos( $result['error_message'], 'Invalid' ) !== false ) {
            return false;
        }

        return true;
    }

    /**
     * Find the first email-type field name in a form.
     *
     * @param  int $form_id
     * @return string|null
     */
    private function auto_detect_email_field( $form_id ) {
        $form   = contactum()->forms->get( $form_id );
        $fields = $form->getFields();

        foreach ( $fields as $field ) {
            if ( isset( $field['template'] ) && $field['template'] === 'email_field' ) {
                return $field['name'] ?? null;
            }
        }

        return null;
    }
}
