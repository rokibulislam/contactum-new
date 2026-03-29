<?php
namespace Contactum;

class Assets {

    private $settings = [];
    private $postdata;

    function __construct() {
        $id   = isset( $_GET['id'] ) ? intval( wp_unslash( $_GET['id'] ) ) : '';
        $this->postdata = get_post( $id );

        add_action( 'admin_enqueue_scripts', array( $this, 'register_builder_backend' ), 10 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );

//          if ( !empty( $this->postdata->ID ) ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_builder_scripts' ] );
//          }

        add_action( 'in_admin_header', [ $this, 'remove_admin_notices' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend' ] );
    }


    public function form_settings_menu() {
        $settings = [];

        $settingsMenus = [
            'form_settings' => [
                'title' => __('Form Settings', 'contactum'),
                'slug'  => 'form_settings',
                'hash'  => 'basic_settings',
                'route' => '/',
            ],
            'email_notifications' => [
                'title' => __('Email Notifications', 'contactum'),
                'slug'  => 'form_settings',
                'hash'  => 'email_notifications',
                'route' => '/email-settings',
            ],
            'all_integrations' => [
                'title' => __('Configure Integrations', 'contactum'),
                'slug'  => 'form_settings',
                'route' => '/all-integrations',
            ],
        ];

        $settingsMenus = apply_filters( 'contactum_form_settings_menu', $settingsMenus ) ;

        return $settingsMenus;
    }

    public function enqueue_admin_scripts() {
//         wp_register_script( 'contactum-tools', CONTACTUM_ASSETS . '/js/tools.js', [ 'jquery'], CONTACTUM_VERSION, true );
//         wp_register_script( 'contactum-chart', CONTACTUM_ASSETS . '/js/chart.js', [ 'jquery'], CONTACTUM_VERSION, true );
       
//         wp_register_style( 'contactum-modules', CONTACTUM_ASSETS . '/css/modules.css', [], CONTACTUM_VERSION );
        
//         wp_enqueue_script( 'contactum-tools' );
//         wp_enqueue_script( 'contactum-chart' );
       
//         wp_enqueue_style( 'contactum-modules' );

        wp_localize_script( 'contactum-tools', 'contactum_tools', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'contactum-nonce' ),
        ]);
    }

    public function remove_admin_notices() {
        remove_all_actions( 'network_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
    }

    public function get_frontend_localized_scripts() {


        
        return apply_filters( 'contactum_frontend_localize_script', [
            'confirmMsg' => __( 'Are you sure?', 'contactum' ),
            'delete_it'  => __( 'Yes, delete it', 'contactum' ),
            'cancel_it'  => __( 'No, cancel it', 'contactum' ),
            'nonce'      => wp_create_nonce( 'contactum_nonce' ),
            'ajaxurl'    => admin_url( 'admin-ajax.php' ),
            'plupload'   => [
                'url'              => admin_url( 'admin-ajax.php' ) . '?nonce=' . wp_create_nonce( 'contactum-upload-nonce' ),
                'flash_swf_url'    => includes_url( 'js/plupload/plupload.flash.swf' ),
                'filters'          => [
                    [
                        'title'      => __( 'Allowed Files', 'contactum' ),
                        'extensions' => '*',
                    ],
                ],
                'multipart'        => true,
                'urlstream_upload' => true,
                'warning'          => __( 'Maximum number of files reached!', 'contactum' ),
                'size_error'       => __( 'The file you have uploaded exceeds the file size limit. Please try again.', 'contactum' ),
                'type_error'       => __( 'You have uploaded an incorrect file type. Please try again.', 'contactum' ),
            ],
            'error_message' => __( 'Please fix the errors to proceed', 'contactum' ),
        ] );
    }


    public function get_admin_localized_scripts() {
        $form               = contactum()->forms->get( $this->postdata->ID );
        $contactum_settings = contactum_get_settings();

        return apply_filters( 'contactum_admin_localize_script', [
            'i18n'    => $this->i18n(),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
            'rest'    => array(
                'root'    => esc_url_raw( get_rest_url() ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
                'version' => 'contactum/v1',
            ),
            'field_settings' => contactum()->fields->get_js_settings(),
            'panel_sections' => contactum()->fields->get_field_groups(),
            'form_fields'    => $form->getFields(),
            // 'integration'    => [],
            'settings'       => $form->getSettings(),
            'notifications'  => $form->getNotifications(),
            'integrations'   => contactum()->integrations->get_integration_js_settings(),
//             'integrations'   => $form->get_integrations(),
            'post'                            => $this->postdata,
            'defaultNotification'             => contactum_get_default_form_notification(),
            'preview_url'                     => esc_url( add_query_arg('new_window', 1, contactum_get_form_preview_url( $form->id  ) ) ),
            'contactum_cond_supported_fields' => array( 'radio_field', 'checkbox_field', 'dropdown_field' ),
            'smart_tags'                      => contactum()->smarttags->getMergeTags(),
            'contactum_settings'              => $contactum_settings,
            'countries' => contactum_get_countries(),
        ]);
    }

        /**
     * i18n translatable strings
     *
     * @since 2.5
     *
     * @return array
     */
    private function i18n() {
        return apply_filters( 'contactum_form_builder_i18n', [
            'advanced_options'      => __( 'Advanced Options', 'contactum' ),
            'quiz_options'          => __( 'Quiz Options', 'contactum' ),
            'delete_field_warn_msg' => __( 'Are you sure you want to delete this field?', 'contactum' ),
            'yes_delete_it'         => __( 'Yes, delete it', 'contactum' ),
            'no_cancel_it'          => __( 'No, cancel it', 'contactum' ),
            'ok'                    => __( 'OK', 'contactum' ),
            'cancel'                => __( 'Cancel', 'contactum' ),
            'close'                 => __( 'Close', 'contactum' ),
            'disable'               => __( 'Disable', 'contactum' ),
            'last_choice_warn_msg'  => __( 'This field must contain at least one choice', 'contactum' ),
            'option'                => __( 'Option', 'contactum' ),
            'row'                   => __( 'Row', 'contactum' ),
            'column'                => __( 'Column', 'contactum' ),
            'last_column_warn_msg'  => __( 'This field must contain at least one column', 'contactum' ),
            'is_a_pro_feature'      => __( 'is available in Pro version', 'contactum' ),
            'pro_feature_msg'       => __( 'Please upgrade to the Pro version to unlock all these awesome features', 'contactum' ),
            'upgrade_to_pro'        => __( 'Get the Pro version', 'contactum' ),
            'select'                => __( 'Select', 'contactum' ),
            'saved_form_data'       => __( 'Saved form data', 'contactum' ),
            'unsaved_changes'       => __( 'You have unsaved changes.', 'contactum' ),
            'areYouSureToLeave'     => __( 'Are you sure to leave this page?', 'contactum' ),
            'copy_shortcode'        => __( 'Click to copy shortcode', 'contactum' ),

            'selectAnImage'         => __( 'Select an image', 'contactum' ),
            'pleaseSelectAnImage'   => __( 'Please select an image', 'contactum' ),
            'uploadAnImage'         => __( 'Upload an image', 'contactum' ),

            'shareYourForm'         => __( 'Share Your Form', 'contactum' ),
            'shareYourFormDesc'     => __( 'Sharing your form enables <strong>anyone</strong> to view and submit the form without inserting the shortcode to a page.', 'contactum' ),
            'shareYourFormText'     => __( 'Anyone with this URL will be able to view and submit this form.', 'contactum' ),
            'areYouSure'            => __( 'Are you sure?', 'contactum' ),
            'selectNotification'    => __( 'You must select a notification', 'contactum' ),
            'areYouSureDesc'        => __( 'Anyone with existing URL won\'t be able to view and submit the form anymore.', 'contactum' ),
            'disableSharing'        => __( 'Disable Sharing', 'contactum' ),
        ] );
    }

    public function register_builder_backend() {
        $screen = get_current_screen();

        // form_settings_menu

        // if ( $screen->base != 'toplevel_page_contactum' ) {
        //     return ;
        // }

        $this->register_styles( $this->get_admin_styles() );
        $this->register_scripts( $this->get_admin_scripts() );

        wp_enqueue_editor();
        wp_enqueue_media();

         $url = esc_url( add_query_arg( [
            'action'   => 'create_template',
            'template' => 'blank',
            '_wpnonce' => wp_create_nonce( 'contactum_create_from_template' ),
        ], admin_url( 'admin.php' ) ) );

        wp_localize_script( 'contactum-forms', 'contactum', [
            'forms'     => contactum()->forms->all(),
            'entries'   => contactum_count_all_entries(),
            'templates' => contactum()->templates->get_templates(),
            'pages'     =>  wp_list_pluck( get_pages(), 'post_title', 'ID' ),
            'url'  => $url,
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'admin_url' => esc_url(  admin_url('admin.php') ),
            'export_nonce'   => wp_create_nonce('contactum-export-forms'),
            'contactum_cond_supported_fields' => array( 'radio_field', 'checkbox_field', 'dropdown_field' ),
            'defaultNotification'             => contactum_get_default_form_notification(),
//          'notifications'  => $form->getNotifications(),
//           'integrations'   => $form->get_integrations(),
            'countries' => contactum_get_countries(),
            'reCaptcha' => get_option('_contactum_reCaptcha_details'),
            'hCaptcha'  => get_option('_contactum_hCaptcha_details'),
            'turnstile' => get_option('_contactum_turnstile_details'),
            'settings_menu' => $this->form_settings_menu(),
            'i18n'    => $this->i18n(),
            'is_pro' => class_exists('ContactumPro') ? true : false,
            'bulk_options_json'  => '{"Countries":["Afghanistan","Albania","Algeria","American Samoa","Andorra","Angola","Anguilla","Antarctica","Antigua and Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bonaire, Sint Eustatius and Saba","Bosnia and Herzegovina","Botswana","Bouvet Island","Brazil","British Indian Ocean Territory","Brunei Darussalam","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Cayman Islands","Central African Republic","Chad","Chile","China","Christmas Island","Cocos Islands","Colombia","Comoros","Congo, Democratic Republic of the","Congo, Republic of the","Cook Islands","Costa Rica","Croatia","Cuba","Cura\u00e7ao","Cyprus","Czech Republic","C\u00f4te d\'Ivoire","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini (Swaziland)","Ethiopia","Falkland Islands","Faroe Islands","Fiji","Finland","France","French Guiana","French Polynesia","French Southern Territories","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guadeloupe","Guam","Guatemala","Guernsey","Guinea","Guinea-Bissau","Guyana","Haiti","Heard and McDonald Islands","Holy See","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey","Jordan","Kazakhstan","Kenya","Kiribati","Kuwait","Kyrgyzstan","Lao People\'s Democratic Republic","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Martinique","Mauritania","Mauritius","Mayotte","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","Niue","Norfolk Island","North Korea","Northern Mariana Islands","Norway","Oman","Pakistan","Palau","Palestine, State of","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Pitcairn","Poland","Portugal","Puerto Rico","Qatar","Romania","Russia","Rwanda","R\u00e9union","Saint Barth\u00e9lemy","Saint Helena","Saint Kitts and Nevis","Saint Lucia","Saint Martin","Saint Pierre and Miquelon","Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Sint Maarten","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Georgia","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Svalbard and Jan Mayen Islands","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tokelau","Tonga","Trinidad and Tobago","Tunisia","Türkiye","Turkmenistan","Turks and Caicos Islands","Tuvalu","US Minor Outlying Islands","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Venezuela","Vietnam","Virgin Islands, British","Virgin Islands, U.S.","Wallis and Futuna","Western Sahara","Yemen","Zambia","Zimbabwe","\u00c5land Islands"],"U.S. States":["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","District of Columbia","Florida","Georgia","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virginia","Washington","West Virginia","Wisconsin","Wyoming","Armed Forces Americas","Armed Forces Europe","Armed Forces Pacific"],"Canadian Province\/Territory":["Alberta","British Columbia","Manitoba","New Brunswick","Newfoundland and Labrador","Northwest Territories","Nova Scotia","Nunavut","Ontario","Prince Edward Island","Quebec","Saskatchewan","Yukon"],"Continents":["Africa","Antarctica","Asia","Australia","Europe","North America","South America"],"Gender":["Male","Female","Prefer Not to Answer"],"Age":["Under 18","18-24","25-34","35-44","45-54","55-64","65 or Above","Prefer Not to Answer"],"Marital Status":["Single","Married","Divorced","Widowed"],"Employment":["Employed Full-Time","Employed Part-Time","Self-employed","Not employed but looking for work","Not employed and not looking for work","Homemaker","Retired","Student","Prefer Not to Answer"],"Job Type":["Full-Time","Part-Time","Per Diem","Employee","Temporary","Contract","Intern","Seasonal"],"Industry":["Accounting\/Finance","Advertising\/Public Relations","Aerospace\/Aviation","Arts\/Entertainment\/Publishing","Automotive","Banking\/Mortgage","Business Development","Business Opportunity","Clerical\/Administrative","Construction\/Facilities","Consumer Goods","Customer Service","Education\/Training","Energy\/Utilities","Engineering","Government\/Military","Green","Healthcare","Hospitality\/Travel","Human Resources","Installation\/Maintenance","Insurance","Internet","Job Search Aids","Law Enforcement\/Security","Legal","Management\/Executive","Manufacturing\/Operations","Marketing","Non-Profit\/Volunteer","Pharmaceutical\/Biotech","Professional Services","QA\/Quality Control","Real Estate","Restaurant\/Food Service","Retail","Sales","Science\/Research","Skilled Labor","Technology","Telecommunications","Transportation\/Logistics","Other"],"Education":["High School","Associate Degree","Bachelor\'s Degree","Graduate or Professional Degree","Some College","Other","Prefer Not to Answer"],"Days of the Week":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],"Months of the Year":["January","February","March","April","May","June","July","August","September","October","November","December"],"How Often":["Every day","Once a week","2 to 3 times a week","Once a month","2 to 3 times a month","Less than once a month"],"How Long":["Less than a month","1-6 months","1-3 years","Over 3 years","Never used"],"Satisfaction":["Very Satisfied","Satisfied","Neutral","Unsatisfied","Very Unsatisfied"],"Importance":["Very Important","Important","Somewhat Important","Not Important"],"Agreement":["Strongly Agree","Agree","Disagree","Strongly Disagree"],"Comparison":["Much Better","Somewhat Better","About the Same","Somewhat Worse","Much Worse"],"Would You":["Definitely","Probably","Not Sure","Probably Not","Definitely Not"],"Size":["Extra Small","Small","Medium","Large","Extra Large"],"Timezone":["(GMT -12-00) Eniwetok, Kwajalein:-12","(GMT -11-00) Midway Island, Samoa:-11","(GMT -10-00) Hawaii:-10","(GMT -9-00) Alaska:-9","(GMT -8-00) Pacific Time (US & Canada):-8","(GMT -7-00) Mountain Time (US & Canada):-7","(GMT -6-00) Central Time (US & Canada), Mexico City:-6","(GMT -5-00) Eastern Time (US & Canada), Bogota, Lima:-5","(GMT -4-00) Atlantic Time (Canada), Caracas, La Paz:-4","(GMT -3-30) Newfoundland:-3.5","(GMT -3-00) Brazil, Buenos Aires, Georgetown:-3","(GMT -2-00) Mid-Atlantic:-2","(GMT -1-00) Azores, Cape Verde Islands:-1","(GMT) Western Europe Time, London, Lisbon, Casablanca:0","(GMT +1-00) Brussels, Copenhagen, Madrid, Paris:1","(GMT +2-00) Kaliningrad, South Africa:2","(GMT +3-00) Baghdad, Riyadh, Moscow, St. Petersburg:3","(GMT +3-30) Tehran:3.5","(GMT +4-00) Abu Dhabi, Muscat, Baku, Tbilisi:4","(GMT +4-30) Kabul:4.5","(GMT +5-00) Ekaterinburg, Islamabad, Karachi, Tashkent:5","(GMT +5-30) Bombay, Calcutta, Madras, New Delhi:5.5","(GMT +5-45) Kathmandu:5.75","(GMT +6-00) Almaty, Dhaka, Colombo:6","(GMT +7-00) Bangkok, Hanoi, Jakarta:7","(GMT +8-00) Beijing, Perth, Singapore, Hong Kong:8","(GMT +9-00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk:9","(GMT +9-30) Adelaide, Darwin:9.5","(GMT +10-00) Eastern Australia, Guam, Vladivostok:10","(GMT +11-00) Magadan, Solomon Islands, New Caledonia:11","(GMT +12-00) Auckland, Wellington, Fiji, Kamchatka:12"]}',
        ] );

        wp_localize_script( 'contactum-addon', 'contactumAddon', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
        ]);
    }

    public function enqueue_builder_scripts() {
        $screen = get_current_screen();
        
        if ( $screen->base != 'toplevel_page_contactum' ) {
            return ;
        }

        $this->enqueue_styles( $this->get_admin_styles() );
        $this->enqueue_scripts( $this->get_admin_scripts() );

//         $localize_script = $this->get_admin_localized_scripts();
//         wp_localize_script( 'contactum-admin', 'contactum', $localize_script );

    }

    public function register_frontend() {
        $this->register_styles( $this->get_frontend_styles() );
        $this->register_scripts( $this->get_frontend_scripts() );
    }

    public function enqueue_frontend() {
        $this->enqueue_styles( $this->get_frontend_styles() );
        $this->enqueue_scripts( $this->get_frontend_scripts() );

        $localize_script = $this->get_frontend_localized_scripts();

        wp_localize_script( 'contactum-frontend', 'frontend', $localize_script );

        wp_localize_script( 'contactum-frontend', 'error_str_obj', [
            'required'   => __( 'is required', 'contactum' ),
            'mismatch'   => __( 'does not match', 'contactum' ),
            'validation' => __( 'is not valid', 'contactum' ),
            'duplicate'  => __( 'requires a unique entry and this value has already been used', 'contactum' ),
        ] );
    }

    public function get_admin_scripts() {

        $form_builder_js_deps = apply_filters( 'contactum_builder_js_deps', [
            'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable',
            'jquery-ui-resizable', 'underscore', 'contactum-clipboard', 'contactum-sweetalert',
            'contactum-jquery-tooltip', 'contactum-selectize'
        ]);

        $scripts = [
            'contactum-forms' => [
                'src'       => CONTACTUM_ASSETS . '/js/forms.js',
                'deps'      => $form_builder_js_deps,
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/forms.js' ),
                'in_footer' => true
            ],
            'contactum-jquery-scrollto' => [
                'src'       => CONTACTUM_ASSETS . '/js/jquery.scrollTo.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/jquery.scrollTo.js' ),
                'in_footer' => true
            ],
            'contactum-selectize' => [
                'src' =>  CONTACTUM_ASSETS . '/js/selectize.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/selectize.min.js' ),
                'in_footer' => true
            ],
            'contactum-jquery-tooltip' => [
                'src'       => CONTACTUM_ASSETS . '/js/tooltip.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/tooltip.js' ),
                'in_footer' => true
            ],
            'contactum-clipboard' => [
                'src'       => CONTACTUM_ASSETS . '/js/clipboard.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/clipboard.min.js' ),
                'in_footer' => true
            ],
            'contactum-sweetalert' => [
                'src'       => CONTACTUM_ASSETS . '/js/sweetalert2.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/sweetalert2.min.js' ),
                'in_footer' => true
            ]
        ];

        return apply_filters( 'contactum_admin_scripts', $scripts );
    }

    public function get_admin_styles() {
        $styles = [
            'contactum-font-awesome' => [
                'src'  => CONTACTUM_ASSETS . '/css/font-awesome/css/font-awesome.min.css',
            ],
            'contactum-sweetalert2' => [
                'src' =>  CONTACTUM_ASSETS . '/css/sweetalert2.min.css'
            ],
            'contactum-selectize' => [
                'src' =>  CONTACTUM_ASSETS . '/css/selectize.css'
            ],
            'contactum-jquery-tooltip' => [
                'src' =>  CONTACTUM_ASSETS . '/css/tooltip.css'
            ],
            'contactum-admin' => [
                'src' =>  CONTACTUM_ASSETS . '/css/admin.css'
            ],
            'contactum-admin-extra' => [
                'src' =>  CONTACTUM_ASSETS . '/css/contactum-admin.css'
            ],
            'contactum-star' => [
                'src' =>  CONTACTUM_ASSETS . '/css/star.css'
            ]
        ];

        return apply_filters( 'contactum_admin_styles', $styles );
    }

    public function get_frontend_styles() {

        $styles = [
            'contactum-frontend' => [
                'src' =>  CONTACTUM_ASSETS . '/css/frontend.css'
            ],
            'jquery-ui' => [
                'src'  => CONTACTUM_ASSETS . '/css/jquery-ui-1.9.1.custom.css',
            ],
            'contactum-sweetalert2' => [
                'src' =>  CONTACTUM_ASSETS . '/css/sweetalert2.min.css'
            ],
            'contactum-choices' => [
                'src' =>  CONTACTUM_ASSETS . '/css/choices.min.css'
            ],
            'contactum-modal' => [
                'src' =>  CONTACTUM_ASSETS . '/css/jquery.modal.min.css'
            ],
            'contactum-flatpickr' => [
                'src' =>  CONTACTUM_ASSETS . '/css/flatpickr.css'
            ],
            'contactum-star' => [
                'src' =>  CONTACTUM_ASSETS . '/css/star.css'
            ],
            'contactum-intlTelInput' => [
                'src' =>  CONTACTUM_ASSETS . '/libs/intl-tel-input/css/intlTelInput.min.css'
            ],
            'contactum-font-awesome' => [
                'src'  => CONTACTUM_ASSETS . '/css/font-awesome/css/font-awesome.min.css',
            ],
        ];

        return apply_filters( 'contactum_frontend_styles', $styles );
    }

    public function get_frontend_scripts() {

        $scripts = [
            'contactum-frontend' => [
                'src'       => CONTACTUM_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider', 'contactum-choices' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true
            ],
            'contactum-math' => [
                'src'       => CONTACTUM_ASSETS . '/js/math.js',
                'deps'      => [ 'contactum-frontend' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/math.js' ),
                'in_footer' => true
            ],
            'contactum-jquery-ui-timepicker' => [
                'src'       => CONTACTUM_ASSETS . '/js/jquery-ui-timepicker-addon.js',
                'deps'      => [ 'jquery-ui-datepicker' ],
                'in_footer' => true,
            ],
            'contactum-sweetalert' => [
                'src'       => CONTACTUM_ASSETS . '/js/sweetalert2.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/sweetalert2.min.js' ),
                'in_footer' => true
            ],
            'contactum-choices' => [
                'src'       => CONTACTUM_ASSETS . '/js/choices.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/choices.min.js' ),
                'in_footer' => true
            ],
            'contactum-upload' => [
                'src'       => CONTACTUM_ASSETS . '/js/upload.js',
                'deps'      => [ 'jquery', 'plupload-handlers', 'jquery-ui-sortable' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/upload.js' ),
                'in_footer' => true
            ],
            'contactum-conditional' => [
                'src'       => CONTACTUM_ASSETS . '/js/conditional.js',
                'deps'      => [ 'jquery' ],
                'in_footer' => true
            ],
            'contactum-modal' => [
                'src'       => CONTACTUM_ASSETS . '/js/jquery.modal.min.js',
                'deps'      => [ 'jquery' ],
                'in_footer' => true
            ],
            'contactum-flatpickr' => [
                'src'       => CONTACTUM_ASSETS . '/js/flatpickr.js',
                'deps'      => [ 'jquery', 'contactum-frontend' ],
                'in_footer' => true
            ],
            'contactum-mask' => [
                'src'       => CONTACTUM_ASSETS . '/js/jquery.mask.min.js',
                'deps'      => [ 'jquery' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/js/jquery.mask.min.js' ),
                'in_footer' => true
            ],

            'contactum-intlTelInput' => [
                'src'       => CONTACTUM_ASSETS . '/libs/intl-tel-input/js/intlTelInput.min.js',
                'deps'      => [ 'jquery','contactum-frontend' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/libs/intl-tel-input/js/intlTelInput.min.js' ),
                'in_footer' => true
            ],

            'contactum-intlTelInputUtils' => [
                'src'       => CONTACTUM_ASSETS . '/libs/intl-tel-input/js/utils.js',
                'deps'      => [ 'jquery', 'contactum-intlTelInput', 'contactum-frontend' ],
                'version'   => filemtime( CONTACTUM_PATH . '/assets/libs/intl-tel-input/js/utils.js' ),
                'in_footer' => true
            ],
        ];

        return apply_filters( 'contactum_frontend_scripts', $scripts );
    }


    private function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : CONTACTUM_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, CONTACTUM_VERSION );
        }
    }

    public function enqueue_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            wp_enqueue_script( $handle );
        }
    }

    public function enqueue_styles( $styles ) {
        foreach ( $styles as $handle => $script ) {
            wp_enqueue_style( $handle );
        }
    }

    public function addcss($formId, $customcss) {
    if (trim($customcss)) {

        if (!did_action('wp_head')) {
            $action = 'wp_head';
        } elseif (!did_action('wp_footer')) {
            $action = 'wp_footer';
        }

        add_action($action, function () use ($formId, $customcss) {
            ?>
            <style type="text/css">
                    <?php echo contactum_kses_css($customcss); ?>
            </style>
            <?php
        });
    }
    }

    public function addJs($formId, $customJS)
    {
        if (trim($customJS)) {
            add_action('wp_footer', function () use ($formId, $customJS) {
                ?>
                <script type="text/javascript">
                    jQuery(document.body).on('contactum_init_<?php echo esc_attr($formId); ?>',
                        function(event, data) {
                            var $form = jQuery(data);
                            var formId = "<?php echo esc_attr($formId); ?>";
                            var $ = jQuery;
                            try {
                                <?php echo contactum_kses_js($customJS); ?>
                            } catch (e) {
                                console.warn('Error in custom JS of Contactum ID: ' + formId);
                                console.error(e);
                            }
                        });
                </script>
                <?php
            }, 100);
        }
    }
}