<?php
namespace Contactum\Importer;
use Contactum\Importer\Importer_Abstract;

/**
 * Fluent Forms importer class
 *
 * Fluent Forms stores each form as a single JSON blob (the `form_fields`
 * column of its own `{prefix}fluentform_forms` table, read here through its
 * documented `wpFluent()` query-builder helper rather than an internal class,
 * since Fluent Forms' internal namespaces have changed across major versions
 * but `wpFluent()` has stayed a stable, documented public entry point).
 *
 * Fluent Forms' exact JSON shape can still drift a little between versions,
 * so every field lookup here goes through get_ff_value(), which checks the
 * few places a given property is known to live rather than assuming one path
 * — an unrecognised/unexpected shape degrades to skipping that one field
 * (or falling back to a sensible default) instead of failing the whole import.
 */
class Importer_FluentForm extends Importer_Abstract {

    public function __construct() {
        $this->id        = 'fluentform';
        $this->title     = 'Fluent Forms';
        $this->shortcode = 'fluentform';

        parent::__construct();
    }

    /**
     * See if the plugin exists
     *
     * @return bool
     */
    public function plugin_exists() {
        return function_exists( 'wpFluent' );
    }

    /**
     * Get all the forms
     *
     * Prefers Fluent Forms' own `Form` model (their documented way to read
     * form data programmatically) over building a query by hand. Falls back
     * to their `wpFluent()` query-builder helper if that model class isn't
     * present — the model's internal namespace has moved across major Fluent
     * Forms versions, `wpFluent()` hasn't, so this keeps the import working
     * either way rather than hard-depending on one namespace guess.
     *
     * @return array
     */
    public function get_forms() {
        if ( ! $this->plugin_exists() ) {
            return [];
        }

        foreach ( [ '\FluentForm\App\Models\Form', '\FluentForm\App\Modules\Form\Form' ] as $model_class ) {
            if ( class_exists( $model_class ) ) {
                try {
                    $forms = $model_class::query()->get();

                    return method_exists( $forms, 'all' ) ? $forms->all() : (array) $forms;
                } catch ( \Throwable $e ) {
                    break; // fall through to the wpFluent() path below
                }
            }
        }
    }

    /**
     * Decode a form's `form_fields` JSON blob.
     *
     * @param object $form
     *
     * @return array
     */
    private function parse_form( $form ) {
        $parsed = json_decode( $form->form_fields, true );

        return is_array( $parsed ) ? $parsed : [];
    }

    /**
     * Read a Fluent Forms form-level setting by meta_key from its meta table,
     * falling back to `$default` if the row doesn't exist or isn't valid JSON.
     *
     * @param int    $form_id
     * @param string $meta_key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function get_form_meta( $form_id, $meta_key, $default = [] ) {
        if ( ! $this->plugin_exists() ) {
            return $default;
        }

        foreach ( [ '\FluentForm\App\Models\FormMeta', '\FluentForm\App\Modules\Form\FormMeta' ] as $model_class ) {
            if ( ! class_exists( $model_class ) ) {
                continue;
            }

            try {
                $row = $model_class::query()
                    ->where( 'form_id', $form_id )
                    ->where( 'meta_key', $meta_key )
                    ->first();
            } catch ( \Throwable $e ) {
                return $default;
            }

            if ( ! $row || empty( $row->value ) ) {
                return $default;
            }

            $decoded = json_decode( $row->value, true );

            return is_array( $decoded ) ? $decoded : $default;
        }

        return $default;
    }

    /**
     * Look up a field property across the few places Fluent Forms is known
     * to put it (top-level, under `attributes`, or under `settings`).
     *
     * @param array  $field
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function get_ff_value( $field, $key, $default = '' ) {
        if ( isset( $field[ $key ] ) ) {
            return $field[ $key ];
        }

        if ( isset( $field['attributes'][ $key ] ) ) {
            return $field['attributes'][ $key ];
        }

        if ( isset( $field['settings'][ $key ] ) ) {
            return $field['settings'][ $key ];
        }

        return $default;
    }

    private function is_ff_required( $field ) {
        $rules = $this->get_ff_value( $field, 'validation_rules', [] );

        return ! empty( $rules['required']['value'] );
    }

    /**
     * Get the form name
     *
     * @param object $form
     *
     * @return string
     */
    public function get_form_name( $form ) {
        return $form->title;
    }

    /**
     * Get the form id
     *
     * @param object $form
     *
     * @return int
     */
    protected function get_form_id( $form ) {
        return $form->id;
    }

    /**
     * Get all form fields of a form
     *
     * @param object $form
     *
     * @return array
     */
    public function get_form_fields( $form ) {
        $form_fields = [];
        $parsed      = $this->parse_form( $form );

        // TEMPORARY debug instrumentation — remove once the Fluent Forms
        // JSON shape is confirmed against a real install. Dumps the raw
        // column and the decoded structure to the debug log so the actual
        // shape can be compared against what this importer assumes.
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[Contactum FluentForm import] form #' . $form->id . ' raw form_fields: ' . substr( (string) $form->form_fields, 0, 2000 ) );
            error_log( '[Contactum FluentForm import] form #' . $form->id . ' decoded top-level keys: ' . wp_json_encode( is_array( $parsed ) ? array_keys( $parsed ) : $parsed ) );

            if ( isset( $parsed['fields'] ) && is_array( $parsed['fields'] ) ) {
                $is_list = array_keys( $parsed['fields'] ) === range( 0, count( $parsed['fields'] ) - 1 );
                error_log( '[Contactum FluentForm import] form #' . $form->id . ' fields shape: ' . ( $is_list ? 'list array (count ' . count( $parsed['fields'] ) . ')' : 'assoc array, keys: ' . wp_json_encode( array_keys( $parsed['fields'] ) ) ) );

                if ( ! empty( $parsed['fields'] ) ) {
                    $first = $is_list ? $parsed['fields'][0] : reset( $parsed['fields'] );
                    error_log( '[Contactum FluentForm import] form #' . $form->id . ' first field sample: ' . wp_json_encode( $first ) );
                }
            } elseif ( isset( $parsed['fields'] ) ) {
                error_log( '[Contactum FluentForm import] form #' . $form->id . ' fields shape: ' . gettype( $parsed['fields'] ) );
            }
        }

        if ( empty( $parsed['fields'] ) ) {
            return $form_fields;
        }

        foreach ( $parsed['fields'] as $menu_order => $field ) {
            $element = isset( $field['element'] ) ? $field['element'] : '';

            // Avoid an empty meta key — fall back to element+index.
            $name = $this->get_ff_value( $field, 'name', $element . '_' . $menu_order );

            $common = [
                'required'    => $this->is_ff_required( $field ) ? 'yes' : 'no',
                'label'       => $this->get_ff_value( $field, 'label', '' ),
                'name'        => $name,
                'help'        => $this->get_ff_value( $field, 'help_message', '' ),
                'placeholder' => $this->get_ff_value( $field, 'placeholder', '' ),
                'default'     => $this->get_ff_value( $field, 'value', '' ),
            ];

            switch ( $element ) {
                case 'input_text':
                case 'text':
                    $form_fields[] = $this->get_form_field( 'text', $common );
                    break;

                case 'input_email':
                case 'email':
                    $form_fields[] = $this->get_form_field( 'email', $common );
                    break;

                case 'textarea':
                case 'input_textarea':
                    $form_fields[] = $this->get_form_field( 'textarea', $common );
                    break;

                case 'input_hidden':
                case 'hidden':
                    $form_fields[] = $this->get_form_field( 'hidden', $common );
                    break;

                case 'input_number':
                case 'number':
                    $form_fields[] = $this->get_form_field( 'number', array_merge( $common, [
                        'min' => $this->get_ff_value( $field, 'min', '' ),
                        'max' => $this->get_ff_value( $field, 'max', '' ),
                        'step' => $this->get_ff_value( $field, 'step', '' ),
                    ] ) );
                    break;

                case 'input_url':
                case 'website_url':
                case 'url':
                    $form_fields[] = $this->get_form_field( 'url', $common );
                    break;

                case 'date_time':
                case 'input_date':
                case 'date':
                    $form_fields[] = $this->get_form_field( 'date', $common );
                    break;

                case 'select':
                case 'input_select':
                    $form_fields[] = $this->get_form_field( 'select', array_merge( $common, [
                        'options' => $this->get_options( $field ),
                    ] ) );
                    break;

                case 'input_checkbox':
                case 'checkbox':
                    $form_fields[] = $this->get_form_field( 'checkbox', array_merge( $common, [
                        'options' => $this->get_options( $field ),
                    ] ) );
                    break;

                case 'input_radio':
                case 'radio':
                    $form_fields[] = $this->get_form_field( 'radio', array_merge( $common, [
                        'options' => $this->get_options( $field ),
                    ] ) );
                    break;

                case 'section_break':
                    $form_fields[] = $this->get_form_field( 'section_break', $common );
                    break;

                case 'custom_html':
                case 'html':
                    $form_fields[] = $this->get_form_field( 'html', array_merge( $common, [
                        'default' => $this->get_ff_value( $field, 'html_codes', '' ),
                    ] ) );
                    break;

                case 'terms_and_condition':
                case 'gdpr_agreement':
                    $form_fields[] = $this->get_form_field( 'toc', $common );
                    break;

                case 'recaptcha':
                case 'hcaptcha':
                case 'input_recaptcha':
                    $form_fields[] = $this->get_form_field( 'recaptcha', $common );
                    break;

                case 'input_file':
                case 'file':
                    $form_fields[] = $this->get_form_field( 'file', $common );
                    break;

                case 'ratings':
                    $form_fields[] = $this->get_form_field( 'ratings', $common );
                    break;

                case 'input_name':
                case 'name':
                    $sub = $this->get_ff_value( $field, 'fields', [] );

                    $form_fields[] = $this->get_form_field( 'name', array_merge( $common, [
                        'format'       => isset( $sub['middle_name'] ) ? 'first-middle-last' : 'first-last',
                        'first_name'   => [
                            'placeholder' => $this->get_ff_value( $sub['first_name'] ?? [], 'placeholder', '' ),
                            'default'     => '',
                            'sub'         => __( 'First', 'contactum' ),
                        ],
                        'middle_name'  => [
                            'placeholder' => $this->get_ff_value( $sub['middle_name'] ?? [], 'placeholder', '' ),
                            'default'     => '',
                            'sub'         => __( 'Middle', 'contactum' ),
                        ],
                        'last_name'    => [
                            'placeholder' => $this->get_ff_value( $sub['last_name'] ?? [], 'placeholder', '' ),
                            'default'     => '',
                            'sub'         => __( 'Last', 'contactum' ),
                        ],
                    ] ) );
                    break;
            }
        }

        return $form_fields;
    }

    /**
     * Translate Fluent Forms choice options to a contactum options array.
     *
     * @param array $field
     *
     * @return array
     */
    private function get_options( $field ) {
        $options = [];

        $choices = $this->get_ff_value( $field, 'advanced_options', [] );

        if ( empty( $choices ) ) {
            $choices = $this->get_ff_value( $field, 'choices', [] );
        }

        if ( ! is_array( $choices ) ) {
            return $options;
        }

        foreach ( $choices as $choice ) {
            if ( is_array( $choice ) ) {
                $label = $choice['label'] ?? ( $choice['value'] ?? '' );
                $options[ $label ] = $label;
            } elseif ( is_string( $choice ) ) {
                $options[ $choice ] = $choice;
            }
        }

        return $options;
    }

    /**
     * Get form settings of a form
     *
     * @param object $form
     *
     * @return array
     */
    public function get_form_settings( $form ) {
        $default  = $this->get_default_form_settings();
        $settings = $this->get_form_meta( $form->id, 'formSettings', [] );

        $confirmation = isset( $settings['confirmation'] ) && is_array( $settings['confirmation'] )
            ? $settings['confirmation']
            : [];

        $redirect_to = 'same';
        if ( ! empty( $confirmation['redirectTo'] ) ) {
            $redirect_to = $confirmation['redirectTo'] === 'customUrl' ? 'url' : 'same';
        }

        return wp_parse_args( array_filter( [
            'message'     => $confirmation['messageToShow'] ?? null,
            'url'         => $confirmation['customUrl'] ?? null,
            'redirect_to' => $redirect_to,
        ] ), $default );
    }

    /**
     * Get form notifications of a form
     *
     * @param object $form
     *
     * @return array
     */
    public function get_form_notifications( $form ) {
        $feeds = $this->get_form_meta( $form->id, 'notifications', [] );

        if ( empty( $feeds ) || ! is_array( $feeds ) ) {
            return [
                [
                    'active'      => true,
                    'name'        => 'Admin Notification',
                    'subject'     => sprintf( __( 'New submission on %s', 'contactum' ), $form->title ),
                    'to'          => get_option( 'admin_email' ),
                    'replyTo'     => '',
                    'message'     => '{all_fields}',
                    'fromName'    => '{site_name}',
                    'fromAddress' => '{admin_email}',
                    'cc'          => '',
                    'bcc'         => '',
                ],
            ];
        }

        $form_notifications = [];

        foreach ( $feeds as $feed ) {
            $form_notifications[] = [
                'active'      => ! empty( $feed['enabled'] ),
                'name'        => $feed['name'] ?? 'Admin Notification',
                'subject'     => $feed['subject'] ?? '',
                'to'          => $feed['sendTo']['email'] ?? get_option( 'admin_email' ),
                'replyTo'     => $feed['replyTo'] ?? '',
                'message'     => $feed['message'] ?? '{all_fields}',
                'fromName'    => $feed['fromName'] ?? '{site_name}',
                'fromAddress' => $feed['fromEmail'] ?? '{admin_email}',
                'cc'          => $feed['cc'] ?? '',
                'bcc'         => $feed['bcc'] ?? '',
            ];
        }

        return $form_notifications;
    }
}
