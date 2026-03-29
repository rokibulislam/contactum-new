<?php
namespace Contactum;

use Contactum\Forms_List_Table;
use Contactum\Entries_List_Table;

class Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_filter( 'parent_file', [ $this, 'fix_parent_menu' ] );
    }


    public function fix_parent_menu( $parent_file ) {
        $current_screen = get_current_screen();
        $post_types     = [ 'contactum_forms' ];

        if ( in_array( $current_screen->post_type, $post_types ) ) {
            $parent_file = 'contactum';
        }

        return $parent_file;
    }

    /**
     * Register form post types
     *
     * @return void
     */
    public function register_post_type() {
        $capability = 'manage_options';

        register_post_type( 'contactum_forms', [
            'label'           => __( 'Forms', 'contactum' ),
            'public'          => false,
            'show_ui'         => false,
            'show_in_menu'    => false, //false,
            'capability_type' => 'post',
            'hierarchical'    => false,
            'query_var'       => false,
            'supports'        => ['title'],
            'capabilities'    => [
                'publish_posts'       => $capability,
                'edit_posts'          => $capability,
                'edit_others_posts'   => $capability,
                'delete_posts'        => $capability,
                'delete_others_posts' => $capability,
                'read_private_posts'  => $capability,
                'edit_post'           => $capability,
                'delete_post'         => $capability,
                'read_post'           => $capability,
            ],
            'labels' => [
                'name'               => __( 'Forms', 'contactum' ),
                'singular_name'      => __( 'Form', 'contactum' ),
                'menu_name'          => __( 'Forms', 'contactum' ),
                'add_new'            => __( 'Add Form', 'contactum' ),
                'add_new_item'       => __( 'Add New Form', 'contactum' ),
                'edit'               => __( 'Edit', 'contactum' ),
                'edit_item'          => __( 'Edit Form', 'contactum' ),
                'new_item'           => __( 'New Form', 'contactum' ),
                'view'               => __( 'View Form', 'contactum' ),
                'view_item'          => __( 'View Form', 'contactum' ),
                'search_items'       => __( 'Search Form', 'contactum' ),
                'not_found'          => __( 'No Form Found', 'contactum' ),
                'not_found_in_trash' => __( 'No Form Found in Trash', 'contactum' ),
                'parent'             => __( 'Parent Form', 'contactum' ),
            ],
        ] );

        register_post_type( 'contactum_input', [
            'public'          => false,
            'show_ui'         => false,
            'show_in_menu'    => false,
        ] );
    }

    public function admin_menu() {
        global $submenu;

        $capability = 'manage_options';
        $slug       = 'contactum';

        $hook = add_menu_page( __( 'Contactum', 'contactum' ), __( 'Contactum', 'contactum' ), $capability, $slug, [ $this, 'forms_page' ], 'dashicons-text' );
        $contactum_forms = add_submenu_page( $slug, __( 'Forms', 'contactum' ), __( 'Forms', 'contactum' ), $capability, 'contactum', [ $this, 'forms_page'] );
        $contactum_entries = add_submenu_page( $slug, __( 'Entries', 'contactum' ), __( 'Entries', 'contactum' ), $capability, 'contactum-entries', [ $this, 'entries_page'] );
        $tools = add_submenu_page( $slug, __( 'Tools', 'contactum' ), esc_html__( 'Tools', 'contactum' ),$capability, 'contactum-tools', [ $this, 'tools_page' ] );
        $settings = add_submenu_page( $slug, __( 'Settings', 'contactum' ), esc_html__( 'Settings', 'contactum' ),$capability, 'contactum-settings', [ $this, 'settings_page' ] );

         $integration = add_submenu_page( $slug, __( 'Integrations', 'contactum' ), esc_html__( 'Integrations', 'contactum' ),$capability, 'contactum-integrations', [ $this, 'integration_page' ] );

        do_action( 'contactum_admin_menu', $slug );

//         add_action( 'load-' . $integration, array( $this, 'load_addon_scripts' ) );

        add_action( 'load-' . $contactum_entries, array( $this, 'load_entries_scripts' ) );

        add_action( 'load-' . $tools, array( $this, 'load_tools_scripts' ) );

        add_action( 'load-' . $settings, array( $this, 'load_settings_scripts' ) );

        add_action( 'load-' . $contactum_forms, array( $this, 'load_form_scripts' ) );

         add_action( 'load-' . $integration, array( $this, 'load_integration_scripts' ) );

    }

    public function forms_page() {
        $action           = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;
        $add_new_page_url = admin_url( 'admin.php?page=contactum&action=add-new' );
        $route           = isset( $_GET['route'] ) ? sanitize_text_field( wp_unslash( $_GET['route'] ) ) : null;
/*
        switch ( $action ) {
            case 'edit':
                require_once CONTACTUM_INCLUDES . '/html/form.php';
                break;
            case 'add-new':
                require_once CONTACTUM_INCLUDES . '/html/form.php';
                break;
            default:
                require_once CONTACTUM_INCLUDES . '/html/form-list-view.php';
                break;
        }
*/      

?>

        <div class="contactum_page_view">
<?php 
        if( empty( $route ) ) {
            include __DIR__ . '/html/menu.php';
        }
    ?>

         <div id="contactum-admin-forms">
            <router-view></router-view>
         </div>


        </div>

     <style>

        .contactum_page_view {
            background: #F8F9FA;
        }

         #contactum-admin-forms {
             padding-left: 24px;
             padding-right: 24px;
         }

     </style>

    <?php
    }

    public function entries_page() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : null;

//         switch ( $action ) {
//             case 'view':
//                 require_once CONTACTUM_INCLUDES . '/html/entry.php';
//                 break;
//             default:
//                 require_once CONTACTUM_INCLUDES . '/html/entry-list-view.php';
//                 break;
//         }

            include __DIR__ . '/html/menu.php';

        ?>
        <div id="contactum-admin-entries">
            <router-view></router-view>
        </div>
        <?php
    }

    public function tools_page() {
    //  require_once CONTACTUM_INCLUDES . '/html/tools.php';
      include __DIR__ . '/html/menu.php';
        ?>
        <div id="contactum-admin-tools"> </div>
        <?php
    }

    public function settings_page() {
        include __DIR__ . '/html/menu.php';
        ?>



<div class="contactum-settings">

  <div class="contactum-settings__sidebar-wrap">
    <aside class="contactum-settings__sidebar contactum_layout_section_sidebar">
      <ul class="contactum-settings__menu">

        <!-- Security -->
        <li class="contactum-settings__menu-item contactum-settings__menu-item--has-submenu">
          <a class="contactum-settings__menu-link" href="#">Security</a>

          <ul class="contactum-settings__submenu">
            <li class="contactum-settings__menu-item">
              <a
                data-hash="google_recaptcha"
                href="<?php echo admin_url('admin.php?page=contactum-settings#google_recaptcha'); ?>"
                data-component="reCaptcha"
                data-settings_key="google_recaptcha"
              >
                Google reCAPTCHA
              </a>
            </li>

            <li class="contactum-settings__menu-item">
              <a
                data-hash="hcaptcha"
                href="<?php echo admin_url('admin.php?page=contactum-settings#hcaptcha'); ?>"
                data-component="hCaptcha"
                data-settings_key="hcaptcha"
              >
                hCaptcha
              </a>
            </li>

            <li class="contactum-settings__menu-item">
              <a
                data-hash="turnstile"
                href="<?php echo admin_url('admin.php?page=contactum-settings#turnstile'); ?>"
                data-component="turnstile"
                data-settings_key="turnstile"
              >
                Turnstile
              </a>
            </li>
          </ul>
        </li>

        <?php
        $integrations = contactum()->integrations->get_integration_js_settings();
        if ( ! empty( $integrations ) ) { ?>

          <!-- Integrations -->
          <li class="contactum-settings__menu-item contactum-settings__menu-item--has-submenu">
            <a class="contactum-settings__menu-link" href="#">Configure Integrations</a>

            <ul class="contactum-settings__submenu">
              <?php foreach ( $integrations as $integration ) {
                $section = $integration['sections'];
                $url = admin_url( 'admin.php?page=contactum-settings#' . $section['id'] );
              ?>
                <li class="contactum-settings__menu-item">
                  <a
                    data-hash="<?php echo esc_attr( $section['id'] ); ?>"
                    href="<?php echo esc_url( $url ); ?>"
                    data-component="<?php echo esc_attr( $section['component'] ); ?>"
                    data-settings_key="<?php echo esc_attr( $section['id'] ); ?>"
                  >
                    <?php echo esc_html( $section['name'] ); ?>
                  </a>
                </li>
              <?php } ?>
            </ul>
          </li>

        <?php } ?>

      </ul>
    </aside>
  </div>

  <!-- Content -->
  <div class="contactum-settings__content">
    <div id="contactum-admin-settings">
      <router-view></router-view>
    </div>
  </div>

</div>



        <?php
    }

    public function integration_page() {
        include __DIR__ . '/html/menu.php';
        ?>
        <div id="contactum-admin-integration"> </div>
        <?php
    }

    public function load_addon_scripts() {
        wp_register_script( 'contactum-addon', CONTACTUM_ASSETS . '/js/addon.js', ['jquery'], CONTACTUM_VERSION, true );
        wp_enqueue_script( 'contactum-addon' );
    }

    public function load_entries_scripts() {
        wp_register_script( 'contactum-entries', CONTACTUM_ASSETS . '/js/entries.js', ['jquery'], CONTACTUM_VERSION, true );
        wp_enqueue_script( 'contactum-entries' );

        wp_enqueue_style('contactum-admin');
        wp_enqueue_style('contactum-admin-extra');

        wp_localize_script( 'contactum-entries', 'contactum', [
            'forms'   => contactum()->forms->all(),
            'entries' => contactum_get_all_entries(),
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ] );

    }

    public function load_tools_scripts() {
        wp_register_script( 'contactum-tools', CONTACTUM_ASSETS . '/js/tools.js', ['jquery'], CONTACTUM_VERSION, true );
        wp_enqueue_script( 'contactum-tools' );

        wp_enqueue_style('contactum-admin');
        wp_enqueue_style('contactum-admin-extra');

        wp_localize_script( 'contactum-tools', 'contactum', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'forms'   => contactum()->forms->all(),
            'entries' => contactum_get_all_entries(),
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
        ] );
    }

    public function load_settings_scripts() {
        wp_register_script( 'contactum-settings', CONTACTUM_ASSETS . '/js/settings.js', ['jquery'], CONTACTUM_VERSION, true );
        wp_enqueue_script( 'contactum-settings' );


        wp_localize_script( 'contactum-settings', 'contactum', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
            'forms'   => contactum()->forms->all(),
            'integrations' => contactum()->integrations->get_integration_js_settings()
        ] );

        wp_enqueue_style( 'contactum-admin' );
        wp_enqueue_style('contactum-admin-extra');

    }

    public function load_form_scripts() {

    }


    public function load_integration_scripts() {
        wp_register_script( 'contactum-integrations', CONTACTUM_ASSETS . '/js/integrations.js', ['jquery'], CONTACTUM_VERSION, true );
        wp_enqueue_script( 'contactum-integrations' );

        wp_localize_script( 'contactum-integrations', 'contactum', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'contactum-form-builder-nonce' ),
           'modules'   => class_exists( 'ContactumPro' ) && function_exists( '\contactum_pro_get_modules' )
                   ? \contactum_pro_get_modules()
                   : \contactum_free_get_modules(),
            'admin_url' => admin_url('admin.php'),
            'is_pro' => class_exists('ContactumPro') ? true : false,
        ] );

        wp_enqueue_style( 'contactum-admin' );
        wp_enqueue_style('contactum-admin-extra');
    }
}