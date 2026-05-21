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
    <aside class="contactum-settings__sidebar">

      <div class="contactum-settings__sidebar-brand">
        <span class="dashicons dashicons-admin-settings"></span>
        <span><?php esc_html_e( 'Settings', 'contactum' ); ?></span>
      </div>

      <ul class="contactum-settings__menu">

        <!-- Security -->
        <li class="contactum-settings__menu-item contactum-settings__menu-item--has-submenu contactum-settings__menu-item--active">
          <span class="contactum-settings__group-label">
            <span class="dashicons dashicons-shield-alt"></span>
            <?php esc_html_e( 'Security', 'contactum' ); ?>
          </span>

          <ul class="contactum-settings__submenu">
            <li class="contactum-settings__menu-item">
              <a
                data-hash="google_recaptcha"
                href="<?php echo esc_url( admin_url( 'admin.php?page=contactum-settings#google_recaptcha' ) ); ?>"
                data-component="reCaptcha"
                data-settings_key="google_recaptcha"
              >
                <span class="dashicons dashicons-shield"></span>
                <?php esc_html_e( 'Google reCAPTCHA', 'contactum' ); ?>
              </a>
            </li>

            <li class="contactum-settings__menu-item">
              <a
                data-hash="hcaptcha"
                href="<?php echo esc_url( admin_url( 'admin.php?page=contactum-settings#hcaptcha' ) ); ?>"
                data-component="hCaptcha"
                data-settings_key="hcaptcha"
              >
                <span class="dashicons dashicons-lock"></span>
                <?php esc_html_e( 'hCaptcha', 'contactum' ); ?>
              </a>
            </li>

            <li class="contactum-settings__menu-item">
              <a
                data-hash="turnstile"
                href="<?php echo esc_url( admin_url( 'admin.php?page=contactum-settings#turnstile' ) ); ?>"
                data-component="turnstile"
                data-settings_key="turnstile"
              >
                <span class="dashicons dashicons-cloud"></span>
                <?php esc_html_e( 'Turnstile', 'contactum' ); ?>
              </a>
            </li>
          </ul>
        </li>

        <?php
        $integrations = contactum()->integrations->get_integration_js_settings();
        if ( ! empty( $integrations ) ) : ?>

          <!-- Integrations -->
          <li class="contactum-settings__menu-item contactum-settings__menu-item--has-submenu">
            <span class="contactum-settings__group-label">
              <span class="dashicons dashicons-admin-plugins"></span>
              <?php esc_html_e( 'Integrations', 'contactum' ); ?>
            </span>

            <ul class="contactum-settings__submenu">
              <?php foreach ( $integrations as $integration ) :
                $section = $integration['sections'];
                $url     = admin_url( 'admin.php?page=contactum-settings#' . $section['id'] );
              ?>
                <li class="contactum-settings__menu-item">
                  <a
                    data-hash="<?php echo esc_attr( $section['id'] ); ?>"
                    href="<?php echo esc_url( $url ); ?>"
                    data-component="<?php echo esc_attr( $section['component'] ); ?>"
                    data-settings_key="<?php echo esc_attr( $section['id'] ); ?>"
                  >
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php echo esc_html( $section['name'] ); ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>

        <?php endif; ?>

        <?php do_action( 'contactum_settings_sidebar_sections' ); ?>

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

<script>
(function () {
  'use strict';

  var COLLAPSED   = 'is-collapsed';
  var HAS_ACTIVE  = 'has-active-child';
  var ITEM_SEL    = '.contactum-settings__menu-item--has-submenu';
  var LABEL_SEL   = '.contactum-settings__group-label';
  var LINK_SEL    = '.contactum-settings__submenu a[data-component]';
  var ACTIVE_CLS  = 'active';

  function init() {
    var sidebar = document.querySelector('.contactum-settings__sidebar');
    if (!sidebar) return;

    var groups = sidebar.querySelectorAll(ITEM_SEL);

    // ── Wire up click-to-toggle on each group label ──────────────────────
    groups.forEach(function (group) {
      var label = group.querySelector(':scope > ' + LABEL_SEL);
      if (!label) return;

      label.addEventListener('click', function () {
        var isNowCollapsed = group.classList.toggle(COLLAPSED);

        // Collapse all other groups when this one is expanded
        if (!isNowCollapsed) {
          groups.forEach(function (other) {
            if (other !== group) {
              other.classList.add(COLLAPSED);
              var otherKey = 'ctm_sidebar_' + groupKey(other);
              sessionStorage.setItem(otherKey, '1');
            }
          });
        }

        updateActiveChild(group);

        // Persist collapse state in sessionStorage so refresh keeps it
        var key = 'ctm_sidebar_' + groupKey(group);
        sessionStorage.setItem(key, isNowCollapsed ? '1' : '0');
      });

      // Restore saved state
      var key = 'ctm_sidebar_' + groupKey(group);
      var saved = sessionStorage.getItem(key);
      if (saved === '1') {
        group.classList.add(COLLAPSED);
      }
    });

    // ── Mark groups that contain the current active link ─────────────────
    markActiveGroups(sidebar);

    // ── Observe future active-class changes (set by settings.js) ─────────
    var observer = new MutationObserver(function () {
      markActiveGroups(sidebar);
    });
    var submenuLinks = sidebar.querySelectorAll(LINK_SEL);
    submenuLinks.forEach(function (link) {
      observer.observe(link, { attributes: true, attributeFilter: ['class'] });
    });
  }

  function markActiveGroups(sidebar) {
    var groups = sidebar.querySelectorAll(ITEM_SEL);
    groups.forEach(function (group) {
      updateActiveChild(group);
    });
  }

  function updateActiveChild(group) {
    var hasActive = !!group.querySelector(LINK_SEL + '.' + ACTIVE_CLS);
    group.classList.toggle(HAS_ACTIVE, hasActive);

    // Auto-expand the group that contains the active link
    if (hasActive && group.classList.contains(COLLAPSED)) {
      group.classList.remove(COLLAPSED);
      var key = 'ctm_sidebar_' + groupKey(group);
      sessionStorage.setItem(key, '0');
    }
  }

  function groupKey(group) {
    var label = group.querySelector(':scope > ' + LABEL_SEL);
    return label ? label.textContent.trim().replace(/\s+/g, '_').toLowerCase() : Math.random();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>



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