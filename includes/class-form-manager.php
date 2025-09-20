<?php
namespace Contactum;

use Contactum\Form;
use WP_Query;

class FormManager {


    public function __construct() {

        add_action('wp_ajax_contactum_get_forms', [ $this, 'contactum_get_forms' ] );
        add_action('wp_ajax_delete_contactum_form', [ $this, 'delete_contactum_form' ] );
        add_action('wp_ajax_duplicate_contactum_form', [ $this, 'duplicate_contactum_form' ] );

        add_action('wp_ajax_contactum_get_form_notifications', [ $this, 'contactum_get_form_notifications_ajax' ] );
        add_action('wp_ajax_contactum_save_form_notifications', [ $this, 'contactum_save_form_notifications' ] );


        add_action('wp_ajax_contactum_get_form_settings', [ $this,'contactum_get_form_settings' ]);
        add_action('wp_ajax_contactum_save_form_settings', [ $this, 'contactum_save_form_settings' ]);

        add_action('wp_ajax_contactum_get_entries', [ $this, 'contactum_get_entries_ajax' ] );
    }

    public function contactum_get_form_settings() {
        check_ajax_referer('contactum-form-builder-nonce');

        $form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : '';
        $form = $this->get( $form_id );
        $data = $form->getSettings();

        wp_send_json_success( $data );
    }


    public function contactum_save_form_settings() {
        check_ajax_referer('contactum-form-builder-nonce');

        $response = [];

        $form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : '';

        $form = $this->get( $form_id );

        if ( isset( $_POST['settings'] ) ) {

            $form_settings = json_encode( $_POST['settings'] );
            $form_settings = json_decode( $form_settings, true );

            update_post_meta( $form_id, 'form_settings', $form_settings );

            $response ['message'] = __( ' Saved Settings', 'contactum');

            wp_send_json_success( $response );
        }
    }


    public function contactum_get_form_notifications_ajax() {
        check_ajax_referer('contactum-form-builder-nonce');

        $form_id = isset( $_GET['form_id'] ) ? sanitize_text_field( $_GET['form_id'] ) : '';
        $form = $this->get( $form_id );
        $data = $form->getNotifications();

        wp_send_json_success( $data );
    }

    public function contactum_save_form_notifications() {
        check_ajax_referer('contactum-form-builder-nonce');

        $response = [];

        $form_id = isset( $_POST['form_id'] ) ? sanitize_text_field( $_POST['form_id'] ) : '';

        $form = $this->get( $form_id );

        if ( isset( $_POST['notifications'] ) ) {

            $form_notifications = json_encode( $_POST['notifications'] );
            $form_notifications = json_decode( $form_notifications, true );

            update_post_meta( $form_id, 'notifications', $form_notifications );

            $notifications = $form->getNotifications();

            wp_send_json_success( $notifications );
        }
    }

    public function contactum_get_forms() {
       check_ajax_referer('contactum-form-builder-nonce');

        $start_date = '';
        $end_date   = '';

        $search    = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        // Get start date if it exists
        if ( isset( $_POST['startdate'] ) && ! empty( $_POST['startdate'] ) ) {
            $start_date_raw = sanitize_text_field( $_POST['startdate'] );
            $start_date = date( 'Y-m-d', strtotime( $start_date_raw ) );
        }

        // Get end date if it exists
        if ( isset( $_POST['enddate'] ) && ! empty( $_POST['enddate'] ) ) {
            $end_date_raw = sanitize_text_field( $_POST['enddate'] );
            $end_date = date( 'Y-m-d', strtotime( $end_date_raw ) );
        }

        // Build date_query only if at least one exists
        $date_query = array();

        if ( $start_date || $end_date ) {
            $date_query[] = array(
                'after'     => $start_date ? $start_date : null,
                'before'    => $end_date ? $end_date : null,
                'inclusive' => true,
            );
        }


        if ( ! empty( $date_query ) ) {
            $args['date_query'] = $date_query;
        }

        if (!empty($search)) {
            $args['s'] = $search;
        }

       $forms = $this->getForms( $args );

       wp_send_json_success( $forms );
    }

    public function duplicate_contactum_form() {
        check_ajax_referer('contactum-form-builder-nonce');

        if (!current_user_can('delete_posts')) {
            wp_send_json_error('You do not have permission to delete posts.');
        }

        $post_id = intval($_POST['post_id']);

        if (get_post($post_id)) {
           $form_id = $this->duplicate( $post_id );
            wp_send_json_success([
                'id' => $form_id,
                'message' => 'Post deleted successfully.'
             ]);
        }  else {
              wp_send_json_error('Post not found.');
          }
    }


    public function delete_contactum_form() {
        check_ajax_referer('contactum-form-builder-nonce');

        if (!current_user_can('delete_posts')) {
            wp_send_json_error('You do not have permission to delete posts.');
        }

        $post_id = intval($_POST['post_id']);

        if (get_post($post_id)) {
            $this->delete( $post_id, true );
            wp_send_json_success('Post deleted successfully.');
        } else {
            wp_send_json_error('Post not found.');
        }
    }

    public function all() {
        return $this->getForms( [ 'posts_per_page' => -1 ] );
    }

    public function getForms( $args = [], $filters = false ) {
        global $wpdb;

        $entries_table = $wpdb->prefix . 'contactum_entries';

        $forms_array = [
            'forms' => [],
            'meta'  => [
                'total' => 0,
                'pages' => 0,
            ],
        ];
        $defaults  = [
            'post_type'   => 'contactum_forms',
            'post_status' => [ 'publish', 'draft', 'pending' ],
        ];

        $args  = wp_parse_args( $args, $defaults );

        $query = new WP_Query( $args );
        $forms = $query->get_posts();

        if ( $forms ) {
            foreach ( $forms as $form ) {

                $form_obj = new Form( $form );

                $forms_array['forms'][$form->ID] = $form_obj;

                $forms_array['forms'][$form->ID]->entries = $form_obj->num_form_entries();
            }
        }

        $forms_array['meta']['total'] = (int) $query->found_posts;
        $forms_array['meta']['pages'] = (int) $query->max_num_pages;

        wp_reset_postdata();

        return $forms_array;
    }

    public function get( $form ) {
        return new Form( $form );
    }

    public function create( $form_name, $fields = [] ) {
        $author = get_current_user_id();

        $form_data = [
            'post_title'  => $form_name,
            'post_type'   => 'contactum_forms',
            'post_status' => 'publish',
            'post_author' => $author
        ];

        $form_id = wp_insert_post( $form_data );

        if( is_wp_error( $form_id ) ) {
            return $form_id;
        }

        if ( $fields ) {

            foreach ( $fields as $menu_order => $field ) {
                wp_insert_post( [
                    'post_type'    => 'contactum_input',
                    'post_status'  => 'publish',
                    'post_content' => maybe_serialize( $field ),
                    'post_parent'  => $form_id,
                    'menu_order'   => $menu_order,
                ] );
            }

        }

        return $form_id;
    }

    public function delete( $form_id, $force = true  ) {
        global $wpdb;
        wp_delete_post( $form_id, $force );

        $wpdb->delete( $wpdb->posts,
            [
                'post_parent' => $form_id,
                'post_type'   => 'contactum_input',
            ]
        );
    }


    public function save( $data ) {
        $saved_fields  = [];
        $new_fields = [];
        wp_update_post( [ 'ID' => $data['form_id'], 'post_status' => 'publish', 'post_title' => $data['post_title'] ] );

        $existing_fields = get_children( [
            'post_parent' => $data['form_id'],
            'post_status' => 'publish',
            'post_type'   => 'contactum_input',
            'numberposts' => '-1',
            'orderby'     => 'menu_order',
            'order'       => 'ASC',
            'fields'      => 'ids',
        ] );

        if ( !empty( $data['form_fields'] ) ) {
            foreach ( $data['form_fields'] as $order => $field ) {
                if ( !empty( $field['is_new'] ) ) {
                    unset( $field['is_new'] );
                    unset( $field['id'] );

                    $field_id = 0;
                } else {
                    $field_id = $field['id'];
                }

                $field_id = contactum_insert_form_field($data['form_id'], $field, $field_id ,$order );

                $new_fields[] = $field_id;

                $field['id'] = $field_id;

                $saved_fields[] = $field;
            }
        }

        $inputs_to_delete = array_diff( $existing_fields, $new_fields );


        if ( !empty( $inputs_to_delete ) ) {
            foreach ( $inputs_to_delete as $delete_id ) {
                wp_delete_post( $delete_id, true );
            }
        }

        update_post_meta( $data['form_id'], 'notifications', $data['notifications'] );
        update_post_meta( $data['form_id'], 'form_settings', $data['form_settings'] );
        update_post_meta( $data['form_id'], 'integrations', $data['integrations'] );
        update_post_meta( $data['form_id'], 'contactum_version', CONTACTUM_VERSION );

        return $saved_fields;
    }

    public function duplicate( $id ) {
        $form = $this->get( $id);

        if ( empty( $form ) ) {
            return;
        }

        $form_id = $this->create( $form->name, $form->getFields() );

        error_log(print_r($id, true) );

        error_log(print_r($form->name, true) );

        error_log(print_r($form->getFields(), true) );

        $data = [
            'form_id'       => absint( $form_id ),
            'post_title'    => sanitize_text_field( $form->name ) . ' (#' . $form_id . ')',
            'form_fields'   => $this->get( $form_id )->getFields(),
            'form_settings' => $form->getSettings(),
            'notifications' => $form->getNotifications(),
            'integrations'  => $form->get_integrations(),
        ];

        $form_fields = $this->save( $data );

        return $form_id;
    }
}
