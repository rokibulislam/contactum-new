<?php

function contactum_entries_forms() {
    $contactum_forms = contactum()->forms->all();

    foreach ( $contactum_forms['forms'] as $key => $form ) {
        // if ( $form->num_form_entries() < 1 ) {
        // if ( $form->num_all_form_entries() < 1 ) {
            // unset( $contactum_forms['forms'][ $key ] );
        // }
    }

    return $contactum_forms['forms'];
}

function contactum_get_entries() {
    $entries = contactum_get_form_entries( $this->form_id, $args );
    $columns = contactum_get_entry_columns( $this->form_id );
}



function contactum_get_default_form_notification() {
    return apply_filters(
        'contactum_get_default_form_notification', [
            'active'       => true,
            'type'         => 'email',
            'smsTo'        => '',
            'smsText'      => '[{form_name}] ' . __( 'New Form Submission', 'contactum' ) . ' #{entry_id}',
            'name'         => __( 'Admin Notification', 'contactum' ),
            'subject'      => '[{form_name}] ' . __( 'New Form Submission', 'contactum' ) . ' #{entry_id}',
            'to'           => '{admin_email}',
            'replyTo'      => '{field:email}',
            'message'      => '{all_fields}',
            'fromName'     => '{site_name}',
            'fromAddress'  => '{admin_email}',
            'cc'           => '',
            'bcc'          => '',
            'contactum_cond' => [
                'condition_status' => 'no',
                'cond_logic'       => 'any',
                'conditions'       => [
                    [
                        'name'             => '',
                        'operator'         => '=',
                        'option'           => '',
                    ],
                ],
            ],
        ]
      );
}


function contactum_get_default_form_settings() {
    return apply_filters(
        'contactum_get_default_form_settings', [
            'redirect_to'        => 'same',
            'message'            => __( 'Thanks for contact us! We will get in touch with you shortly.', 'contactum' ),
            'page_id'            => '',
            'url'                => '',
            'pages'              =>  wp_list_pluck( get_pages(), 'post_title', 'ID' ),
            'submit_text'        => __( 'Submit', 'contactum' ),
            'schedule_form'      => 'false',
            'schedule_start'     => '',
            'schedule_end'       => '',
            'sc_pending_message' => __( 'Form submission hasn\'t been started yet', 'contactum' ),
            'sc_expired_message' => __( 'Form submission is now closed.', 'contactum' ),
            'require_login'      => 'false',
            'req_login_message'  => __( 'You need to login to submit a query.', 'contactum' ),
            'limit_entries'      => 'false',
            'limit_number'       => '100',
            'limit_message'      => __( 'Sorry, we have reached the maximum number of submissions.', 'contactum' ),
            'label_position'             => 'above',
            'use_theme_css'              => 'contactum-style',
            'custom_css' => '',
            'custom_js'  => ''
        ]
      );
}

function contactum_format_text( $content ) {
    $content = wptexturize( $content );
    $content = convert_smilies( $content );
    $content = wpautop( $content );
    $content = make_clickable( $content );

    return $content;
}

function contactum_insert_form_field( $form_id, $field = [], $field_id = null, $order = 0 ) {
    $args = [
        'post_type'    => 'contactum_input',
        'post_parent'  => $form_id,
        'post_status'  => 'publish',
        'post_content' => maybe_serialize( wp_unslash( $field ) ),
        'menu_order'   => $order,
    ];

    if ( $field_id ) {
        $args['ID'] = $field_id;
    }

    if ( $field_id ) {
        return wp_update_post( $args );
    } else {
        return wp_insert_post( $args );
    }
}

function contactum_get_all_entries( $args = [] ) {

    global $wpdb;

    $defaults = [
        'number'  => 100,
        'offset'  => 0,
        'orderby' => 'created_at',
        'status'  => 'publish',
        'order'   => 'DESC',
    ];

    $r = wp_parse_args( $args, $defaults );

    // $query = 'SELECT id, form_id, user_id, INET_NTOA( user_ip ) as ip_address, created_at
    //         FROM ' . $wpdb->contactum_entries . ' status = \'' . $r['status'] . '\'' .
    //         ' ORDER BY ' . $r['orderby'] . ' ' . $r['order'];

    $query = 'SELECT e.id, e.form_id, p.post_title, e.user_id, e.status, INET_NTOA( e.user_ip ) as ip_address, e.created_at, p.post_title as form_name FROM ' . $wpdb->contactum_entries  . ' e
    INNER JOIN ' . $wpdb->posts . ' p ON e.form_id = p.ID';
    
    $where = []; // store conditions

    // Filter by form_id
    if ( isset( $r['form_id'] ) ) {
        $where[] = $wpdb->prepare( 'e.form_id = %d', $r['form_id'] );
    }

    // Filter by start and end dates
    if ( !empty( $r['start_date'] ) && !empty( $r['end_date'] ) ) {
        $where[] = $wpdb->prepare( 'e.created_at BETWEEN %s AND %s', $r['start_date'], $r['end_date'] );
    }

    // 🔎 Search by post title
    if ( !empty( $r['search'] ) ) {
        $like = '%' . $wpdb->esc_like( $r['search'] ) . '%';
        $where[] = $wpdb->prepare( 'p.post_title LIKE %s', $like );
    }


    // Combine WHERE clauses if any
    if ( !empty( $where ) ) {
        $query .= ' WHERE ' . implode( ' AND ', $where );
    }

    if ( !empty( $r['offset'] ) && !empty( $r['number'] ) ) {
        // $query .= ' LIMIT ' . $r['offset'] . ', ' . $r['number'];
    }

    $results = $wpdb->get_results( $query );

    return $results;
}

function contactum_get_form_entries( $form_id, $args = [] ) {
    global $wpdb;

    $defaults = [
        'number'  => 10,
        'offset'  => 0,
        'orderby' => 'created_at',
        'status'  => 'publish',
        'order'   => 'DESC',
    ];

    $r = wp_parse_args( $args, $defaults );

    $query = 'SELECT id, form_id, user_id, INET_NTOA( user_ip ) as ip_address, created_at
            FROM ' . $wpdb->contactum_entries .
            ' WHERE form_id = ' . $form_id . ' AND status = \'' . $r['status'] . '\'' .
            ' ORDER BY ' . $r['orderby'] . ' ' . $r['order'];

    if ( !empty( $r['offset'] ) && !empty( $r['number'] ) ) {
        $query .= ' LIMIT ' . $r['offset'] . ', ' . $r['number'];
    }

    $results = $wpdb->get_results( $query );

    return $results;
}


function contactum_count_form_entries( $form_id, $status = 'publish' ) {
    global $wpdb;

    return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT count(id) FROM ' . $wpdb->contactum_entries . ' WHERE form_id = %d AND status = %s', $form_id, $status ) );
}

function contactum_count_all_form_entries( $form_id, $status = 'publish' ) {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT count(id) FROM ' . $wpdb->contactum_entries . ' WHERE form_id = %d', $form_id ) );
}

function contactum_get_entry( $entry_id ) {
    global $wpdb;

    $cache_key = 'contactum-entry-' . $entry_id;
    $entry     = wp_cache_get( $cache_key, 'contactum' );

    if ( false === $entry ) {
        $query = 'SELECT id, form_id, user_id, user_device, referer, INET_NTOA( user_ip ) as ip_address, created_at
            FROM ' . $wpdb->contactum_entries . '
            WHERE id = %d';

        $entry = $wpdb->get_row( $wpdb->prepare( $query, $entry_id ) );
        wp_cache_set( $cache_key, $entry );
    }

    return $entry;
}


function contactum_get_entry_columns( $form_id, $limit = 6 ) {
    $fields  = contactum()->forms->get( $form_id )->getFields();
    $columns = [];
    // filter by input types
    if ( $limit ) {
        $fields = array_filter( $fields, function ( $item ) {
            return in_array( $item['template'], [ 'text_field', 'name_field', 'dropdown_field', 'radio_field', 'email_field', 'url_field' ] );
        } );
    }

    if ( $fields ) {
        foreach ( $fields as $field ) {
            $columns[ $field['name'] ] = $field['label'];
        }
    }

    // if passed 0/false, return all columns
    if ( $limit && sizeof( $columns ) > $limit ) {
        $columns = array_slice( $columns, 0, $limit ); // max 6 columns
    }

    return apply_filters( 'contactum_get_entry_columns', $columns, $form_id );
}

function contactum_get_pain_text( $value ) {
    if ( is_serialized( $value ) ) {
        $value = unserialize( $value );
    }

    if ( is_array( $value ) ) {
        $string_value = [];

        if ( is_array( $value ) ) {
            foreach ( $value as $key => $single_value ) {
                if ( is_array( $single_value ) || is_serialized( $single_value ) ) {
                    $single_value = contactum_get_pain_text( $single_value );
                }

                $single_value = ucwords( str_replace( [ '_', '-' ], ' ', $key ) ) . ': ' . ucwords( $single_value );

                $string_value[] = $single_value;
            }

            $value = implode( ' | ', $string_value );
        }
    }

    $value = trim( strip_tags( $value ) );

    return $value;
}

function contactum_get_form_preview_url( $form_id, $new_window = false ) {

    $url = add_query_arg(
        array(
            'contactum_form_preview' => absint( $form_id ),
        ),
        home_url()
    );

    if ( $new_window ) {
        $url = add_query_arg(
            array(
                'new_window' => 1,
            ),
            $url
        );
    }

    return $url;
}

function contactum_get_form_entries_url( $form_id ) {
    $url = esc_url( admin_url( 'admin.php' ) ). '?page=contactum-entries&amp;form_id='. esc_attr($form_id );

    return $url; 
}

function contactum_get_form_export_url( $form_id ) {
    $url = esc_url( admin_url( 'admin-post.php' ) ). '?action=contactum_export_form_entries&amp;form_id='. esc_attr($form_id );

    return $url;
}

function contactum_get_client_ip() {
    $ipaddress = '';

    if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
    } elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
    } elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED'] ) );
    } elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED_FOR'] ) );
    } elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['HTTP_FORWARDED'] ) );
    } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ipaddress = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}


function contactum_get_settings( $key = '' ) {
    $settings = get_option( 'contactum_settings', [] );

    if ( empty( $key ) ) {
        return $settings;
    }

    if ( isset( $settings[ $key ] ) ) {
        return $settings[ $key ];
    }
}


function contactum_get_options( $option_name ,$key = '' ) {
    $settings = get_option( $option_name, [] );

    if ( empty( $key ) ) {
        return $settings;
    }

    if ( isset( $settings[ $key ] ) ) {
        return $settings[ $key ];
    }
}


function contactum_get_countries() {

    $countries = array(
        array(
            'name' => 'Afghanistan',
            'code' => 'AF',
        ),
        array(
            'name' => 'Åland Islands',
            'code' => 'AX',
        ),
        array(
            'name' => 'Albania',
            'code' => 'AL',
        ),
        array(
            'name' => 'Algeria',
            'code' => 'DZ',
        ),
        array(
            'name' => 'American Samoa',
            'code' => 'AS',
        ),
        array(
            'name' => 'AndorrA',
            'code' => 'AD',
        ),
        array(
            'name' => 'Angola',
            'code' => 'AO',
        ),
        array(
            'name' => 'Anguilla',
            'code' => 'AI',
        ),
        array(
            'name' => 'Antarctica',
            'code' => 'AQ',
        ),
        array(
            'name' => 'Antigua and Barbuda',
            'code' => 'AG',
        ),
        array(
            'name' => 'Argentina',
            'code' => 'AR',
        ),
        array(
            'name' => 'Armenia',
            'code' => 'AM',
        ),
        array(
            'name' => 'Aruba',
            'code' => 'AW',
        ),
        array(
            'name' => 'Australia',
            'code' => 'AU',
        ),
        array(
            'name' => 'Austria',
            'code' => 'AT',
        ),
        array(
            'name' => 'Azerbaijan',
            'code' => 'AZ',
        ),
        array(
            'name' => 'Bahamas',
            'code' => 'BS',
        ),
        array(
            'name' => 'Bahrain',
            'code' => 'BH',
        ),
        array(
            'name' => 'Bangladesh',
            'code' => 'BD',
        ),
        array(
            'name' => 'Barbados',
            'code' => 'BB',
        ),
        array(
            'name' => 'Belarus',
            'code' => 'BY',
        ),
        array(
            'name' => 'Belgium',
            'code' => 'BE',
        ),
        array(
            'name' => 'Belize',
            'code' => 'BZ',
        ),
        array(
            'name' => 'Benin',
            'code' => 'BJ',
        ),
        array(
            'name' => 'Bermuda',
            'code' => 'BM',
        ),
        array(
            'name' => 'Bhutan',
            'code' => 'BT',
        ),
        array(
            'name' => 'Bolivia',
            'code' => 'BO',
        ),
        array(
            'name' => 'Bosnia and Herzegovina',
            'code' => 'BA',
        ),
        array(
            'name' => 'Botswana',
            'code' => 'BW',
        ),
        array(
            'name' => 'Bouvet Island',
            'code' => 'BV',
        ),
        array(
            'name' => 'Brazil',
            'code' => 'BR',
        ),
        array(
            'name' => 'British Indian Ocean Territory',
            'code' => 'IO',
        ),
        array(
            'name' => 'Brunei Darussalam',
            'code' => 'BN',
        ),
        array(
            'name' => 'Bulgaria',
            'code' => 'BG',
        ),
        array(
            'name' => 'Burkina Faso',
            'code' => 'BF',
        ),
        array(
            'name' => 'Burundi',
            'code' => 'BI',
        ),
        array(
            'name' => 'Cambodia',
            'code' => 'KH',
        ),
        array(
            'name' => 'Cameroon',
            'code' => 'CM',
        ),
        array(
            'name' => 'Canada',
            'code' => 'CA',
        ),
        array(
            'name' => 'Cape Verde',
            'code' => 'CV',
        ),
        array(
            'name' => 'Cayman Islands',
            'code' => 'KY',
        ),
        array(
            'name' => 'Central African Republic',
            'code' => 'CF',
        ),
        array(
            'name' => 'Chad',
            'code' => 'TD',
        ),
        array(
            'name' => 'Chile',
            'code' => 'CL',
        ),
        array(
            'name' => 'China',
            'code' => 'CN',
        ),
        array(
            'name' => 'Christmas Island',
            'code' => 'CX',
        ),
        array(
            'name' => 'Cocos (Keeling) Islands',
            'code' => 'CC',
        ),
        array(
            'name' => 'Colombia',
            'code' => 'CO',
        ),
        array(
            'name' => 'Comoros',
            'code' => 'KM',
        ),
        array(
            'name' => 'Congo',
            'code' => 'CG',
        ),
        array(
            'name' => 'Congo, The Democratic Republic of the',
            'code' => 'CD',
        ),
        array(
            'name' => 'Cook Islands',
            'code' => 'CK',
        ),
        array(
            'name' => 'Costa Rica',
            'code' => 'CR',
        ),
        array(
            'name' => 'Cote D"Ivoire',
            'code' => 'CI',
        ),
        array(
            'name' => 'Croatia',
            'code' => 'HR',
        ),
        array(
            'name' => 'Cuba',
            'code' => 'CU',
        ),
        array(
            'name' => 'Cyprus',
            'code' => 'CY',
        ),
        array(
            'name' => 'Czech Republic',
            'code' => 'CZ',
        ),
        array(
            'name' => 'Denmark',
            'code' => 'DK',
        ),
        array(
            'name' => 'Djibouti',
            'code' => 'DJ',
        ),
        array(
            'name' => 'Dominica',
            'code' => 'DM',
        ),
        array(
            'name' => 'Dominican Republic',
            'code' => 'DO',
        ),
        array(
            'name' => 'Ecuador',
            'code' => 'EC',
        ),
        array(
            'name' => 'Egypt',
            'code' => 'EG',
        ),
        array(
            'name' => 'El Salvador',
            'code' => 'SV',
        ),
        array(
            'name' => 'Equatorial Guinea',
            'code' => 'GQ',
        ),
        array(
            'name' => 'Eritrea',
            'code' => 'ER',
        ),
        array(
            'name' => 'Estonia',
            'code' => 'EE',
        ),
        array(
            'name' => 'Ethiopia',
            'code' => 'ET',
        ),
        array(
            'name' => 'Falkland Islands (Malvinas)',
            'code' => 'FK',
        ),
        array(
            'name' => 'Faroe Islands',
            'code' => 'FO',
        ),
        array(
            'name' => 'Fiji',
            'code' => 'FJ',
        ),
        array(
            'name' => 'Finland',
            'code' => 'FI',
        ),
        array(
            'name' => 'France',
            'code' => 'FR',
        ),
        array(
            'name' => 'French Guiana',
            'code' => 'GF',
        ),
        array(
            'name' => 'French Polynesia',
            'code' => 'PF',
        ),
        array(
            'name' => 'French Southern Territories',
            'code' => 'TF',
        ),
        array(
            'name' => 'Gabon',
            'code' => 'GA',
        ),
        array(
            'name' => 'Gambia',
            'code' => 'GM',
        ),
        array(
            'name' => 'Georgia',
            'code' => 'GE',
        ),
        array(
            'name' => 'Germany',
            'code' => 'DE',
        ),
        array(
            'name' => 'Ghana',
            'code' => 'GH',
        ),
        array(
            'name' => 'Gibraltar',
            'code' => 'GI',
        ),
        array(
            'name' => 'Greece',
            'code' => 'GR',
        ),
        array(
            'name' => 'Greenland',
            'code' => 'GL',
        ),
        array(
            'name' => 'Grenada',
            'code' => 'GD',
        ),
        array(
            'name' => 'Guadeloupe',
            'code' => 'GP',
        ),
        array(
            'name' => 'Guam',
            'code' => 'GU',
        ),
        array(
            'name' => 'Guatemala',
            'code' => 'GT',
        ),
        array(
            'name' => 'Guernsey',
            'code' => 'GG',
        ),
        array(
            'name' => 'Guinea',
            'code' => 'GN',
        ),
        array(
            'name' => 'Guinea-Bissau',
            'code' => 'GW',
        ),
        array(
            'name' => 'Guyana',
            'code' => 'GY',
        ),
        array(
            'name' => 'Haiti',
            'code' => 'HT',
        ),
        array(
            'name' => 'Heard Island and Mcdonald Islands',
            'code' => 'HM',
        ),
        array(
            'name' => 'Holy See (Vatican City State)',
            'code' => 'VA',
        ),
        array(
            'name' => 'Honduras',
            'code' => 'HN',
        ),
        array(
            'name' => 'Hong Kong',
            'code' => 'HK',
        ),
        array(
            'name' => 'Hungary',
            'code' => 'HU',
        ),
        array(
            'name' => 'Iceland',
            'code' => 'IS',
        ),
        array(
            'name' => 'India',
            'code' => 'IN',
        ),
        array(
            'name' => 'Indonesia',
            'code' => 'ID',
        ),
        array(
            'name' => 'Iran, Islamic Republic Of',
            'code' => 'IR',
        ),
        array(
            'name' => 'Iraq',
            'code' => 'IQ',
        ),
        array(
            'name' => 'Ireland',
            'code' => 'IE',
        ),
        array(
            'name' => 'Isle of Man',
            'code' => 'IM',
        ),
        array(
            'name' => 'Israel',
            'code' => 'IL',
        ),
        array(
            'name' => 'Italy',
            'code' => 'IT',
        ),
        array(
            'name' => 'Jamaica',
            'code' => 'JM',
        ),
        array(
            'name' => 'Japan',
            'code' => 'JP',
        ),
        array(
            'name' => 'Jersey',
            'code' => 'JE',
        ),
        array(
            'name' => 'Jordan',
            'code' => 'JO',
        ),
        array(
            'name' => 'Kazakhstan',
            'code' => 'KZ',
        ),
        array(
            'name' => 'Kenya',
            'code' => 'KE',
        ),
        array(
            'name' => 'Kiribati',
            'code' => 'KI',
        ),
        array(
            'name' => 'Korea, Democratic People"S Republic of',
            'code' => 'KP',
        ),
        array(
            'name' => 'Korea, Republic of',
            'code' => 'KR',
        ),
        array(
            'name' => 'Kuwait',
            'code' => 'KW',
        ),
        array(
            'name' => 'Kyrgyzstan',
            'code' => 'KG',
        ),
        array(
            'name' => 'Lao People"S Democratic Republic',
            'code' => 'LA',
        ),
        array(
            'name' => 'Latvia',
            'code' => 'LV',
        ),
        array(
            'name' => 'Lebanon',
            'code' => 'LB',
        ),
        array(
            'name' => 'Lesotho',
            'code' => 'LS',
        ),
        array(
            'name' => 'Liberia',
            'code' => 'LR',
        ),
        array(
            'name' => 'Libyan Arab Jamahiriya',
            'code' => 'LY',
        ),
        array(
            'name' => 'Liechtenstein',
            'code' => 'LI',
        ),
        array(
            'name' => 'Lithuania',
            'code' => 'LT',
        ),
        array(
            'name' => 'Luxembourg',
            'code' => 'LU',
        ),
        array(
            'name' => 'Macao',
            'code' => 'MO',
        ),
        array(
            'name' => 'Macedonia, The Former Yugoslav Republic of',
            'code' => 'MK',
        ),
        array(
            'name' => 'Madagascar',
            'code' => 'MG',
        ),
        array(
            'name' => 'Malawi',
            'code' => 'MW',
        ),
        array(
            'name' => 'Malaysia',
            'code' => 'MY',
        ),
        array(
            'name' => 'Maldives',
            'code' => 'MV',
        ),
        array(
            'name' => 'Mali',
            'code' => 'ML',
        ),
        array(
            'name' => 'Malta',
            'code' => 'MT',
        ),
        array(
            'name' => 'Marshall Islands',
            'code' => 'MH',
        ),
        array(
            'name' => 'Martinique',
            'code' => 'MQ',
        ),
        array(
            'name' => 'Mauritania',
            'code' => 'MR',
        ),
        array(
            'name' => 'Mauritius',
            'code' => 'MU',
        ),
        array(
            'name' => 'Mayotte',
            'code' => 'YT',
        ),
        array(
            'name' => 'Mexico',
            'code' => 'MX',
        ),
        array(
            'name' => 'Micronesia, Federated States of',
            'code' => 'FM',
        ),
        array(
            'name' => 'Moldova, Republic of',
            'code' => 'MD',
        ),
        array(
            'name' => 'Monaco',
            'code' => 'MC',
        ),
        array(
            'name' => 'Mongolia',
            'code' => 'MN',
        ),
        array(
            'name' => 'Montserrat',
            'code' => 'MS',
        ),
        array(
            'name' => 'Morocco',
            'code' => 'MA',
        ),
        array(
            'name' => 'Mozambique',
            'code' => 'MZ',
        ),
        array(
            'name' => 'Myanmar',
            'code' => 'MM',
        ),
        array(
            'name' => 'Namibia',
            'code' => 'NA',
        ),
        array(
            'name' => 'Nauru',
            'code' => 'NR',
        ),
        array(
            'name' => 'Nepal',
            'code' => 'NP',
        ),
        array(
            'name' => 'Netherlands',
            'code' => 'NL',
        ),
        array(
            'name' => 'Netherlands Antilles',
            'code' => 'AN',
        ),
        array(
            'name' => 'New Caledonia',
            'code' => 'NC',
        ),
        array(
            'name' => 'New Zealand',
            'code' => 'NZ',
        ),
        array(
            'name' => 'Nicaragua',
            'code' => 'NI',
        ),
        array(
            'name' => 'Niger',
            'code' => 'NE',
        ),
        array(
            'name' => 'Nigeria',
            'code' => 'NG',
        ),
        array(
            'name' => 'Niue',
            'code' => 'NU',
        ),
        array(
            'name' => 'Norfolk Island',
            'code' => 'NF',
        ),
        array(
            'name' => 'Northern Mariana Islands',
            'code' => 'MP',
        ),
        array(
            'name' => 'Norway',
            'code' => 'NO',
        ),
        array(
            'name' => 'Oman',
            'code' => 'OM',
        ),
        array(
            'name' => 'Pakistan',
            'code' => 'PK',
        ),
        array(
            'name' => 'Palau',
            'code' => 'PW',
        ),
        array(
            'name' => 'Palestinian Territory, Occupied',
            'code' => 'PS',
        ),
        array(
            'name' => 'Panama',
            'code' => 'PA',
        ),
        array(
            'name' => 'Papua New Guinea',
            'code' => 'PG',
        ),
        array(
            'name' => 'Paraguay',
            'code' => 'PY',
        ),
        array(
            'name' => 'Peru',
            'code' => 'PE',
        ),
        array(
            'name' => 'Philippines',
            'code' => 'PH',
        ),
        array(
            'name' => 'Pitcairn',
            'code' => 'PN',
        ),
        array(
            'name' => 'Poland',
            'code' => 'PL',
        ),
        array(
            'name' => 'Portugal',
            'code' => 'PT',
        ),
        array(
            'name' => 'Puerto Rico',
            'code' => 'PR',
        ),
        array(
            'name' => 'Qatar',
            'code' => 'QA',
        ),
        array(
            'name' => 'Reunion',
            'code' => 'RE',
        ),
        array(
            'name' => 'Romania',
            'code' => 'RO',
        ),
        array(
            'name' => 'Russian Federation',
            'code' => 'RU',
        ),
        array(
            'name' => 'RWANDA',
            'code' => 'RW',
        ),
        array(
            'name' => 'Saint Helena',
            'code' => 'SH',
        ),
        array(
            'name' => 'Saint Kitts and Nevis',
            'code' => 'KN',
        ),
        array(
            'name' => 'Saint Lucia',
            'code' => 'LC',
        ),
        array(
            'name' => 'Saint Pierre and Miquelon',
            'code' => 'PM',
        ),
        array(
            'name' => 'Saint Vincent and the Grenadines',
            'code' => 'VC',
        ),
        array(
            'name' => 'Samoa',
            'code' => 'WS',
        ),
        array(
            'name' => 'San Marino',
            'code' => 'SM',
        ),
        array(
            'name' => 'Sao Tome and Principe',
            'code' => 'ST',
        ),
        array(
            'name' => 'Saudi Arabia',
            'code' => 'SA',
        ),
        array(
            'name' => 'Senegal',
            'code' => 'SN',
        ),
        array(
            'name' => 'Serbia and Montenegro',
            'code' => 'CS',
        ),
        array(
            'name' => 'Seychelles',
            'code' => 'SC',
        ),
        array(
            'name' => 'Sierra Leone',
            'code' => 'SL',
        ),
        array(
            'name' => 'Singapore',
            'code' => 'SG',
        ),
        array(
            'name' => 'Slovakia',
            'code' => 'SK',
        ),
        array(
            'name' => 'Slovenia',
            'code' => 'SI',
        ),
        array(
            'name' => 'Solomon Islands',
            'code' => 'SB',
        ),
        array(
            'name' => 'Somalia',
            'code' => 'SO',
        ),
        array(
            'name' => 'South Africa',
            'code' => 'ZA',
        ),
        array(
            'name' => 'South Georgia and the South Sandwich Islands',
            'code' => 'GS',
        ),
        array(
            'name' => 'Spain',
            'code' => 'ES',
        ),
        array(
            'name' => 'Sri Lanka',
            'code' => 'LK',
        ),
        array(
            'name' => 'Sudan',
            'code' => 'SD',
        ),
        array(
            'name' => 'Suriname',
            'code' => 'SR',
        ),
        array(
            'name' => 'Svalbard and Jan Mayen',
            'code' => 'SJ',
        ),
        array(
            'name' => 'Swaziland',
            'code' => 'SZ',
        ),
        array(
            'name' => 'Sweden',
            'code' => 'SE',
        ),
        array(
            'name' => 'Switzerland',
            'code' => 'CH',
        ),
        array(
            'name' => 'Syrian Arab Republic',
            'code' => 'SY',
        ),
        array(
            'name' => 'Taiwan, Province of China',
            'code' => 'TW',
        ),
        array(
            'name' => 'Tajikistan',
            'code' => 'TJ',
        ),
        array(
            'name' => 'Tanzania, United Republic of',
            'code' => 'TZ',
        ),
        array(
            'name' => 'Thailand',
            'code' => 'TH',
        ),
        array(
            'name' => 'Timor-Leste',
            'code' => 'TL',
        ),
        array(
            'name' => 'Togo',
            'code' => 'TG',
        ),
        array(
            'name' => 'Tokelau',
            'code' => 'TK',
        ),
        array(
            'name' => 'Tonga',
            'code' => 'TO',
        ),
        array(
            'name' => 'Trinidad and Tobago',
            'code' => 'TT',
        ),
        array(
            'name' => 'Tunisia',
            'code' => 'TN',
        ),
        array(
            'name' => 'Turkey',
            'code' => 'TR',
        ),
        array(
            'name' => 'Turkmenistan',
            'code' => 'TM',
        ),
        array(
            'name' => 'Turks and Caicos Islands',
            'code' => 'TC',
        ),
        array(
            'name' => 'Tuvalu',
            'code' => 'TV',
        ),
        array(
            'name' => 'Uganda',
            'code' => 'UG',
        ),
        array(
            'name' => 'Ukraine',
            'code' => 'UA',
        ),
        array(
            'name' => 'United Arab Emirates',
            'code' => 'AE',
        ),
        array(
            'name' => 'United Kingdom',
            'code' => 'GB',
        ),
        array(
            'name' => 'United States',
            'code' => 'US',
        ),
        array(
            'name' => 'United States Minor Outlying Islands',
            'code' => 'UM',
        ),
        array(
            'name' => 'Uruguay',
            'code' => 'UY',
        ),
        array(
            'name' => 'Uzbekistan',
            'code' => 'UZ',
        ),
        array(
            'name' => 'Vanuatu',
            'code' => 'VU',
        ),
        array(
            'name' => 'Venezuela',
            'code' => 'VE',
        ),
        array(
            'name' => 'Viet Nam',
            'code' => 'VN',
        ),
        array(
            'name' => 'Virgin Islands, British',
            'code' => 'VG',
        ),
        array(
            'name' => 'Virgin Islands, U.S.',
            'code' => 'VI',
        ),
        array(
            'name' => 'Wallis and Futuna',
            'code' => 'WF',
        ),
        array(
            'name' => 'Western Sahara',
            'code' => 'EH',
        ),
        array(
            'name' => 'Yemen',
            'code' => 'YE',
        ),
        array(
            'name' => 'Zambia',
            'code' => 'ZM',
        ),
        array(
            'name' => 'Zimbabwe',
            'code' => 'ZW',
        ),
    );

    // $countries = json_encode( $countries );

    return $countries;
}

function contactum_allowed_extensions() {
    $extesions = [
        'images' => [
            'ext'   => 'jpg,jpeg,gif,png,bmp',
            'label' => __( 'Images', 'contactum' ),
        ],
        'audio'  => [
            'ext'   => 'mp3,wav,ogg,wma,mka,m4a,ra,mid,midi',
            'label' => __( 'Audio', 'contactum' ),
        ],
        'video'  => [
            'ext'   => 'avi,divx,flv,mov,ogv,mkv,mp4,m4v,divx,mpg,mpeg,mpe',
            'label' => __( 'Videos', 'contactum' ),
        ],
        'pdf'    => [
            'ext'   => 'pdf',
            'label' => __( 'PDF', 'contactum' ),
        ],
        'office' => [
            'ext'   => 'doc,ppt,pps,xls,mdb,docx,xlsx,pptx,odt,odp,ods,odg,odc,odb,odf,rtf,txt',
            'label' => __( 'Office Documents', 'contactum' ),
        ],
        'zip'    => [
            'ext'   => 'zip,gz,gzip,rar,7z',
            'label' => __( 'Zip Archives', 'contactum' ),
        ],
        'exe'    => [
            'ext'   => 'exe',
            'label' => __( 'Executable Files', 'contactum' ),
        ],
        'csv'    => [
            'ext'   => 'csv',
            'label' => __( 'CSV', 'contactum' ),
        ],
    ];

    return apply_filters( 'contactum_allowed_extensions', $extesions );
}


/**
 * Get form integration settings
 *
 * @param int $form_id
 *
 * @return array
 */
function contactum_get_form_integrations( $form_id ) {
    $integrations = get_post_meta( $form_id, 'integrations', true );

    if ( !$integrations ) {
        return [];
    }

    return $integrations;
}

/**
 * Check if an integration is active
 *
 * @param int    $form_id
 * @param string $integration_id
 *
 * @return bool
 */
function contactum_is_integration_active( $form_id, $integration_id ) {
    $integrations = contactum_get_form_integrations( $form_id );

    if ( !$integrations ) {
        return false;
    }

    foreach ( $integrations as $id => $integration ) {
        if ( $integration_id == $id && $integration->formenable == true ) {
            return $integration;
        }
    }

    return false;
}


function  contactum_count_all_entries() {
        global $wpdb;
        $table = $wpdb->prefix . 'contactum_entries';

        $results = $wpdb->get_results(
            "SELECT form_id, COUNT(*) AS count
             FROM $table
             GROUP BY form_id",
            ARRAY_A
        );

        return $results;
}

function contactum_get_entries_report() {

     global $wpdb;

     $form_id = isset($_POST['form_id']) ? sanitize_text_field( $_POST['form_id'] ) : '';

     $args = [];

     if( $form_id ) {
         $args[ 'form_id'] = $form_id;
     }

     // Get start date if it exists
     if ( isset( $_POST['startdate'] ) && ! empty( $_POST['startdate'] ) ) {
         $start_date_raw = sanitize_text_field( $_POST['startdate'] );
         $start_date = date( 'Y-m-d', strtotime( $start_date_raw ) );
         $args['start_date'] = $start_date;
     } else {
        $args['start_date'] = date('Y-m-01');
     }

     // Get end date if it exists
     if ( isset( $_POST['enddate'] ) && ! empty( $_POST['enddate'] ) ) {
         $end_date_raw = sanitize_text_field( $_POST['enddate'] );
         $end_date = date( 'Y-m-d', strtotime( $end_date_raw ) );
         $args['end_date'] = $end_date;
     } else {
        $args['end_date'] = date('Y-m-d');
     }


//      $query = " SELECT DATE_FORMAT(created_at,'%M') AS month_name, COUNT(*) AS total_entries FROM ".  $wpdb->contactum_entries . ' e INNER JOIN ' . $wpdb->posts . ' p ON e.form_id = p.ID';
        $query = " SELECT DATE(created_at) AS date, COUNT(e.id) AS total_entries FROM ".  $wpdb->contactum_entries . ' e INNER JOIN ' . $wpdb->posts . ' p ON e.form_id = p.ID';
     $conditions = [];

     if ( isset( $args['form_id'] ) ) {
         $conditions[] = $wpdb->prepare( ' e.form_id = %d', $form_id );
     }

     if ( !empty( $args['start_date'] ) && !empty( $args['end_date'] ) ) {
        $conditions[] = $wpdb->prepare( ' e.created_at BETWEEN %s AND %s', $args['start_date'], $args['end_date'] );
     }

     if ( empty( $conditions ) ) {
//          $conditions[] = 'created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)';
     }

//     $query .= ' WHERE ' . implode(' AND ', $conditions);
    $query .= " GROUP BY DATE(created_at) ORDER BY created_at ASC";
//     $query .= ' GROUP BY MONTH(created_at) ORDER BY MIN(created_at)';

    $results = $wpdb->get_results($query,OBJECT_K);


    // 🔑 Build full date range with zeros
    $range = [];
    $period = new DatePeriod(
        new DateTime($args['start_date']),
        new DateInterval('P1D'),
        (new DateTime($args['end_date'] ))->modify('+1 day')
    );

    foreach ($period as $date) {
        $d = $date->format('Y-m-d');
        $range[$d] = isset($results[$d]) ? (int)$results[$d]->total_entries : 0;
    }

    return $range;
}



function contactum_kses_js($content)
{
    return $content ? preg_replace('/<script.*?>[\s\S]*<\/script>/is', '', $content) : '';
}

function contactum_kses_css($css)
{
    return preg_match('#</?\w+#', $css) ? '' : $css;
}


// add_action('load-post.php', function() {
add_action('admin_init', function () {
    if (isset($_GET['embed_shortcode'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'contactum-form-builder-nonce')) {
        
        $post_id = intval($_GET['post']);
        $shortcode = sanitize_text_field($_GET['embed_shortcode']);

        if ($post_id && get_post_status($post_id)) {
            $content = get_post_field('post_content', $post_id);

            if (strpos($content, $shortcode) === false) {
                $content .= "\n\n" . $shortcode;

                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $content
                ]);
            }
        } else {

                $page_title = sanitize_text_field($_GET['title']);
                $page_content = $shortcode;
                $page_slug = get_page_by_path( $page_title );
                    
                // Create page
                $page_id = wp_insert_post( array(
                    'post_title'   => $page_title,
                    'post_name'    => $page_slug,
                    'post_content' => $page_content,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ) );

                $redirect_url = remove_query_arg(['embed_shortcode', '_wpnonce']);

                wp_safe_redirect(admin_url("post.php?post={$page_id}&action=edit"));
            
                exit;
        }

        // Remove query args and redirect clean
        $redirect_url = remove_query_arg(['embed_shortcode', '_wpnonce']);
        wp_safe_redirect($redirect_url);
        exit;
    }
});



add_action( 'init', function() {
    $manager = new \Contactum\IntegrationManager();
    $manager->getIntegrations();
});



function cleantalk_validate($accessKey)
{
    $cleanTalkRequest = [
        'method_name' => 'notice_paid_till',
        'auth_key' => $accessKey,
    ];

    $response = wp_remote_post(
        'https://api.cleantalk.org/',
        [
            'body'    => \http_build_query($cleanTalkRequest, true),
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]
    );

    if (is_wp_error($response)) {
        return false;
    }

    $response = json_decode(wp_remote_retrieve_body($response));

    if ($response->data->moderate == 1 && $response->data->valid == 1 && $response->data->product_id == 1) {
        return true;
    } else {
        return false;
    }
}

function contactum_free_get_modules() {

    $purchaseUrl = 'https://wpcontactum.com/';

    return [
    /*
        'mailchimp' => [
            'name'         => __('Mailchimp', 'contactum'),
            'description'  => __('Mailchimp module allows you to create Mailchimp newsletter signup forms in WordPress', 'contactum'),
            'thumbnail'    => CONTACTUM_ASSETS. '/images/integrations/mailchimp.png',
            'enabled'      => 'no',
            'category'     => 'crm',
        ],
    */
        'paypal' => [
            'name'        => __('PayPal', 'contactum'),
            'description'  => __('Accept Payments via paypal as a part of your form submission', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/paypal.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'payment',
        ],
        'stripe' => [
            'name'        => __('Stripe', 'contactum'),
            'description'  => __('Accept Payments via stripe as a part of your form submission', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/stripe.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'payment',
        ],
        'trello' => [
            'name'        => __('Trello', 'contactum'),
            'description'  => __('Trello module lets you create Trello cards automatically whenever a form is submitted.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/trello.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'aweber' => [
            'name'        => __('Aweber', 'contactum'),
            'description'  => __(' Subscribe a contact to Aweber when a form is submited', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/aweber.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'activecampaign' => [
            'name'        => __('ActiveCampaign', 'contactum'),
            'description'  => __('Build signup forms in WordPress and integrate ActiveCampaign to effortlessly grow your email list.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/activecampaign.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'campaign_monitor' => [
            'name'        => __('Campaign Monitor', 'contactum'),
            'description'  => __('Build Campaign Monitor newsletter signup forms in WordPress to simplify and enhance your email marketing efforts.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/campaignmonitor.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'constatantcontact' => [
            'name'        => __('Constant Contact', 'contactum'),
            'description'  => __('Integrate Constant Contact with Contactum to create subscription forms directly in WordPress and expand your email list.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/constantcontact.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'convertkit' => [
            'name'        => __('ConvertKit', 'contactum'),
            'description'  => __('Integrate ConvertKit with Contactum to create subscription forms directly in WordPress and expand your email list.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/convertkit.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'getresponse' => [
            'name'         => __('GetResponse', 'contactum'),
            'description'  => __('Grow your email list with Contactum and GetResponse by creating subscription forms and capturing new leads.', 'contactum'),
            'thumbnail'    => CONTACTUM_ASSETS. '/images/integrations/getresponse.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'hubspot' => [
            'name'         => __('Hubspot', 'contactum'),
            'description'  => __('Integrate HubSpot with Contactum to automatically subscribe contacts whenever a form is submitted.', 'contactum'),
            'thumbnail'    => CONTACTUM_ASSETS. '/images/integrations/hubspot.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'icontact' => [
            'name'        => __('iContact', 'contactum'),
            'description'  => __('Integrate iContact with Contactum to automatically add contacts whenever a form is submitted.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/icontact.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'moosend' => [
            'name'        => __('MooSend', 'contactum'),
            'description'  => __('Integrate MooSend with Contactum to automatically add contacts whenever a form is submitted.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/moosend_logo.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'sendfox' => [
            'name'        => __('SendFox', 'contactum'),
            'description'  => __('Integrate SendFox with Contactum to automatically add contacts when a form is submitted.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/sendfox.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'mailerlite' => [
            'name'        => __('MailerLite', 'contactum'),
            'description'  => __('MailerLite is an email marketing platform tailored for designers and their clients. With Contactum, you can easily add subscribers to MailerLite.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/mailerlite.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'sendinblue' => [
            'name'        => __('Brevo (formerly SendInBlue)', 'contactum'),
            'description'  => __('Subscribe a contact to Sendinblue when a form is submited', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/brevo.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'drip' => [
            'name'         => __('Drip', 'contactum'),
            'description'  => __('This module allows you to add Drip subscribers directly from WordPress, helping you grow your email list.', 'contactum'),
            'thumbnail'    => CONTACTUM_ASSETS. '/images/integrations/drip.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],

        'zohocrm' => [
            'name'        => __('Zoho CRM', 'contactum'),
            'description'  => __('Zoho CRM is an online sales management platform that centralizes your sales, marketing, and customer support in a single CRM system.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/zohocrm.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],

        'automizy' => [
            'name'        => __('Automizy', 'contactum'),
            'description'  => __('Connect Automizy with Contactum and subscribe a contact when a form is submitted.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/automizy.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'salesforce' => [
            'name'        => __('Salesforce', 'contactum'),
            'description'  => __('Integrate Salesforce with Contactum to keep your contacts and other data organized and streamlined.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/salesforce.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'airtable' => [
            'name'        => __('Airtable', 'contactum'),
            'description'  => __('Airtable is a low-code platform for creating collaborative apps. Tailor your workflows, work seamlessly with your team, and achieve impactful results.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/airtable.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
        'mailjet' => [
            'name'        => __('Mailjet', 'contactum'),
            'description'  => __('Mailjet is a user-friendly, all-in-one email marketing platform.', 'contactum'),
            'thumbnail'         => CONTACTUM_ASSETS. '/images/integrations/mailjet.png',
            'enabled'      => 'no',
            'purchase_url' => $purchaseUrl,
            'category'     => 'crm',
        ],
    ];
}
