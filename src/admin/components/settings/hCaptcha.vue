<template>
  <div>

    <el-form label-position="top">

      <div class="contactum_card">

        <div class="contactum_card_head">
          <h2 class="title">  hCaptcha Settings </h2>
          <p class="text"> Contactum integrates with hCaptcha, a free service that protects your website from spam and abuse. Please note, these settings are required only if you decide to use the hCaptcha field. Read more about hCaptcha.
            Please generate API key and API secret using hCaptcha >
            <a href="https://www.hcaptcha.com/" target="_blank">Read more about hCaptcha.</a>
          </p>
          <p class="text"><b>Please generate API key and API secret using hCaptcha</b></p>
        </div>

        <div class="contactum_card_bodya">
            <!--Site key-->
        <el-form-item class="contactum-form-item">
          <template slot="label">
            Site Key
            <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
              <div slot="content">
                <p>Enter your hCaptcha Site Key, if you do not have a key you can register for one at the provided link,  hCaptcha is a free service.</p>
              </div>
              <i class="text-primary"></i>
            </el-tooltip>
          </template>
          <el-input v-model="hCaptcha.siteKey" @change="load"></el-input>
        </el-form-item>

        <!--Secret key-->
        <el-form-item class="contactum-form-item">
          <template slot="label">
            Secret Key
            <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
              <div slot="content">
                <p>Enter your hCaptcha Secret Key, if you do not have a key you can register for one at the provided link. hCaptcha is a free service.</p>
              </div>
              <i class="text-primary"></i>
            </el-tooltip>
          </template>
          <el-input type="password" v-model="hCaptcha.secretKey" @change="load"></el-input>
        </el-form-item>

        <!--Validate Keys-->
        <el-form-item :label="'Validate Keys'" v-if="siteKeyChanged">
          <div class="h-captcha" id="hCaptcha" :data-sitekey="hCaptcha.siteKey"></div>
        </el-form-item>

        <div v-if="hCaptcha_status" size="sm" type="success-soft">
          <p> Your hCaptcha is valid </p>
        </div>

        </div>

      </div>

        <div class="mt-4">
          <el-button
              type="primary"
              icon="el-icon-success"
              @click="save"
              :disabled="disabled"
              :loading="false"
          >Save Settings</el-button>

          <el-button
              type="danger"
              icon="ff-icon ff-icon-trash"
              @click="clearSettings"
              :clearing="false"
          >Clear Settings</el-button>

        </div>

    </el-form>

  </div>

</template>

<script>
export default {
  name: "hCaptcha",
  data() {
    return {
      hCaptcha: {
        siteKey: "",
        secretKey: "",
        token: ""
      },
      hCaptcha_status: false,
      siteKeyChanged: false,
      disabled: false,
      saving: false,
      clearing: false,
    };
  },
  methods: {
    load() {

      if (!this.validate()) {
        this.disabled = false;
        this.siteKeyChanged = false;
        return;
      } else {
        this.disabled = true;
        this.siteKeyChanged = true;
      }

      this.$nextTick(() => {

        let id = "hCaptcha";
        let $hCaptcha = jQuery("#" + id);
        let siteKey = this.hCaptcha.siteKey;

        const self = this;

        $hCaptcha.html("");

        if (typeof hcaptcha !== 'undefined') {

          const widgetId = hcaptcha.render(id, {
            sitekey: siteKey,
            callback: function(token) {
              self.hCaptcha.token = token;
              self.disabled = false;
            },
            'error-callback': function() {
              self.disabled = true;
              self.$notify({
                title: 'Warning',
                message: 'hCaptcha script failed to load.',
                type: 'warning'
              });
            }
          });

        } else {
          self.disabled = true;
          self.$notify({
            title: 'Warning',
            message: 'hCaptcha script failed to load.',
            type: 'warning'
          });
        }

      });

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
        settings_key: 'hCaptcha',
        settings: this.hCaptcha
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
      });
    },

    clearSettings() {
      this.clearing = true;
      var self = this;

      jQuery.post(contactum.ajaxurl, {
        action: 'contactum_save_global_settings',
        _ajax_nonce: contactum.nonce,
        settings_key: 'hCaptcha',
        settings: this.hCaptcha,
        action_type: 'clear-settings'
      }).done((response) => {
        if (response.success) {
          this.hCaptcha_status = response.data.status;
          this.hCaptcha = {siteKey: '', secretKey: ''};
          if (this.hCaptcha_status == 1) {
            this.$notify({
              title: 'Success',
              message: response.data.message,
              type: 'success'
            });
          } else {
            this.reCaptcha_status = 0;
            this.$notify({
              title: 'Warning',
              message: response.data.message,
              type: 'warning'
            });
          }
        } else {
          console.warn("⚠️ Fail:", response.data);
        }
      }).fail((xhr, textStatus, errorThrown) => {
        
      }).always(function() {

        self.clearing = false;
      });
    },

    validate() {
      return !!(this.hCaptcha.siteKey && this.hCaptcha.secretKey);
    },

    getHCaptchaSettings() {

      jQuery.post(contactum.ajaxurl, {
        action: 'contactum_get_global_settings',
        _ajax_nonce: contactum.nonce,
        settings_key: 'hCaptcha',
        key: [
          '_contactum_hCaptcha_details',
          '_contactum_hCaptcha_keys_status'
        ]
      }).done((response) => {
        const hCaptcha = response.data.settings._contactum_hCaptcha_details || {siteKey: '', secretKey: ''};
        if (!hCaptcha.api_version) {
          hCaptcha.api_version = 'v2_visible';
        }
        this.hCaptcha = hCaptcha;
        this.hCaptcha_status = response.data.settings._contactum_hCaptcha_keys_status;
      })
    }
  },
  mounted() {
    this.getHCaptchaSettings();
  },
  created() {
    let hCaptchaScript = document.createElement("script");
    hCaptchaScript.setAttribute("src", "https://js.hcaptcha.com/1/api.js");
    document.body.appendChild(hCaptchaScript);
  },
}
</script>