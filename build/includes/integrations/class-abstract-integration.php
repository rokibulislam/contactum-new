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


    public function __construct() {
        add_action( 'contactum_save_global_integration_settings_'.$this->id, [ $this, 'saveGlobalSettings' ], 10, 2 );
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
