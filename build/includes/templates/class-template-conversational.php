<?php
namespace Contactum\Templates;

use Contactum\Templates\Contactum_Form_Template;
/**
 * Contactum form template
 */
class Template_Conversational extends Contactum_Form_Template {

    public function __construct() {
        parent::__construct();

        $this->enabled     = true;
        $this->title       = __( 'Conversational Form', 'contactum' );
        $this->description = __( 'One question at a time — visitors answer a single field per screen and move on automatically.', 'contactum' );
        $this->image       = CONTACTUM_ASSETS . '/images/form-template/blank.png';
        $this->category    = 'default';
    }

    /**
     * Get the form fields
     *
     * @return array
     */
    public function get_form_fields() {
        $all_fields = $this->get_register_fields();

        $form_fields = [
            array_merge( $all_fields['name_field']->get_field_props(), [
                'required' => 'yes',
                'name'     => 'name',
            ] ),
            array_merge( $all_fields['email_field']->get_field_props(), [
                'required' => 'yes',
                'name'     => 'email',
            ] ),
            array_merge( $all_fields['radio_field']->get_field_props(), [
                'required' => 'yes',
                'label'    => __( 'How can we help?', 'contactum' ),
                'name'     => 'reason',
                'options'  => [
                    [ 'label' => __( 'Sales question', 'contactum' ), 'value' => 'sales', 'photo' => '', 'calc_value' => 0 ],
                    [ 'label' => __( 'Support request', 'contactum' ), 'value' => 'support', 'photo' => '', 'calc_value' => 0 ],
                    [ 'label' => __( 'Something else', 'contactum' ), 'value' => 'other', 'photo' => '', 'calc_value' => 0 ],
                ],
            ] ),
            array_merge( $all_fields['textarea_field']->get_field_props(), [
                'required' => 'yes',
                'label'    => __( 'Tell us more', 'contactum' ),
                'name'     => 'message',
            ] ),
        ];

        return $form_fields;
    }

    /**
     * Conversational Form's whole point is the one-question-at-a-time
     * display — turn the setting on by default so the template is usable
     * immediately, without a trip to Form Settings first.
     *
     * @return array
     */
    public function get_form_settings() {
        return array_merge( parent::get_form_settings(), [
            'conversational_mode' => 'true',
        ] );
    }
}
