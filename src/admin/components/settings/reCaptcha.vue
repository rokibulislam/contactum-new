<template>
  <div>
    <el-form label-position="top">
      <div class="contactum_card">
          <div class="contactum_card_head">
            <h2 class="title"> Google reCAPTCHA Settings </h2>
            <p class="text"> Contactum integrates with reCAPTCHA, a free service that protects your website from spam and abuse. Please note,
            these settings are required only if you decide to use the reCAPTCHA field. Read more about reCAPTCHA.  <a href="http://www.google.com/recaptcha/" target="_blank">Read more about reCAPTCHA</a> </p>
            <p class="text"><b>Please generate API key and API secret using reCAPTCHA</b></p>
          </div>

        <div class="contactum_card_body">
              <!--Site key-->
              <el-form-item class="ff-form-item">
                <template slot="label">
                  reCAPTCHA Version
                  <el-tooltip class="item" placement="bottom-start" popper-class="ff_tooltip_wrap">
                    <div slot="content">
                      <p>Please select which reCAPTCHA version you would like to use</p>
                    </div>
                    <i class="ff-icon ff-icon-info-filled text-primary"></i>
                  </el-tooltip>
                </template>

                <el-radio-group @change="load" v-model="reCaptcha.api_version">
                  <el-radio label="v2_visible">Version 2 (Visible reCAPTCHA)</el-radio>
                  <el-radio label="v3_invisible">Version 3 (Invisible reCAPTCHA)</el-radio>
                </el-radio-group>
              </el-form-item>


              <!--Site key-->
              <el-form-item class="ff-form-item">
                <template slot="label">Site Key
                  <el-tooltip class="item" placement="bottom-start" popper-class="ff_tooltip_wrap">
                    <div slot="content">
                      <p>Enter your reCAPTCHA Site Key, if you do not have a key you can register for one at the provided link. reCAPTCHA is a free service.</p>
                    </div>
                    <i class="ff-icon ff-icon-info-filled text-primary"></i>
                  </el-tooltip>
                </template>
                <el-input v-model="reCaptcha.siteKey" @change="load"></el-input>
              </el-form-item>

              <!--Secret key-->
              <el-form-item class="ff-form-item">
                <template slot="label">Secret Key
                  <el-tooltip class="item" placement="bottom-start" popper-class="ff_tooltip_wrap">
                    <div slot="content">
                      <p>Enter your reCAPTCHA Secret Key, if you do not have a key you can register for one at the provided link, reCAPTCHA is a free service.</p>
                    </div>
                    <i class="ff-icon ff-icon-info-filled text-primary"></i>
                  </el-tooltip>
                </template>
                <el-input type="password" v-model="reCaptcha.secretKey" @change="load"></el-input>
              </el-form-item>

              <!--Validate Keys-->
              <el-form-item>
                <template slot="label" >Validate Keys</template>
                <div
                    id="reCaptcha"
                    :data-sitekey="reCaptcha.siteKey"
                    :data-size="size"
                />
              </el-form-item>

            <div v-if="reCaptcha_status && !disabled" size="sm" type="success-soft">
              <p>Your reCAPTCHA is valid</p>
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
  name: "reCaptcha",
  data() {
    return {
      reCaptcha: {
        siteKey: '',
        secretKey: '',
        api_version: ''
      },
      reCaptcha_status: false,
      siteKeyChanged: false,
      disabled: false,
      saving: false,
      clearing: false,
      size: 'normal'
    }
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
        this.reCaptcha_status = false;
      }

      this.$nextTick(() => {
        let id = 'reCaptcha';
        let siteKey = this.reCaptcha.siteKey;
        let $reCaptcha = jQuery('#' + id);
        $reCaptcha.html('');

        window.___grecaptcha_cfg.clients = {};

        let widgetID = grecaptcha.render(id, {
          'sitekey': siteKey,
          'callback': (token) => {
            this.reCaptcha.token = token;
            this.disabled = false;
          }
        });

        if (this.reCaptcha.api_version != 'v2_visible') {
              grecaptcha.execute(widgetID, {action: 'submit'})
              .then((token) => {
                this.reCaptcha.token = token;
                this.disabled = false;
              });
        }
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
            settings_key: 'reCaptcha',
            settings: this.reCaptcha
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
          settings_key: 'reCaptcha',
        settings: this.reCaptcha,
        action_type: 'clear-settings'
      }).done((response) => {
        if (response.success) {
          this.reCaptcha_status = response.data.status;
          this.reCaptcha = {siteKey: '', secretKey: ''};
          if (this.reCaptcha_status == 1) {
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

        }
      })
      .fail((xhr, textStatus, errorThrown) => {

      }).always(function() {
        this.clearing = false;
      });
    },

    validate() {
      return !!(this.reCaptcha.siteKey && this.reCaptcha.secretKey);
    },

    getReCaptchaSettings() {
      jQuery.post(contactum.ajaxurl, {
        action: 'contactum_get_global_settings',
        _ajax_nonce: contactum.nonce,
        settings_key: 'reCaptcha',
        key: [
          '_contactum_reCaptcha_details',
          '_contactum_reCaptcha_keys_status'
        ]
      }).done((response) => {
        const recaptcha = response.data.settings._contactum_reCaptcha_details || {siteKey: '', secretKey: ''};
        if (!recaptcha.api_version) {
          recaptcha.api_version = 'v2_visible';
        }
        this.reCaptcha = recaptcha;
        this.reCaptcha_status = response.data.settings._contactum_reCaptcha_keys_status;
      })
    }
  },

  mounted() {
    this.getReCaptchaSettings();
  },
  created() {
    let recaptchaScript = document.createElement('script');
    recaptchaScript.setAttribute('src', 'https://www.google.com/recaptcha/api.js');
    document.body.appendChild(recaptchaScript);
  },
}
</script>