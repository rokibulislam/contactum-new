        <div class="contactum_settings_wrap">
          <div class="contactum_settings_sidebar_wrap">
            <span class="contactum_sidebar_toggle" title="Toggle Setting"> <i class="ff-icon ff-icon-arrow-right"></i></span>
            <div class="contactum_settings_sidebar contactum_layout_section_sidebar">
              <ul class="contactum_settings_list contactum_list_button">
                <li class="contactum_list_button_item has_sub_menu">
                  <a class="contactum_list_button_link" href="#"> Security </a>
                  <ul class="contactum_list_submenu">
                  <li class="contactum_list_button_item">
                      <a data-hash="google_recaptcha" 
                      href="<?php echo admin_url('admin.php?page=contactum-settings#google_recaptcha'); ?>" 
                      data-component="reCaptcha"
                       data-settings_key="google_recaptcha">Google reCAPTCHA</a>
                  </li>
                  <li class="contactum_list_button_item">
                      <a data-hash="hcaptcha" href="<?php echo admin_url('admin.php?page=contactum-settings#hcaptcha'); ?>" 
                      data-component="hCaptcha"
                      data-settings_key="hcaptcha">hCaptcha</a>
                  </li>
                  <li class="contactum_list_button_item">
                      <a data-hash="turnstile" href="<?php echo admin_url('admin.php?page=contactum-settings#turnstile'); ?>" 
                      data-component="turnstile" data-settings_key="turnstile">turnstile</a>
                  </li>
                  </ul>
                </li>

              <?php
                  $integrations = contactum()->integrations->get_integration_js_settings();
                  if( !empty( $integrations ) ) { ?>

                <li class="contactum_list_button_item has_sub_menu">
                  <a class="contactum_list_button_link" href="#"> Configure Integrations </a>
                  <ul class="contactum_list_submenu has_sub_menu">
                      <?php
                      foreach( $integrations as  $integration ) {
                          $section = $integration['sections'];
                          $url = admin_url("admin.php?page=contactum-settings#". $section['id'] );
                      ?>
                        <li class="contactum_list_button_item">
                            <a data-hash="<?php echo  $section['id']; ?>"
                            href="<?php echo  $url; ?>"
                            data-component="<?php echo $section['component']; ?>" data-settings_key="<?php echo  $section['id']; ?>"><?php echo $section['name'] ; ?></a>
                        </li>
                      <?php } ?>
                   </ul>
                </li>

                <?php  } ?>
              </ul>
            </div>
          </div>

           <div class="settings_content">
               <div id="contactum-admin-settings">
                     <router-view></router-view>
               </div>
            </div>
        </div>