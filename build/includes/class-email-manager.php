<?php
namespace Contactum;

class EmailManager  {

    private $gateway = null;

    public function __construct() {

        $this->gateway = $this->getSendingGateway();
    }

    public function registerGateways() {

        $gateways = apply_filters( 'contactum_register_email_gateways', [
            'wordpress' => new Contactum_Emailer_WPMail(),
        ] );

        return $gateways;
    }



    public function getSendingGateway() {

        $gateway = 'wordpress';

        $available_gateways = $this->registerGateways();

        if ( array_key_exists( $gateway, $available_gateways ) ) {

            return $available_gateways[ $gateway ];
        }
    }

    public function send( $to, $subject, $body, $headers ) {
        
        return $this->gateway->send( $to, $subject, $body, $headers );
    }
}
