<?php
namespace Contactum\Templates;

use Contactum\Templates\Contactum_Form_Template;

class Template_Conversational extends Contactum_Form_Template {
        
        public function __construct() {
                parent::__construct();
                $this->enabled     = true;
                $this->title       = __( 'Conversational Form', 'contactum' );
                $this->description = __( 'Create a simple Conversational form.', 'contactum' );
                $this->image       = CONTACTUM_ASSETS . '/images/form-template/blank.png';
                $this->category    = 'default';
        }

        public function get_form_fields() {
        	return __return_empty_array();
        }

        public function is_conversion() {
            return true;
        }
}