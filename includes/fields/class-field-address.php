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

        wp_enqueue_script( 'jquery-ui-autocomplete' );

        wp_register_script( 'custom_map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCKWDThF0zcAHSzWe3tvNfZwcu-AQiItzM&libraries=places', [], '1.0', true );
        wp_enqueue_script( 'custom_map' );
        
        $country_select_show_list = isset( $field_settings['address']['country_select']['country_select_show_list'] )  ? $field_settings['address']['country_select']['country_select_show_list'] : [];
        $country_select_hide_list = isset(  $field_settings['address']['country_select']['country_select_hide_list'] ) ? $field_settings['address']['country_select']['country_select_hide_list'] : [];
        $list_visibility_option   = $field_settings['address']['country_select']['country_list_visibility_opt_name'];
    ?>
        <li <?php $this->print_list_attributes( $field_settings ); ?>>
            <?php $this->print_label( $field_settings ); ?>
            <div class="contactum-fields address-fields">
                <?php foreach( $field_settings['address'] as $each_field => $field_array ) {
                        $field_name = $field_settings['name'] . '[' . $each_field . ']' ;
                    if ( isset( $field_array['checked'] ) && !empty( $field_array['checked'] ) ) {
                ?>
                        <div class="contactum-sub-fields">
                            <label class="contact-form-sub-label">  <?php echo $field_array['label']; ?>
                                <span class="required"><?php echo ( isset( $field_array['required'] ) && !empty($field_array['required']) ) ? '*' : ''; ?></span>
                            </label>
                            <?php

                                if ( in_array( $field_array['type'], array( 'text', 'hidden', 'email', 'password') ) ) {
                                    $required = isset( $field_array['required'] ) && !empty( $field_array['required'] ) ? 'required' : '';
                                    printf('<input type="%s" name="%s" value="%s" placeholder="%s" class="textfield contactum-el-form-control %s" size="40" autocomplete="%s" %s/>',
                                        $field_array['type'],
                                        $field_name,
                                        "",
                                        $field_array['placeholder'],
                                        'contactum_map_autocomplete',
                                        "",
                                        $required
                                    );
                                } elseif ( in_array( $field_array['type'],array( 'select') ) ) {
                                    if ( $each_field == 'country_select' ) {
                                        printf('<select
                                            name="%s"
                                            data-required="%s"
                                            data-type="select"
                                            class="contactum-el-form-control %s">
                                            </select>',
                                            $field_name,
                                            $field_settings['required'],
                                            $field_name . $form_id
                                        );

                                        $countries         = contactum_get_countries();
                                        $banned_countries  = $country_select_hide_list;
                                        $allowed_countries =  $country_select_show_list;
                                        $sel_country       = !empty( $value ) ? $value : '' ;
                                        $option_string     = '<option value="-1">Select Country</option>';

                                        if ( $list_visibility_option == 'hide' ) {
                                            foreach ( $countries as $country ) {
                                                if ( in_array( $country['code'], $banned_countries ) ) {
                                                    continue;
                                                }
                                                $selected = ( $sel_country == $country['code'] ) ? 'selected':'';
                                                $option_string .= "<option value=\"{$country['code']}\" { $selected } > {$country['name']}  </option>";
                                            }
                                        } else if ( $list_visibility_option == 'show' ) {
                                            foreach ( $countries as $country ) {
                                                if ( in_array( $country['code'], $allowed_countries )  ) {
                                                    $selected = ( $sel_country == $country['code'] ) ? 'selected':'';
                                                    $option_string .= "<option value=\"{$country['code']}\" { $selected } > {$country['name']}  </option>";
                                                }
                                            }
                                        } else {
                                            foreach ( $countries as $country ) {
                                                $selected = ( $sel_country == $country['code'] ) ? 'selected':'';
                                                $option_string .= "<option value=\"{$country['code']}\" { $selected } > {$country['name']}  </option>";
                                            }
                                        }




                                        $script = "jQuery(document).ready(function($){
                                            var field_name =`{$field_name}`;
                                            $('select[name=\"'+ field_name +'\"]').html(`{$option_string}`);

            $( '.contactum_map_autocomplete' ).autocomplete({
                source: function (request, response) {
                    if( request.term.length > 1 ) {
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ address: request.term }, function (results, status) {
                            if (status === google.maps.GeocoderStatus.OK) {
                                response(
                                    $.map(results, function (item) {
                                        
                                        let postalCode, city, country;
                                        
                                        for (var i = 0; i < item.address_components.length; i++) {
                                            var component = item.address_components[i];
                                            
                                            if (component.types.includes('postal_code')) {
                                              postalCode = component.long_name;
                                            } else if (component.types.includes('locality')) {
                                              city = component.long_name;
                                            } else if (component.types.includes('country')) {
                                              country = component.long_name;
                                            }
                                        }


                                        return {
                                            label: item.formatted_address,
                                            value: item.formatted_address,
                                            location: item.geometry.location,
                                            postcode: postalCode,
                                            city: city,
                                            country: country,
                                        };
                                    })
                                );
                            }
                        });
                    }
                },

                select: function (event, ui) {
                    $('#EventZip').val(ui.item.postcode);
                    $('#EventCountry').val(ui.item.country).trigger('change');
                    $('input [name=\'address[street_address]\']').val(ui.item.formatted_address);
                    $('input [name=\'address[city_name]\']').val(ui.item.city);
                }
            });

                                        });";

                                       wp_add_inline_script( 'contactum-frontend', $script );
                                    }
                               }
                            ?>
                        </div>
                    <?php }
                } ?>
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
                    'label'         => __( 'Address Line 1', 'contactum' ),
                    'value'         => '',
                    'placeholder'   => ''
                ),

                'street_address2'   => array(
                    'checked'       => 'checked',
                    'type'          => 'text',
                    'required'      => false,
                    'label'         => __( 'Address Line 2', 'contactum' ),
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