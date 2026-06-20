<template>
  <div class="contactum_settings_wrap">
    <div class="contactum_settings_sidebar_wrap">
        <span class="contactum_sidebar_toggle" title="Toggle Setting">
          <i class="ff-icon ff-icon-arrow-right"></i>
        </span>
      <div class="contactum_settings_sidebar contactum_layout_section_sidebar">
        <ul class="contactum_settings_list contactum_list_button">
          <li class="contactum_list_button_item has_sub_menu">
            <a class="contactum_list_button_link" href="#"> Settings </a>
            <ul class="contactum_list_submenu">
              <li class="contactum_list_button_item">
                <a href="#layout"> Form Layout </a>
              </li>
              <li class="contactum_list_button_item">
                <a href="#confirmation"> Confirmation Settings </a>
              </li>
              <li class="contactum_list_button_item">
                <a href="#scheduling"> Scheduling & Restrictions </a>
              </li>
              <li class="contactum_list_button_item">
                <a href="#custom-css-js"> custom css & js</a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
    <div class="settings_content">
      <div id="contactum-admin-settings">
        <div class="contactum_card" id="layout">
          <table class="form-table">
            <tbody>
            <tr class="contactum-label-position">
              <th> Label Position </th>
              <td>
                <el-select v-model="settings.label_position">
                  <el-option v-for="label_position in label_positions" :key="label_position.value" :label="label_position.label" :value="label_position.value"></el-option>
                </el-select>
                <p class="description"> Where the labels of the form should display </p>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="contactum_card" id="confirmation">
          <table class="form-table">
            <tbody>
            <tr class="contactum-redirect-to" >
              <th> Redirect To </th>
              <td>
                <el-select v-model="settings.redirect_to">
                  <el-option v-for="redirect_to in redirects_to" :key="redirect_to.value" :label="redirect_to.label" :value="redirect_to.value"></el-option>
                </el-select>
                <p class="description"> After successful submit, where the page will redirect to. This redirect option will not work if Show Report in Frontend option is enabled </p>
              </td>
            </tr>
            <tr class="contactum-same-page" v-show="settings.redirect_to == 'same'">
              <th> Message to show </th>
              <td>
                <wp_editor :value="settings.message" @content-changed="onEditorContentChange" />
                <!--                        <el-input type="textarea" :rows="3" :cols="40" v-model="settings.message"></el-input>-->
              </td>
            </tr>
            <tr class="contactum-page-id" v-show="settings.redirect_to == 'page'">
              <th> Page </th>
              <td>
                <el-select v-model="settings.page_id">
                  <el-option v-for="(page,index) in settings.pages" :key="index" :label="index" :value="page"></el-option>
                </el-select>
              </td>
            </tr>
            <tr class="contactum-url" v-show="settings.redirect_to == 'url'">
              <th> Custom URL </th>
              <td>
                <el-input type="url" v-model="settings.url"></el-input>
              </td>
            </tr>
            <tr class="contactum-submit-text">
              <th> Submit Button text </th>
              <td>
                <el-input type="text" v-model="settings.submit_text" class="regular-text"></el-input>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="contactum_card" id="scheduling">
          <table class="form-table">
            <tbody>
            <tr class="contactum-schedule-entries">
              <th> Schedule form </th>
              <td>
                <label class="contactum-switch">
                  <el-checkbox v-model="settings.schedule_form"></el-checkbox>
                  <span class="switch-slider round"></span> Schedule form for a period
                </label>
                <p class="description"> Schedule for a time period the form is active. </p>
              </td>
            </tr>
            <tr class="contactum-schedule-period" v-show="settings.schedule_form == true ">
              <th> Schedule Period </th>
              <td class="schedule-field">
                <div class="label"> From <el-date-picker v-model="settings.schedule_start" type="date" placeholder="From"></el-date-picker>
                </div>
                <div class="label"> To <el-date-picker v-model="settings.schedule_end" type="date" placeholder="To"></el-date-picker>
                </div>
              </td>
            </tr>
            <tr class="contactum-schedule-pending" v-show="settings.schedule_form == true">
              <th> Form Pending Message </th>
              <td>
                <wp_editor :value="settings.sc_pending_message" @content-changed="onEditorContentChange" />
                <!--                        <el-input type="textarea" :rows="3" :cols="40" v-model="settings.sc_pending_message"></el-input>-->
              </td>
            </tr>
            <tr class="contactum-schedule-expired" v-show="settings.schedule_form == true">
              <th> Form Expired Message </th>
              <td>
                <wp_editor :value="settings.sc_expired_message" />
                <!--                        <el-input type="textarea" :rows="3" :cols="40" v-model="settings.sc_expired_message"></el-input>-->
              </td>
            </tr>
            <tr class="contactum-require-login">
              <th> Require Login </th>
              <td>
                <label class="contactum-switch">
                  <el-checkbox v-model="settings.require_login"></el-checkbox>
                  <span class="switch-slider round"></span> Require user to be logged in
                </label>
              </td>
            </tr>
            <tr class="contactum-limit-message" v-show="settings.require_login == true">
              <th> Require Login Message </th>
              <td>
                <wp_editor :value="settings.req_login_message" @content-changed="onEditorContentChange" />
                <!--                        <el-input type="textarea" :rows="3" :cols="40" v-model="settings.req_login_message"></el-input>-->
              </td>
            </tr>
            <tr class="contactum-limit-entries">
              <th> Limit Entries </th>
              <td>
                <label class="contactum-switch">
                  <el-checkbox v-model="settings.limit_entries"></el-checkbox>
                  <span class="switch-slider round"></span> Enable form entry limit
                </label>
                <p class="description"> Limit the number of entries allowed for this form </p>
              </td>
            </tr>
            <tr class="contactum-number-entries" v-show="settings.limit_entries == true">
              <th> Number of Entries </th>
              <td>
                <el-input type="number" v-model="settings.limit_number"></el-input>
              </td>
            </tr>
            <tr class="contactum-limit-message" v-show="settings.limit_entries == true">
              <th> Limit Reached Message </th>
              <td>
                <wp_editor :value="settings.limit_message" @content-changed="onEditorContentChange" />
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <div class="contactum_card" id="custom-css-js">
          <table class="form-table">
            <tbody>
            <tr class="contactum-use-theme-css">
              <th> Use Theme CSS</th>
              <td>
                <select v-model="settings.use_theme_css">
                  <option value="contactum-theme-style"> Yes </option>
                  <option value="contactum-style"> No </option>
                </select>
                <p class="description"> Selecting <strong>Yes</strong> will use your theme's style for form fields. </p>
              </td>
            </tr>
            <tr id="custom-css-js">
              <th> Custom Css </th>
              <td>
                <div>
                  <p>You can write your custom CSS here for this form. This css will be applied in this current form only. You may add #contactum_form_{{id}} as your css selector prefix to target this specific form. </p>
                  <AceCSSEditor mode="css" v-model="settings.custom_css" />
                  <p> Please don't include tag </p>
                </div>
              </td>
            </tr>
            <tr>
              <th> Custom JS {{ settings.custom_js }} </th>
              <td>
                <div>
                  <p>Your additional JS code will run after this form initialized. Please provide valid javascript code. Invalid JS code may break the Form. The Following Javascript variables are available that you can use : $form: The Javascript(jQuery) DOM object of the Form</p>
                  <AceJSEditor mode="javascript" v-model="settings.custom_js" /> Please don't include tag
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script>

import wp_editor from '../components/common/wp-editor.vue'
import AceJSEditor from "../components/common/ace-editor-js.vue";
import AceCSSEditor from "../components/common/ace-editor-css.vue";
import ace from "ace-builds/src-noconflict/ace";
import "ace-builds/src-noconflict/mode-javascript"; // or mode-css, mode-html, etc.
import "ace-builds/src-noconflict/theme-monokai";   // theme
// Import Ace modes and themes
import "ace-builds/src-noconflict/mode-css";

export default {
  name: "FormSettings",
  components: {
    wp_editor,
    AceJSEditor,
    AceCSSEditor
  },
  props: ['id'],
  data: function() {
        return {
            settings: {},
            label_positions: [
                {
                    'value': 'above',
                    'label': 'Above Element'
                },
                {
                    'value': 'left',
                    'label': 'Left of Element'
                },
                {
                    'value': 'right',
                    'label': 'Right of Element'
                },
                {
                    'value': 'hidden',
                    'label': 'Hidden'
                }
            ],

            redirects_to: [
                {
                    'value': 'same',
                    'label': 'Same Page'
                }, 
                {
                    'value': 'page',
                    'label': 'To a page'
                }, 

                {
                    'value': 'url',
                    'label': 'To a custom URL'
                }, 
            ],
        }
    },
  mounted() {
    this.fetchSettings();
  },
  methods: {

    saveSettings() {

        let data = {
            action: 'contactum_save_form_settings',
            _ajax_nonce: contactum.nonce,
            form_id: this.id,
            settings: this.settings,
        };

        jQuery.ajax({
            url: contactum.ajaxurl,
            type: 'post',
            data: data,
            success: function (response) {
                self.settings = response.data;
            }
        });
    },
    
    fetchSettings() {
        var self = this;
      
        let data = {
            action: 'contactum_get_form_settings',
            _ajax_nonce: contactum.nonce,
            form_id: this.id
        };

        jQuery.ajax({
            url: contactum.ajaxurl,
            type: 'post',
            data: data,
            success: function (response) {
                console.log( response );
                self.settings = {
                    ...response.data,
                    schedule_form: response.data.schedule_form === 'true',
                    require_login:  response.data.require_login === 'true',
                    limit_entries:  response.data.require_login === 'true',
                   // use_theme_css:  response.data.use_theme_css === 'true',
                }
            }
        });
    },

    onEditorContentChange() {

    }
  }
};
</script>

<style type="text/css" scoped>

    .notification-toolbar {
        display: flex;
        justify-content: space-between;
    }

    .form-settings {
        padding-left: 20px;
    }

    .schedule-field {
        display: flex;
        label {
            margin-right: 5px;
        }
    }
</style>
