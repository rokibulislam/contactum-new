<?php

namespace Contactum\Integrations;

/**
 * The Integration abstract class
 *
 */
abstract class Contactum_Integration {

    /**
     * The integration id
     *
     * @var bool
     */
    public $id;

    /**
     * If the integration is enabled
     *
     * @var bool
     */
    public $enabled;

    /**
     * Integration title
     *
     * @var string
     */
    public $title;

    /**
     * Integration title
     *
     * @var string
     */
    public $description;

    /**
     * URL to the integration icon
     *
     * @var string
     */
    public $icon;

    /**
     * The settings fields for this integrations
     *
     * @var array
     */
    public $settings_fields = [];


    public $sections = [];

    /**
     * The settings fields for this integrations
     *
     * @var array
     */
    protected $template = null;

    /**
     * The settings settings_template
     *
     * @var array
     */
    protected $settings_template = null;

    /**
     * The method (already hooked to `contactum_entry_submission`) that
     * performs this integration's outbound API call. Set via
     * enable_api_retry() so retry_api_action() knows what to re-run.
     *
     * @var string|null
     */
    protected $api_retry_method = null;


    public function __construct() {
        add_action( 'contactum_save_global_integration_settings_'.$this->id, [ $this, 'saveGlobalSettings' ], 10, 2 );
    }

    /**
     * Record the outcome of an outbound API call this integration made, so
     * it shows up in Contactum → Tools → API Log.
     *
     * @param int    $form_id
     * @param int    $entry_id
     * @param bool   $success
     * @param string $note      Human-readable result, e.g. an error message.
     */
    protected function log_api_result( $form_id, $entry_id, $success, $note = '' ) {
        if ( ! function_exists( 'contactum' ) || empty( contactum()->api_logger ) ) {
            return;
        }

        contactum()->api_logger->log( [
            'action'   => $this->id,
            'form_id'  => $form_id,
            'entry_id' => $entry_id,
            'status'   => $success ? 'success' : 'failed',
            'note'     => $note,
            'data'     => [
                'entry_id' => $entry_id,
                'form_id'  => $form_id,
            ],
        ] );
    }

    /**
     * Opt this integration into API Log retries.
     *
     * @param string $submit_method Name of the method already hooked to
     *                               `contactum_entry_submission` on this
     *                               integration. It will be re-invoked as
     *                               $this->{$submit_method}( $entry_id, $form_id, 0, [] )
     *                               when an admin clicks "Retry" in the API Log.
     */
    protected function enable_api_retry( $submit_method ) {
        $this->api_retry_method = $submit_method;
        add_action( 'contactum_api_log_retry_' . $this->id, [ $this, 'retry_api_action' ], 10, 3 );
    }

    /**
     * Generic retry handler for `contactum_api_log_retry_{$this->id}`.
     * Re-runs the integration's own submission handler; that handler is
     * expected to call log_api_result() again, producing a fresh log row
     * with the outcome of the retry.
     */
    public function retry_api_action( $data, $log_id, $log ) {
        $entry_id = ! empty( $data['entry_id'] ) ? absint( $data['entry_id'] ) : absint( $log->entry_id );
        $form_id  = ! empty( $data['form_id'] ) ? absint( $data['form_id'] ) : absint( $log->form_id );

        if ( ! $entry_id || ! $form_id || empty( $this->api_retry_method ) || ! function_exists( 'contactum' ) ) {
            if ( function_exists( 'contactum' ) && ! empty( contactum()->api_logger ) ) {
                contactum()->api_logger->update_status( $log_id, 'failed', __( 'Retry failed: missing entry context.', 'contactum' ) );
            }
            return;
        }

        contactum()->api_logger->update_status( $log_id, 'manual_retry', __( 'Retried — see the newest log entry below for the outcome.', 'contactum' ) );

        call_user_func( [ $this, $this->api_retry_method ], $entry_id, $form_id, 0, [] );
    }

    /**
     * Get the integration title
     *
     * @return string
     */
    public function get_title() {
        return apply_filters( 'contactum_integration_title', $this->title, $this );
    }

    /**
     * Get the integration id
     *
     * @return string
     */
    public function get_id() {
        return apply_filters( 'contactum_integration_title', $this->id, $this );
    }

    /**
     * Get intgration icon
     *
     * @return string
     */
    public function get_icon() {
        return apply_filters( 'contactum_integration_icon', $this->icon, $this );
    }

    public function get_desciption() {
        return apply_filters( 'contactum_integration_description', $this->description, $this );
    }

    /**
     * Check if the integration is enabled
     *
     * @return bool
     */
    public function is_enabled() {
        return $this->enabled == true;
    }

    /**
     * Check if it's a pro field
     *
     * @return bool
     */
    public function is_pro() {
        return false;
    }

    /**
     * Get the settings fields
     *
     * @return array
     */
    public function get_settings_fields() {
        return $this->settings_fields;
    }

    /**
     * Get the integration settings for the component
     *
     * @return array
     */
    public function get_js_settings() {
        return [
            'formenable' => false,
            'integration' => $this->getIntegrationDefaults(),
            'integration_fields' => $this->get_integration_settings(),
            'value'    => $this->getGlobalSettings(),
            'id'       => $this->get_id(),
            'title'    => $this->get_title(),
            'description' => $this->get_desciption(),
            'icon'     => $this->get_icon(),
            'settings' => $this->get_settings_fields(),
            'sections' => $this->get_settings_section(),
            'pro'      => $this->is_pro(),
        ];
    }


    public function getIntegrationDefaults()
    {
        $settings = [
            'enabled'                => false,
            'list_id'                => '',
            'list_name'              => '',
            'first_name'             => '',
            'last_name'              => '',
            'email'                  => '',
            'doubleOptIn'            => false,
        ];

        return $settings;
    }

    /**
     * Check if it's the forms page
     *
     * @return bool
     */
    public function is_contactum_page() {
        if ( get_current_screen()->base != 'toplevel_page_contactum' ) {
            return false;
        }

        return true;
    }
}
