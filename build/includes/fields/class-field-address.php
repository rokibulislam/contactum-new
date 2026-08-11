<?php

namespace Contactum\Fields;
use Contactum\Fields\Contactum_Field;

class Field_Address extends  Contactum_Field {

	public function __construct() {
        $this->name       = __( 'Address', 'contactum' );
        $this->input_type = 'address_field';
        $this->icon       = 'address-card-o';
    }

    public function render( $field_settings, $form_id ) {

        $country_show   = isset( $field_settings['address']['country_select']['country_select_show_list'] )
            ? $field_settings['address']['country_select']['country_select_show_list'] : [];
        $country_hide   = isset( $field_settings['address']['country_select']['country_select_hide_list'] )
            ? $field_settings['address']['country_select']['country_select_hide_list'] : [];
        $visibility     = $field_settings['address']['country_select']['country_list_visibility_opt_name'] ?? 'all';
    ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings ); ?>
            <div class="contactum-fields address-fields">
                <?php foreach ( $field_settings['address'] as $sub_key => $sub ) :
                    $field_name = $field_settings['name'] . '[' . $sub_key . ']';
                    if ( empty( $sub['checked'] ) ) continue;
                    $required_attr = ! empty( $sub['required'] ) ? 'required' : '';
                ?>
                    <div class="contactum-sub-fields">
                        <label class="contact-form-sub-label">
                            <?php echo esc_html( $sub['label'] ); ?>
                            <?php if ( ! empty( $sub['required'] ) ) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ( in_array( $sub['type'], [ 'text', 'hidden', 'email', 'password' ], true ) ) : ?>
                            <input
                                type="<?php echo esc_attr( $sub['type'] ); ?>"
                                name="<?php echo esc_attr( $field_name ); ?>"
                                value=""
                                placeholder="<?php echo esc_attr( $sub['placeholder'] ?? '' ); ?>"
                                class="textfield contactum-el-form-control"
                                size="40"
                                data-subfield="<?php echo esc_attr( $sub_key ); ?>"
                                <?php echo $required_attr; ?>
                            />

                        <?php elseif ( $sub['type'] === 'select' && $sub_key === 'country_select' ) :
                            $select_class = 'ctm-country-' . absint( $form_id ) . '-' . esc_attr( $field_settings['name'] );
                        ?>
                            <select
                                name="<?php echo esc_attr( $field_name ); ?>"
                                data-required="<?php echo esc_attr( $field_settings['required'] ?? '' ); ?>"
                                data-type="select"
                                data-subfield="country_select"
                                class="contactum-el-form-control <?php echo esc_attr( $select_class ); ?>">
                            </select>
                            <?php
                            $countries     = contactum_get_countries();
                            $option_string = '<option value="-1">Select Country</option>';

                            foreach ( $countries as $country ) {
                                if ( $visibility === 'hide' && in_array( $country['code'], $country_hide, true ) ) continue;
                                if ( $visibility === 'show' && ! in_array( $country['code'], $country_show, true ) ) continue;
                                $option_string .= sprintf(
                                    '<option value="%s">%s</option>',
                                    esc_attr( $country['code'] ),
                                    esc_html( $country['name'] )
                                );
                            }

                            $json_opts = wp_json_encode( $option_string );
                            $script    = "(function(){var s=document.querySelector('select.{$select_class}');if(s)s.innerHTML={$json_opts};})();";
                            wp_add_inline_script( 'contactum-frontend', $script );
                        endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </li>
    <?php }

    public function get_options_settings() {
        $default_options = $this->get_default_option_settings( true, array( 'width', 'required' ) );

        $settings = array(
            array(
                'name'          => 'address',
                'title'         => __( 'Address Fields', 'contact' ),
                'type'          => 'address',
                'section'       => 'advanced',
                'priority'      => 21,
                'help_text'     => '',
            )
        );

        return array_merge( $default_options, $settings );

        return $default_options;
    }

    public function get_field_props() {
        $defaults = $this->default_attributes();
        $props    = array(
            'enable_g_autocomplete' => false,
            'enable_g_map'  => false,
            'save_coordinates' => false,
            'address_desc'  => '',
            'autocomplete_provider' => '',
            'address'       => array(
                'street_address'    => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => true,
                    'label'         => __( 'Address', 'contactum' ),
                    'value'         => '',
                    'placeholder'   => ''
                ),

                'city_name'         => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => true,
                    'label'         => __( 'City', 'contactum' ),
                    'value'         => '',
                    'placeholder'   => ''
                ),

                'state'             => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => true,
                    'label'         => __( 'State', 'contactum' ),
                    'value'         => '',
                    'placeholder'   => ''
                ),

                'zip'               => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => true,
                    'label'         => __( 'Zip Code', 'contactum' ),
                    'value'         => '',
                    'placeholder'   => ''
                ),

                'country_select'    => array(
                    'checked'                           => 'checked',
                    'type'                              => 'select',
                    'required'                          => true,
                    'label'                             => __( 'Country', 'contactum' ),
                    'value'                             => '',
                    'country_list_visibility_opt_name'  => 'all',
                    'country_select_hide_list'          => array(),
                    'country_select_show_list'          => array()
                )
            )
        );

        return array_merge( $defaults, $props );
    }

    public function prepare_entry( $field, $post_data = [] ) {

        if ( isset( $post_data[ $field['name'] ] ) && is_array( $post_data[ $field['name'] ] ) ) {
            foreach ( $post_data[ $field['name'] ] as $address_field => $field_value ) {
                $entry_value[ $address_field ] = sanitize_text_field( $field_value );
            }
        }

        return $entry_value;
    }
}