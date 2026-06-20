<template>
  <div class="panel-field-address">

    <label class="label-hr">{{ field.title }}</label>

    <ul class="address-fields">
      <li v-for="(sub, subKey) in editfield.address" :key="subKey" class="address-list-item">

        <div class="address-field-header">
          <div class="header-left">
            <i class="el-icon-menu drag-handle"></i>
            <el-checkbox v-model="editfield.address[subKey].checked"></el-checkbox>
            <span class="field-label-text">{{ sub.label || subKey }}</span>
          </div>
          <div class="header-right">
            <label class="required-checkbox-wrapper">
              <el-checkbox v-model="editfield.address[subKey].required"></el-checkbox>
              <span>Required</span>
            </label>
            <i
              :class="[show_details[subKey] ? 'el-icon-caret-top' : 'el-icon-caret-bottom', 'el-icon-clickable']"
              @click="toggle_show_details(subKey)"
            ></i>
          </div>
        </div>

        <!-- Text sub-fields -->
        <template v-if="'country_select' !== subKey">
          <div v-show="show_details[subKey]" class="address-input-fields">
            <div class="address-input-item">
              <label class="contactum-label">Label</label>
              <el-input v-model="editfield.address[subKey].label" />
            </div>
            <div class="address-input-item">
              <label class="contactum-label">Placeholder</label>
              <el-input v-model="editfield.address[subKey].placeholder" />
            </div>
          </div>
        </template>

        <!-- Country sub-field -->
        <template v-else>
          <div v-show="show_details[subKey]" class="address-input-fields">

            <div class="address-input-item">
              <label class="contactum-label">Label</label>
              <el-input v-model="editfield.address.country_select.label" />
            </div>

            <div class="address-input-item">
              <label class="contactum-label">Default Country</label>
              <el-select v-model="default_country" style="width:100%" filterable>
                <el-option
                  v-for="country in countries"
                  :key="country.code"
                  :label="country.name"
                  :value="country.code"
                />
              </el-select>
            </div>

            <div class="address-input-item panel-field-option-select">
              <label class="label-title-block contactum-label">Country List</label>
              <el-radio-group
                v-model="editfield.address.country_select.country_list_visibility_opt_name"
                style="display:flex"
                class="country-tabs"
              >
                <el-radio-button label="all">Show All</el-radio-button>
                <el-radio-button label="hide">Hide These</el-radio-button>
                <el-radio-button label="show">Only These</el-radio-button>
              </el-radio-group>

              <el-select
                v-show="active_visibility === 'hide'"
                v-model="country_in_hide_list"
                multiple
                style="width:100%"
                filterable
              >
                <el-option v-for="c in countries" :key="c.code" :value="c.code" :label="c.name" />
              </el-select>

              <el-select
                v-show="active_visibility === 'show'"
                v-model="country_in_show_list"
                multiple
                style="width:100%"
                filterable
              >
                <el-option v-for="c in countries" :key="c.code" :value="c.code" :label="c.name" />
              </el-select>
            </div>

          </div>
        </template>

      </li>
    </ul>

    <!-- Autocomplete Provider -->
    <div class="address-input-item">
      <label class="label-title-block contactum-label">Autocomplete Provider</label>
      <el-select v-model="editfield.autocomplete_provider" placeholder="Select provider" style="width:100%">
        <el-option label="None" value="none" />
        <el-option label="Google Maps" value="google" :disabled="!has_gmap_api" />
        <el-option label="OpenStreetMap / Nominatim (free)" value="html5" />
      </el-select>
    </div>

    <small
      v-if="!has_gmap_api && editfield.autocomplete_provider === 'google'"
      class="ctm-hint ctm-hint--warn"
    >
      Google Maps API key required. Configure it in <strong>Integrations → Google Map</strong>.
    </small>

    <small
      v-if="editfield.autocomplete_provider === 'html5'"
      class="ctm-hint"
    >
      OpenStreetMap (Nominatim) is free — no API key needed. Rate-limited to 1 req/sec; best for low-traffic forms.
    </small>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
export default {
  name: "field_address",
  mixins: [option_field],
  data() {
    return {
      show_details: {
        street_address: false,
        city_name:      false,
        state:          false,
        zip:            false,
        country_select: false,
      },
      has_gmap_api: !!(window.contactum && window.contactum.has_gmap_key),
    };
  },
  computed: {
    countries() {
      return window.contactum.countries || [];
    },
    active_visibility() {
      return this.editfield.address.country_select.country_list_visibility_opt_name;
    },
    default_country: {
      get() { return this.editfield.address.country_select.value; },
      set(val) { this.update_country_list('value', val); },
    },
    country_in_hide_list: {
      get() { return this.editfield.address.country_select.country_select_hide_list; },
      set(val) { this.update_country_list('country_select_hide_list', val); },
    },
    country_in_show_list: {
      get() { return this.editfield.address.country_select.country_select_show_list; },
      set(val) { this.update_country_list('country_select_show_list', val); },
    },
  },
  methods: {
    toggle_show_details(subKey) {
      this.$set(this.show_details, subKey, !this.show_details[subKey]);
    },
    update_country_list(property, value) {
      this.$store.dispatch('update_editing_form_field', {
        id:       this.editfield.id,
        property: 'address',
        value: {
          ...this.editfield.address,
          country_select: {
            ...this.editfield.address.country_select,
            [property]: value,
          },
        },
      });
    },
  },
};
</script>

<style scoped>
.panel-field-address {
  display: block;
}

.address-fields {
  list-style: none;
  padding: 0;
  margin: 10px 0 16px;
}

.address-list-item {
  background: #fff;
  border: 1px solid #e8e8e8;
  border-radius: 6px;
  margin-bottom: 6px;
  overflow: hidden;
}

.address-field-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 9px 12px;
  background: #fafafa;
}

.header-left,
.header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.drag-handle {
  color: #ccc;
  cursor: grab;
  font-size: 14px;
}

.field-label-text {
  font-size: 13px;
  font-weight: 500;
  color: #333;
}

.required-checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #666;
  cursor: pointer;
}

.el-icon-clickable {
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  color: #909399;
  font-size: 14px;
}
.el-icon-clickable:hover {
  background: #f0f0f0;
}

.address-input-fields {
  background: #f9f9f9;
  padding: 12px;
  border-top: 1px solid #f0f0f0;
}

.address-input-item {
  margin-bottom: 12px;
}
.address-input-item:last-child {
  margin-bottom: 0;
}

.country-tabs {
  margin-bottom: 8px;
}

.ctm-hint {
  display: block;
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
  line-height: 1.5;
}
.ctm-hint--warn {
  color: #b45309;
}
</style>
