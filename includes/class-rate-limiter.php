<?php
namespace Contactum;

/**
 * Per-IP submission rate limiting — a different layer than Honeypot
 * (class-honeypot.php): the honeypot catches bots that fill hidden fields,
 * this catches rapid repeat submissions from the same IP regardless of
 * whether they'd otherwise look human (manual spam, a stuck retry loop, a
 * script hammering the endpoint).
 *
 * Zero-configuration and always-on by default, same philosophy as the
 * honeypot, tuned via filters rather than a settings UI. Tracked per
 * IP+form (not globally) so heavy legitimate traffic on one form never
 * counts against a visitor's limit on a different form.
 */
class RateLimiter {

    public function __construct() {
        add_filter( 'contactum_check_spam', [ $this, 'check_spam' ], 10, 4 );
    }

    private function transient_key( $form_id, $ip ) {
        return 'contactum_rl_' . $form_id . '_' . md5( $ip );
    }

    /**
     * Hooked to `contactum_check_spam`.
     *
     * @param array $errors
     * @param int   $form_id
     * @param array $entry_fields
     * @param array $post_data
     * @return array
     */
    public function check_spam( $errors, $form_id, $entry_fields, $post_data ) {
        if ( ! empty( $errors ) ) {
            return $errors;
        }

        if ( ! apply_filters( 'contactum_rate_limit_enabled', true, $form_id ) ) {
            return $errors;
        }

        $ip = contactum_get_client_ip();
        if ( ! $ip ) {
            return $errors;
        }

        $limit  = (int) apply_filters( 'contactum_rate_limit_max', 5, $form_id );
        $window = (int) apply_filters( 'contactum_rate_limit_window', 10 * MINUTE_IN_SECONDS, $form_id );
        $key    = $this->transient_key( $form_id, $ip );
        $bucket = get_transient( $key );
        $now    = time();

        // A fixed window keyed off when it actually started, not just the
        // transient's own TTL — set_transient() re-arms the TTL on every
        // write, so relying on that alone would let a steady drip of
        // submissions push the window back indefinitely and never trip.
        if ( ! is_array( $bucket ) || ! isset( $bucket['start'] ) || ( $now - $bucket['start'] ) >= $window ) {
            $bucket = [ 'start' => $now, 'count' => 0 ];
        }

        if ( $bucket['count'] >= $limit ) {
            $errors['rate_limit'] = __( 'Too many submissions from your network. Please wait a few minutes and try again.', 'contactum' );
            return $errors;
        }

        $bucket['count']++;
        set_transient( $key, $bucket, $window );

        return $errors;
    }
}
