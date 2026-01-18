<template>
  <div class="builder form-preview-stage">
    <form
      method="post"
      action
      id="contactum-form-builder"
      class="contactum-form-builde"
      v-on:submit.prevent="save_form_builder"
    >
      <div class="builder-header">
<!--        v-if="activeTab=='editor'"
            v-if="post && post.post_title && activeTab == 'editor' "
-->
        <span v-if="post &&  post.post_title"
          class="form-title"
          @click.prevent="post_title_editing = true"
        >
          <i class="el-icon-edit"></i> {{ post.post_title }}
          <input
              type="hidden"
              name="post_title"
              v-model="post.post_title"
          />
        </span>
        
        <div class="contactum-nav">
          <!-- nav-tab-wrapper  'nav-tab' -->
          <ul class="contactum-tabs">
            <li>
              <a
                href="#form-builder-container"
                :class="[ isActiveTab( 'editor' ) ? 'nav-tab-active' : '']"
                @click.prevent="makeActive('editor')"
              >Editor</a>
            </li>
            <li>
              <a
                href="#"
                :class="[ isActiveTab( 'settings' ) ? 'nav-tab-active' : '']"
                @click.prevent="makeActive('settings')"
              >Settings</a>
            </li>
            <li v-if="hasNotification()">
              <a
                href="#"
                :class="[ isActiveTab( 'notifications' ) ? 'nav-tab-active' : '']"
                @click.prevent="makeActive('notifications')"
              >Notifications</a>
            </li>
<!--            v-if="hasIntegration()"-->
            <li>
              <a
                href="#"
                :class="[ isActiveTab( 'integrations' ) ? 'nav-tab-active' : '']"
                @click.prevent="makeActive('integrations')"
              >Integration</a>
            </li>
          </ul>
        </div>

        <div class="builder-save">
          
          <span class="form-id btn btn-copy" title="click to copy shortcode" :data-clipboard-text="shortcode" v-if="post && post.ID">
              <!-- <i class="fa fa-clipboard" aria-hidden="true"></i> -->
              <i class="el-icon-document-copy"> </i>
              [contactum id="{{ post.ID }}"]
          </span>
<!--          v-if="activeTab=='editor'"-->
          <div class="save_form_builder">
            <EmbedModal 
              :visible.sync="showembeddialog"
              @close="showembeddialog = false;"
              @embed-form = "save_form_builder"
              :shortcode="shortcode"
            />
            <el-button @click.prevent="showembeddialog = true" type="primary"> Embed </el-button>
            <button type="submit"> Save Form <span v-if="loading == true" class="showLoading">
            <i class="fa fa-spinner fa-pulse" aria-hidden="true"></i></span></button>
<!--            <a :href="preview_url()" target="_blank"> <i class="el-icon-view"> </i> Preview </a>-->
          </div>
        
        </div>
     
      </div>


      <!-- <div class="tab-contents"> -->
        <div class="form-builder-container" v-show="isActiveTab('editor')">
          <header>
            <span v-show="post_title_editing">
              <RenameForm
                  :post="post"
                  :post_title_editing="post_title_editing"
                  :visible.sync="post_title_editing"
                   @rename-form = "save_form_builder"
                   @close="post_title_editing = false"
              />
            
            </span>
          </header>
          <div class="builder-body">
            
            <section class="form-field">
              <ul v-if="settings"
                :class="[ 'contactum-form', 'sortable-list', 'form-label-' + settings.label_position]"
              >

            <li v-if="!form_fields.length" class="empty-state">
              <h3>Start building your form</h3>
              <p>Drag fields from the right panel to begin.</p>
            </li>

                <li
                  v-for="(field, index) in form_fields"
                  :key="index"
                  :class="[ field.name, field.css, 'form-field-' + field.template, 'field-items',
                                    field.width ? 'field-size-' + field.width : '',
                                    ]"
                  :data-index="index"
                  data-source="stage"
                >
                  <div class="contactum-label">
                    <label v-if="!is_full_width(field.template) && ( field.template != 'submit_field' && field.template != 'name_field' ) ">
                      {{ field.label }}
                      <span
                        v-if="field.required && 'yes' === field.required"
                        class="required"
                      >*</span>
                    </label>
                  </div>

                  <!-- {{  field.template }} -->
                  <component :is="'form_' + field.template" :field="field"></component>
                  <div class="control-button">
                    <!-- <i class="el-icon-rank" v-if="field.template != 'submit_field'"> </i> -->
                    <i class="fa fa-arrows move"></i>
                    <button @click.prevent="select_field(field)">
                      <!-- <i class="fa fa-pencil"></i> -->
                      <i class="el-icon-edit"></i>
                    </button>
                    <button @click.prevent="delete_field(index)">
                      <!-- <i class="fa fa-trash"></i> -->
                      <i class="el-icon-delete"></i>
                    </button>
                    <button @click.prevent="duplicate_field(field,index)">
                      <!-- <i class="fa fa-clone"></i> -->
                      <i class="el-icon-copy-document"> </i>
                    </button>
                  </div>
                </li>
              </ul>
              <input type="hidden" name="contactum_form_id" :value="post.ID" />

              <div class="submit_wrapper" v-if="hasSubmitField() == undefined">
                <input type="submit" class="btn-submit" :value="settings.submit_text" disabled />
              </div>
            </section>
            <div class="field-panel">
              <div class="forms-fields-tab">
                <button
                  id="add-fields"
                  :class="['fields','form_fields' === current_panel ? 'active' : '' ]"
                  @click.prevent="sidebartab('form_fields')"
                >Add Fields</button>
                <button
                  id="field-options"
                  :class="['field_options' === current_panel ? 'active' : '', !form_fields.length ? 'disabled' : '']"
                  @click.prevent="sidebartab('field_options')"
                  :disabled="!form_fields.length"
                >Field Options</button>
              </div>
              <div class="forms-sidbar-tab-content">
                <component :is="current_panel"></component>
              </div>
            </div>
          </div>
        </div>

        <div class="form-builder-settings" v-show="isActiveTab('settings')">
          <form_settings :id="id" />
        </div>

        <div class="form-builder-notifications" v-show="isActiveTab('notifications')">
          <form_notifications @save-notification="save_form_builder"/>
        </div>

        <div class="form-builder-notifications" v-if="isActiveTab('integrations')">
          <Intergrations @save-integration="save_form_builder" />
        </div>
      <!-- </div> -->

      <!-- OTHER MODAL/POPUP COMPONENTS -->


      <!--
      <editorInserter :visible.sync="editorInserterVisible">
       -->

    </form>
  </div>
</template>

<script>
import axios from "axios";
import  ProFeature from '../dialog/ProFeature.vue'
import { v4 as uuidv4 } from "uuid";
import draggable from "vuedraggable";
import form_notifications from "../form-notifications/index.vue";
import form_settings from "../form-settings/index.vue";
import Intergrations from "../intergration/index.vue";
import form_fields from "../form-fields/index.vue";
import field_options from "../field-options/index.vue";

import form_text_field from "../form-templates/text.vue";
import form_textarea_field from "../form-templates/textarea.vue";
import form_url_field from "../form-templates/url.vue";
import form_name_field from "../form-templates/name.vue";
import form_email_field from "../form-templates/email.vue";
import form_checkbox_field from "../form-templates/checkbox.vue";
import form_radio_field from "../form-templates/radio.vue";
import form_image_field from "../form-templates/image.vue";
import form_date_field from "../form-templates/date.vue";
import form_dropdown_field from "../form-templates/dropdown.vue";
import form_multiple_select from "../form-templates/multiselect.vue";
import form_html_field from "../form-templates/html.vue";
import form_hidden_field from "../form-templates/hidden.vue";
import form_section_break from "../form-templates/section.vue";
import form_number_field from "../form-templates/number.vue";
import form_recaptcha from "../form-templates/recaptcha.vue";
import form_math_captcha from "../form-templates/math-captcha.vue";
import form_submit_field from "../form-templates/submit.vue";
import form_password_field from "../form-templates/password.vue";
import form_hcaptcha from "../form-templates/hcaptcha.vue";
import form_turnstile from "../form-templates/turnstile.vue";

import form_phone_field from "../pro/form-template/phone.vue";
import form_address_field from "../pro/form-template/address.vue";
import form_country_field from "../pro/form-template/country.vue";
import form_file_field from "../pro/form-template/file.vue";
import form_gmap_field from "../pro/form-template/gmap.vue";
import form_shortcode_field from "../pro/form-template/shortcode.vue";
import form_hook_field from "../pro/form-template/hook.vue";
import form_step_field from "../pro/form-template/multistep.vue";
import form_checkbox_grid from "../pro/form-template/checkbox-grid.vue";
import form_multiple_choice_grid from "../pro/form-template/multiple-choice-grid.vue";
import form_linear_scale from "../pro/form-template/linear.vue";
import form_toc from "../pro/form-template/terms.vue";
import form_signature_field from "../pro/form-template/signature.vue";
import form_total from "../pro/form-template/total.vue";
import form_single_product from "../pro/form-template/single-product.vue";
import form_multiple_product from "../pro/form-template/multiple-product.vue";
import form_payment_method from "../pro/form-template/payment-method.vue";
import form_rating_field from "../pro/form-template/rating.vue";
import form_repeat_field from "../pro/form-template/repeat.vue";
import form_column_field from "../form-templates/column.vue";
import form_subscription_field from "../pro/form-template/subscription.vue";
import form_coupon_field from "../pro/form-template/coupon.vue";

import RenameForm from "../dialog/RenameForm.vue";
import EmbedModal from "../dialog/EmbedModal.vue";

export default {
  name: "Builder",
  props: ["id"],
  components: {
    draggable,
    form_notifications,
    form_settings,
    Intergrations,
    form_text_field,
    form_textarea_field,
    form_url_field,
    form_name_field,
    form_email_field,
    form_checkbox_field,
    form_radio_field,
    form_image_field,
    form_date_field,
    form_dropdown_field,
    form_multiple_select,
    form_html_field,
    form_hidden_field,
    form_section_break,
    form_number_field,
    form_recaptcha,
    field_options,
    form_fields,
    form_phone_field,
    form_address_field,
    form_country_field,
    form_file_field,
    form_gmap_field,
    form_shortcode_field,
    form_hook_field,
    form_step_field,
    form_checkbox_grid,
    form_multiple_choice_grid,
    form_linear_scale,
    form_toc,
    form_signature_field,
    form_total,
    form_single_product,
    form_multiple_product,
    form_payment_method,
    form_rating_field,
    form_math_captcha,
    form_submit_field,
    form_repeat_field,
    form_column_field,
    form_password_field,
    form_hcaptcha,
    form_turnstile,
    form_subscription_field,
    form_coupon_field,
    RenameForm,
    EmbedModal,
    ProFeature
  },
  data() {
    return {
      form_fields_components: [],
      post_title_editing: false,
      activeTab: "editor",
      loading: false,
      dialogVisible: false,
      showembeddialog: false,
      editorInserterVisible: false
    };
  },
  computed: {
    panel_sections: function () {
      return this.$store.getters.panel_sections;
    },
    field_settings: function () {
      return this.$store.getters.field_settings;
    },
    form_fields: {
      get: function () {
        return this.$store.getters.form_fields;
      },
      set: function (value) {
        this.$store.dispatch("set_form_fields", value);
      },
    },
    post: function () {
      return this.$store.getters.post;
    },
    current_panel: function () {
      return this.$store.getters.current_panel;
    },
    notifications: function () {
      return this.$store.getters.notifications;
    },
    settings: function () {
      return this.$store.getters.settings;
    },
    integrations: function () {
      return this.$store.getters.integrations;
    },
    shortcode: function() {
      return this.post && this.post.ID ? `[contactum id="${this.post.ID}"]` : '';
    }
  },

  methods: {
    hasSubmitField: function() {
       let response =  this.form_fields.find( field => field.template== "submit_field" );

       return response;
       
    },
    hasIntegration: function () {
      return Object.keys(this.integrations).length > 0 ? true : false;
    },
    hasNotification: function () {
      return this.notifications && Object.keys(this.notifications).length > 0 ;
    },
    // makeActive: function (tab, event) {
    makeActive: async function (tab) {
      var self = this;
      this.activeTab = tab;
      if (tab === 'integrations' && (!this.integrations || Object.keys(this.integrations).length === 0)) {
        jQuery.post(window.contactum.ajaxurl, {
          action: 'contactum_get_integrations',
          post_id: this.id,
          _ajax_nonce: window.contactum.nonce
        }, (response, textStatus, xhr) => {
          if (response.success) {
            this.$store.dispatch("set_form_integrations", response.data);
          }
        });
      }
    },

    isActiveTab: function (val) {
      return this.activeTab === val;
    },

    add_field(field) {
      this.$store.dispatch("add_field", field);
    },

    delete_field(index) {
      this.$confirm('Are you sure you want to delete this field?', 'Warning', {
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        type: 'warning',
        center: true, // optional, centers the dialog
      }).then(() => {
        this.$store.dispatch("delete_field", index);
      }).catch(() => {
        // Optional: action if user cancels
        this.$message({
          type: 'info',
          message: 'Deletion cancelled',
          position: "bottom-right"
        });
      });

    /*
      this.$swal({
        title: "Are you sure?",
        text: "you want to delete this field?",
        icon: "warning",
        type: "warning",
        showCloseButton: true,
        showCancelButton: true,
        cancelButtonColor: "#e0e0e0",
        confirmButtonColor: "#e53935",
        confirmButtonText: "Yes Delete",
        cancelButtonText: " No Cancel",
        customClass: {
          icon: "my-swal-icon",
        },
      }).then((result) => {
        if (result.value) {
          this.$swal(
              "Deleted",
              "You successfully deleted this Field",
              "success"
          );
          this.$store.dispatch("delete_field", index);
        }
      });

      */
    },

    select_field(field) {
      this.$store.dispatch("select_field", field.id);
    },

    duplicate_field(field, index) {
      let payload = {
        ...field,
        id: uuidv4(),
        is_new: true,
        index: index,
      };


      let same_template_fields = this.form_fields.filter((form_field) => {
        return form_field.template === field.template;
      });

      if (same_template_fields.length) {
        payload.name = payload.label.replace(/\W/g, "_").toLowerCase();
        payload.name += "_" + same_template_fields.length;
      }

      this.$store.dispatch("duplicate_field", payload);
    },

    is_full_width: function (template) {
      if (
        this.field_settings[template] &&
        this.field_settings[template].is_full_width
      ) {
        return true;
      }

      return false;
    },

    is_invisible: function (field) {
      return field.recaptcha_type &&
        "invisible_recaptcha" === field.recaptcha_type
        ? true
        : false;
    },

    save_form_builder() {
      this.loading = true;
      var self = this;

      /*
      const payload = {
        action: "save_contactum_form",
        form_data: new FormData(document.getElementById("contactum-form-builder")),
        form_fields: JSON.stringify(this.form_fields),
        notifications: JSON.stringify(this.notifications),
        settings: JSON.stringify(this.settings),
        integrations: JSON.stringify(this.integrations),
        contactum_form_builder_nonce: contactum.nonce,
      };
      */

      const payload = {
          action: "save_contactum_form",
        form_data: jQuery("#contactum-form-builder").serialize(),
          form_fields: JSON.stringify(this.form_fields),
          notifications: JSON.stringify(this.notifications),
          settings: JSON.stringify(this.settings),
          integrations: JSON.stringify(this.integrations),
          contactum_form_builder_nonce: contactum.nonce,
      };

      jQuery.post(
        contactum.ajaxurl,
        payload,
        (response, textStatus, xhr) => {
            this.loading = false;

          if (response.data.form_fields) {
            this.$store.dispatch("set_form_fields", response.data.form_fields);
          }

          if (response.data.notifications) {
            this.$store.dispatch(
              "set_form_notification",
              response.data.notifications
            );
          }

          if (response.data.form_settings) {
            this.$store.dispatch(
              "set_form_settings",
              response.data.form_settings
            );
          }
          if (response.data.integrations) {
            this.$store.dispatch(
              "set_form_integrations",
              response.data.integrations
            );
          }

          this.$notify.success({
            title: '',
            message: 'Form successfully Save.',
            position: "bottom-right"
          });

        }
      );
    },
    preview_url: function () {
      return window.contactum.preview_url;
    },
    sidebartab(value) {
      this.$store.dispatch("set_current_panel", value);
    },

    add_field(field_template, toIndex) {
      var payload = {
        ...this.field_settings[field_template].field_props,
        id: uuidv4(),
        toIndex: toIndex,
      };

      if (!payload.name && payload.label) {
        payload.name = payload.label.replace(/\W/g, "_").toLowerCase();

        var same_template_fields = this.form_fields.filter((form_field) => {
          return form_field.template === field_template;
        });

        if (same_template_fields.length) {
          payload.name += "_" + same_template_fields.length;
        }
      }

      this.$store.dispatch("add_field", payload);
    },

    swap_form_field_elements(payload) {
      this.$store.dispatch("swap_form_field_elements", payload);
    },

    initSortable() {
      var self  = this;

      jQuery(".form-preview-stage .contactum-form.sortable-list").sortable({
        placeholder: "form-preview-stage-dropzone",
        items: ".field-items",
        handle: ".control-button .move",
        scroll: true,
        activate: function (event, ui) {

        },
        over: function () {
        },
        update: function (e, ui) {
          var item = ui.item[0],
              data = item.dataset,
              source = data.source,
              toIndex = parseInt(jQuery(ui.item).index()),
              payload = {
                toIndex: toIndex,
              };

          if ("panel" === source) {
            var field_template = ui.item[0].dataset.formField;
            self.add_field(field_template, toIndex);

            // remove button from stage
            jQuery(this)
                .find(".panel-button.ui-draggable.ui-draggable-handle")
                .remove();
          } else if ("stage" === source) {
            payload.fromIndex = parseInt(data.index);
            self.swap_form_field_elements(payload);
          }
        },
      });

    }
  },

  mounted: function () {
    var self = this;

    const postId = this.id // Your form post ID
    const nonce = window.contactum?.nonce // If needed for authentication
    const ajaxurl = window.contactum?.ajaxurl // If needed for authentication

    jQuery.post(ajaxurl, {
      action: 'contactum_get_admin_data',
      post_id: postId,
      _ajax_nonce: nonce
    },
    (response, textStatus, xhr) => {
      if (response.success) {
        this.$store.dispatch("setContactumData", response.data);
      } else {

      }
    });

    // bind jquery ui sortable
    this.$nextTick(() => {
      this.initSortable();
    });

    //bind clipboard
    let clipboard = new ClipboardJS(".form-id");

    jQuery(".form-id").tooltip();

    clipboard.on("success", function (e) {
      self.$notify.success({
        title: 'Success',
        message: 'Copied to Clipboard.',
        position: "bottom-right"
      });
      e.clearSelection();
    });
  },

  watch: {
    form_fields: {
      handler: function () {
        this.isDirty = true;
        this.$nextTick(() => {
          this.initSortable();
        });
      },
      deep: true,
    },
  },
};
</script>

<style scoped lang="scss">

// $primary-color: #007bff;
// $secondary-color: #6c757d;
$background-color: #fff;
$button-background-color: #0076FF;
$text-color: #000;
$button-background-secondary-color: #dedede;
$button-text-secondary-color: #545454;

.builder {
    display: flex;
    .builder-header {
        display: flex;
        margin-bottom: 5px;
        background: $background-color;
        padding-top: 10px;

        align-items: center;
        border-bottom: 1px solid #e5e7eb;

        .contactum-nav {
            display: flex;
            flex: 1;
            .contactum-tabs {
                flex: 1;
                display: flex;
                gap: 25px;
                li a {
                  text-decoration: none;
                  color: $button-text-secondary-color;
                  font-size: 15px;
                  font-weight: 600;
                  padding: 8px 0;
                  position: relative;
                }

                li a.nav-tab-active {
                  color: $button-background-color;
                }


              li a.nav-tab-active::after {
                content: "";
                position: absolute;
                bottom: -12px;
                left: 0;
                width: 100%;
                height: 2px;
                background: #0076ff;
              }

            }

            .nav-tab-wrapper {
                padding-top: 0px !important;
            }

            .nav-tab-wrapper li {
                margin-bottom: 0px;
            }
        }

        .builder-save {
            display: flex;
            justify-content: flex-end;
            flex: 2;
            box-sizing: border-box;
            align-items: start;
            gap: 15px;
        }
    }

    .save_form_builder {
        display: flex;
        align-items: start;
        gap: 15px;
        // justify-content: flex-end;
        button, a {
            display: block;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 14px;
            background: $button-background-color;
            color: #fff;
            margin-right: 5px;
            outline: none;
            transition: 2s background;
            border: 1px solid transparent;
            border-radius: 8px;
        }

        button:hover {
            background: $button-background-color;
        }

        a {
          text-decoration: none;
          text-align: center;
          background: $button-background-secondary-color;
          color: $button-text-secondary-color
        }
    }
}

.field-panel {
    flex-basis: 40%;
    background: #f9f9f9;
    .forms-fields-tab {
        display: flex;
        width: 100%;
        background: $background-color;

        button {
          width: 50%;
          display: block;
          padding: 15px 0;
          font-weight: 600;
          text-align: center;
          color: #23282d;
          background: #fff;
          border: none;
          cursor: pointer;
          outline: none;
        }

        button.active {
            border-bottom: 2px solid $button-background-color;
        }
    }
}

.form-field {
  flex-basis: 60%;
  margin-right: 15px;
  background: none;
  box-sizing: border-box;
  padding: 10px;
}

form#contactum-form-builder {
  width: 100%;
}

.builder-body {
    display: flex;
    .form-field ul {
        li {
          width: 100%;
          min-width: 70px;
          padding-left: 10px;
          padding-right: 10px;
          padding-bottom: 10px;
          box-sizing: border-box;
          position: relative;
          margin-bottom: 20px;

          &:hover {
            background-color: rgba(30,31,33,.05);
          }

            .control-button {
              display: none;
              position: absolute;
              justify-content: center;
              align-items: center;
              -webkit-box-align: center;
              background: #f9f9f9;
              background: #000;
              top: 0;
              right: 0;
              gap: 5px;
               padding: 4px;


              button {
                color: #fff;
                background: #000;
                padding: 8px 11px;
                display: inline-flex;
                border: none;
                cursor: pointer;
              }

              button:hover {
                background: $button-background-color;
              }
            }
        }

        li:hover > .control-button {
              display: block;
        }  

        li ul {
            margin-top: 5px;
        }
    }

    .form-field ul
    .submit_wrapper {
      display: flex;
    }

    .submit_wrapper .btn-submit {
      border: 1px solid transparent;
      border-radius: 8px;
      padding: 10px 20px;
      background: $button-background-color;
      color: #fff;
      display: inline-block;
      border: none;
      color: #fff;
      // min-width: 120px;
      margin-top: 30px;
      font-size: 14px;
      font-weight: 400;
      cursor: pointer;
    }
}
.contactum-fields {
    margin-bottom: 10px;
}

ul.contactum-form  {
    
    border: 1px dashed #cfcfcf;
    min-height: 70px;
    // margin: 0 10px;
    margin-left: 0px;

    li.field-size-small .contactum-fields {
        width: 30%;
    }
    li.field-size-medium .contactum-fields {
        width: 65%;
    }
    li.field-size-large .contactum-fields {
        width: 100%;
    }

    li.name {
        .contactum-fields {
            display: flex;
            justify-content: space-between;
            div {
              margin-right: 10px;
            }
        }
    }

    li.field-items {
      background: #ffffff;
      border-radius: 12px;
      padding: 16px;
      border: 1px solid #e5e7eb;
      transition: all 0.2s ease;
      
      &:hover {
        border-color: #0076ff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
      }
    }
}

ul.contactum-form.form-label-above li .contactum-label {
  display: block;
  width: 100%;
  margin-bottom: 10px;
}

ul.contactum-form.form-label-hidden li .contactum-label {
  display: none;
}

.form-builder-container {
    section {
        height: calc(100vh - 170px);
        overflow-y: auto;
    }

    header {
        margin-bottom: 20px;
        margin-top: 10px;

        button {
            // width: 40px;
        }

        span i.fa.fa-edit {
          font-size: 20px;
          cursor: pointer;
        }

        span.form-id {
          // background: #7e3bd0;
          background: #0076FF;
          padding: 5px 10px;
          color: #fff;
          display: inline-block;
          margin-left: 5px;
          cursor: pointer;
        }
    }
}

.form-preview-stage .field-items .control-button i.move {
  cursor: move;
  color: #fff;
}

ul.contactum-form.form-label-left li,
ul.contactum-form.form-label-right li {
  display: flex;
  justify-content: space-between;
}

ul.contactum-form.form-label-left li div.contactum-label,
ul.contactum-form.form-label-right li div.contactum-label {
  flex-basis: 20%;
}

ul.contactum-form.form-label-right {
    li {
        flex-direction: row-reverse;
        div.contactum-fields {
            flex-basis: 75%;
        }
    }
}


.btn {
  padding: 8px 15px;
}

.btn-copy {
    background: #dedede;
    color: #545454;
    overflow: hidden;
    opacity: 1;
    border: none;
    cursor: copy;
    border-radius: 8px;
}

.form-title {
  padding: 15px;
  display: block;
}

.modal-input {
  width: 100%;
  margin-top: 10px;
  margin-bottom: 10px;
  display: block;
}


.forms-sidbar-tab-content {
  padding: 20px;
  box-sizing: border-box;
  background-color: transparent;
}


.empty-state {
  height: 100%;
  border: 2px dashed #cbd5e1;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  color: #64748b;
}

</style>
