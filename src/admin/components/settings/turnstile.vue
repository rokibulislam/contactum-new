<template>

    <el-form label-position="top">
      <div class="contactum_card">

        <div class="contactum_card_head">
          <h2 class="title"> Cloudflare Turnstile Settings </h2>
          <p class="text"> CONTACTUM integrates with Cloudflare Turnstile, a free service that protects your website from spam and abuse. Please note, these settings are required only if you decide to use the Turnstile field. Read more about Cloudflare Turnstile.
            Please generate API key and API secret using Cloudflare Turnstile
          <a href="https://www.cloudflare.com/en-gb/products/turnstile/" target="_blank">Read more about Cloudflare Turnstile.</a>
          </p>
          <p class="text"><b>Please generate API key and API secret using Cloudflare Turnstile</b></p>
        </div>

        <div class="contactum_card_bdy">

        <!--Site key-->
          <el-form-item class="contactum-form-item">
            <template slot="label">Site Key
              <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
                <div slot="content">
                  <p>Enter your Turnstile Site Key, if you do not have a key you can register for one at the provided link Turnstile is a free service.
                  </p>
                </div>
                <i class="ff-icon ff-icon-info-filled text-primary"></i>
              </el-tooltip>
            </template>
            <el-input v-model="turnstile.siteKey" @change="load"></el-input>
          </el-form-item>

          <!--Secret key-->
          <el-form-item class="contactum-form-item">
            <template slot="label">Secret Key
              <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
                <div slot="content">
                  <p>Enter your Turnstile Secret Key, if you do not have a key you can register for one at the provided link, Turnstile is a free service.</p>
                </div>
                <i class="ff-icon ff-icon-info-filled text-primary"></i>
              </el-tooltip>
            </template>
            <el-input type="password" v-model="turnstile.secretKey" @change="load"></el-input>
          </el-form-item>

          <el-form-item class="contactum-form-item">
            <template slot="label">Appearance Mode
              <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
                <div slot="content">
                  <p>You can select how the turnstile will appear
                  </p>
                </div>
                <i class="ff-icon ff-icon-info-filled text-primary"></i>
              </el-tooltip>
            </template>
            <el-radio class="mr-3" v-model="turnstile.appearance" label="always">Managed</el-radio>
            <el-radio class="mr-3" v-model="turnstile.appearance" label="execute">Non-interactive</el-radio>
            <el-radio class="mr-3" v-model="turnstile.appearance" label="interaction-only">Invisible</el-radio>
          </el-form-item>

          <el-form-item class="contactum-form-item">
            <template slot="label">Theme
              <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
                <div slot="content">
                  <p>Choose a theme for the field</p>
                </div>
                <i class="ff-icon ff-icon-info-filled text-primary"></i>
              </el-tooltip>
            </template>
            <el-radio v-model="turnstile.theme" label="auto">Auto</el-radio>
            <el-radio v-model="turnstile.theme" label="light">Light</el-radio>
            <el-radio v-model="turnstile.theme" label="dark">Dark</el-radio>
          </el-form-item>

          <!--Validate Keys-->
          <el-form-item :label="'Validate Keys'" v-if="siteKeyChanged">
            <div
                class="cf-turnstile"
                id="turnstile"
                :data-sitekey="turnstile.siteKey"
            ></div>
          </el-form-item>

        <div v-if="turnstile_status && !disabled" size="sm" type="success-soft">
          <p> Your Cloudflare Turnstile is valid </p>
        </div>

        </div>

      </div>

      <div class="mt-4">
        {{ disabled }} {{ loading }}
        <el-button
            type="primary"
            icon="el-icon-success"
            @click="save"
            :disabled="disabled || loading"
            :loading="false"
        >Save Settings
        </el-button>

        <el-button
            type="danger"
            icon="ff-icon ff-icon-trash"
            @click="clearSettings"
            :loading="false"
        >Clear Settings</el-button>
      </div>

    </el-form>
</template>

<script>

export default {

  name: "turnstile",

  data() {
    return {
      turnstile: {
        siteKey: "",
        secretKey: "",
        invisible: "no",
        appearance: 'always',
        theme: 'auto'
      },
      turnstile_status: false,
      siteKeyChanged: false,
      disabled: false,
      saving: false,
      clearing: false,
      loading: false,
    };
  },

  methods: {

    load() {

      if (!this.validate()) {
        this.disabled = false;
        this.loading = false;
        this.siteKeyChanged = false;
        return;
      }

      this.disabled = true;
      this.loading = true;
      this.siteKeyChanged = true;
      this.turnstile_status = false;

      this.$nextTick(() => {
        let id = '#turnstile';
        let $turnstile = jQuery(id);
        let siteKey = this.turnstile.siteKey;
        $turnstile.html('');
        this.disabled = true;
        this.loading = true;

        let widgetID = turnstile.render(id, {
          sitekey: siteKey,
          theme: this.turnstile.theme,
          callback: (token) => {
            this.turnstile.token = token;
            this.disabled  = false;
            this.loading = false;
          }
        });

        this.disabled = false;
        this.loading = false;
      })

    },

    save() {

      if (!this.validate()) {
        return this.$notify({
          title: 'Warning',
          message: 'Missing required fields.',
          type: 'warning'
        });
      }

      this.saving = true;

      jQuery.post(contactum.ajaxurl, {
            action: 'contactum_save_global_settings',
            _ajax_nonce: contactum.nonce,
            settings_key: 'turnstile',
            settings: this.turnstile
          },
          (response, textStatus, xhr) => {
            if (response.success) {
              this.$notify({
                title: 'Success',
                message: response.data.message,
                type: 'success'
              });
            } else {
              this.$notify({
                title: 'Warning',
                message: response.data.message,
                type: 'warning'
              });
            }
          }
      );

    },

    clearSettings() {
      this.clearing = true;
      var self = this;

      jQuery.post(contactum.ajaxurl, {
        action: 'contactum_save_global_settings',
        _ajax_nonce: contactum.nonce,
        settings_key: 'turnstile',
        settings: this.turnstile,
        action_type: 'clear-settings'
      }).done((response) => {
        if (response.success) {
          this.turnstile_status = response.data.status;
          this.turnstile = {siteKey: '', secretKey: ''};
          if (this.turnstile_status == 1) {
            this.$notify({
              title: 'Success',
              message: response.data.message,
              type: 'success'
            });
          } else {
            this.turnstile_status = 0;
            this.$notify({
              title: 'Warning',
              message: response.data.message,
              type: 'warning'
            });
          }
        } else {

        }
      }).fail((xhr, textStatus, errorThrown) => {

      }).always(function() {
        self.clearing = false;
      });
    },

    validate() {
      return !!(this.turnstile.siteKey && this.turnstile.secretKey);
    },

    getTurnstileSettings() {
      jQuery.post(contactum.ajaxurl, {
        action: 'contactum_get_global_settings',
        _ajax_nonce: contactum.nonce,
        settings_key: 'turnstile',
        key: [
          '_contactum_turnstile_details',
          '_contactum_turnstile_keys_status'
        ]
      }).done((response) => {
        const turnstile = response.data.settings._contactum_turnstile_details || {siteKey: '', secretKey: ''};
        this.turnstile = turnstile;
        this.turnstile_status = response.data.settings._contactum_turnstile_keys_status;
        if (this.turnstile?.invisible == 'yes') {
          this.turnstile.appearance = 'interaction-only';
        }
      })
    },
  },

  mounted() {
    this.getTurnstileSettings();
  },

  created() {
    let turnstileScript = document.createElement('script');
    turnstileScript.setAttribute('src', 'https://challenges.cloudflare.com/turnstile/v0/api.js');
    document.body.appendChild(turnstileScript);
  }
}
</script>