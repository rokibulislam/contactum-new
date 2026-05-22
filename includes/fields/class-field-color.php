<?php

namespace Contactum\Fields;

use Contactum\Fields\Contactum_Field;

class Field_Color extends Contactum_Field {

    public function __construct() {
        $this->name       = __( 'Color Picker', 'contactum' );
        $this->input_type = 'color_picker';
        $this->icon       = 'tint';
    }

    public function render( $field_settings, $form_id ) {
        $value = ! empty( $field_settings['default'] ) ? $field_settings['default'] : '#000000';
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>
            <div class="contactum-fields">
                <input
                    type="color"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    id="<?php echo esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                    class="contactum-el-form-control contactum-color-picker <?php echo esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                    value="<?php echo esc_attr( $value ); ?>"
                    data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                    data-type="color"
                    data-errormessage="<?php echo esc_attr( $field_settings['message'] ); ?>"
                />
            </div>
            <?php $this->help_text( $field_settings ); ?>
        </li>
        <?php
    }

    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();

        $color_options = [
            [
                'name'      => 'default',
                'title'     => __( 'Default Color', 'contactum' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => __( 'Default color value (e.g. #ff0000)', 'contactum' ),
            ],
        ];

        return array_merge( $default_options, $color_options );
    }

    public function get_field_props() {
        $defaults = $this->default_attributes();

        return array_merge( $defaults, [
            'default' => '#000000',
        ] );
    }
}
