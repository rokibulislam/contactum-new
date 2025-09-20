<?php

namespace Contactum\Integrations;

use Contactum\Integrations\Contactum_Integration;

use Contactum\Notification as Contactum_Notification;

class MailchimpIntegration extends  Contactum_Integration {

    public $option_key; 
    public $title; 
    public $icon; 
    public $description; 

    public $integrationSettings;

    public function __construct() {
        
        $this->option_key = 'contactum_mailchimp';

        $this->id    = 'mailchimp';
        $this->title = __( 'Mailchimp Settings', 'contactum-pro' );
        $this->icon  = CONTACTUM_ASSETS .'/images/integrations/mailchimp.png';
        $this->description = 'Mailchimp is a marketing platform for small businesses. Send beautiful emails, connect your e-commerce store, advertise, and build your brand. Use Contactum to collect customer information and automatically add it to your Mailchimp campaign list. If you don\'t have a Mailchimp account, you can <a href="https://mailchimp.com/" target="_blank">sign up for one here</a>.';

        add_action( 'contactum_save_global_integration_settings_'.$this->id, [ $this, 'saveGlobalSettings' ], 10, 2 );
        add_action( 'wp_ajax_contactum_'.$this->id.'_update_list', [ $this, 'update_list' ] );
        add_action( 'contactum_entry_submission', array( $this, 'subscribe_user' ), 10, 4 );
    }



    public function get_integration_settings() {

        $this->integrationSettings = [
            [
                'name' => 'list_id',
                'type' => 'list_ajax_options',
                'placeholder' => 'List',
                'label_tips' => __("Select Your List", 'contactum-pro'),
                'label' => __('List', 'contactum-pro'),
                // 'options' => $this->getLists()
                'options' => []
            ],
            [
                'name' => 'email',
                'type' => 'text',
                'placeholder' => 'Email',
                'label_tips' => __("Enter Your Email", 'contactum-pro'),
                'label' => __('Email', 'contactum-pro'),
            ],
            [
                'name' => 'first_name',
                'type' => 'text',
                'placeholder' => 'First Name',
                'label_tips' => __("Enter Your First Name", 'contactum-pro'),
                'label' => __('FirstName', 'contactum-pro'),
            ],
            [
                'name' => 'last_name',
                'type' => 'text',
                'placeholder' => 'Last Name',
                'label_tips' => __("Enter Your Last Name", 'contactum-pro'),
                'label' => __('LastName', 'contactum-pro'),
            ],

            [
                'name'            => 'doubleOptIn',
                'require_list'   => true,
                'label'          => __('Double Opt-in', 'contactum'),
                'tips'           => __('When the double opt-in option is enabled, Mailchimp will send a confirmation email to the user and will only add them to your <br /Mailchimp list upon confirmation.', 'contactum-pro'),
                'type'      => 'checkbox-single',
                'checkbox_label' => __('Enable Double Opt-in', 'contactum'),
            ],
        ];


        return $this->integrationSettings;
        
    }


    public function get_settings_section() {
        
        $this->sections = [
            'id'    => 'mailchimp',
            'title' => '',
            'name' => __( 'Mailchimp', 'contactum-pro' ),
            'icon'  => 'dashicons-admin-appearance',
            'component' => 'general-integration-settings'
        ];

        return $this->sections;
    }

    public function get_settings_fields() {

        $this->settings_fields = [
            'valid_message'    => __('Your Mailchimp API Key is valid', 'contactum-pro'),
            'invalid_message'  => __('Your Mailchimp API Key is not valid', 'contactum-pro'),
            'hide_on_valid'    => true,
            'discard_settings' => [
                'section_description' => __('Your mailchimp API integration is up and running', 'contactum-pro'),
                'button_text'         => __('Disconnect mailchimp', 'contactum-pro'),
                'data'                => [
                    'apiKey' => '',
                ],
                'show_verify' => true,
            ],


            'fields' => [
                [
                    'name' => 'apiKey',
                    'label' => __( 'API Key', 'contactum-pro' ),
                    'desc' => __( 'desc', 'contactum-pro' ),
                    'type' => 'text',
                ]
            ]
        ];

        return $this->settings_fields;
    }


    public function getGlobalSettings()
    {
        $globalSettings = get_option( $this->option_key );
        
        if (! $globalSettings) {
            $globalSettings = [];
        }

        $defaults = [
            'apiKey' => '',
            'status' => '',
        ];

        return wp_parse_args($globalSettings, $defaults);
    }

    public function saveGlobalSettings( $mailChimp, $settings_key )
    {

        if (! $mailChimp['apiKey']) {
            
            $mailChimpSettings = [
                'apiKey' => '',
                'status' => false,
            ];

            update_option( $this->option_key, $mailChimpSettings, 'no');
            
            wp_send_json_success([
                'message' => __('Your settings has been updated and disconnected', 'contactum'),
                'status'  => false,
            ], 200);
        }

        // Verify API key now
        try {
            
            $MailChimp = new MailChimpAPI($mailChimp['apiKey']);
            $result = $MailChimp->get('lists');
            if (! $result ) {
                throw new \Exception($MailChimp->getLastError());
            }
            
        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage(),
            ], 400);
        }

        // Mailchimp key is verified now, Proceed now

        $mailChimpSettings = [
            'apiKey' => sanitize_text_field($mailChimp['apiKey']),
            'status' => true,
        ];

        // Update the reCaptcha details with siteKey & secretKey.
        update_option( $this->option_key, $mailChimpSettings, 'no');

        wp_send_json_success([
            'message' => __('Your mailchimp api key has been verified and successfully set', 'contactum'),
            'status'  => true,
        ], 200);
    }


    private function getLists()
    {
        $settings = get_option($this->option_key);

        try {
            
            $MailChimp = new MailChimpAPI($settings['apiKey']);
            
            $lists = $MailChimp->get('lists');

            if (empty( $lists ) ) {
                return [];
            }
        } catch (\Exception $exception) {
            return [];
        }

        $formattedLists = [];
        foreach ($lists['lists'] as $list) {
            $formattedLists[$list['id']] = $list['name'];
        }

        return $formattedLists;
    }


    public function subscribe_user( $entry_id, $form_id, $page_id, $form_settings ) {

        $integration = contactum_is_integration_active( $form_id, $this->id );

        if ( false === $integration ) {
            return;
        }

        if ( empty( $integration->integration->list_id  ) || empty( $integration->integration->email ) ) {
            return;
        }


        $email = Contactum_Notification::replaceFieldTags( $integration->integration->email, $entry_id );

        if ( empty( $email ) ) {
            return;
        }

        $first_name = Contactum_Notification::replaceNameTag( $integration->integration->first_name, $entry_id );
        $last_name  = Contactum_Notification::replaceNameTag( $integration->integration->last_name, $entry_id );

        
        $interests_obj = array();
        /*
        foreach ( $integration->interests as $key => $interest ) {

            if ( isset( $interest->selected ) && $interests = $interest->selected ) {

                if ( is_array( $interests ) ) {

                    foreach ( $interests as $key => $interest_id ) {
                        $interests_obj[ $interest_id ] = true;
                    }

                } elseif ( is_string( $interests ) ) {

                    $interests_obj[ $interests ] = true;
                }
            }
        }
        */

        error_log(print_r($integration,true));
        error_log(print_r($email,true));
        error_log(print_r($first_name,true));
        error_log(print_r($last_name,true));


        $mailchimp_args = array(
            'email_address' => $email,
            'status'        => 'subscribed',
            'merge_fields'  => array(
                'FNAME' => $first_name,
                'LNAME' => $last_name,
            ),
            // 'double_optin' => $integration->double,
            'double_optin' => true,
        );

        if ( !empty( $interests_obj ) ) {
            $mailchimp_args['interests']   = $interests_obj;
        }

        $settings = $this->getGlobalSettings();

        $mailChimp = new MailChimpAPI( $settings['apiKey'] );

        $response = $mailChimp->post( 'lists/' . $integration->integration->list_id . '/members', $mailchimp_args );

        if ( ! isset( $response['status'] ) || $response['status']  != 'subscribed' ) {
            // contactum()->log('MailChimp: ' . $response);
        }
    }


    public function update_list() {

        check_ajax_referer('contactum-form-builder-nonce');

        $lists = (array) $this->getLists();

        wp_send_json_success( $lists );
    }

}
