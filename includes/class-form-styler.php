<?php
namespace Contactum;

/**
 * Orchestrates the Advanced Form Styler: AJAX get/save, caching the
 * generated CSS on the form itself (regenerated only on save, not on every
 * page load), and injecting it — plus a picked preset skin — on the
 * frontend. Mirrors Fluent Forms Pro's FormStyler.php orchestration, ported
 * to Contactum's own data model and hook names.
 */
class FormStyler {

    const META_STYLES   = '_contactum_form_styles';
    const META_SELECTED = '_contactum_selected_style';
    const META_CSS      = '_contactum_form_styler_css';

    /** @var FormStylerGenerator */
    private $generator;

    public function __construct() {
        $this->generator = new FormStylerGenerator();

        add_action( 'wp_ajax_contactum_get_form_styler', [ $this, 'get_styler' ] );
        add_action( 'wp_ajax_contactum_save_form_styler', [ $this, 'save_styler' ] );
        add_action( 'wp_ajax_contactum_preview_form_styler', [ $this, 'preview_styler' ] );

        // contactum_form_fields_top already fires once per rendered form,
        // early in Frontend::render_form() — wp_add_inline_style() defers
        // actual output to <head> regardless of where it's called from, so
        // this needs no changes to the core render path at all.
        add_action( 'contactum_form_fields_top', [ $this, 'enqueue_form_css' ], 10, 2 );
    }

    // ── Presets ──────────────────────────────────────────────────────────────

    public function get_presets() {
        $presets = [
            'ctm_modern' => [
                'label' => __( 'Modern', 'contactum' ),
                'src'   => CONTACTUM_ASSETS . '/css/styler-presets/modern.css',
            ],
            'ctm_minimal' => [
                'label' => __( 'Minimal', 'contactum' ),
                'src'   => CONTACTUM_ASSETS . '/css/styler-presets/minimal.css',
            ],
        ];

        return apply_filters( 'contactum_styler_presets', $presets );
    }

    // ── Frontend output ──────────────────────────────────────────────────────

    public function enqueue_form_css( $form, $form_fields ) {
        $form_id = is_object( $form ) ? $form->getId() : (int) $form;
        if ( ! $form_id ) {
            return;
        }

        $selected = get_post_meta( $form_id, self::META_SELECTED, true );

        if ( $selected && 'ctm_custom' !== $selected ) {
            $presets = $this->get_presets();
            if ( isset( $presets[ $selected ] ) ) {
                wp_enqueue_style( 'contactum-styler-' . $selected, $presets[ $selected ]['src'], [], CONTACTUM_VERSION );
            }
        }

        $css = get_post_meta( $form_id, self::META_CSS, true );
        if ( $css ) {
            wp_add_inline_style( 'contactum-frontend', $css );
        }
    }

    // ── AJAX ─────────────────────────────────────────────────────────────────

    private function check_admin() {
        check_ajax_referer( 'contactum-form-builder-nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Unauthorized operation', 'contactum' ) ] );
        }
    }

    public function get_styler() {
        $this->check_admin();

        $form_id = absint( $_POST['form_id'] ?? 0 );
        if ( ! $form_id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid form ID', 'contactum' ) ] );
        }

        $form = \contactum()->forms->get( $form_id );

        wp_send_json_success( [
            'styles'          => get_post_meta( $form_id, self::META_STYLES, true ) ?: new \stdClass(),
            'selected_style'  => get_post_meta( $form_id, self::META_SELECTED, true ) ?: '',
            'presets'         => $this->get_presets(),
            'preview_url'     => contactum_get_form_preview_url( $form_id ),
            'has_step'        => (bool) $form->hasField( 'step_field' ),
            'has_section_break' => (bool) $form->hasField( 'section_break' ),
            'has_grid'        => (bool) $form->hasField( 'checkbox_grid' ) || (bool) $form->hasField( 'multiple_choice_grid' ),
            'has_range_slider' => (bool) $form->hasField( 'range_slider_field' ),
            'has_checkable'   => (bool) $form->hasField( 'checkbox_field' ) || (bool) $form->hasField( 'radio_field' ),
        ] );
    }

    public function save_styler() {
        $this->check_admin();

        $form_id = absint( $_POST['form_id'] ?? 0 );
        if ( ! $form_id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid form ID', 'contactum' ) ] );
        }

        $selected = isset( $_POST['selected_style'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_style'] ) ) : '';
        $raw      = isset( $_POST['styles'] ) ? wp_unslash( $_POST['styles'] ) : '{}';
        $styles = $this->parse_posted_styles( $raw );

        update_post_meta( $form_id, self::META_STYLES, $styles );
        update_post_meta( $form_id, self::META_SELECTED, $selected );

        $css = ( 'ctm_custom' === $selected || ! $selected )
            ? $this->generator->generate( '.contactum_form_' . $form_id, $styles )
            : '';

        update_post_meta( $form_id, self::META_CSS, $css );

        wp_send_json_success( [
            'message' => __( 'Styles saved.', 'contactum' ),
            'css'     => $css,
        ] );
    }

    /**
     * Generate CSS from *unsaved* draft styles — lets the Styler UI show a
     * live preview as the admin edits, without writing anything until they
     * actually click Save.
     */
    public function preview_styler() {
        $this->check_admin();

        $form_id = absint( $_POST['form_id'] ?? 0 );
        if ( ! $form_id ) {
            wp_send_json_error( [ 'message' => __( 'Invalid form ID', 'contactum' ) ] );
        }

        $selected = isset( $_POST['selected_style'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_style'] ) ) : '';
        $raw      = isset( $_POST['styles'] ) ? wp_unslash( $_POST['styles'] ) : '{}';
        $styles   = $this->parse_posted_styles( $raw );

        $css = ( 'ctm_custom' === $selected || ! $selected )
            ? $this->generator->generate( '.contactum_form_' . $form_id, $styles )
            : '';

        $preset_url = '';
        if ( $selected && 'ctm_custom' !== $selected ) {
            $presets = $this->get_presets();
            if ( isset( $presets[ $selected ] ) ) {
                $preset_url = $presets[ $selected ]['src'];
            }
        }

        wp_send_json_success( [
            'css'        => $css,
            'preset_url' => $preset_url,
        ] );
    }

    private function parse_posted_styles( $raw ) {
        $styles = json_decode( $raw, true );

        if ( ! is_array( $styles ) ) {
            $styles = [];
        }

        return $this->sanitize_styles( $styles );
    }

    /**
     * Recursively strip anything that isn't a plain scalar/array — the
     * generator only ever reads known scalar keys out of this structure, but
     * sanitizing defensively here means a malformed payload can't smuggle
     * something unexpected into post meta.
     *
     * @param array $styles
     * @return array
     */
    private function sanitize_styles( array $styles ) {
        array_walk_recursive( $styles, function ( &$value ) {
            if ( is_string( $value ) ) {
                $value = sanitize_text_field( $value );
            }
        } );

        return $styles;
    }
}
