<?php

namespace Contactum\Fields;
use Contactum\Fields\Contactum_Field;

class Field_Column extends Contactum_Field {

    public function __construct() {
        $this->name       = __( 'Columns', 'contactum' );
        $this->input_type = 'column_field';
        $this->icon       = 'columns';
    }

    public function render( $field_settings, $form_id, $type = 'post', $post_id = null ) {
        $use_theme_css = isset( $form_settings['use_theme_css'] ) ? $form_settings['use_theme_css'] : 'contactum-style';
        $i             = 1;
        $columns       = $field_settings['columns'];
        $columns_size  = $field_settings['inner_columns_size'];
        $column_space  = $field_settings['column_space'];
        $inner_fields  = $field_settings['inner_fields'];
        $atts          = []; ?>
        <li class="contactum-el">
            <div class="contactum-field-columns <?php echo 'has-columns-' . esc_attr( $columns ); ?>" data-style="<?php echo esc_attr( $use_theme_css ); ?>">
                <div class="contactum-column-field-inner-columns">
                    <div class="contactum-column">
                        <?php while ( $i <= $columns ) { ?>
                            <div class="<?php echo 'column-' . esc_attr( $i ) . ' items-of-column-' . esc_attr( $columns ); ?> contactum-column-inner-fields">
                                <ul class="contactum-column-fields">
                                    <?php contactum()->fields->render_fields( $inner_fields['column-' . $i], $form_id, $atts, $type, $post_id ); ?>
                                </ul>
                            </div>

                            <?php $i++; ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </li>
        <?php
    }


    public function get_options_settings() {
        $options = [
            [
                'name'      => 'columns',
                'title'     => __( 'Number of Columns', 'contactum' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Title of the section', 'contactum' ),
            ],
            [
                'name'      => 'column_space',
                'title'     => __( 'Space Between Columns', 'contactum' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => __( 'Add padding space between columns. e.g: 10', 'contactum' ),
            ],
            [
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'contactum' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => __( 'Provide a container class name for this field. Available classes: contactum-col-half, contactum-col-half-last, contactum-col-one-third, contactum-col-one-third-last', 'contactum' ),
            ],
        ];

        return apply_filters( 'contactum_column_field_option_settings', $options );
    }


    public function get_field_props() {
    
        $props = [
            'input_type'        => 'column_field',
            'template'          => $this->get_type(),
            'id'                => 0,
            'is_new'            => true,
            'is_meta'           => 'no',
            'columns'           => 3,
            'min_column'        => 1,
            'max_column'        => 3,
            'column_space'      => '5',
            'inner_fields'      => [
                'column-1'   => [],
                'column-2'   => [],
                'column-3'   => [],
            ],
            'inner_columns_size' => [
                'column-1'   => '33.33%',
                'column-2'   => '33.33%',
                'column-3'   => '33.33%',
            ],
        ];

        return $props;
    }
}