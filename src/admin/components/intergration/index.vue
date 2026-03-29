<template>
  
  <div class="integration_page">

    <div class="modules_header mb-5">
        <h4 class="title mb-2"> Contactum Forms Modules </h4>
        <p class="text"> Here is the list of all Contactum Forms modules. You can enable or disable the modules based on your need </p>
    </div>

    <div class="integration-wrapper">
      <el-row :gutter="24">
        <el-col :md="12" :lg="8" v-for=" ( integration, index ) in integrations" :key="integration.id">
            <div class="panel">
              <div class="panel-body">

                <div class="panel-body-heading">
                  <img class="icon" :src="integration.icon" :alt="integration.title"  />
                  <h4 class="panel-body-title"> {{  integration.title }} </h4>
                </div>
              </div>

              <div class="panel-footer">
                <div class="panel-footer-group">
                  <div>
                    <el-switch
                        @change="changeStatus(integration, $event, index)"
                        v-model="integration.formenable"
                    />
                    {{ integration.formenable ? 'Enabled' : 'Disabled' }}
                  </div>
                    <a
                      href="#"
                      @click.prevent="openDialog(integration)"
                      v-if="integration.formenable"
                    >  <i class="el-icon-setting"></i> </a>

                        <el-button
      v-if="integration.formenable"
      size="mini"
      type="text"
      @click.prevent="openDialog(integration)"
    >
      <i class="el-icon-setting"></i> Settings
    </el-button>
                </div>
              </div>
            </div>
        </el-col>
      </el-row>

      <!-- Settings dialog -->
      <el-dialog
          title="Integration Settings"
          :visible.sync="dialogVisible"
          width="600px"
      >
        <div v-if="dialogIntegration">
          
          <ul v-if="dialogIntegration.formenable">

            <li v-for=" ( field, index ) in dialogIntegration.integration_fields">

              <p>
                {{ field.label }}
                <el-button @click="fetchlists(dialogIntegration, index)" v-if="field.type == 'list_ajax_options'">   <span class="el-icon-refresh" > </span>  </el-button>
              </p>

              <template v-if="field.type == 'text'">
                <div class="integraion-field">
                  <el-input :placeholder="field.placeholder"  v-model="dialogIntegration.integration[field.name]"></el-input>
<!--                  <merge_tags  @insert="insertValue" :field="field.name" filter="email_field" :name="dialogIntegration.id"/>-->
                  <merge_tags  @insert="insertValue" :field="field.name"  :name="dialogIntegration.id"/>
                </div>
              </template>

              <template v-if="field.type == 'list_ajax_options'">
                <div class="integraion-field">
                  <el-select
                      filterable
                      clearable
                      v-model="dialogIntegration.integration[field.name]"
                      :placeholder="field.placeholder">
                    <el-option
                        v-for="(list_name, list_key) in field.options"
                        :key="list_key"
                        :value="list_key"
                        :label="list_name"
                    ></el-option>
                  </el-select>
                </div>
              </template>

              <template v-if="field.type == 'select'">
                <div class="integraion-field">
                  <el-select
                      filterable
                      clearable
                      v-model="dialogIntegration.integration[field.name]"
                      :placeholder="field.placeholder">
                    <el-option
                        v-for="(list_name, list_key) in field.options"
                        :key="list_key"
                        :value="list_key"
                        :label="list_name"
                    ></el-option>
                  </el-select>
                </div>
              </template>

              <template v-if="field.type == 'checkbox-single'">
                <el-checkbox v-model="dialogIntegration.integration[field.name]">
                  {{ field.checkbox_label }}
                </el-checkbox>
              </template>

            </li>
          </ul>


        </div>

        <!-- Footer buttons -->
        <template #footer>
          <el-button @click="dialogVisible = false">Cancel</el-button>
          <el-button type="primary" @click="saveIntegration">Save</el-button>
        </template>
      </el-dialog>
    </div>
  </div>
</template>

<script type="text/javascript">

import merge_tags from '../merge-tags/index.vue';

import mailchimp from "../pro/modules/mailchimp.vue";
import activecampaign from "../pro/modules/active-campaign.vue";
import slack from "../pro/modules/slack.vue";
import mailpoet from "../pro/modules/mailpoet.vue";
import campaign_monitor from "../pro/modules/campaign-monitor.vue";
import aweber from "../pro/modules/aweber.vue";
import constant_contact from "../pro/modules/constant-contact.vue";
import convertkit from "../pro/modules/convertkit.vue";
import getresponse from "../pro/modules/getresponse.vue";
import icontact from "../pro/modules/icontact.vue";
import mailerlite from "../pro/modules/mailerlite.vue";
import mautic from "../pro/modules/mautic.vue";
import moosend from "../pro/modules/moosend.vue";
import sendfox from "../pro/modules/sendfox.vue";
import sendinblue from "../pro/modules/sendinblue.vue";
import hubspot from "../pro/modules/hubspot.vue";
import Salesforce from "../pro/modules/salesforce.vue";
import trello from "../pro/modules/trello.vue";
import zoho from "../pro/modules/zoho.vue";

export default {
  name: "Intergrations",
  components: {
    merge_tags,
    mailchimp,
    activecampaign,
    slack,
    mailpoet,
    campaign_monitor,
    aweber,
    constant_contact,
    convertkit,
    getresponse,
    icontact,
    mailerlite,
    mautic,
    moosend,
    sendfox,
    sendinblue,
    hubspot,
    Salesforce,
    trello,
    zoho
  },
  data() {
    return {
      search: '',
      module_type: 'all',
      selectedId: "",
      integrations_component:[],
      dialogVisible: false,
      dialogIntegration: null
    };
  },
  computed: {
    integrations: function() {
      return this.$store.getters.integrations;
    }
  },
  methods: {

    saveIntegration() {
      this.$emit('save-integration', this.dialogIntegration);
      this.dialogVisible = false; // close dialog after saving
    },

    fetchlists(dialogIntegration, index) {
      var self = this;
      jQuery.post(window.contactum.ajaxurl, {
        action: `contactum_${dialogIntegration.id}_update_list`,
        _ajax_nonce: window.contactum.nonce
      }, (response, textStatus, xhr) => {
        console.log( response );
        if (response.success) {
          dialogIntegration.integration_fields[index].options = response.data;
        }
      });
    },

    openDialog(integration) {
      this.dialogIntegration = integration;
      this.dialogVisible = true;
    },

    toggleSettings(integration) {
      this.selectedId = integration.id;
    },
    changeStatus(integration, value, index) {
      this.$store.commit("updateIntegrationProperty", {
        index: index,
        property: 'formenable',
        value: value
      });
    },

    insertValue: function(type,field, integration, property) {
      let value = ( field !== undefined ) ? '{' + type + ':' + field + '}' : '{' + type + '}';
   //   this.integrations[integration].integration[property] = this.integrations[integration].integration[property] + value;

      this.$store.commit("updateIntegrationNestedProperty", {
        type: type,
        field: field,
        integration: integration,
        property: property,
        value: value
      });

    }
  }
};
</script>

<style scoped lang="scss">

.modules_header {
  .title {
    font-size: 20px;
    font-weight: 600;
    color: #303133;
  }

  .text {
    font-size: 14px;
    color: #909399;
  }
}

.integraion-field {
  position: relative;
}

.integration_page {
  padding: 20px;

  .el-col {
    margin-bottom: 30px;
  }
}

.integration-wrapper {
  margin-top: 20px;
  .panel {
    background: #ffff;
    border: 1px solid #e4e4e4;
    border-radius: none;
    display: flex;
    flex-direction: column;

    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);

     &:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      border-color: #dcdfe6;
    }


    .panel-body {
      padding: 20px;
      margin-bottom: 24px;
      &-heading {
        display: flex;
        align-items: center;
      }
      &-title {
        margin-left: 20px;
      }
      img {
        max-width: 28px;
        max-height: 28px;
        object-fit: cover;
        object-position: left;
      }
    }
    .panel-footer {
      border-top: 1px solid #e4e4e4;
      margin-top: auto;
      padding: 14px 20px;
      &-group {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      button {
        font-weight: 500;
      }
    }
  }
}


.pro-badge {
  background: linear-gradient(135deg, #ff9f43, #ff6b6b);
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 6px;
  margin-left: 6px;
}

</style>
