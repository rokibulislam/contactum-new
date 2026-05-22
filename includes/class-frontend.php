<?php
namespace Contactum;

class Frontend {

    public function __construct() {
        add_shortcode( 'contactum', [ $this, 'render' ] );
        add_action( 'contactum_form_fields_top', array( $this, 'prepare_form_calculation' ), 10, 2 );
    }

    public function show_error( $message, $type = 'info' ) {
        return sprintf( '<div class="contactum-%s">%s</div>', $type, $message );
    }

    public function render( $atts ) {
        extract( shortcode_atts( [ 'id' => 0 ], $atts ) );

        contactum()->assets->register_frontend();
        contactum()->assets->enqueue_frontend();

        ob_start();

        $form = contactum()->forms->get( $id );

            
        if ( !$form->id ) {
            return $this->show_error( __( 'The form couldn\'t be found.', 'contactum' ) );
        }

        $isOpen = $form->isSubmissionOpen();

        if ( is_wp_error( $isOpen ) ) {
            return $this->show_error( $isOpen->get_error_message() );
        }

        $this->render_form( $form, $atts );

        $this->addCustomCssJs( $form );

        $this->add_view_tracking( $form->id );

        return ob_get_clean();
    }

    private function render_form( $form, $atts ) {
        $form_fields = $form->getFields();
        $form_settings = $form->getSettings();

        if( isset( $atts['modal'] ) && 'true' == $atts['modal'] ) {
            wp_enqueue_script( 'contactum-modal' );
            wp_enqueue_style( 'contactum-modal' );

            $modal_class = 'modal';
            $modal_id    = 'modal-form';
            $modal_style = 'style="display:none"';
        } else {
            $modal_class = $modal_id = $modal_style = '';
        }

        $script = "if ( typeof contactum_conditional_items === 'undefined' ) {
                window.contactum_conditional_items = [];
            }
            if ( typeof contactum_plupload_items === 'undefined' ) {
                window.contactum_plupload_items = [];
            }
            if ( typeof contactum_map_items === 'undefined' ) {
                window.contactum_map_items = [];
            }
        ";

        wp_add_inline_script( 'contactum-frontend', $script );

        ?>
        <form class="contactum-form-add contactum-style <?php echo esc_attr( $modal_class ); ?> contactum_form_<?php echo esc_attr( $form->id ); ?>"
         action="" method="post" <?php echo esc_attr( $modal_style ); ?>
         id="<?php // echo esc_attr($modal_id); ?>contactum_form_<?php echo esc_attr( $form->id ); ?>" >
            <ul class="contactum-form form-label-<?php echo esc_attr( $form_settings['label_position'] ); ?>">
               <?php

                    do_action( 'contactum_form_fields_top', $form, $form_fields );

                    contactum()->fields->render_fields( $form_fields, $form->id, $atts );
                    if( contactum()->fields->hassubmit_fields( $form_fields, $form->id, $atts ) ) {

                    } else {
                        $this->submit_button( $form->id );
                    }
               ?>
            </ul>
        </form>

        <?php
            if( isset( $atts['modal'] ) && 'true' == $atts['modal'] ) {
                printf( '<p> <a class="modal-btn" href="#modal-form" rel="modal:open"> Open Modal </a> </p>');
            }
    }

    private function submit_button( $form_id ) { ?>
        <li class="submit submit_wrapper contactum-submit">
            <!-- <div class="contactum-label"> &nbsp; </div> -->
            <?php esc_attr( wp_nonce_field( 'contactum_form_frontend' ) ); ?>
            <input type="hidden" name="form_id"          value="<?php echo esc_attr( $form_id ); ?>">
            <input type="hidden" name="page_id"          value="<?php echo get_the_ID(); ?>">
            <input type="hidden" name="action"           value="contactum_frontend_submit">
            <input type="hidden" name="_ctm_form_start"  value="<?php echo esc_attr( time() ); ?>">
            <input type="submit" class="btn btn-submit contactum_submit_btn  contactum_submit_<?php echo esc_attr( $form_id ); ?>"
            name="submit" value="Submit" />
        </li>
        <?php
    }


    private function add_view_tracking( $form_id ) {
        $nonce    = wp_create_nonce( 'contactum_form_frontend' );
        $ajax_url = esc_js( admin_url( 'admin-ajax.php' ) );
        $script   = "(function(){
            if(window.jQuery){
                jQuery.post('" . $ajax_url . "',{
                    action:'contactum_track_form_view',
                    form_id:" . intval( $form_id ) . ",
                    _ajax_nonce:'" . esc_js( $nonce ) . "'
                });
            }
        })();";
        wp_add_inline_script( 'contactum-frontend', $script );
    }

    public function addCustomCssJs( $form ) {
        $form_fields = $form->getFields();
        $form_settings = $form->getSettings();
    //    $this->addcss( $form->id, $form_settings['custom_css'] );
       $this->addjs( $form->id,$form, $form_settings['custom_js']);

        contactum()->assets->addcss($form->id, $form_settings['custom_css']);
        contactum()->assets->addJs($form->id, $form_settings['custom_js']);
    }

    public function addjs( $formId, $form, $customJS ) {

        if (trim($customJS)) {
            add_action('wp_footer', function () use ( $formId, $customJS) {

           $inline_script = "
           jQuery(document.body).on('contactum_init_" . esc_js($formId) . "', function(event, data) {
                var form = jQuery(data[0]);
                console.log(\"hello calculate \");
                var formId = ".  esc_attr($formId) ."
                calculation($, $theForm);
           });
           ";
            wp_add_inline_script('contactum-frontend', $inline_script);



                ?>

                <?php
            }, 100);
        }
    }


    public function prepare_form_calculation( $form, $form_fields ) {

        $calculation_vars = array(); 
        $field_names = array();

        foreach ( $form_fields as $field ) {

            if( isset( $field['name'] ) ) {
                $field_names[] = $field['name'];
                $temp = $field['name'];
            }

            if ( isset( $field['enable_calculation'] ) && ! $field['formula_field'] ) {
                continue;
            }

            if ( isset( $field['formula_field'] ) && !empty( $field['formula_field'] ) ) {
                $calculation_vars['formulas']["$temp"] = str_replace( '%', '*.01', $field['formula_field']);
            }
        }

        // print_r($calculation_vars);
        // exit;

        $calculation_vars = apply_filters( 'contactum_calculation_variables', $calculation_vars );

        wp_localize_script( 'contactum-frontend', 'contactumCalculationObj', array(
            // 'form_fields'        => $field_names,
            'calculation_vars'   => $calculation_vars,
        ) );
    }
}
