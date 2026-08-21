<?php
namespace Contactum;

/**
 * Records a change-summary entry every time a form is saved, so the builder
 * can show a "History" timeline of past states and let a field be restored
 * from one. Piggybacks on WordPress post meta (the form is itself a post)
 * instead of a new table — every revision shares the same meta_key, one row
 * per save, ordered by meta_id.
 *
 * Fields in this plugin are individually stored child posts (see Form::
 * getFields()), not one JSON blob, so a captured entry's `old_data` is the
 * full field array as it existed immediately before the save that produced
 * this entry — restoring means handing that array back to the builder,
 * which is exactly what it already accepts for a freshly-loaded form.
 */
class FormHistory {

    const META_KEY    = 'contactum_history_entry';
    const MAX_ENTRIES  = 10;

    /**
     * @param int   $form_id
     * @param array $old_fields Full field array as it was immediately before this save.
     * @param array $new_fields Full field array as it was just written to the DB.
     */
    public function record( $form_id, $old_fields, $new_fields ) {
        $changes = $this->diff_fields( (array) $old_fields, (array) $new_fields );

        if ( empty( $changes ) ) {
            return;
        }

        $entry = [
            'change_title' => $this->summarize( $changes ),
            'timestamp'    => current_time( 'mysql' ),
            'old_data'     => array_values( (array) $old_fields ),
            'changes'      => $changes,
        ];

        // wp_slash() is required here: add_post_meta() unconditionally
        // wp_unslash()es its value before storing (it assumes the value may
        // carry magic-quotes-style slashes from $_POST), which otherwise
        // strips the backslash-escapes out of this JSON string — e.g. the
        // \" around a quoted field label — corrupting it before it ever
        // reaches the DB.
        add_post_meta( $form_id, self::META_KEY, wp_slash( wp_json_encode( $entry ) ), false );

        $this->prune( $form_id );
    }

    public function get( $form_id ) {
        global $wpdb;

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC",
            $form_id,
            self::META_KEY
        ) );

        $entries = [];

        foreach ( $rows as $row ) {
            $entry = json_decode( $row->meta_value, true );

            if ( ! is_array( $entry ) || ! isset( $entry['timestamp'] ) ) {
                continue;
            }

            $entry['id']       = (int) $row->meta_id;
            $entry['time_ago'] = human_time_diff( strtotime( $entry['timestamp'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'contactum' );

            $entries[] = $entry;
        }

        return $entries;
    }

    public function clear( $form_id ) {
        delete_post_meta( $form_id, self::META_KEY );
    }

    private function prune( $form_id ) {
        global $wpdb;

        $ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s ORDER BY meta_id DESC",
            $form_id,
            self::META_KEY
        ) );

        $stale = array_slice( $ids, self::MAX_ENTRIES );

        foreach ( $stale as $meta_id ) {
            delete_metadata_by_mid( 'post', $meta_id );
        }
    }

    /**
     * Field-by-field diff matched by the `id` each field carries — a real
     * WP post ID for a field that already existed, a client-generated uuid
     * for one that was still new at save time.
     */
    private function diff_fields( $old_fields, $new_fields ) {
        $changes = [];

        $old_by_id = $this->index_by_id( $old_fields );
        $new_by_id = $this->index_by_id( $new_fields );

        $has_added_or_removed = false;

        foreach ( $new_by_id as $id => $field ) {
            if ( ! isset( $old_by_id[ $id ] ) ) {
                $changes[]             = [ 'type' => 'added', 'label' => $this->field_label( $field ) ];
                $has_added_or_removed = true;
            }
        }

        foreach ( $old_by_id as $id => $field ) {
            if ( ! isset( $new_by_id[ $id ] ) ) {
                $changes[]             = [ 'type' => 'removed', 'label' => $this->field_label( $field ) ];
                $has_added_or_removed = true;
            }
        }

        foreach ( $new_by_id as $id => $field ) {
            if ( ! isset( $old_by_id[ $id ] ) ) {
                continue;
            }

            if ( $this->normalize( $old_by_id[ $id ] ) !== $this->normalize( $field ) ) {
                $changes[] = [ 'type' => 'modified', 'label' => $this->field_label( $field ) ];
            }
        }

        // Only worth flagging as a distinct "reordered" change when nothing
        // was added/removed — otherwise the order difference is just a side
        // effect of the add/remove and would be a redundant, confusing line.
        if ( ! $has_added_or_removed ) {
            $shared_old_order = array_values( array_intersect( array_keys( $old_by_id ), array_keys( $new_by_id ) ) );
            $shared_new_order = array_values( array_intersect( array_keys( $new_by_id ), array_keys( $old_by_id ) ) );

            if ( $shared_old_order !== $shared_new_order ) {
                $changes[] = [ 'type' => 'reordered', 'label' => __( 'Fields', 'contactum' ) ];
            }
        }

        return $changes;
    }

    private function index_by_id( $fields ) {
        $indexed = [];

        foreach ( $fields as $field ) {
            if ( is_array( $field ) && isset( $field['id'] ) ) {
                $indexed[ (string) $field['id'] ] = $field;
            }
        }

        return $indexed;
    }

    private function field_label( $field ) {
        return $field['label'] ?? $field['name'] ?? $field['template'] ?? __( 'Field', 'contactum' );
    }

    private function normalize( $field ) {
        unset( $field['id'], $field['is_new'] );
        ksort( $field );

        return wp_json_encode( $field );
    }

    private function summarize( $changes ) {
        if ( 1 === count( $changes ) ) {
            $change = $changes[0];

            switch ( $change['type'] ) {
                case 'added':
                    return sprintf( __( 'Added "%s" field', 'contactum' ), $change['label'] );
                case 'removed':
                    return sprintf( __( 'Removed "%s" field', 'contactum' ), $change['label'] );
                case 'modified':
                    return sprintf( __( 'Modified "%s" field', 'contactum' ), $change['label'] );
                case 'reordered':
                    return __( 'Reordered fields', 'contactum' );
            }
        }

        return sprintf( __( '%d changes', 'contactum' ), count( $changes ) );
    }
}
