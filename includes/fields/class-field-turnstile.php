<?php

namespace Contactum\Fields;

use Contactum\Fields\Contactum_Field;

class Field_Turnstile extends Contactum_Field {

	public function __construct() {
        $this->name       = __( 'Turnstile', '' );
        $this->input_type = 'turnstile';
        $this->icon       = 'address-card-o';
    }

    public function render( $field_settings, $form_id ) {

        $turnstile  = get_option('_contactum_turnstile_details');

        $siteKey    = $turnstile['siteKey'];
        $siteSecret = $turnstile['secretKey'];
        $theme      = $turnstile['theme'];

        wp_enqueue_script(
            'turnstile',
            'https://challenges.cloudflare.com/turnstile/v0/api.js',
            [],
            CONTACTUM_VERSION,
            true
        );

        $turnstileBlock = "<div
        data-sitekey='" . esc_attr($siteKey) . "'
        data-theme='" . esc_attr($theme) . "'
        id='contactum-turnstile-{$form_id}'
        class='ff-el-turnstile cf-turnstile'
        data-callback='turnstileCallback'></div>";

        echo $turnstileBlock;
    }

    /**
     * Custom validator
     *
     * @return array
     */
    public function get_validator() {
        return [
            'callback'      => 'has_turnstile_api_keys',
            'button_class'  => 'button-faded',
            'msg_title'     => __( 'Site key and Secret key', 'contactum' ),
            'msg'           => sprintf(
                __( 'You need to set Site key and Secret key in <a href="%s" target="_blank">Settings</a> in order to use "turnstile" field. <a href="%s" target="_blank">Click here to get the these key</a>.', 'contactum' ),
                admin_url( 'admin.php?page=contactum-settings#turnstile' ),
                'https://www.cloudflare.com/en-gb/application-services/products/turnstile/'
              ),
        ];
    }


    public static function validate($token, $secret)
    {
        $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $response = wp_remote_post($verifyUrl, [
            'method' => 'POST',
            'body'   => [
                'secret'   => $secret,
                'response' => $token,
            ],
        ]);

        $isValid = false;

        if (!is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response));
            $isValid = $result->success;
        }

        return $isValid;
    }

    public function get_options_settings() {
        $settings = [
            [
                'name'          => 'label',
                'title'         => __( 'Label', 'contactum' ),
                'type'          => 'text',
                'section'       => 'basic',
                'priority'      => 10,
                'help_text'     => __( 'Label of the section', 'contactum' ),
            ]
        ];

        return $settings;
    }

    public function get_field_props() {
        $props = [
            'template' => $this->get_type(),
            'label'    => '',
            'image'    => CONTACTUM_ASSETS . '/images/turnstile-placeholder.png'
        ];

        return $props;
    }
}