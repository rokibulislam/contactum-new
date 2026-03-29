<?php

namespace Contactum\Fields;
use Contactum\Fields\Contactum_Field;

class Field_Number extends  Contactum_Field {

	public function __construct() {
        $this->name       = __( 'Numeric', '' );
        $this->input_type = 'number_field';
        $this->icon       = 'hashtag';
    }

    public function render( $field_settings, $form_id ) {
        $value = $field_settings['default'];
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>

        <?php
                $this->print_label( $field_settings );
                if( isset($field_settings['enable_calculation']) && $field_settings['enable_calculation'] == 1 ) { 
                printf('<div class="contactum-fields"> <input
                        id="%s"
                        type="number"
                        class="contactum-el-form-control %s"
                        min="%s"
                        max="%s"
                        step="%s"
                        name="%s"
                        placeholder="%s"
                        value="%s"
                        size="%s"
                        data-required="%s"
                        data-type="text"
                        data-calculation="true"
                    /> </div>',
                    esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ),
                    esc_attr( $field_settings['name'] ).'_'. esc_attr( $form_id ),
                    esc_attr($field_settings['min_value_field']),
                    $field_settings['max_value_field'] == 0 ? '' : esc_attr($field_settings['max_value_field']),
                    esc_attr( $field_settings['step_text_field'] ),
                    esc_attr( $field_settings['name'] ),
                    esc_attr( $field_settings['placeholder'] ),
                    esc_attr( $value ),
                    esc_attr( $field_settings['size'] ),
                    esc_attr( $field_settings['required'] ),
                    esc_attr( $value )
                );

            } else {
                
                printf('<div class="contactum-fields"> <input
                        id="%s"
                        type="number"
                        class="contactum-el-form-control %s"
                        min="%s"
                        max="%s"
                        step="%s"
                        name="%s"
                        placeholder="%s"
                        value="%s"
                        size="%s"
                        data-required="%s"
                        data-type="text"
                        data-calculation="false"
                    /> </div>',
                    esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ),
                    esc_attr( $field_settings['name'] ).'_'. esc_attr( $form_id ),
                    esc_attr($field_settings['min_value_field']),
                    $field_settings['max_value_field'] == 0 ? '' : esc_attr($field_settings['max_value_field']),
                    esc_attr( $field_settings['step_text_field'] ),
                    esc_attr( $field_settings['name'] ),
                    esc_attr( $field_settings['placeholder'] ),
                    esc_attr( $value ),
                    esc_attr( $field_settings['size'] ),
                    esc_attr( $field_settings['required'] ),
                ); 
            }

                $this->help_text( $field_settings );
            ?>
        </li>
    <?php }

    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();

        $settings = array(
            array(
                'name'          => 'step_text_field',
                'title'         => __( 'Step', '' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 9,
                'help_text'     => '',
            ),

            array(
                'name'          => 'min_value_field',
                'title'         => __( 'Min Value', '' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 11,
                'help_text'     => '',
            ),

            array(
                'name'          => 'max_value_field',
                'title'         => __( 'Max Value', '' ),
                'type'          => 'text',
                'variation'     => 'number',
                'section'       => 'advanced',
                'priority'      => 13,
                'help_text'     => '',
            ),

            // array(
            //     'name'          => 'duplicate',
            //     'title'         => 'No Duplicates',
            //     'type'          => 'checkbox',
            //     'is_single_opt' => true,
            //     'options'       => array(
            //         'no'   => __( 'Unique Values Only', '' )
            //     ),
            //     'default'       => '',
            //     'section'       => 'advanced',
            //     'priority'      => 23,
            //     'help_text'     => __( 'Select this option to limit user input to unique values only. This will require that a value entered in a field does not currently exist in the entry database for that field.', '' ),
            // ),
        );


        $calculation_options = [
            [
                'name'          => 'enable_calculation',
                'title'         => __( 'Enable Calculation', 'contactum-pro' ),
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => array(
                    'true'   => __( 'Enable Calculation', 'contactum-pro' )
                ),
                'default'       => '',
                'section'       => 'advanced',
                'priority'      => 24,
                'help_text'     => __( 'Select this option to enable calculation in this field.', 'contactum-pro' ),
            ],
            [
                'name'          => 'calculation_options',
                'title'         => __( 'Calculation Options', 'contactum-pro' ),
                'type'          => 'calculation_options',
                'section'       => 'advanced',
                'priority'      => 24,
            ]
        ];

        return array_merge( $default_options, $settings, $calculation_options );
    }

    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props    = array(
            'step_text_field'   => '0',
            'min_value_field'   => '0',
            'max_value_field'   => '0',
            'enable_calculation' => false,
            'formula_field' => ''
            // 'duplicate'         => '',
        );

        return array_merge( $defaults, $props );
    }
}
