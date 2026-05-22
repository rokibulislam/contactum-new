<?php
namespace Contactum\Integrations;

class S3Integration extends Contactum_Integration {

    public $option_key = 'contactum_s3';

    public function __construct() {
        $this->id          = 's3';
        $this->title       = __( 'Amazon S3', 'contactum' );
        $this->description = __( 'Store form file and image uploads directly on Amazon S3 instead of (or alongside) your server.', 'contactum' );
        $this->icon        = 'dashicons-cloud-upload';

        parent::__construct();

        add_action( 'contactum_after_file_uploaded',          [ $this, 'maybe_upload' ], 10, 3 );
        add_action( 'wp_ajax_contactum_s3_test_connection',   [ $this, 'ajax_test_connection' ] );
    }

    // ── Per-form integration defaults ─────────────────────────────────────────

    public function getIntegrationDefaults() {
        return [
            'enabled'       => false,
            'folder_prefix' => '',
            'delete_local'  => false,
        ];
    }

    public function get_integration_settings() {
        return [
            [
                'name'        => 'enabled',
                'label'       => __( 'Enable S3 for this form', 'contactum' ),
                'type'        => 'checkbox-single',
                'placeholder' => __( 'Upload files from this form to S3', 'contactum' ),
            ],
            [
                'name'        => 'folder_prefix',
                'label'       => __( 'Folder Prefix', 'contactum' ),
                'type'        => 'text',
                'placeholder' => __( 'e.g. form-7/  (leave blank for global default)', 'contactum' ),
                'help'        => __( 'Appended after the global path prefix.', 'contactum' ),
            ],
            [
                'name'        => 'delete_local',
                'label'       => __( 'Delete local copy after upload', 'contactum' ),
                'type'        => 'checkbox-single',
                'placeholder' => __( 'Remove file from server after uploading to S3', 'contactum' ),
            ],
        ];
    }

    // ── Global settings section (settings sidebar) ────────────────────────────

    public function get_settings_section() {
        return [
            'id'        => 's3',
            'title'     => '',
            'name'      => __( 'Amazon S3', 'contactum' ),
            'icon'      => 'dashicons-cloud-upload',
            'component' => 'S3Settings',
        ];
    }

    public function get_settings_fields() {
        return [
            'valid_message'   => __( 'S3 connected. File uploads will be stored in your bucket.', 'contactum' ),
            'invalid_message' => __( 'Could not connect to S3. Check your credentials and bucket name.', 'contactum' ),
            'hide_on_valid'   => false,
            'fields'          => [
                [
                    'name'        => 'access_key',
                    'label'       => __( 'Access Key ID', 'contactum' ),
                    'type'        => 'text',
                    'placeholder' => 'AKIAIOSFODNN7EXAMPLE',
                ],
                [
                    'name'        => 'secret_key',
                    'label'       => __( 'Secret Access Key', 'contactum' ),
                    'type'        => 'password',
                    'placeholder' => __( 'Your AWS secret key', 'contactum' ),
                ],
                [
                    'name'        => 'bucket',
                    'label'       => __( 'Bucket Name', 'contactum' ),
                    'type'        => 'text',
                    'placeholder' => 'my-forms-bucket',
                ],
                [
                    'name'        => 'region',
                    'label'       => __( 'Region', 'contactum' ),
                    'type'        => 'text',
                    'placeholder' => 'us-east-1',
                    'help'        => __( 'e.g. us-east-1, eu-west-1, ap-southeast-1', 'contactum' ),
                ],
                [
                    'name'        => 'path_prefix',
                    'label'       => __( 'Path Prefix', 'contactum' ),
                    'type'        => 'text',
                    'placeholder' => 'contactum/uploads/',
                    'help'        => __( 'Leading folder applied to all uploads. Include trailing slash.', 'contactum' ),
                ],
                [
                    'name'    => 'acl',
                    'label'   => __( 'File Visibility', 'contactum' ),
                    'type'    => 'select',
                    'options' => [
                        [ 'value' => 'public-read', 'label' => __( 'Public — direct URL access', 'contactum' ) ],
                        [ 'value' => 'private',     'label' => __( 'Private — authenticated access only', 'contactum' ) ],
                    ],
                ],
                [
                    'name'        => 'delete_local',
                    'label'       => __( 'Delete local copy after upload', 'contactum' ),
                    'type'        => 'checkbox-single',
                    'placeholder' => __( 'Save server disk space by removing files after S3 upload', 'contactum' ),
                ],
            ],
        ];
    }

    // ── Global settings CRUD ──────────────────────────────────────────────────

    public function getGlobalSettings() {
        $saved = get_option( $this->option_key, [] );
        return wp_parse_args( $saved, [
            'access_key'   => '',
            'secret_key'   => '',
            'bucket'       => '',
            'region'       => 'us-east-1',
            'path_prefix'  => 'contactum/',
            'acl'          => 'public-read',
            'delete_local' => false,
            'status'       => false,
        ] );
    }

    public function saveGlobalSettings( $data, $settings_key ) {
        $valid_acl = [ 'public-read', 'private' ];

        $settings = [
            'access_key'   => sanitize_text_field( $data['access_key']  ?? '' ),
            'secret_key'   => sanitize_text_field( $data['secret_key']  ?? '' ),
            'bucket'       => sanitize_text_field( $data['bucket']       ?? '' ),
            'region'       => sanitize_text_field( $data['region']       ?? 'us-east-1' ),
            'path_prefix'  => sanitize_text_field( $data['path_prefix']  ?? 'contactum/' ),
            'acl'          => in_array( $data['acl'] ?? '', $valid_acl, true ) ? $data['acl'] : 'public-read',
            'delete_local' => ! empty( $data['delete_local'] ),
            'status'       => false,
        ];

        if ( $settings['access_key'] && $settings['secret_key'] && $settings['bucket'] ) {
            $client             = $this->make_client( $settings );
            $test               = $client->test_connection();
            $settings['status'] = $test['success'];
        }

        update_option( $this->option_key, $settings, 'no' );
        wp_send_json_success( $this->safe_settings( $settings ) );
    }

    // ── AJAX: on-demand connection test ──────────────────────────────────────

    public function ajax_test_connection() {
        check_ajax_referer( 'contactum-form-builder-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'contactum' ) );
        }

        $settings = $this->getGlobalSettings();

        if ( ! $settings['access_key'] || ! $settings['secret_key'] || ! $settings['bucket'] ) {
            wp_send_json_error( __( 'Please save your AWS credentials and bucket name first.', 'contactum' ) );
        }

        $result = $this->make_client( $settings )->test_connection();

        if ( $result['success'] ) {
            // Persist the verified status.
            $settings['status'] = true;
            update_option( $this->option_key, $settings, 'no' );
            wp_send_json_success( $result['message'] );
        } else {
            wp_send_json_error( $result['message'] );
        }
    }

    // ── Upload hook ───────────────────────────────────────────────────────────

    /**
     * Hooked to `contactum_after_file_uploaded`.
     * Fires after a file is saved as a WP attachment.
     *
     * @param int    $attach_id   WordPress attachment ID.
     * @param string $local_path  Absolute local file path.
     * @param int    $form_id     Form the upload belongs to (0 if unknown).
     */
    public function maybe_upload( $attach_id, $local_path, $form_id ) {
        $global = $this->getGlobalSettings();

        if ( ! $global['status'] || ! $global['access_key'] || ! $global['secret_key'] || ! $global['bucket'] ) {
            return;
        }

        // Check per-form integration toggle.
        $integration = false;
        if ( $form_id ) {
            $integration = contactum_is_integration_active( $form_id, $this->id );
            if ( false === $integration ) {
                return;
            }
        }

        // Build S3 key.
        $file_name    = wp_basename( $local_path );
        $global_prefix = rtrim( $global['path_prefix'] ?? 'contactum/', '/' ) . '/';
        $form_prefix   = '';

        if ( $integration && ! empty( $integration->integration->folder_prefix ) ) {
            $form_prefix = rtrim( $integration->integration->folder_prefix, '/' ) . '/';
        }

        $key          = $global_prefix . $form_prefix . $file_name;
        $content_type = get_post_mime_type( $attach_id ) ?: 'application/octet-stream';
        $acl          = $global['acl'] ?? 'public-read';

        $result = $this->make_client( $global )->put_object( $key, $local_path, $content_type, $acl );

        if ( is_wp_error( $result ) ) {
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( '[Contactum S3] Upload failed: ' . $result->get_error_message() );
            }
            return;
        }

        // Store S3 URL and key on the attachment post.
        update_post_meta( $attach_id, '_contactum_s3_url', esc_url_raw( $result['url'] ) );
        update_post_meta( $attach_id, '_contactum_s3_key', sanitize_text_field( $result['key'] ) );

        // Determine whether to delete the local copy.
        $delete = (bool) ( $global['delete_local'] ?? false );
        if ( $integration && isset( $integration->integration->delete_local ) ) {
            $delete = (bool) $integration->integration->delete_local;
        }

        if ( $delete && file_exists( $local_path ) ) {
            @unlink( $local_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function make_client( $settings ) {
        return new S3Client(
            $settings['access_key'],
            $settings['secret_key'],
            $settings['bucket'],
            $settings['region'] ?: 'us-east-1'
        );
    }

    /**
     * Strip the secret key before sending to the browser.
     */
    private function safe_settings( $settings ) {
        if ( ! empty( $settings['secret_key'] ) ) {
            $settings['secret_key'] = str_repeat( '•', 20 );
        }
        return $settings;
    }

    /**
     * Override: strip secret before exposing via get_js_settings().
     */
    public function getGlobalSettings_safe() {
        return $this->safe_settings( $this->getGlobalSettings() );
    }
}
