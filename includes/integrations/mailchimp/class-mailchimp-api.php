<?php
namespace Contactum\Integrations;
/**
 *  MailChimp Class
 *
 *  Handle API request and connect with mailchimp
 */
class MailChimpAPI
{
    private $api_key;
    private $api_endpoint = 'https://<dc>.api.mailchimp.com/3.0';
    private $verify_ssl   = false;

    /**
     * Create a new instance
     * @param string $api_key Your MailChimp API key
     */
    function __construct($api_key)
    {
        error_log((print_r($api_key, true)));

        $this->api_key = $api_key;
        list(, $datacentre) = explode('-', $this->api_key);
        $this->api_endpoint = str_replace('<dc>', $datacentre, $this->api_endpoint);
    }

    public function call($method, $args=array())
    {
        return $this->makeRequest('GET', $method, $args);
    }

    public function get($method, $args=array())
    {
        return $this->makeRequest('GET', $method, $args);
    }

    public function post($method, $args=array())
    {
        return $this->makeRequest('POST', $method, $args);
    }

    /**
     * Performs the underlying HTTP request. Not very exciting
     * @param  string $method The API method to be called
     * @param  array  $args   Assoc array of parameters to be passed
     * @return array          Assoc array of decoded result
     */
    private function makeRequest($method, $endpoint, $args=array())
    {
         // error_log(print_r($method, true));
        // exit;
        $json_data = json_encode($args);
        $url       = $this->api_endpoint . '/' . $endpoint;

        if ($method === 'GET' && !empty($args)) {
            $url = add_query_arg($args, $url);
            $body = null;
        } else {
            $body = !empty($args) ? json_encode($args) : null;
        }

        $args = array(
            'headers'   => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $this->api_key,
            ),
            'body'      =>  $body,
            'sslverify' => false,
        );

        switch ( $method ) {
            case 'GET':
                $result = wp_remote_get( $url, $args);
                break;

            case 'POST':
                $result = wp_remote_post( $url, $args);
                break;
        }

        $result = wp_remote_retrieve_body( $result );

        return $result ? json_decode( $result, true ) : false;
    }
}