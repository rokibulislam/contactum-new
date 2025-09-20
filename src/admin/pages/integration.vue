<template>
  <div class="integration_page">
    <div class="modules_header mb-5">
      <h4 class="title mb-2"> Contactum Forms Modules </h4>
      <p class="text"> Here is the list of all Contactum Forms modules. You can enable or disable the modules based on your need </p>
    </div>

    <el-row class="mb-3" :gutter="24" v-if="filteredAddons.length > 0">

<!--      <el-col :span="12">-->
<!--        <div class="ff_module_selectors">-->
<!--          <el-radio-group class="contactum_radio_group" v-model="module_type">-->
<!--            <el-radio-button label="all"> All </el-radio-button>-->
<!--            <el-radio-button label="crm"> CRM & SASS Integrations </el-radio-button>-->
<!--            <el-radio-button label="wp_core"> WP Core Modules </el-radio-button>-->
<!--          </el-radio-group>-->
<!--        </div>-->
<!--      </el-col>-->

      <el-col :span="18">
      <div class="pull-right activate-deactivate-all" v-if="is_pro">
        <el-button type="primary" @click.prevent="toggleModule('all','activate')"> Activate All</el-button>
        <el-button type="danger" @click.prevent="toggleModule('all','deactivate')"> deactivate All </el-button>
      </div>

      </el-col>

      <el-col :span="6">

        <div class="contactum_mdoules_search">
          <el-input placeholder="Search Modules" v-model="search" class="el-input-gray-light" prefix-icon="el-icon-search"></el-input>
        </div>
      </el-col>

    </el-row>

    <div class="integration-wrapper">
      <el-row :gutter="24">
        <el-col :md="12" :lg="8" v-for="(integration, index ) in filteredAddons" :key="integration.id">
          <div class="contactum_cad">
            <div class="panel-body">
              <div class="panel-body-heading contactum_media_group">
                <img class="icon" :src="integration.thumbnail" :alt="integration.name"  />
                <h4 class="panel-body-title"> {{  integration.name }}</h4>
              </div>
              <p> {{ integration.description }} </p>
            </div>

            <div class="panel-footer">
              <div class="panel-footer-group">
                <div v-if="is_pro">
                  <el-switch
                      v-model="integration.enable"
                      :active-value="1"
                      :inactive-value="0"
                      @change="toggleState(integration, $event, index)"
                  />
                  <span class="ml-2 fs-15"> {{ integration.enable == true ? 'Enabled' : 'Disabled' }} </span>
                </div>
                <a
                    :href="`${admin_url}?page=contactum-settings#${integration.author_uri}`"
                    v-if="integration.enable"
                >  <i class="el-icon-setting"></i> </a>
                

                <el-button v-if="!is_pro" @click="goToPro" > Upgrade to Pro </el-button>

              </div>
            </div>


          </div>
        </el-col>
      </el-row>
    </div>

  </div>

</template>

<script>

export default {
  name: "integration",
  data() {
    return {
      search: '',
      module_type: "",
      integrations: [],
      admin_url: contactum.admin_url,
      is_pro: window.contactum.is_pro
    }
  },
  computed: {
    filteredAddons() {
      let addons = this.integrations;
      if (this.search) {
        const lowerSearch = this.search.toLowerCase();
        return Object.entries(addons)
            .filter(([key, value]) => value.name.toLowerCase().includes(lowerSearch))
            .map(([key, value]) => ({
              path: key,
              ...value
            }));
      }
      return Object.entries(addons).map(([key, value]) => ({
        path: key,
        ...value
      }));
    }
  },
  mounted() {
    if( this.is_pro ) {
      this.getIntegration();
    } else {
      this.integrations = window.contactum.modules;
    }

  },
  methods: {

    goToPro() {
      window.open("https://wpcontactum.com/", "_blank");
    },

    getIntegration() {
      var self = this;
      let data = {
        action: 'contactum_get_modules',
        nonce: contactum.nonce
      };

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'GET',
        data: data,
        success: function (response) {
          self.integrations = response.data.all;
        }
      });

    },

    toggleState(integration, value, index) {
      var self = this;

      let data = {
        action: 'contactum_toggle_modules',
        module: integration['path'],
        nonce: contactum.nonce
      };

      if( value == '1' ) {
        data.type = 'activate';
      } else {
        data.type = 'deactivate';
      }

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: data,
        success: function (response) {
          if( response.success ) {
            let state = response.data;
            self.integrations[integration['path']].enable = state === 'Activated' ? 1 : 0;
          }
        },
        complete: function () {

        }
      });

    },

    toggleModule: function(module, state) {

      if ( state=== 'activate' ) {
        var data = {
              action: 'contactum_toggle_all_modules',
              type: 'activate',
              nonce: contactum.nonce
        };
      } else {
          var  data = {
            action: 'contactum_toggle_all_modules',
            type: 'deactivate',
            nonce: contactum.nonce
          };
      }

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: data,
        success: function (response) {

        },
        complete: function(response) {
          window.location.reload(true);
        }
      });
    },


    saveStatus(integration) {

    }

  }
}
</script>

<style scoped lang="scss">


.panel-footer-group {
  display: flex;
  justify-content: space-between;
}


.contactum_cad {
  min-height: 160px;
  img {
    height: 20px;
  }

  & .contactum_media_group {
    display: flex;
    align-items: center;
    gap: 20px;
  }

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
      }
    }
  }

</style>