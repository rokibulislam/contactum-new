<template>
  <div class="panel-field-address">
    
    <label class="label-hr">{{ field.title }} </label>

    <ul class="address-fields">
    
      <li v-for="(address, field) in editfield.address" class="address-list-item">
        
        <div class="address-field-header">

          <div class="header-left">
            <i class="el-icon-menu drag-handle"></i>
            <el-checkbox v-model="editfield.address[field].checked" @change="field === 'country_select' ? toggle_address_checked(field) : null"></el-checkbox>
            <span class="field-label-text">{{ field === 'country_select' ? 'Country' : address.label }}</span>
          </div>

          <div class="header-right">
            <label class="required-checkbox-wrapper">
              <el-checkbox v-model="editfield.address[field].required" @change="toggle_address_required(field)"></el-checkbox>
              <span>Required</span>
            </label>
            <i :class="[show_details[field] ? 'el-icon-caret-top' : 'el-icon-caret-bottom', 'el-icon-clickable']"  @click="toggle_show_details(field)"></i>
          </div>
        
        </div>
        
        <template v-if="'country_select' !== field">
          
            <div v-show="show_details[field]" class="address-input-fields">
                
                <div class="address-input-item"> 
                  <label class="contactum-label"> Label  </label> 
                  <el-input v-model="address.label"></el-input>
                </div>
                
                <div class="address-input-item"> 
                  <label class="contactum-label"> Default </label>  
                   <el-input v-model="address.value"></el-input>
                </div class="address-input-item">
                
                <div class="address-input-item"> 
                    <label class="contactum-label"> Placeholder  </label>  
                    <el-input v-model="address.placeholder"></el-input>
                </div>

            </div>

        </template>

        <template v-else>

            <div v-show="show_details[field]" class="address-input-fields">
                
                <div  class="address-input-item">
                  <label class="contactum-label"> Label </label>
                  <el-input type="text" v-model="address.label" />
                </div>

                <div  class="address-input-item">
                    
                  <label class="contactum-label"> Default Country </label>
                    
                    <el-select v-model="default_country" :style="{width: '100%'}">
                        <el-option
                        v-for="country in countries"
                        :key="country.code"
                        :label="country.name"
                        :value="country.code">
                        </el-option>
                    </el-select>

                </div>

                
                <div class="address-input-item panel-field-opt-select country-list-selector-container panel-field-option-select">
                  
                  <label class="label-title-block contactum-label"> Country List </label>

                  <el-radio-group v-model="editfield.address.country_select.country_list_visibility_opt_name" :style="{display: 'flex'}" class="country-tabs">
                      <el-radio-button label="all"> Show all </el-radio-button>
                      <el-radio-button label="hide">Hide These</el-radio-button>
                      <el-radio-button label="show">Only Show These</el-radio-button>
                  </el-radio-group>

                  <select
                      v-show="'all' === active_visibility"
                      :class="['country-list-selector selectize-element-group', 'all' === active_visibility ? 'active' : '']"
                      disabled
                  >
                      <option value=""> Select Countries </option>
                  </select>

                  <el-select
                      v-show="'hide' === active_visibility"
                      v-model="country_in_hide_list"
                      :class="active_visibility === 'hide' ? 'active' : ''"
                      multiple
                      @change="onChange"
                      :style="{width: '100%'}"
                  >
                      <el-option v-for="country in countries" :key="country.code" :value="country.code" :label="country.code">{{ country.name }}</el-option>
                  </el-select>

                  <el-select
                      v-show="'show' === active_visibility"
                      :class="['show' === active_visibility ? 'active' : '']"
                      v-model="country_in_show_list"
                      multiple
                      @change="onChange"
                      :style="{width: '100%'}"
                  >
                      <el-option v-for="country in countries" :key="country.code" :value="country.code" :label="country.code">{{ country.name }}</el-option>
                  </el-select>
              </div>
            </div>
        </template>
      </li>
    </ul>

    <!-- Autocomplete Provider Dropdown (manual) -->

      <div  class="address-input-item">
        <label class="label-title-block contactum-label"> Autocomplete Provider </label>
        
        <el-select v-model="editfield.autocomplete_provider" placeholder="Select provider" style="width: 100%;">
            <el-option label="None" value="none" />
            <el-option label="Google Maps" value="google" :disabled="!has_gmap_api" />
            <el-option label="OpenStreetMap Geolocation (Nominatim)" value="html5" :disabled="!has_pro" />
        </el-select>
      
        </div>

        <small v-if="!has_gmap_api && editfield.autocomplete_provider === 'google'">
           Google Maps API key required. Configure in FluentForm Pro settings.
        </small>

        <small v-if="editfield.autocomplete_provider === 'html5'" class="mb-3" style="display: inline-block;">
            Address autocomplete with OpenStreetMap (Nominatim) is limited to 1 request per second across all users and attribution required. Best for forms with low traffic. For high-traffic sites, consider Google Maps
        </small>

        <small v-if="!has_pro">
           Autocomplete with Coordinates is available in Fluent Forms Pro.
        </small>

    </div>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
export default {
  name: "field_address",
  mixins: [option_field],
  data: function() {
    return {
        default_country: this.editfield.address.country_select.value,
        show_details: {
            street_address:  false,
            street_address2: false,
            city_name:       false,
            state:           false,
            zip:             false,
            country_select:  false,
        },
        has_gmap_api: false,
        // has_gmap_api: !!window.contactum.gmap_api,
        has_pro: window.contactum.has_pro,
        pro_mock: false,
    }
  },
  computed: {
    Autocomplete() {
      return Autocomplete
    },
    countries: function() {
        return contactum.countries;
    },
    active_visibility: function () {
        return this.editfield.address.country_select.country_list_visibility_opt_name;
    },

    country_in_hide_list: function () {
        return this.editfield.address.country_select.country_select_hide_list;
    },

    country_in_show_list: function () {
        return this.editfield.address.country_select.country_select_show_list;
    }
  },
    mounted: function () {
        this.bind_selectize();
    },
  methods: {
    toggle_address_checked: function(field) {
        this.editfield.address[field].checked = !this.editfield.address[field].checked;
    },

    toggle_address_required: function(field) {
        this.editfield.address[field].required = !this.editfield.address[field].required;
    },

    toggle_show_details: function (field) {
        this.show_details[field] = !this.show_details[field];
    },

    bind_selectize() {
        var self = this;

        jQuery(this.$el).find('.default-country').selectize({
            plugins: ['remove_button'],
        }).on('change', function () {
            var value = jQuery(this).val();
            self.default_country = value;
            self.update_country_list("value", value);
        })

        jQuery(this.$el).find('.country-list-selector').selectize({
            plugins: ['remove_button'],
        }).on('change', function (e) {
            var select      = jQuery(this),
                visibility  = e.target.dataset.visibility,
                value       = select.val(),
                list        = '';

            switch(visibility) {
                case 'hide':
                    list = 'country_select_hide_list';
                    break;

                case 'show':
                    list = 'country_select_show_list';
                    break;
            }

            if ( !value ) {
                value = [];
            }

            self.update_country_list(list, value);
        });
    },
    update_country_list: function(property, value) {
        let address = { ...this.editfield.address };
        address.country_select[property] = value;

        this.$store.dispatch("update_editing_form_field", {
            id: this.editfield.id,
            property: "address",
            value: address
        });
    },
    set_visibility: function(value) {
      this.update_country_list("country_list_visibility_opt_name", value);
    },

    showProMessage(name) {
      this.$notify.error(`Enable ${name} Available on pro version`);
      this.pro_mock = false;
    }
  }
};
</script>

<style scoped>
    .panel-field-address {
        display: block;
    }

    .address-fields {
      list-style: none;
      padding: 0;
      margin: 10px 0;
    }

    .address-list-item {
      background: #fff;
      border: 1px solid #f0f0f0;
      border-radius: 4px;
      margin-bottom: 8px;
      overflow: hidden;
    }

    .address-field-header {    
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      background: #fff;
    }

    .drag-handle {
      color: #ccc;
      cursor: grab;
      font-size: 14px;
    }

    .el-icon-clickable {
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
      color: #909399;
    }

    .el-icon-clickable:hover {
      background-color: #f5f7fa;
    }

    .address-field-header > .el-icon-caret-bottom {
        border-radius: 3px;
        font-size: 18px;
        margin-top: -2px;
        padding: 2px 4px;
        transition: .2s;
    }

    .header-left, .header-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .address-input-fields {
      background: #f9f9f9;
      padding: 10px;
      color: #212121;
    }

    .address-input-item {
      margin: 0 0 15px 0;
    }

    .panel-field-option-select label {
        display: block;
        margin-bottom: 5px;
    }

    .panel-field-option-select .panel-field-btn-group {
        display: flex;
        justify-content: space-between;
    }

    .country-tabs {
      margin-bottom: 10px;
    }
</style>
