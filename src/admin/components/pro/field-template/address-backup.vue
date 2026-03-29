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
          
          <!-- <div class="panel-child-field address-field-option">
                <div>
                    <label>
                        <el-checkbox v-model="editfield.address[field].checked" checked="editfield.address[field].checked" > </el-checkbox>
                      {{ address.label }}
                    </label>
                </div>

                <div>
                    <label>
                         <el-checkbox v-model="editfield.address[field].required" checked="editfield.address[field].required" @change="toggle_address_required"> </el-checkbox>
                      {{ 'Required' }} 
                    </label>
                    <i class="el-icon-caret-bottom el-icon-clickable pull-right" @click="toggle_show_details(field)"></i>
                </div>
          </div> -->

            <div v-show="show_details[field]" class="address-input-fields">
                <p> <label> Label <el-input v-model="address.label"></el-input> </label> </p>
                <p> <label> Default <el-input v-model="address.value"></el-input> </label>  </p>
                <p> <label> Placeholder <el-input v-model="address.placeholder"></el-input> </label>  </p>
            </div>
        </template>

        <template v-else>
            <!-- <div class="address-title-header panel-child-field">
                <div>
                    <label>
                      <el-checkbox v-model="address.checked" checked="address.checked"  @change="toggle_address_checked"> </el-checkbox>
                      {{ field }}
                    </label>
                </div>

                <div>
                    <label>
                        <el-checkbox v-model="address.required" checked="address.required" @change="toggle_address_required(field)"> </el-checkbox>
                      Required
                    </label>

                    <button
                        type="button"
                        class="button button-link button-dropdown"
                        @click="toggle_show_details(field)"
                    >
                        <i class="el-icon-arrow-down"></i>
                    </button>
                </div>
            </div> -->

            <div v-show="show_details[field]" class="child-address-country">
                <div  class="country-label">
                    <p>
                        <label class="contactum-label"> Label </label>
                      <el-input type="text" v-model="address.label" />
                    </p>
                </div>

                <div class="address-country-fields">
                    <label class="contactum-label"> Default Country
                        <el-select v-model="default_country" :style="{width: '100%'}">
                            <el-option
                            v-for="country in countries"
                            :key="country.code"
                            :label="country.name"
                            :value="country.code">
                            </el-option>
                        </el-select>
                    </label>

                    <div class="panel-field-opt-select country-list-selector-container panel-field-option-select">
                        <label class="label-title-block contactum-label"> Country List </label>
                        <el-radio-group v-model="editfield.address.country_select.country_list_visibility_opt_name" :style="{display: 'flex'}">
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
                            class="[ 'hide' === active_visibility ? 'active' : '']"
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
            </div>
        </template>
      </li>
    </ul>


    <template v-if="has_pro">
      <el-checkbox true-label="yes" false-label="no" v-model="editfield.enable_g_autocomplete"> Enable Autocomplete(Google API) </el-checkbox>
      <el-checkbox true-label="yes" false-label="no" v-model="editfield.enable_g_map">Enable Map(Google Map)</el-checkbox>
    </template>

    <template v-else>
      <el-checkbox true-label="yes" false-label="no" v-model="pro_mock" @change="showProMessage('Autocomplete')"> Enable Autocomplete(Google API) </el-checkbox>
      <el-checkbox true-label="yes" false-label="no" v-model="pro_mock" @change="showProMessage('Google Map')">Enable Map(Google Map)</el-checkbox>
    </template>

    <div v-if="has_gmap_api  && editfield.enable_g_autocomplete =='yes'" >
      <div slot="label">Save Coordinates
        <el-tooltip poper-class="ff_tooltip_wrap" :content="'Please enable Geolocation API. First it will try HTML API if Fails it will use Google Geolocation API'" placement="top">
          <i class="tooltip-icon el-icon-info"></i>
        </el-tooltip>
      </div>
      <el-checkbox  true-label="yes" false-label="no" v-model="editfield.save_coordinates">See User Location on Map (Latitude & Longitude
      </el-checkbox>
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
        has_gmap_api: true,
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

    .drag-handle {
    color: #ccc;
    cursor: grab;
    font-size: 14px;
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
      /*
      background: #797979;
      padding: 10px;
      color: #fff;
      */
      background: #f9f9f9;
      padding: 10px;
      color: #212121;
    }

    .panel-child-field {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
      gap: 10px;
    }

    .panel-field-option-select label {
        display: block;
        margin-bottom: 5px;
    }

    .panel-field-option-select .panel-field-btn-group {
        display: flex;
        justify-content: space-between;
    }

</style>
