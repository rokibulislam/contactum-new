<?php
namespace Contactum;

/**
 * Tracks outbound API / integration calls (webhooks, Mailchimp, etc.) so a
 * failed delivery can be inspected and retried from the admin — the
 * "API Log" counterpart to the general-purpose Contactum\Logger activity log.
 */
class ApiLogger {

    public static function table() {
        global $wpdb;

        return $wpdb->prefix . 'contactum_scheduled_actions';
    }

    /**
     * Insert (or, when $id is passed, update) an API log entry.
     *
     * @param  array $data {
     *     @type string $action     Integration id, e.g. 'webhook', 'mailchimp'
     *     @type int    $form_id
     *     @type int    $entry_id
     *     @type string $status     'success' | 'failed' | 'pending' | 'manual_retry'
     *     @type string $note
     *     @type array  $data       Arbitrary payload needed to retry this action
     * }
     *
     * @return int|false Row id, or false on failure.
     */
    public function log( $data ) {
        global $wpdb;

        if ( empty( $data ) ) {
            return false;
        }

        $this->maybe_create_table();

        $defaults = [
            'action'   => '',
            'form_id'  => 0,
            'entry_id' => 0,
            'status'   => 'pending',
            'note'     => '',
            'data'     => [],
        ];

        $data = wp_parse_args( $data, $defaults );

        $inserted = $wpdb->insert( self::table(), [
            'action'      => sanitize_text_field( $data['action'] ),
            'form_id'     => absint( $data['form_id'] ),
            'entry_id'    => absint( $data['entry_id'] ),
            'status'      => sanitize_text_field( $data['status'] ),
            'note'        => wp_kses_post( (string) $data['note'] ),
            'retry_count' => 0,
            'data'        => wp_json_encode( $data['data'] ),
            'created_at'  => current_time( 'mysql' ),
            'updated_at'  => current_time( 'mysql' ),
        ] );

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Update the status/note of an existing API log row (used by retry handlers).
     */
    public function update_status( $id, $status, $note = '' ) {
        global $wpdb;

        return $wpdb->update(
            self::table(),
            [
                'status'     => sanitize_text_field( $status ),
                'note'       => wp_kses_post( (string) $note ),
                'updated_at' => current_time( 'mysql' ),
            ],
            [ 'id' => absint( $id ) ]
        );
    }

    /**
     * Fetch a filtered, paginated list of API logs.
     *
     * @param  array $args {
     *     @type int    $form_id
     *     @type string $status
     *     @type string $action
     *     @type string $search
     *     @type string $date_from  Y-m-d
     *     @type string $date_to    Y-m-d
     *     @type int    $page
     *     @type int    $per_page
     * }
     *
     * @return array{logs: array, total: int}
     */
    public function get_logs( $args = [] ) {
        global $wpdb;

        $this->maybe_create_table();

        $args = wp_parse_args( $args, [
            'form_id'   => 0,
            'status'    => '',
            'action'    => '',
            'search'    => '',
            'date_from' => '',
            'date_to'   => '',
            'page'      => 1,
            'per_page'  => 20,
        ] );

        $table = self::table();

        $where  = [ '1=1' ];
        $values = [];

        if ( ! empty( $args['form_id'] ) ) {
            $where[]  = 'l.form_id = %d';
            $values[] = absint( $args['form_id'] );
        }

        if ( ! empty( $args['status'] ) ) {
            $where[]  = 'l.status = %s';
            $values[] = sanitize_text_field( $args['status'] );
        }

        if ( ! empty( $args['action'] ) ) {
            $where[]  = 'l.action = %s';
            $values[] = sanitize_text_field( $args['action'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $where[]  = '(l.note LIKE %s)';
            $values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }

        if ( ! empty( $args['date_from'] ) ) {
            $where[]  = 'l.created_at >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }

        if ( ! empty( $args['date_to'] ) ) {
            $where[]  = 'l.created_at <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }

        $where_sql = implode( ' AND ', $where );

        $per_page = max( 1, absint( $args['per_page'] ) );
        $page     = max( 1, absint( $args['page'] ) );
        $offset   = ( $page - 1 ) * $per_page;

        $select = "SELECT l.*, p.post_title as form_title
            FROM $table l
            LEFT JOIN {$wpdb->posts} p ON p.ID = l.form_id
            WHERE $where_sql
            ORDER BY l.id DESC
            LIMIT %d OFFSET %d";

        $count_sql = "SELECT COUNT(*) FROM $table l WHERE $where_sql";

        if ( $values ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $where_sql built from static fragments above, values bound via prepare()
            $logs  = $wpdb->get_results( $wpdb->prepare( $select, array_merge( $values, [ $per_page, $offset ] ) ) );
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $where_sql built from static fragments above, values bound via prepare()
            $total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $values ) );
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $where_sql is the static '1=1' fragment; LIMIT/OFFSET still bound via prepare()
            $logs  = $wpdb->get_results( $wpdb->prepare( $select, [ $per_page, $offset ] ) );
            $total = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- static query, nothing to prepare
        }

        foreach ( $logs as $log ) {
            $log->form_title = $log->form_title ? $log->form_title : __( '(unknown form)', 'contactum' );
            $log->retryable  = (bool) has_action( 'contactum_api_log_retry_' . $log->action );
        }

        return [
            'logs'  => $logs,
            'total' => $total,
        ];
    }

    /**
     * Distinct statuses/actions/forms for the filter dropdowns.
     */
    public function get_filters() {
        global $wpdb;

        $this->maybe_create_table();

        $table = self::table();

        $statuses = $wpdb->get_col( "SELECT DISTINCT status FROM $table WHERE status != '' ORDER BY status ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- static query
        $actions  = $wpdb->get_col( "SELECT DISTINCT action FROM $table WHERE action != '' ORDER BY action ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- static query

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- static query
        $forms = $wpdb->get_results(
            "SELECT DISTINCT l.form_id, p.post_title
            FROM $table l
            LEFT JOIN {$wpdb->posts} p ON p.ID = l.form_id
            WHERE l.form_id IS NOT NULL AND l.form_id != 0
            ORDER BY l.form_id DESC"
        );

        $formatted_forms = [];

        foreach ( $forms as $form ) {
            $formatted_forms[] = [
                'form_id' => (int) $form->form_id,
                'title'   => $form->post_title ? $form->post_title : __( '(unknown form)', 'contactum' ),
            ];
        }

        return [
            'statuses'  => array_values( $statuses ),
            'actions'   => array_values( $actions ),
            'forms'     => $formatted_forms,
        ];
    }

    /**
     * Re-run a failed (or any) API action using its stored retry payload.
     *
     * Fires `contactum_api_log_retry_{action}` so the owning integration
     * (webhook, mailchimp, or a contact-pro module) can replay the call and
     * report the outcome back via update_status().
     *
     * @param  int $id
     * @return \WP_Error|object Updated log row, or WP_Error if not found / not retryable.
     */
    public function retry( $id ) {
        global $wpdb;

        $id  = absint( $id );
        $log = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a static constant, id is bound via prepare()

        if ( ! $log ) {
            return new \WP_Error( 'not-found', __( 'API log entry does not exist', 'contactum' ) );
        }

        if ( ! has_action( 'contactum_api_log_retry_' . $log->action ) ) {
            return new \WP_Error( 'not-retryable', __( 'This action cannot be retried', 'contactum' ) );
        }

        $wpdb->update(
            self::table(),
            [
                'status'      => 'manual_retry',
                'retry_count' => (int) $log->retry_count + 1,
                'updated_at'  => current_time( 'mysql' ),
            ],
            [ 'id' => $id ]
        );

        $payload = json_decode( (string) $log->data, true );

        do_action( 'contactum_api_log_retry_' . $log->action, is_array( $payload ) ? $payload : [], $id, $log );

        return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is a static constant, id is bound via prepare()
    }

    /**
     * Delete log rows by id.
     *
     * @param  array $ids
     * @return int Number of rows deleted.
     */
    public function delete( $ids = [] ) {
        global $wpdb;

        $ids = array_filter( array_map( 'absint', (array) $ids ) );

        if ( ! $ids ) {
            return 0;
        }

        $table        = self::table();
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders bound via prepare() below
        return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($placeholders)", $ids ) );
    }

    /**
     * Lazily create the table for sites that installed before this feature
     * shipped and therefore never ran the activation migration.
     */
    private function maybe_create_table() {
        if ( get_option( 'contactum_db_scheduled_actions_table_added' ) ) {
            return;
        }

        global $wpdb;

        $table = self::table();

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off existence check
            update_option( 'contactum_db_scheduled_actions_table_added', 1, 'no' );
            return;
        }

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty( $wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty( $wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `action` varchar(50) DEFAULT NULL,
            `form_id` bigint(20) unsigned DEFAULT NULL,
            `entry_id` bigint(20) unsigned DEFAULT NULL,
            `status` varchar(20) DEFAULT NULL,
            `note` longtext,
            `retry_count` int(11) unsigned NOT NULL DEFAULT 0,
            `data` longtext,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `form_id` (`form_id`),
            KEY `entry_id` (`entry_id`),
            KEY `action` (`action`),
            KEY `status` (`status`)
        ) $collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'contactum_db_scheduled_actions_table_added', 1, 'no' );
    }
}
