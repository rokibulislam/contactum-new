<?php
namespace Contactum\Fields;

use Contactum\Fields\Contactum_Field;

class Field_Range_Slider extends Contactum_Field {

    public function __construct() {
        $this->name       = __( 'Range Slider', 'contactum' );
        $this->input_type = 'range_slider_field';
        $this->icon       = 'sliders';
    }

    public function render( $field_settings, $form_id ) {
        $min     = isset( $field_settings['min_value'] )  ? (float) $field_settings['min_value']  : 0;
        $max     = isset( $field_settings['max_value'] )  ? (float) $field_settings['max_value']  : 100;
        $step    = isset( $field_settings['step_value'] ) ? (float) $field_settings['step_value'] : 1;
        $default = isset( $field_settings['default'] )    ? (float) $field_settings['default']    : $min;
        $prefix  = isset( $field_settings['prefix'] )     ? esc_html( $field_settings['prefix'] ) : '';
        $suffix  = isset( $field_settings['suffix'] )     ? esc_html( $field_settings['suffix'] ) : '';
        $show_val = isset( $field_settings['show_value'] ) && $field_settings['show_value'] === 'yes';
        $field_id = esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id );
        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings, $form_id ); ?>
            <div class="contactum-fields contactum-range-slider-wrap">
                <?php if ( $show_val ) : ?>
                    <output class="contactum-range-output" for="<?php echo $field_id; ?>">
                        <?php echo $prefix; ?><span><?php echo esc_html( $default ); ?></span><?php echo $suffix; ?>
                    </output>
                <?php endif; ?>
                <input
                    id="<?php echo $field_id; ?>"
                    type="range"
                    class="contactum-range-slider <?php echo esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ); ?>"
                    name="<?php echo esc_attr( $field_settings['name'] ); ?>"
                    min="<?php echo esc_attr( $min ); ?>"
                    max="<?php echo esc_attr( $max ); ?>"
                    step="<?php echo esc_attr( $step ); ?>"
                    value="<?php echo esc_attr( $default ); ?>"
                    data-required="<?php echo esc_attr( $field_settings['required'] ); ?>"
                    data-type="range_slider"
                    <?php if ( $show_val ) : ?>
                    oninput="this.previousElementSibling.querySelector('span').textContent = this.value"
                    <?php endif; ?>
                />
                <div class="contactum-range-labels">
                    <span><?php echo $prefix . esc_html( $min ) . $suffix; ?></span>
                    <span><?php echo $prefix . esc_html( $max ) . $suffix; ?></span>
                </div>
            </div>
            <?php $this->help_text( $field_settings ); ?>
        </li>
        <?php
    }

    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();

        $settings = [
            [
                'name'      => 'min_value',
                'title'     => __( 'Min Value', 'contactum' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'basic',
                'priority'  => 12,
                'help_text' => '',
            ],
            [
                'name'      => 'max_value',
                'title'     => __( 'Max Value', 'contactum' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'basic',
                'priority'  => 13,
                'help_text' => '',
            ],
            [
                'name'      => 'step_value',
                'title'     => __( 'Step', 'contactum' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'advanced',
                'priority'  => 9,
                'help_text' => __( 'Increment between selectable values.', 'contactum' ),
            ],
            [
                'name'      => 'default',
                'title'     => __( 'Default Value', 'contactum' ),
                'type'      => 'text',
                'variation' => 'number',
                'section'   => 'basic',
                'priority'  => 14,
                'help_text' => __( 'Initial slider position.', 'contactum' ),
            ],
            [
                'name'          => 'show_value',
                'title'         => __( 'Show Current Value', 'contactum' ),
                'type'          => 'checkbox',
                'is_single_opt' => true,
                'options'       => [
                    'yes' => __( 'Display value above slider', 'contactum' ),
                ],
                'default'   => 'yes',
                'section'   => 'advanced',
                'priority'  => 10,
                'help_text' => '',
            ],
            [
                'name'      => 'prefix',
                'title'     => __( 'Value Prefix', 'contactum' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 11,
                'help_text' => __( 'e.g. $ or €', 'contactum' ),
            ],
            [
                'name'      => 'suffix',
                'title'     => __( 'Value Suffix', 'contactum' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 12,
                'help_text' => __( 'e.g. kg or %', 'contactum' ),
            ],
        ];

        return array_merge( $default_options, $settings );
    }

    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props = [
            'min_value'  => '0',
            'max_value'  => '100',
            'step_value' => '1',
            'default'    => '50',
            'show_value' => 'yes',
            'prefix'     => '',
            'suffix'     => '',
        ];

        return array_merge( $defaults, $props );
    }

    public function prepare_entry( $field, $post_data = [] ) {
        $value = isset( $post_data[ $field['name'] ] ) ? $post_data[ $field['name'] ] : '';
        return floatval( $value );
    }
}
