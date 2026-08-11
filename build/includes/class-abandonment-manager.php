<?php
namespace Contactum;

class AbandonmentManager {

    const OPTION_KEY  = 'contactum_abandonment_settings';
    const CRON_ACTION = 'contactum_send_abandonment_followup';

    public function __construct() {
        add_action( 'wp_ajax_nopriv_contactum_track_abandonment',      [ $this, 'track' ] );
        add_action( 'wp_ajax_contactum_track_abandonment',             [ $this, 'track' ] );
        add_action( 'wp_ajax_nopriv_contactum_abandonment_converted',  [ $this, 'mark_converted' ] );
        add_action( 'wp_ajax_contactum_abandonment_converted',         [ $this, 'mark_converted' ] );
        add_action( 'wp_ajax_contactum_get_abandonment_settings',      [ $this, 'get_settings' ] );
        add_action( 'wp_ajax_contactum_save_abandonment_settings',     [ $this, 'save_settings' ] );
        add_action( self::CRON_ACTION,                                 [ $this, 'send_followup' ], 10, 1 );
    }

    // ── Frontend AJAX: record an abandonment ─────────────────────────────────

    public function track() {
        check_ajax_referer( 'contactum_form_frontend' );

        $form_id      = absint( $_POST['form_id'] ?? 0 );
        $session_hash = sanitize_text_field( wp_unslash( $_POST['session_hash'] ?? '' ) );
        $filled_raw   = wp_unslash( $_POST['filled_fields'] ?? '[]' );
        $page_url     = esc_url_raw( wp_unslash( $_POST['page_url'] ?? '' ) );

        if ( ! $form_id || ! $session_hash ) {
            wp_send_json_error();
        }

        $filled_fields = json_decode( $filled_raw, true );
        if ( ! is_array( $filled_fields ) ) {
            $filled_fields = [];
        }

        // Only capture email when follow-up is enabled (privacy).
        $settings = $this->get_options();
        $email    = '';
        if ( ! empty( $settings['followup_enabled'] ) ) {
            $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        }

        global $wpdb;
        $table = $wpdb->prefix . 'contactum_form_abandonments';

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO `{$table}`
                    (form_id, session_hash, filled_fields, email, page_url, user_ip, user_device, converted, follow_up_sent, abandoned_at)
                 VALUES (%d, %s, %s, %s, %s, %s, %s, 0, 0, NOW())
                 ON DUPLICATE KEY UPDATE
                    filled_fields = VALUES(filled_fields),
                    email         = VALUES(email),
                    abandoned_at  = NOW()",
                $form_id,
                $session_hash,
                wp_json_encode( $filled_fields ),
                $email,
                $page_url,
                contactum_get_client_ip(),
                contactum_detect_device()
            )
        );

        // Schedule follow-up when we have an email address.
        if ( ! empty( $settings['followup_enabled'] ) && ! empty( $email ) ) {
            $delay   = max( 1, absint( $settings['followup_delay'] ?? 60 ) );
            $record_id = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM `{$table}` WHERE form_id = %d AND session_hash = %s AND follow_up_sent = 0",
                    $form_id, $session_hash
                )
            );
            if ( $record_id ) {
                wp_schedule_single_event(
                    time() + ( $delay * MINUTE_IN_SECONDS ),
                    self::CRON_ACTION,
                    [ $record_id ]
                );
            }
        }

        wp_send_json_success();
    }

    // ── Frontend AJAX: form was submitted successfully ────────────────────────

    public function mark_converted() {
        check_ajax_referer( 'contactum_form_frontend' );

        $form_id      = absint( $_POST['form_id'] ?? 0 );
        $session_hash = sanitize_text_field( wp_unslash( $_POST['session_hash'] ?? '' ) );

        if ( ! $form_id || ! $session_hash ) {
            wp_send_json_error();
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'contactum_form_abandonments',
            [ 'converted' => 1 ],
            [ 'form_id' => $form_id, 'session_hash' => $session_hash ],
            [ '%d' ],
            [ '%d', '%s' ]
        );

        wp_send_json_success();
    }

    // ── WP Cron: send follow-up email ─────────────────────────────────────────

    public function send_followup( $record_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'contactum_form_abandonments';

        $record = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$table}` WHERE id = %d AND converted = 0 AND follow_up_sent = 0",
                $record_id
            )
        );

        if ( ! $record || empty( $record->email ) ) {
            return;
        }

        $settings = $this->get_options();

        $subject = ! empty( $settings['followup_subject'] )
            ? sanitize_text_field( $settings['followup_subject'] )
            : __( 'Did you forget to finish?', 'contactum' );

        $body = ! empty( $settings['followup_body'] )
            ? wp_kses_post( $settings['followup_body'] )
            : __( "Hi,\n\nYou started filling out a form on our site but didn't finish. Click here to come back any time.\n\nThanks!", 'contactum' );

        $headers = [];
        if ( ! empty( $settings['followup_from_email'] ) ) {
            $from_name  = sanitize_text_field( $settings['followup_from_name'] ?? get_bloginfo( 'name' ) );
            $from_email = sanitize_email( $settings['followup_from_email'] );
            $headers[]  = "From: {$from_name} <{$from_email}>";
        }

        wp_mail( $record->email, $subject, $body, $headers );

        $wpdb->update(
            $table,
            [ 'follow_up_sent' => 1 ],
            [ 'id' => (int) $record_id ],
            [ '%d' ],
            [ '%d' ]
        );
    }

    // ── Admin AJAX: settings get/save ─────────────────────────────────────────

    public function get_settings() {
        check_ajax_referer( 'contactum-form-builder-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'contactum' ) );
        }

        wp_send_json_success( $this->get_options() );
    }

    public function save_settings() {
        check_ajax_referer( 'contactum-form-builder-nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized operation', 'contactum' ) );
        }

        $raw = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : [];

        if ( is_string( $raw ) ) {
            $raw = json_decode( $raw, true ) ?: [];
        }

        $settings = [
            'followup_enabled'    => ! empty( $raw['followup_enabled'] ),
            'followup_delay'      => max( 1, absint( $raw['followup_delay'] ?? 60 ) ),
            'followup_from_name'  => sanitize_text_field( $raw['followup_from_name']  ?? '' ),
            'followup_from_email' => sanitize_email( $raw['followup_from_email'] ?? '' ),
            'followup_subject'    => sanitize_text_field( $raw['followup_subject'] ?? '' ),
            'followup_body'       => wp_kses_post( $raw['followup_body'] ?? '' ),
        ];

        update_option( self::OPTION_KEY, $settings, 'no' );
        wp_send_json_success( $settings );
    }

    // ── Query helpers (used by analytics) ────────────────────────────────────

    /**
     * Returns total non-converted abandonments in a date range.
     */
    public static function count_abandonments( $form_id = 0, $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'contactum_form_abandonments';

        if ( $form_id && $start_date && $end_date ) {
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `{$table}` WHERE converted = 0 AND form_id = %d AND DATE(abandoned_at) BETWEEN %s AND %s",
                    $form_id, $start_date, $end_date
                )
            );
        }

        if ( $start_date && $end_date ) {
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `{$table}` WHERE converted = 0 AND DATE(abandoned_at) BETWEEN %s AND %s",
                    $start_date, $end_date
                )
            );
        }

        return 0;
    }

    /**
     * Returns abandonments per day as [ 'Y-m-d' => count ] map.
     */
    public static function abandonments_by_date( $form_id = 0, $start_date = '', $end_date = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'contactum_form_abandonments';

        if ( $form_id ) {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT DATE(abandoned_at) AS date, COUNT(*) AS total
                     FROM `{$table}`
                     WHERE converted = 0 AND form_id = %d AND DATE(abandoned_at) BETWEEN %s AND %s
                     GROUP BY DATE(abandoned_at)",
                    $form_id, $start_date, $end_date
                ),
                OBJECT_K
            );
        } else {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT DATE(abandoned_at) AS date, COUNT(*) AS total
                     FROM `{$table}`
                     WHERE converted = 0 AND DATE(abandoned_at) BETWEEN %s AND %s
                     GROUP BY DATE(abandoned_at)",
                    $start_date, $end_date
                ),
                OBJECT_K
            );
        }

        $map = [];
        foreach ( $rows as $d => $row ) {
            $map[ $d ] = (int) $row->total;
        }

        return $map;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function get_options() {
        return wp_parse_args( get_option( self::OPTION_KEY, [] ), [
            'followup_enabled'    => false,
            'followup_delay'      => 60,
            'followup_from_name'  => get_bloginfo( 'name' ),
            'followup_from_email' => get_option( 'admin_email' ),
            'followup_subject'    => __( 'Did you forget to finish?', 'contactum' ),
            'followup_body'       => __( "Hi,\n\nYou started filling out a form on our site but didn't finish. Click here to come back any time.\n\nThanks!", 'contactum' ),
        ] );
    }
}
