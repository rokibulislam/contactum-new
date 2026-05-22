<?php
namespace Contactum;

use Contactum\integrations\MailchimpIntegration;
use Contactum\Integrations\WebhookIntegration;
use Contactum\Integrations\CleanTalkIntegration;
use Contactum\Integrations\S3Integration;

class IntegrationManager {


    public function __construct() {

        add_action( 'wp_ajax_contactum_save_global_integrations', [ $this, 'save_global_integrations' ] );

        add_action( 'wp_ajax_contactum_get_admin_integrations', [ $this, 'contactum_get_contactum_get_admin_integrations' ] );

        add_action( 'wp_ajax_contactum_get_integrations', [ $this, 'contactum_get_integrations' ] );
    }

    public function contactum_get_contactum_get_admin_integrations() {
        check_ajax_referer('contactum-form-builder-nonce');

        $settings = $_POST['settings_key'];

        $integrations = contactum()->integrations->get_integration_js_settings();

        wp_send_json_success( $integrations[$settings] );
    }


    public function contactum_get_integrations() {
         check_ajax_referer('contactum-form-builder-nonce');
         $post_id            = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
         $form               = contactum()->forms->get( $post_id );
         $integrations        = $form ? $form->get_integrations() : [];

         wp_send_json_success( $integrations );
    }

    public function save_global_integrations() {
        check_ajax_referer('contactum-form-builder-nonce');

        $settings_key = $_POST['settings_key'];
        $integration = $_POST['integration'];

        $data = [
            'settings_key' => $settings_key,
            'integration'  => $integration
        ];



        do_action( 'contactum_save_global_integration_settings_'. $settings_key, $integration , $settings_key );

        wp_send_json_success( $data ); 
    }

    /**
     * The integration instances
     *
     * @var array
     */
    public $integrations = [];

    public function getIntegration( $integration_type ) {
		$integrations = $this->getIntegrations();

		if ( array_key_exists( $integration_type, $integrations ) ) {
            return $integrations[ $integration_type ];
        }

        return false;
	}

    /**
     * Return loaded integrations.
     *
     * @return array
     */
    public function getIntegrations() {
        $integrations = array();

        $integrations['mailchimp']  = new MailchimpIntegration();
        $integrations['webhook']    = new WebhookIntegration();
        $integrations['cleantalk']  = new CleanTalkIntegration();
        $integrations['s3']         = new S3Integration();

        $this->integrations = apply_filters( 'contactum_integrations', $integrations );


        return $this->integrations;
    }

    public function get_integration_js_settings() {
        $settings = [];
        $integrations = $this->getIntegrations();
        
        $security_only = [ 'cleantalk' ];

        if( !empty( $integrations ) ) {
            foreach ( $this->getIntegrations() as $integration_id => $integration ) {
                if ( in_array( $integration_id, $security_only, true ) ) {
                    continue;
                }
                if ( method_exists( $integration, 'get_js_settings' ) ) {
                    $settings[ $integration_id ] = $integration->get_js_settings();
                }
            }
        }

        return $settings;
    }
}