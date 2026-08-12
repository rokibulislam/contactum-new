<?php
namespace Contactum;

/**
 * Builds Contactum field-definition arrays from a flat (type, args) shape.
 *
 * This mirrors Importer\Importer_Abstract::get_form_field() — both the
 * importer and the AI form generator are really the same problem (turning
 * an external field list into a saved Contactum form), so this is the
 * shared piece rather than two copies drifting apart. Kept separate from
 * Importer_Abstract itself (rather than refactoring it into a trait there)
 * to avoid touching that already-working, already-tested code for this.
 */
class FieldBuilder {

    /**
     * Default (inactive) conditional-logic block attached to every field.
     *
     * @return array
     */
    public static function default_conditional() {
        return [
            'condition_status' => 'no',
            'cond_field'       => [],
            'cond_operator'    => [ '=' ],
            'cond_option'      => [ '- select -' ],
            'cond_logic'       => 'all',
        ];
    }

    /**
     * Build a single field-definition array.
     *
     * @param string $type One of: text, email, textarea, select, multiselect,
     *                     date, number, url, checkbox, radio, hidden,
     *                     section_break, html, toc, recaptcha, file, name.
     * @param array  $args
     *
     * @return array|null Null if $type isn't recognised.
     */
    public static function field( $type, $args = [] ) {
        $defaults = [
            'required'    => 'no',
            'label'       => '',
            'name'        => '',
            'help'        => '',
            'css_class'   => '',
            'placeholder' => '',
            'value'       => '',
            'default'     => '',
            'options'     => [],
            'step'        => '',
            'min'         => '',
            'max'         => '',
            'extension'   => [],
            'max_size'    => 1024,
            'first_name'  => [],
            'middle_name' => [],
            'last_name'   => [],
            'format'      => 'first-last',
        ];

        $args       = wp_parse_args( $args, $defaults );
        $conditional = self::default_conditional();

        switch ( $type ) {
            case 'text':
                return [
                    'input_type' => 'text',
                    'template'   => 'text_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'placeholder'=> $args['placeholder'],
                    'default'    => $args['default'],
                    'size'       => 40,
                    'contactum_cond' => $conditional,
                ];

            case 'email':
                return [
                    'input_type' => 'email',
                    'template'   => 'email_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'placeholder'=> $args['placeholder'],
                    'default'    => $args['default'],
                    'size'       => 40,
                    'contactum_cond' => $conditional,
                ];

            case 'textarea':
                return [
                    'input_type' => 'textarea',
                    'template'   => 'textarea_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'rows'       => 5,
                    'cols'       => 25,
                    'placeholder'=> $args['placeholder'],
                    'default'    => $args['default'],
                    'rich'       => 'no',
                    'contactum_cond' => $conditional,
                ];

            case 'select':
                return [
                    'input_type' => 'select',
                    'template'   => 'dropdown_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'selected'   => '',
                    'inline'     => 'no',
                    'options'    => $args['options'],
                    'contactum_cond' => $conditional,
                ];

            case 'multiselect':
                return [
                    'input_type' => 'multiselect',
                    'template'   => 'multiple_select',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'selected'   => '',
                    'first'      => __( '- select -', 'contactum' ),
                    'options'    => $args['options'],
                    'contactum_cond' => $conditional,
                ];

            case 'date':
                return [
                    'input_type' => 'date',
                    'template'   => 'date_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'format'     => 'dd/mm/yy',
                    'time'       => '',
                    'contactum_cond' => $conditional,
                ];

            case 'number':
                return [
                    'input_type'      => 'numeric_text',
                    'template'        => 'number_field',
                    'required'        => $args['required'],
                    'label'           => $args['label'],
                    'name'            => $args['name'],
                    'is_meta'         => 'yes',
                    'help'            => $args['help'],
                    'css'             => $args['css_class'],
                    'placeholder'     => $args['placeholder'],
                    'default'         => $args['value'],
                    'size'            => 40,
                    'step_text_field' => $args['step'],
                    'min_value_field' => $args['min'],
                    'max_value_field' => $args['max'],
                    'contactum_cond'  => $conditional,
                ];

            case 'url':
                return [
                    'input_type' => 'url',
                    'template'   => 'url_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'placeholder'=> $args['placeholder'],
                    'default'    => '',
                    'size'       => 40,
                    'contactum_cond' => $conditional,
                ];

            case 'checkbox':
                return [
                    'input_type' => 'checkbox',
                    'template'   => 'checkbox_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'selected'   => '',
                    'inline'     => 'no',
                    'options'    => $args['options'],
                    'contactum_cond' => $conditional,
                ];

            case 'radio':
                return [
                    'input_type' => 'radio',
                    'template'   => 'radio_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'selected'   => '',
                    'inline'     => 'no',
                    'options'    => $args['options'],
                    'contactum_cond' => $conditional,
                ];

            case 'hidden':
                return [
                    'input_type' => 'hidden',
                    'template'   => 'hidden_field',
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'contactum_cond' => $conditional,
                ];

            case 'section_break':
                return [
                    'input_type' => 'section_break',
                    'template'   => 'section_break',
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'contactum_cond' => $conditional,
                ];

            case 'html':
                return [
                    'input_type' => 'html',
                    'template'   => 'html_field',
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'html'       => $args['default'],
                    'contactum_cond' => $conditional,
                ];

            case 'toc':
                return [
                    'input_type'    => 'toc',
                    'template'      => 'toc',
                    'required'      => $args['required'],
                    'name'          => $args['name'],
                    'description'   => $args['label'],
                    'is_meta'       => 'yes',
                    'show_checkbox' => true,
                    'contactum_cond' => $conditional,
                ];

            case 'recaptcha':
                return [
                    'input_type'     => 'recaptcha',
                    'template'       => 'recaptcha',
                    'required'       => $args['required'],
                    'label'          => $args['label'],
                    'name'           => $args['name'],
                    'recaptcha_type' => 'enable_no_captcha',
                    'is_meta'        => 'yes',
                    'help'           => '',
                    'css'            => $args['css_class'],
                    'size'           => 40,
                    'contactum_cond' => $conditional,
                ];

            case 'file':
                return [
                    'input_type' => 'file_upload',
                    'template'   => 'file_field',
                    'required'   => $args['required'],
                    'label'      => $args['label'],
                    'name'       => $args['name'],
                    'is_meta'    => 'yes',
                    'help'       => $args['help'],
                    'css'        => $args['css_class'],
                    'max_size'   => $args['max_size'],
                    'count'      => '1',
                    'extension'  => $args['extension'],
                    'contactum_cond' => $conditional,
                ];

            case 'name':
                return [
                    'input_type'  => 'name',
                    'template'    => 'name_field',
                    'required'    => $args['required'],
                    'label'       => $args['label'],
                    'name'        => $args['name'],
                    'is_meta'     => 'yes',
                    'format'      => $args['format'],
                    'first_name'  => wp_parse_args( $args['first_name'], [ 'placeholder' => '', 'default' => '', 'sub' => __( 'First', 'contactum' ) ] ),
                    'middle_name' => wp_parse_args( $args['middle_name'], [ 'placeholder' => '', 'default' => '', 'sub' => __( 'Middle', 'contactum' ) ] ),
                    'last_name'   => wp_parse_args( $args['last_name'], [ 'placeholder' => '', 'default' => '', 'sub' => __( 'Last', 'contactum' ) ] ),
                    'hide_subs'   => false,
                    'help'        => $args['help'],
                    'css'         => $args['css_class'],
                    'contactum_cond' => $conditional,
                ];

            default:
                return null;
        }
    }

    /**
     * Default form settings, same shape Importer_Abstract uses.
     *
     * @return array
     */
    public static function default_form_settings() {
        return [
            'redirect_to'           => 'same',
            'message'               => __( 'Thanks for contacting us! We will get in touch with you shortly.', 'contactum' ),
            'page_id'               => '',
            'url'                   => '',
            'submit_text'           => __( 'Submit', 'contactum' ),
            'schedule_form'         => 'false',
            'schedule_start'        => '',
            'schedule_end'          => '',
            'humanpresence_enabled' => false,
            'sc_pending_message'    => __( 'Form submission hasn\'t been started yet', 'contactum' ),
            'sc_expired_message'    => __( 'Form submission is now closed.', 'contactum' ),
            'require_login'         => 'false',
            'req_login_message'     => __( 'You need to login to submit a query.', 'contactum' ),
            'limit_entries'         => 'false',
            'limit_number'          => '1000',
            'limit_message'         => __( 'Sorry, we have reached the maximum number of submissions.', 'contactum' ),
            'label_position'        => 'above',
        ];
    }
}
