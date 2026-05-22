<?php
namespace Contactum\Integrations;

use Contactum\Notification as Contactum_Notification;

class WebhookIntegration extends Contactum_Integration {

    public $option_key = 'contactum_webhook';

    public function __construct() {
        $this->id          = 'webhook';
        $this->title       = __( 'Webhook / Zapier', 'contactum' );
        $this->description = __( 'Send form submissions as HTTP POST requests to any URL. Works with Zapier, Make (Integromat), n8n, and custom endpoints.', 'contactum' );
        $this->icon        = 'dashicons-rest-api';

        parent::__construct();

        add_action( 'contactum_entry_submission',         [ $this, 'send_webhook' ], 20, 4 );
        add_action( 'wp_ajax_contactum_webhook_send_test', [ $this, 'send_test' ] );
    }

    public function getIntegrationDefaults() {
        return [
            'enabled'        => false,
            'url'            => '',
            'request_format' => '',
        ];
    }

    public function get_integration_settings() {
        return [
            [
                'name'        => 'enabled',
                'label'       => __( 'Enable Webhook', 'contactum' ),
                'type'        => 'checkbox-single',
                'placeholder' => __( 'Enable for this form', 'contactum' ),
            ],
            [
                'name'        => 'url',
                'label'       => __( 'Webhook URL', 'contactum' ),
                'type'        => 'text',
                'placeholder' => 'https://hooks.zapier.com/hooks/catch/…',
            ],
            [
                'name'    => 'request_format',
                'label'   => __( 'Request Format', 'contactum' ),
                'type'    => 'select',
                'options' => [
                    [ 'name' => 'JSON (recommended)', 'value' => 'json' ],
                    [ 'name' => 'Form Encoded',       'value' => 'form' ],
                ],
            ],
        ];
    }

    public function get_settings_section() {
        return [
            'id'        => 'webhook',
            'title'     => '',
            'name'      => __( 'Webhook / Zapier', 'contactum' ),
            'icon'      => 'dashicons-rest-api',
            'component' => 'WebhookSettings',
        ];
    }

    public function get_settings_fields() {
        return [
            'valid_message'   => __( 'Default webhook URL saved.', 'contactum' ),
            'invalid_message' => '',
            'hide_on_valid'   => false,
            'fields'          => [
                [
                    'name'        => 'default_url',
                    'label'       => __( 'Default Webhook URL', 'contactum' ),
                    'type'        => 'text',
                    'placeholder' => 'https://hooks.zapier.com/hooks/catch/…',
                    'help'        => __( 'Optional. Used as a fallback when no per-form URL is configured.', 'contactum' ),
                ],
                [
                    'name'    => 'request_format',
                    'label'   => __( 'Default Request Format', 'contactum' ),
                    'type'    => 'select',
                    'options' => [
                        [ 'name' => 'JSON (recommended)', 'value' => 'json' ],
                        [ 'name' => 'Form Encoded',       'value' => 'form' ],
                    ],
                ],
            ],
        ];
    }

    public function getGlobalSettings() {
        $saved = get_option( $this->option_key, [] );
        return wp_parse_args( $saved, [
            'default_url'    => '',
            'request_format' => 'json',
            'status'         => false,
        ] );
    }

    public function saveGlobalSettings( $data, $settings_key ) {
        $url    = isset( $data['default_url'] )    ? esc_url_raw( $data['default_url'] ) : '';
        $format = ( isset( $data['request_format'] ) && 'form' === $data['request_format'] ) ? 'form' : 'json';
        $status = ! empty( $url );

        $settings = [
            'default_url'    => $url,
            'request_format' => $format,
            'status'         => $status,
        ];

        update_option( $this->option_key, $settings, 'no' );
        wp_send_json_success( $settings );
    }

    // ── Form-submission handler ──────────────────────────────────────────────

    public function send_webhook( $entry_id, $form_id, $page_id, $form_settings ) {
        $integration = contactum_is_integration_active( $form_id, $this->id );

        if ( false === $integration ) {
            return;
        }

        $global = $this->getGlobalSettings();

        $url = ! empty( $integration->integration->url )
            ? esc_url_raw( $integration->integration->url )
            : $global['default_url'];

        if ( empty( $url ) ) {
            return;
        }

        $format = ! empty( $integration->integration->request_format )
            ? $integration->integration->request_format
            : $global['request_format'];

        $payload = $this->build_payload( $entry_id, $form_id );
        $this->dispatch( $url, $payload, $format );
    }

    // ── Test-send AJAX ───────────────────────────────────────────────────────

    public function send_test() {
        check_ajax_referer( 'contactum-form-builder-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'contactum' ) );
        }

        $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

        if ( empty( $url ) ) {
            wp_send_json_error( __( 'Please enter a URL before sending a test.', 'contactum' ) );
        }

        $payload = [
            'test'         => true,
            'source'       => 'Contactum',
            'message'      => __( 'Test payload from Contactum webhook integration.', 'contactum' ),
            'submitted_at' => current_time( 'mysql' ),
            'fields'       => [
                'name'    => 'Test User',
                'email'   => 'test@example.com',
                'message' => 'This is a test webhook delivery.',
            ],
        ];

        $response = wp_remote_post( $url, [
            'body'        => wp_json_encode( $payload ),
            'headers'     => [ 'Content-Type' => 'application/json' ],
            'timeout'     => 10,
            'blocking'    => true,
            'data_format' => 'body',
        ] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );

        if ( $code >= 200 && $code < 300 ) {
            /* translators: %d HTTP status code */
            wp_send_json_success( sprintf( __( 'Test delivered (HTTP %d).', 'contactum' ), $code ) );
        }

        /* translators: %d HTTP status code */
        wp_send_json_error( sprintf( __( 'Server returned HTTP %d. Check your URL and try again.', 'contactum' ), $code ) );
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function build_payload( $entry_id, $form_id ) {
        $form       = contactum()->forms->get( $form_id );
        $form_title = get_the_title( $form_id );
        $fields     = $form->getFields();

        // Build field-name → label map
        $label_map = [];
        foreach ( $fields as $field ) {
            if ( ! empty( $field['name'] ) ) {
                $label_map[ $field['name'] ] = ! empty( $field['label'] )
                    ? $field['label']
                    : $field['name'];
            }
        }

        // Read entry metadata
        $raw_meta   = get_metadata( 'contactum_entry', $entry_id );
        $field_data = [];

        foreach ( $raw_meta as $key => $values ) {
            $label               = isset( $label_map[ $key ] ) ? $label_map[ $key ] : $key;
            $field_data[ $label ] = count( $values ) === 1 ? $values[0] : $values;
        }

        return [
            'entry_id'     => $entry_id,
            'form_id'      => $form_id,
            'form_name'    => $form_title,
            'submitted_at' => current_time( 'mysql' ),
            'fields'       => $field_data,
        ];
    }

    private function dispatch( $url, $payload, $format = 'json' ) {
        if ( 'json' === $format ) {
            $args = [
                'body'        => wp_json_encode( $payload ),
                'headers'     => [ 'Content-Type' => 'application/json' ],
                'timeout'     => 15,
                'blocking'    => false,
                'data_format' => 'body',
            ];
        } else {
            // Flatten for form-encoded — fields become top-level keys
            $body = array_merge(
                [
                    'entry_id'     => $payload['entry_id'],
                    'form_id'      => $payload['form_id'],
                    'form_name'    => $payload['form_name'],
                    'submitted_at' => $payload['submitted_at'],
                ],
                (array) $payload['fields']
            );
            $args = [
                'body'     => $body,
                'timeout'  => 15,
                'blocking' => false,
            ];
        }

        wp_remote_post( $url, $args );
    }
}
