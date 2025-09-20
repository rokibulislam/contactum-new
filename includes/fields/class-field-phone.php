<?php
namespace Contactum\Fields;
use Contactum\Fields\Contactum_Field;

class Field_Phone extends  Contactum_Field {

	public function __construct() {
        $this->name       = __( 'Phone', 'contactum' );
        $this->input_type = 'phone_field';
        $this->icon       = 'phone';
    }

    public function render( $field_settings, $form_id ) {
        $value = strtolower( $field_settings['country_list']['name'] );

        ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php
                $this->print_label( $field_settings, $form_id );
                printf('<div class="contact-fields"><input
                    id="%s"
                    type="text"
                    class="contactum-el-form-control %s"
                    data-required="%s"
                    data-type="text"
                    name="%s"
                    placeholder="%s"
                    size="%s"
                    autocomplete="url"
                    class="contact-el-form-control"
                /> </div>',
                esc_attr( $field_settings['name'] ) . '_' . esc_attr( $form_id ),
                esc_attr( $field_settings['name'] ).'_'. esc_attr( $form_id ),
                esc_attr( $field_settings['required'] ),
                esc_attr( $field_settings['name'] ),
                esc_attr( $field_settings['placeholder'] ),
                esc_attr( $field_settings['size'] )
            );

            $this->help_text( $field_settings );

            $name        = esc_attr( $field_settings['name'] );
            $id        = esc_attr( $field_settings['name'] ).'_'. esc_attr( $form_id );
            $mask_option = !empty( $field_settings['mask_options'] ) ? $field_settings['mask_options'] : '';

            $script = "jQuery(document).ready(function($){   
                if (typeof intlTelInput == 'undefined') {
                    return;
                }             
                var telInput = jQuery(document).find(`#{$id}`);

                if (!telInput.length) {
                            return;
                }

            /*
              var itlOptions = JSON.parse('{\"separateDialCode\":false,\"nationalMode\":true,\"autoPlaceholder\":\"aggressive\",\"formatOnDisplay\":true,\"initialCountry\":\"auto\"}');
              itlOptions.geoIpLookup = function (success, failure) {
                    jQuery.get(\"https://ipinfo.io\", function (res) {
                        return true;
                    }, \"json\").always(function (resp) {
                        var countryCode = (resp && resp.country) ? resp.country : '';
                            success(countryCode);
                            });
                            };
            */


                var iti = intlTelInput(telInput[0], {
                    initialCountry: `{$value}`,
                });
                
                if (telInput.val()) {

                }

                telInput.on('change', function () {
                    if (typeof intlTelInputUtils !== 'undefined') { // utils are lazy loaded, so must check
                        var currentText = iti.getNumber(intlTelInputUtils.numberFormat.E164);
                        if (iti.isValidNumber() && typeof currentText === 'string') { // sometimes the currentText is an object :)
                            iti.setNumber(currentText); // will autoformat because of formatOnDisplay=true
                        }
                    }
                });

            });";

            wp_add_inline_script( 'contactum-intlTelInput', $script );
        ?>
            </li>
    <?php }

    public function get_options_settings() {
        $default_options = $this->get_default_option_settings();

        $settings = [
            [
                'name'          => 'country_list',
                'title'         => '',
                'type'          => 'country_list',
                'section'       => 'basic',
                'priority'      => 22,
                'help_text'     => '',
            ]
        ];

        return array_merge( $default_options, $settings );
    }

    public function get_field_props() {
        $defaults = $this->default_attributes();

        $props    = array(
            'input_type'        => 'country_list',
            'country_list'  => array(
                'name'                             => '',
                'country_list_visibility_opt_name' => 'all', // all, hide, show
                'country_select_show_list'         => array(),
                'country_select_hide_list'         => array()
            ),
            'is_meta'           => 'yes',
            'id'                => 0,
            'is_new'            => true,
        );

       return array_merge( $defaults, $props );
    }
}