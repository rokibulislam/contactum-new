<?php
namespace Contactum;

class Logger {

    public static function table() {
        global $wpdb;

        return $wpdb->prefix . 'contactum_logs';
    }

    /**
     * Insert an activity log entry.
     *
     * @param  array $data {
     *     @type int    $form_id
     *     @type int    $entry_id
     *     @type string $component    e.g. 'form_submission', 'email_notification', 'mailchimp'
     *     @type string $status       'success' | 'failed' | 'info' | 'blocked'
     *     @type string $title
     *     @type string $description
     * }
     *
     * @return int|false Inserted row id, or false on failure.
     */
    public function log( $data ) {
        global $wpdb;

        if ( empty( $data ) ) {
            return false;
        }

        $this->maybe_create_table();

        $defaults = [
            'form_id'     => 0,
            'entry_id'    => 0,
            'component'   => '',
            'status'      => 'info',
            'title'       => '',
            'description' => '',
        ];

        $data = wp_parse_args( $data, $defaults );

        $data['description'] = wp_kses( (string) $data['description'], [
            'br'     => [],
            'b'      => [],
            'strong' => [],
            'i'      => [],
            'em'     => [],
            'code'   => [],
            'p'      => [],
            'a'      => [ 'href' => [], 'title' => [], 'target' => [], 'rel' => [] ],
        ] );

        $inserted = $wpdb->insert( self::table(), [
            'form_id'     => absint( $data['form_id'] ),
            'entry_id'    => absint( $data['entry_id'] ),
            'component'   => sanitize_text_field( $data['component'] ),
            'status'      => sanitize_text_field( $data['status'] ),
            'title'       => sanitize_text_field( $data['title'] ),
            'description' => $data['description'],
            'created_at'  => current_time( 'mysql' ),
        ] );

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Fetch a filtered, paginated list of logs.
     *
     * @param  array $args {
     *     @type int    $form_id
     *     @type string $status
     *     @type string $component
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
            'component' => '',
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

        if ( ! empty( $args['component'] ) ) {
            $where[]  = 'l.component = %s';
            $values[] = sanitize_text_field( $args['component'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $where[]  = '(l.title LIKE %s OR l.description LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $like;
            $values[] = $like;
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
        }

        return [
            'logs'  => $logs,
            'total' => $total,
        ];
    }

    /**
     * Distinct statuses/components/forms for the filter dropdowns.
     */
    public function get_filters() {
        global $wpdb;

        $this->maybe_create_table();

        $table = self::table();

        $statuses = $wpdb->get_col( "SELECT DISTINCT status FROM $table WHERE status != '' ORDER BY status ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- static query
        $components = $wpdb->get_col( "SELECT DISTINCT component FROM $table WHERE component != '' ORDER BY component ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- static query

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
            'statuses'   => array_values( $statuses ),
            'components' => array_values( $components ),
            'forms'      => $formatted_forms,
        ];
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

        $table       = self::table();
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders bound via prepare() below
        return (int) $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE id IN ($placeholders)", $ids ) );
    }

    /**
     * Lazily create the logs table for sites that installed before this
     * feature shipped and therefore never ran the activation migration.
     */
    private function maybe_create_table() {
        if ( get_option( 'contactum_db_logs_table_added' ) ) {
            return;
        }

        global $wpdb;

        $table = self::table();

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off existence check
            update_option( 'contactum_db_logs_table_added', 1, 'no' );
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
            `form_id` bigint(20) unsigned DEFAULT NULL,
            `entry_id` bigint(20) unsigned DEFAULT NULL,
            `component` varchar(50) DEFAULT NULL,
            `status` varchar(20) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `description` longtext,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `form_id` (`form_id`),
            KEY `entry_id` (`entry_id`),
            KEY `component` (`component`),
            KEY `status` (`status`)
        ) $collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'contactum_db_logs_table_added', 1, 'no' );
    }
}
