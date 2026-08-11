<?php
namespace Contactum\Integrations;

/**
 * Minimal Amazon S3 client using AWS Signature Version 4.
 * No external dependencies — uses wp_remote_request throughout.
 */
class S3Client {

    private $access_key;
    private $secret_key;
    private $bucket;
    private $region;

    public function __construct( $access_key, $secret_key, $bucket, $region ) {
        $this->access_key = $access_key;
        $this->secret_key = $secret_key;
        $this->bucket     = $bucket;
        $this->region     = $region ?: 'us-east-1';
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Upload a local file to S3.
     *
     * @param  string $key          S3 object key (path inside bucket).
     * @param  string $file_path    Absolute path to the local file.
     * @param  string $content_type MIME type.
     * @param  string $acl          'public-read' or 'private'.
     * @return array|\WP_Error      ['url' => string, 'key' => string] on success.
     */
    public function put_object( $key, $file_path, $content_type = 'application/octet-stream', $acl = 'public-read' ) {
        if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
            return new \WP_Error( 's3_file_missing', 'Local file not found or not readable.' );
        }

        $body       = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $body_hash  = hash( 'sha256', $body );
        $date_long  = gmdate( 'Ymd\THis\Z' );
        $date_short = gmdate( 'Ymd' );
        $host       = $this->host();
        $uri        = '/' . $this->encode_key( $key );

        $to_sign = [
            'content-length'       => (string) strlen( $body ),
            'content-type'         => $content_type,
            'host'                 => $host,
            'x-amz-acl'            => $acl,
            'x-amz-content-sha256' => $body_hash,
            'x-amz-date'           => $date_long,
        ];

        $auth = $this->authorization( 'PUT', $uri, '', $to_sign, $body_hash, $date_short, $date_long );

        $response = wp_remote_request(
            'https://' . $host . $uri,
            [
                'method'  => 'PUT',
                'body'    => $body,
                'timeout' => 60,
                'headers' => [
                    'Content-Type'         => $content_type,
                    'Content-Length'       => strlen( $body ),
                    'x-amz-acl'            => $acl,
                    'x-amz-content-sha256' => $body_hash,
                    'x-amz-date'           => $date_long,
                    'Authorization'        => $auth,
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code( $response );

        if ( 200 !== $code ) {
            return new \WP_Error(
                's3_put_failed',
                sprintf( 'S3 upload failed (HTTP %d): %s', $code, $this->parse_error( wp_remote_retrieve_body( $response ) ) )
            );
        }

        return [
            'url' => 'https://' . $host . $uri,
            'key' => $key,
        ];
    }

    /**
     * Delete an object from S3.
     *
     * @param  string $key S3 object key.
     * @return bool
     */
    public function delete_object( $key ) {
        // SHA-256 of an empty payload.
        $body_hash  = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
        $date_long  = gmdate( 'Ymd\THis\Z' );
        $date_short = gmdate( 'Ymd' );
        $host       = $this->host();
        $uri        = '/' . $this->encode_key( $key );

        $to_sign = [
            'host'                 => $host,
            'x-amz-content-sha256' => $body_hash,
            'x-amz-date'           => $date_long,
        ];

        $auth = $this->authorization( 'DELETE', $uri, '', $to_sign, $body_hash, $date_short, $date_long );

        $response = wp_remote_request(
            'https://' . $host . $uri,
            [
                'method'  => 'DELETE',
                'timeout' => 30,
                'headers' => [
                    'x-amz-content-sha256' => $body_hash,
                    'x-amz-date'           => $date_long,
                    'Authorization'        => $auth,
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        return 204 === $code || 200 === $code;
    }

    /**
     * Verify credentials and bucket access by writing and deleting a tiny test object.
     *
     * @return array  ['success' => bool, 'message' => string]
     */
    public function test_connection() {
        $test_key  = ltrim( '.contactum-test-' . time(), '/' );
        $test_file = wp_tempnam( 'ctm_s3_test' );

        file_put_contents( $test_file, 'contactum-s3-ok' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

        $result = $this->put_object( $test_key, $test_file, 'text/plain', 'private' );

        @unlink( $test_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

        if ( is_wp_error( $result ) ) {
            return [ 'success' => false, 'message' => $result->get_error_message() ];
        }

        $this->delete_object( $test_key );

        return [ 'success' => true, 'message' => __( 'Connection successful. Your bucket is reachable.', 'contactum' ) ];
    }

    /**
     * Return the public URL for an object key.
     *
     * @param  string $key
     * @return string
     */
    public function object_url( $key ) {
        return 'https://' . $this->host() . '/' . $this->encode_key( ltrim( $key, '/' ) );
    }

    // ── AWS Signature V4 helpers ──────────────────────────────────────────────

    private function authorization( $method, $uri, $query, $headers_map, $body_hash, $date_short, $date_long ) {
        ksort( $headers_map );

        $canonical_headers = '';
        $signed_headers    = '';

        foreach ( $headers_map as $k => $v ) {
            $canonical_headers .= $k . ':' . trim( $v ) . "\n";
            $signed_headers    .= $k . ';';
        }

        $signed_headers = rtrim( $signed_headers, ';' );

        $canonical_request = implode( "\n", [
            $method,
            $uri,
            $query,
            $canonical_headers,
            $signed_headers,
            $body_hash,
        ] );

        $credential_scope = "{$date_short}/{$this->region}/s3/aws4_request";

        $string_to_sign = implode( "\n", [
            'AWS4-HMAC-SHA256',
            $date_long,
            $credential_scope,
            hash( 'sha256', $canonical_request ),
        ] );

        $signature = hash_hmac( 'sha256', $string_to_sign, $this->signing_key( $date_short ) );

        return "AWS4-HMAC-SHA256 Credential={$this->access_key}/{$credential_scope}, SignedHeaders={$signed_headers}, Signature={$signature}";
    }

    private function signing_key( $date_short ) {
        $k_date    = hash_hmac( 'sha256', $date_short,    'AWS4' . $this->secret_key, true );
        $k_region  = hash_hmac( 'sha256', $this->region,  $k_date,    true );
        $k_service = hash_hmac( 'sha256', 's3',           $k_region,  true );
        return hash_hmac( 'sha256', 'aws4_request',        $k_service, true );
    }

    private function host() {
        return "{$this->bucket}.s3.{$this->region}.amazonaws.com";
    }

    /**
     * URI-encode each segment of the key; preserve '/' separators (S3 Sig V4 spec).
     */
    private function encode_key( $key ) {
        return implode( '/', array_map( 'rawurlencode', explode( '/', ltrim( $key, '/' ) ) ) );
    }

    private function parse_error( $xml ) {
        if ( preg_match( '/<Message>(.*?)<\/Message>/s', $xml, $m ) ) {
            return wp_strip_all_tags( $m[1] );
        }
        return 'Unknown S3 error.';
    }
}
