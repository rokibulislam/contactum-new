<?php
namespace Contactum;

use Contactum\Fields\Field_Hcaptcha as HCaptcha;
use Contactum\Fields\Field_Turnstile as Turnstile;
use Contactum\Fields\Field_Recaptcha as ReCaptcha;


class SettingsManager {

    public function __construct() {

        add_action( 'wp_ajax_contactum_get_global_settings', [ $this, 'getGlobalSetting' ] );

        add_action( 'wp_ajax_contactum_save_global_settings', [ $this, 'save_global_settings' ] );

        add_action( 'contactum_save_global_settings_reCaptcha', [ $this, 'storeReCaptcha' ] );

        add_action( 'contactum_save_global_settings_hCaptcha', [ $this, 'storeHCaptcha' ] );

        add_action( 'contactum_save_global_settings_turnstile', [ $this, 'storeTurnstile' ] );
    }

    public function getGlobalSetting()
    {
        check_ajax_referer('contactum-form-builder-nonce'); // security check

        $values = [];
        $key = $_POST['key'];

        if (is_array($key)) {
            foreach ($key as $key_item) {
                $sanitizedKey = sanitize_text_field($key_item);
                $values[$key_item] = get_option($sanitizedKey);
            }
        } else {
            $values[$key] = get_option($key);
        }

        $values = apply_filters('contactum_get_global_settings_values', $values, $key);

        wp_send_json_error([
            'settings_key' => $key,
            'settings' => $values
        ]);

    }

    public function save_global_settings() {
        check_ajax_referer('contactum-form-builder-nonce'); // security check

        $settings_key = sanitize_text_field($_POST['settings_key']);
        $settings = $_POST['settings']; // Validate more as needed

         error_log(print_r( 'contactum_save_global_settings_'. $settings_key, true ) );

        do_action( 'contactum_save_global_settings_'.$settings_key, $settings );

        // update_option('contactum_' . $settings_key, $settings);

        wp_send_json_success([  
            'settings_key' => $settings_key,
            'settings' => $settings
        ]);

    }

    public function storeReCaptcha( $settings )
    {   

        // Get the full POST array
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        // $data = isset($_POST['settings']) ? $_POST['settings'] : [];
        $data = $settings;

        error_log(print_r($data, true));

        if ('clear-settings' == $action) {
            
            delete_option('_contactum_reCaptcha_details');
            update_option('_contactum_reCaptcha_keys_status', false, 'no');

            return wp_send_json_success([
                'message' => __('Your reCAPTCHA settings are deleted.', 'contactum'),
                'status'  => false,
            ]);
        }

        // Extract fields from POST with sanitization
        $token     = isset($data['token']) ? sanitize_text_field($data['token']) : '';
        $secretKey = isset($data['secretKey']) ? sanitize_text_field($data['secretKey']) : '';
        $apiVersion = isset($data['api_version']) ? sanitize_text_field($data['api_version']) : 'v2_visible';

        // Validate API version
        $allowed_versions = ['v2_visible', 'v3_invisible'];
        
        if (!in_array($apiVersion, $allowed_versions, true)) {
            $apiVersion = 'v2_visible';
        }


        if ($token) {
            $version = isset($data['api_version']) ? sanitize_text_field($data['api_version']) : 'v2_visible';
            $status = ReCaptcha::validate($token, $secretKey, $version);

            if ($status) {
                
                $siteKey   = isset($data['siteKey']) ? sanitize_text_field($data['siteKey']) : '';
                
                $captchaData = [
                    'siteKey'     => $siteKey,
                    'secretKey'   => $secretKey,
                    'api_version' => $apiVersion,
                ];

                update_option('_contactum_reCaptcha_details', $captchaData, 'no');
                update_option('_contactum_reCaptcha_keys_status', $status, 'no');
                
                return wp_send_json_success([
                    'message' => __('Your reCAPTCHA is valid and saved.', 'contactum'),
                    'status'  => $status,
                ]);

            } else {
                $message = __('Sorry, Your reCAPTCHA is not valid. Please try again', 'contactum');
            }

        } else {
           
            $message = __('Please validate your reCAPTCHA first and then hit save. test', 'contactum');
            
            $status = get_option('_contactum_reCaptcha_keys_status');
           
            if ($status) {
                $message = __('Your reCAPTCHA details are already valid. So no need to save again.', 'contactum');
            }
        }

        return wp_send_json_error([
            'message' => $message,
            'status'  => $status,
        ]);
    }


    public function storeHCaptcha( $settings ) {

        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        $data = $settings;

        if ('clear-settings' == $action) {

            delete_option('_contactum_hCaptcha_details');

            update_option('_contactum_hCaptcha_keys_status', false, 'no');

            return wp_send_json_success([
                'message' => __('Your hCaptcha settings are deleted.', 'contactum'),
                'status'  => false,
            ]);
        }


        // Extract fields from POST with sanitization
        $token     = isset($data['token']) ? sanitize_text_field($data['token']) : '';
        $secretKey = isset($data['secretKey']) ? sanitize_text_field($data['secretKey']) : '';

        if ($token) {
            
            $status = HCaptcha::validate($token, $secretKey);

            if ($status) {

                $captchaData = [
                    'siteKey'   => sanitize_text_field( $data['siteKey'] ),
                    'secretKey' => $secretKey,
                ];

                update_option('_contactum_hCaptcha_details', $captchaData, 'no');
                update_option('_contactum_hCaptcha_keys_status', $status, 'no');

                return wp_send_json_success([
                    'message' => __('Your hCaptcha is valid and saved.', 'contactum'),
                    'status'  => $status,
                ]);
            } else {
                $message = __('Sorry, Your hCaptcha is not valid, Please try again', 'contactum');
            }
        } else {
            
            $message = __('Please validate your hCaptcha first and then hit save.', 'contactum');
            $status = get_option('_contactum_hCaptcha_keys_status');
            
            if ($status) {
                $message = __('Your hCaptcha details are already valid, So no need to save again.', 'contactum');
            }
        }

        return wp_send_json_success([
            'message' => $message,
            'status'  => $status,
        ]);
    }


    public function storeCleantalk( $settings )
    {
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';

        $data = $settings;

        if ('clear-settings' == $action ) {
           
            delete_option('_contactum_cleantalk_details');

            return wp_send_json_success([
                'message' => __('Your CleanTalk settings are deleted.', 'contactum'),
                'status'  => false,
            ]);
        }


        // Extract fields from POST with sanitization
        $accessKey     = isset($data['tokaccessKeyen']) ? sanitize_text_field($data['accessKey']) : '';
        $validation = isset($data['validation']) ? sanitize_text_field($data['validation']) : '';

        $status = false;

        if ($accessKey) {
            
            $status = cleantalk_validate($accessKey);
            
            if ($status) {
                
                $captchaData = [
                    'accessKey'   => sanitize_text_field($accessKey),
                    'status'      => true,
                    'validation'  => $validation
                ];

                update_option('_contactum_cleantalk_details', $captchaData, 'no');

                return wp_send_json_success([
                    'message' => __('Your CleanTalk is valid and saved.', 'contactum'),
                    'status'  => $status,
                ]);
            }
        }

        $message = __('Sorry, Your CleanTalk is not valid, Please try again', 'contactum');

        $captchaData = [
            'accessKey'   => '',
            'status'      => $status,
            'validation' => ''
        ];
        
        update_option('_contactum_cleantalk_details', $captchaData, 'no');

        return wp_send_json_success([
            'message' => $message,
            'status'  => $status,
        ]);
    }

    public function storeTurnstile($settings)
    {
        $action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '';
        $data = $settings; 

        if ('clear-settings' == $action ) {

            delete_option('_contactum_turnstile_details');
            update_option('_contactum_turnstile_keys_status', false, 'no');

            return wp_send_json_success([
                'message' => __('Your Turnstile settings are deleted.', 'contactum'),
                'status'  => false,
            ]);
        }

        // Extract fields from POST with sanitization
        $token     = isset($data['token']) ? sanitize_text_field($data['token']) : '';
        $secretKey = isset($data['secretKey']) ? sanitize_text_field($data['secretKey']) : '';

        $captchaData = [
            'siteKey'    => sanitize_text_field($data['siteKey']),
            'secretKey'  => $secretKey,
            'invisible'  => 'no',
            'appearance' => sanitize_text_field($data['appearance']),
            'theme'      => sanitize_text_field($data['theme']),

        ];

        if ($token) {
          
            $status = Turnstile::validate($token, $secretKey);

            if ($status) {
            
                update_option('_contactum_turnstile_details', $captchaData, 'no');
                update_option('_contactum_turnstile_keys_status', $status, 'no');
              
                return wp_send_json_success([
                    'message' => __('Your Turnstile Keys are valid. and saved', 'contactum'),
                    'status'  => $status,
                ]);

            } else {
                $message = __('Sorry, Your Turnstile Keys are not valid. Please try again!', 'contactum');
            }
        } else {
            
            $message = __('Please validate your Turnstile first and then hit save.', 'contactum');
            
            $status = get_option('_contactum_turnstile_keys_status');

            if ( $status ) {
                
                update_option('_contactum_turnstile_details', $captchaData, 'no');
                
                $message = __('Your Turnstile settings is saved.', 'contactum');

                return wp_send_json_error([
                    'message' => $message,
                    'status'  => $status,
                ]);
            }
        }

        return wp_send_json_success([
            'message' => $message,
            'status'  => $status,
        ]);
    }
}