<?php

namespace Contactum\Fields;

use Contactum\Fields\Contactum_Field;

class Field_Hcaptcha extends  Contactum_Field {

	public function __construct() {
        $this->name       = __( 'hCaptcha', '' );
        $this->input_type = 'hcaptcha';
        $this->icon       = 'address-card-o';
    }

    public function render( $field_settings, $form_id ) {
        $hCaptcha =  get_option('_contactum_hCaptcha_details');
        $siteKey  = $hCaptcha['siteKey'];

        wp_enqueue_script(
            'hcaptcha',
            'https://js.hcaptcha.com/1/api.js',
            [],
            CONTACTUM_VERSION,
            true
        );

        $hcaptchaBlock = "<li class='contactum-el'> 
            <div
                data-sitekey='" . esc_attr($siteKey) . "'
                id='contactum-hcaptcha-{$form_id}'
                class='contactum-el-hcaptcha h-captcha'
            ></div>
        </li>";

        echo $hcaptchaBlock;
    }

    public static function validate($token, $secret = null)
    {
        $verifyUrl = 'https://hcaptcha.com/siteverify';
        $hCaptcha  =  get_option('_contactum_hCaptcha_details');
        $secret = $secret ?: $hCaptcha['secretKey'];

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

    /**
     * Custom validator
     *
     * @return array
     */
    public function get_validator() {
        return [
            'callback'      => 'has_hcaptcha_api_keys',
            'button_class'  => 'button-faded',
            'msg_title'     => __( 'Site key and Secret key', 'contactum' ),
            'msg'           => sprintf(
                __( 'You need to set Site key and Secret key in <a href="%s" target="_blank">Settings</a> in order to use "hcaptcha" field. <a href="%s" target="_blank">Click here to get the these key</a>.', 'contactum' ),
                admin_url( 'admin.php?page=contactum-settings#hcaptcha' ),
                'https://www.hcaptcha.com/'
              ),
        ];
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
            'image'    => CONTACTUM_ASSETS . '/images/hcaptcha-placeholder.png'
        ];

        return $props;
    }
}