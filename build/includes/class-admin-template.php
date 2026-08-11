<?php
namespace Contactum;

class Admin_Template {

	public function __construct() {
        add_filter( 'admin_action_create_template', [ $this, 'create_template' ] );

        add_filter( 'admin_post_contactum_export_forms', [ $this, 'export_forms' ] );
        add_filter( 'admin_post_contactum_export_form_entries', [ $this, 'export_form_entries' ] );

        add_filter( 'wp_ajax_contactum_export_forms', [ $this, 'export_forms' ] );
        add_action('wp_ajax_nopriv_contactum-export-forms',  [ $this, 'export_forms' ] );
	}


	public function create_template() {
        $get_data = wp_unslash( $_GET );
        $template = isset( $get_data['template'] ) ? sanitize_text_field( $get_data['template'] ) : '';

        if( empty( $template ) ){
            return ;
        }

        $template_obj = contactum()->templates->get_template( $template );

        if( $template_obj == false ) {
            return ;
        }

        $form_id = contactum()->templates->create( $template );
        wp_redirect( admin_url( 'admin.php?page=contactum&route=builder#/forms/'. $form_id ) );
        // wp_redirect( admin_url( 'admin.php?page=contactum&action=edit&id='. $form_id . '/forms/' . $form_id ) );
        exit;
	}

    public function export_forms() {

        // check_admin_referer( 'contactum-export-forms' );
        $post_data = wp_unslash( $_REQUEST );
        // if ( !isset( $post_data['contactum-export-form'] ) ) {
        //     return;
        // }
        $export_type = isset( $post_data['export_type'] ) ? sanitize_text_field( wp_unslash( $post_data['export_type'] ) ) : 'all';
        $selected    = isset( $post_data['selected_forms'] ) ? array_map( 'absint', $post_data['form_id'] ) : [];
        // $selected    = isset( $post_data['selected_forms'] ) ? $post_data['selected_forms'] : [];
        
        switch ( $export_type ) {
            case 'all':
                Tools::export_to_json();

                return;

            case 'selected':
               
                Tools::export_to_json( $selected );

                return;
        }

        exit;
    }


    public function export_form_entries() {
        $form_id = isset( $_REQUEST['form_id'] ) ? absint( $_REQUEST['form_id'] ) : 0;

        if ( !$form_id ) {
            return;
        }

        $entry_array   = [];
        $columns       = contactum_get_entry_columns( $form_id, false );
        $total_entries = contactum_count_form_entries( $form_id );
        $entries       = contactum_get_form_entries( $form_id, [
            'number'       => $total_entries,
            'offset'       => 0,
        ] );
        $extra_columns =  [
            'ip_address' => __( 'IP Address', 'contactum' ),
            'created_at' => __( 'Date', 'contactum' ),
        ];

        $columns = array_merge( [ 'id' => 'Entry ID' ], $columns, $extra_columns );

        foreach ( $entries as $entry ) {
            $temp = [];

            foreach ( $columns as $column_id => $label ) {
                switch ( $column_id ) {
                    case 'id':
                        $temp[ $column_id ] = $entry->id;
                        break;

                    case 'ip_address':
                        $temp[ $column_id ] = $entry->ip_address;
                        break;

                    case 'created_at':
                        $temp[ $column_id ] = $entry->created_at;
                        break;

                    default:

                        $value              = EntryMeta::get_entry_meta( $entry->id, $column_id, true );
                        $value              = contactum_get_pain_text( $value );
                        $temp[ $column_id ] = str_replace( ' | ', ' ', $value );

                        break;
                }
            }

            $entry_array[] = $temp;
        }

        error_reporting( 0 );

        if ( ob_get_contents() ) {
            ob_clean();
        }

        $blogname  = sanitize_title( strtolower( str_replace( ' ', '-', get_option( 'blogname' ) ) ) );
        $file_name = $blogname . '-contactum-entries-' . time() . '.csv';

        // force download
        header( 'Content-Type: application/force-download' );
        header( 'Content-Type: application/octet-stream' );
        header( 'Content-Type: application/download' );

        // disposition / encoding on response body
        header( "Content-Disposition: attachment;filename={$file_name}" );
        header( 'Content-Transfer-Encoding: binary' );

        $handle = fopen( 'php://output', 'w' );

        //handle UTF-8 chars conversion for CSV
        fprintf( $handle, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // put the column headers
        fputcsv( $handle, array_values( $columns ) );

        // put the entry values
        foreach ( $entry_array as $row ) {
            fputcsv( $handle, $row );
        }

        fclose( $handle );

        exit;
    }
}
