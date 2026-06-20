<?php

namespace Contactum;

use Contactum\Fields\Contactum_Form_Field_Pro;

/**
 * Text Field Class
 */
class Contactum_Form_Field_GMap extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Google Map', 'contactum' );
        $this->input_type = 'google_map';
        $this->icon       = 'map-marker';
        $this->description = __('Google Map is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}

/**
 * Text Field Class
 */
class Contactum_Form_Field_Hook extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Action Hook', 'contactum' );
        $this->input_type = 'action_hook';
        $this->icon       = 'anchor';
        $this->description = __('Action Hook is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}


/**
 * Rating Field Class
 */
class Contactum_Form_Field_Linear_Scale extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Linear Scale', 'contactum' );
        $this->input_type = 'linear_scale';
        $this->icon       = 'ellipsis-h';
        $this->description = __('Linear Scale is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}

/**
 * Checkbox Grids Field Class
 */
class Contactum_Form_Field_Checkbox_Grid extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Checkbox Grid', 'contactum' );
        $this->input_type = 'checkbox_grid';
        $this->icon       = 'th';
        $this->description = __('Checkbox Grid is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}

/**
 * Multiple Choice Grids Field Class
 */
class Contactum_Form_Field_Multiple_Choice_Grid extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Multiple Choice Grid', 'contactum' );
        $this->input_type = 'multiple_choice_grid';
        $this->icon       = 'braille';
        $this->description = __('Multiple Checkbox Grid is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}

/**
 * Repeat Field Class
 */
class Contactum_Form_Field_Repeat extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Repeat Field', 'contactum' );
        $this->input_type = 'repeat_field';
        $this->icon       = 'text-width';
        $this->description = __('Repeat Field is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}

/**
 * Shortcode Field Class
 */
class Contactum_Form_Field_Shortcode extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Shortcode', 'contactum' );
        $this->input_type = 'shortcode';
        $this->icon       = 'calendar-o';
        $this->description = __('Shortcode is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}

/**
 * Step Field Class
 */
class Contactum_Form_Field_Step extends Contactum_Form_Field_Pro {
    public function __construct() {
        $this->name       = __( 'Step Start', 'contactum' );
        $this->input_type = 'step_start';
        $this->icon       = 'step-forward';
        $this->description = __('Step Start is not available with the free version. Please upgrade to pro to get all the advanced features.', 'Contactum');
    }
}



/**
 * Multiple product
 */

class Contactum_Field_Multiple_product extends Contactum_Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Multiple Product', 'contactum' );
        $this->input_type = 'multiple_product';
        $this->icon       = 'user';
        $this->description = __('', 'Contactum');
    }

}

/**
 * Single product
 */

class Contactum_Field_Single_product extends Contactum_Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Single Product', 'contactum' );
        $this->input_type = 'single_product';
        $this->icon       = 'user';
        $this->description = __('', 'Contactum');
    }
}


/**
 * Coupon
 */
class Contactum_Field_Coupon extends Contactum_Form_Field_Pro {

    public function __construct() {
        
        $this->name       = __( 'Coupon', 'Contactum' );
        $this->input_type = 'coupon_field';
        $this->icon       = 'columns';
        $this->description = __('', 'Contactum');
    }
}


/**
 * Subscription
 */
class Contactum_Field_Subscription extends Contactum_Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Subscription Field', 'Contactum' );
        $this->input_type = 'subscription_field';
        $this->icon       = 'columns';
        $this->description = __('', 'Contactum');
    }
}



/**
 * Total
 */
class Contactum_Field_Total extends Contactum_Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Total Field', 'Contactum' );
        $this->input_type = 'total';
        $this->icon       = 'columns';
        $this->description = __('', 'Contactum');
    }
}


/**
 * Total
 */
class Contactum_Field_Payment_Method extends Contactum_Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Payment Method', 'Contactum' );
        $this->input_type = 'payment_method';
        $this->icon       = 'columns';
        $this->description = __('', 'Contactum');
    }
}


/**
 * Payment Summary stub — full implementation lives in contact-pro.
 */
class Contactum_Field_Payment_Summary extends Contactum_Form_Field_Pro {

    public function __construct() {
        $this->name       = __( 'Payment Summary', 'contactum' );
        $this->input_type = 'payment_summary';
        $this->icon       = 'list-alt';
    }

    public function is_full_width() {
        return true;
    }

    public function get_field_props() {
        return array_merge( $this->default_attributes(), [
            'currency_symbol'   => '$',
            'currency_position' => 'left',
            'tax_rate'          => 0,
            'tax_label'         => __( 'Tax', 'contactum' ),
            'subtotal_label'    => __( 'Subtotal', 'contactum' ),
            'total_label'       => __( 'Total', 'contactum' ),
            'empty_message'     => __( 'No items selected yet.', 'contactum' ),
            'is_meta'           => 'no',
            'required'          => 'no',
        ] );
    }

    public function get_options_settings() {
        return [
            [
                'name'      => 'label',
                'title'     => __( 'Field Label', 'contactum' ),
                'type'      => 'text',
                'section'   => 'basic',
                'priority'  => 10,
                'help_text' => __( 'Enter a title for this summary block', 'contactum' ),
            ],
            [
                'name'      => 'payment_summary',
                'title'     => __( 'Summary Options', 'contactum' ),
                'type'      => 'payment_summary',
                'section'   => 'basic',
                'priority'  => 11,
                'help_text' => '',
            ],
            [
                'name'      => 'width',
                'title'     => __( 'Field Size', 'contactum' ),
                'type'      => 'radio',
                'options'   => [
                    'small'  => __( 'Small', 'contactum' ),
                    'medium' => __( 'Medium', 'contactum' ),
                    'large'  => __( 'Large', 'contactum' ),
                ],
                'section'   => 'advanced',
                'priority'  => 21,
                'default'   => 'large',
                'inline'    => true,
            ],
            [
                'name'      => 'css',
                'title'     => __( 'CSS Class Name', 'contactum' ),
                'type'      => 'text',
                'section'   => 'advanced',
                'priority'  => 22,
                'help_text' => '',
            ],
        ];
    }
}