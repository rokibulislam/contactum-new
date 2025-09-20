<template>
<div>
  <div v-if="settings.settings && settings.settings.hide_on_valid && integration.status" class="contactum_cad">
      <div class="contactum_cad_head">
        <h5 class="title">{{ settings.title }}</h5>
        <p class="text" v-html="settings.description"></p>
      </div>
      <div class="contactum_cad_body">
        <div class="el-alert el-alert--success is-light contactum_state_box">
          <div class="mb-4 contactum_icon_btn mx-auto success">
            <i class="el-icon el-icon-check"></i>
          </div>
          <h3 class="mb-4" v-html="settings.settings.discard_settings.section_description"></h3>
          <el-button v-if="settings.settings.discard_settings.show_verify" v-loading="saving" @click="save()" type="primary"
                     icon="el-icon-success"> Verify Connection Again
          </el-button>
          <el-button @click="disconnect(settings.settings.discard_settings.data)" type="danger">
            {{ settings.settings.discard_settings.button_text }}
          </el-button>
        </div>
      </div>
    </div>

    <div v-else >
        <el-form label-position="top">
          <el-skeleton :loading="loading" animated :rows="10" :class="loading ? '' : ''">
          <div class="contactum_cad">
            <div class="contactum_cad_head">
              <h2 class="title"> {{ settings.title }} </h2>
              <p class="text" v-html="settings.description">  </p>
            </div>

              <div class="contactum_cad_body">

                  <el-form-item class="ff-form-item" v-for="(field,fieldKey) in (settings.settings && settings.settings.fields ? settings.settings.fields : [])" :key="fieldKey">

                          <template slot="label" v-if="field.label"> {{ field.label }} </template>

                           <template v-if="field.type == 'text'">
                            <el-input :placeholder="field.placeholder" :type="field.type" v-model="integration[field.name]"></el-input>
                           </template>

                          <template v-if="field.type == 'number'">
                            <el-input :placeholder="field.placeholder" :type="field.type" v-model="integration[field.name]"></el-input>
                          </template>

                          <template v-if="field.type == 'password'">
                            <el-input :placeholder="field.placeholder" :type="field.type" v-model="integration[field.name]"></el-input>
                          </template>

                          <template v-if="field.type == 'textarea'">
                            <el-input :placeholder="field.placeholder" :type="field.type" v-model="integration[field.name]"></el-input>
                          </template>

                          <template v-if="field.type == 'select'">
                            <el-select class="" v-model="integration[field.name]">
                              <el-option
                                  v-for="(optionName, optionValue) in field.options"
                                  :key="optionValue"
                                  :label="optionName"
                                  :value="optionValue"></el-option>
                            </el-select>
                          </template>

                          <p class="text-note mt-2" v-if="field.tips">{{ field.tips }}</p>

                    </el-form-item>

                  <div v-if="settings.settings">
                    <div v-if="integration.status">
                      <p><i class="el-icon-success"></i> {{ settings.settings.valid_message }}</p>
                    </div>

                    <div v-else>
                      <p><i class="ff-icon ff-icon-close-circle-filled"></i> {{ settings.settings.invalid_message }}</p>
                    </div>
                  </div>

                  <p v-if="error_message">{{ error_message }}</p>

              </div>

            </div>

          </el-skeleton>


          <el-button v-loading="saving" type="primary" icon="el-icon-success" @click="save"> Save Settings </el-button>

        </el-form>
    </div>

</div>

</template>

<script>
import mailerlite from "../pro/modules/mailerlite.vue";
import campaignMonitor from "../pro/modules/campaign-monitor.vue";

export default {
  name: "GeneralIntegrationSettings",
   props: {
    setting_key: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      integration: {},
      settings: {},
      loading: false,
      saving: false,
      error_message: ''
    }
  },
  mounted() {
    this.getIntegrationSettings();
  },
  methods: {
    save() {
      this.saving = true;
      jQuery.post(contactum.ajaxurl, {
            action: 'contactum_save_global_integrations',
            _ajax_nonce: contactum.nonce,
            settings_key: this.setting_key,
            integration: this.integration
          },
          (response, textStatus, xhr) => {
            if (response.success) {
              const status = response.data.status === true || response.data.status === "1" || response.data.status === "success";
              this.$set(this.integration, 'status', status);
              this.$notify({
                title: 'Success',
                message: response.data.message,
                type: 'success',
                position: 'bottom-right'
              });
              this.saving = false;
            } else {
              // this.integration.status = false;
              this.$set(this.integration, 'status', false);
              this.$notify({
                title: 'warning',
                message: response.data.message,
                type: 'warning',
                position: 'bottom-right'
              });
              this.saving = false;
            }
          });
    },
    getIntegrationSettings() {
      jQuery.post(contactum.ajaxurl, {
        action: 'contactum_get_admin_integrations',
        _ajax_nonce: contactum.nonce,
        'settings_key': this.setting_key
      },
      (response, textStatus, xhr) => {
        if (response.success) {
          this.settings = response.data;
          this.integration = response.data.value;
        } else {

        }
      });
     },

    disconnect(data) {
      this.integration = data;
      this.save();
    }
  },

  watch: {
    setting_key(newKey, oldKey) {
      this.loadSettings(); // or whatever method to fetch/render new data
    }
  },
}
</script>

<style>

.contactum_state_box {
  border-radius: 8px;
  display: block;
  padding: 32px;
  text-align: center;
}

.contactum_icon_btn {
  align-items: center;
  background-color: #1a7efb;
  border-radius: 50%;
  color: #fff;
  display: flex
;
  font-size: 28px;
  height: 58px;
  justify-content: center;
  text-align: center;
  width: 58px;
}

.contactum_icon_btn.success {
  background-color: #00b27f;
  color: #fff;
}



label {
  align-items: center;
  color: #1e1f21 !important;
  display: flex;
  font-size: 15px;
  font-weight: 500;
}


</style>