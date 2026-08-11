<?php
namespace Contactum;

/**
 * Zero-dependency, always-on spam trap: a text field that's invisible to real
 * visitors (hidden off-screen via CSS, not display:none, so bots that skip
 * display:none fields don't get a free pass) but visible to bots that fill
 * every field blindly, plus a minimum-time-to-submit check reusing the
 * existing `_ctm_form_start` hidden field already rendered in every form.
 *
 * Unlike the CleanTalk integration (class-cleantalk-integration.php), this
 * needs no API key and runs entirely locally — the two layers are
 * complementary, not redundant, and both hook the same `contactum_check_spam`
 * filter so they compose without conflict.
 */
class Honeypot {

    const FIELD_NAME = 'ctm_hp_website';

    public function __construct() {
        add_filter( 'contactum_check_spam', [ $this, 'check_spam' ], 10, 4 );
    }

    /**
     * Render the honeypot input. Called from Frontend::render_form() right
     * before the closing </ul>, on every form.
     *
     * @param int $form_id
     * @return void
     */
    public function render( $form_id ) {
        if ( ! apply_filters( 'contactum_honeypot_enabled', true, $form_id ) ) {
            return;
        }
        ?>
        <li class="contactum-honeypot-field" aria-hidden="true">
            <label for="<?php echo esc_attr( self::FIELD_NAME . '_' . $form_id ); ?>">
                <?php esc_html_e( 'Leave this field empty', 'contactum' ); ?>
            </label>
            <input
                type="text"
                id="<?php echo esc_attr( self::FIELD_NAME . '_' . $form_id ); ?>"
                name="<?php echo esc_attr( self::FIELD_NAME ); ?>"
                value=""
                tabindex="-1"
                autocomplete="off"
            />
        </li>
        <?php
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

        if ( ! apply_filters( 'contactum_honeypot_enabled', true, $form_id ) ) {
            return $errors;
        }

        // The bait field was filled in — only a script filling every field
        // blindly would do this, since real visitors never see it.
        if ( ! empty( $post_data[ self::FIELD_NAME ] ) ) {
            $errors['honeypot'] = __( 'Your submission looks automated. Please try again.', 'contactum' );
            return $errors;
        }

        // Submitted implausibly fast after the form was rendered.
        $form_start = isset( $post_data['_ctm_form_start'] ) ? absint( $post_data['_ctm_form_start'] ) : 0;
        if ( ! $form_start ) {
            return $errors;
        }

        $elapsed     = max( 0, time() - $form_start );
        $min_seconds = (int) apply_filters( 'contactum_honeypot_min_seconds', 3, $form_id );

        if ( $elapsed < $min_seconds ) {
            $errors['honeypot'] = __( 'Your submission looks automated. Please try again.', 'contactum' );
        }

        return $errors;
    }
}
