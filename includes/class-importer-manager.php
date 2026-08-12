<?php
namespace Contactum;

use Contactum\Importer\Importer_WPForms;
use Contactum\Importer\Importer_Ninja_Forms;
use Contactum\Importer\Importer_GF;
use Contactum\Importer\Importer_CF7;
use Contactum\Importer\Importer_Caldera_Forms;
use Contactum\Importer\Importer_FluentForm;

/**
 * Importer Manager
 *
 * @since 1.1.0
 */
class Importer_Manager {

    /**
     * Instantiated importers, keyed by id. Memoized so a second call to
     * get_importers() (e.g. from the Tools "Migrate" tab's localized data)
     * doesn't re-instantiate every importer and double-register their
     * admin_notice / AJAX hooks.
     *
     * @var array
     */
    private $importers = [];

    public function __construct() {
        $this->importers = $this->build_importers();
    }

    /**
     * Fetch the instantiated importers
     *
     * @return array
     */
    public function get_importers() {
        if ( empty( $this->importers ) ) {
            $this->importers = $this->build_importers();
        }

        return $this->importers;
    }

    /**
     * A lightweight, JS-friendly summary of every registered importer —
     * id, display name, and whether its source plugin is currently active.
     *
     * @return array
     */
    public function get_importer_summary() {
        $summary = [];

        foreach ( $this->get_importers() as $key => $importer ) {
            $summary[] = [
                'key'    => $key,
                'id'     => $importer->id,
                'title'  => $importer->title,
                'active' => (bool) $importer->plugin_exists(),
            ];
        }

        return $summary;
    }

    /**
     * Require the importer classes and instantiate one of each.
     *
     * @return array
     */
    private function build_importers() {
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-abstract.php';
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-cf7.php';
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-gf.php';
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-wpforms.php';
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-ninja-forms.php';
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-caldera-forms.php';
        require_once CONTACTUM_INCLUDES . '/importer/class-importer-fluentform.php';

        $importers = [
            'cf7'        => new Importer_CF7(),
            'gravity'    => new Importer_GF(),
            'wpforms'    => new Importer_WPForms(),
            'ninjaforms' => new Importer_Ninja_Forms(),
            'caldera'    => new Importer_Caldera_Forms(),
            'fluentform' => new Importer_FluentForm(),
        ];

        return apply_filters( 'contactum_form_importers', $importers );
    }
}
