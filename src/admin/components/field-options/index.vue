<template>
<div class="field-settings-wrapper">

    <div v-if=" editfield_id == 0" class="options-fileds-section text-center">
        <p>
            <span class="loader"></span>
        </p>
    </div>
      <div class="panel-field" v-else>


        <div name="slide-fade" class="panel-field-group panel-header">
            <h2 @click="toggleBasic" class="group-header" >Basic Options
                <i :class="[show_basic_settings ?  'el-icon-arrow-up' : 'el-icon-arrow-down', 'toggle-icon' ]"> </i>
            </h2>
          <transition name="slide-fade">
            <div v-if="show_basic_settings" class="group-content">
              <div v-for="(field,index) in basic_settings" :key="index" class="field-control-item">
                <component :is="'field_' + field.type " :field="field" :editfield="editfield"></component>
              </div>
            </div>
          </transition>
        </div>
        <div name="slide-fade" class="panel-field-group panel-header">
          <h2  @click="toggleAdvanced" class="group-header"> Advanced Options
            <i :class="[show_advanced_settings ? 'el-icon-arrow-up' : 'el-icon-arrow-down', 'toggle-icon']"> </i>
          </h2>
          <transition name="slide-fade">
            <div v-if="show_advanced_settings" class="group-content">
              <div v-for="(field,index) in advanced_settings" :key="index" class="field-control-item">

                <component :is="'field_' + field.type " :field="field" :editfield="editfield"></component>
              </div>
            </div>
          </transition>
        </div>
      </div>
</div>
</template>

<script>
import field_text from "../field-template/text.vue";
import field_text_meta from "../field-template/text-meta.vue";
import field_textarea from "../field-template/textarea.vue";
import field_select from "../field-template/select.vue";
import field_radio from "../field-template/radio.vue";
import field_name from "../field-template/name.vue";
import field_multiselect from "../field-template/multiselect.vue";
import field_checkbox from "../field-template/checkbox.vue";
import field_option_data from "../field-template/option-data.vue";
import field_conditional_logic from "../field-template/conditional-logic.vue";
import field_dynamic from "../field-template/dynamic.vue";
import field_text_with_tag from "../field-template/text-with-tag.vue";
import field_required from "../field-template/required.vue";
import field_html_help_text from "../field-template/html-help-text.vue";
import field_color from "../field-template/color.vue";
import field_selectbtnstyle from '../field-template/selectbtnstyle.vue'
import field_editor from '../field-template/editor.vue'

import field_address from "../pro/field-template/address.vue";
import field_country_list from "../pro/field-template/country-list.vue";
import field_linear from "../pro/field-template/linear.vue";
import field_multiple_product from "../pro/field-template/multiple-product.vue";
import field_quantity from "../pro/field-template/quantity.vue";
import field_product_data from "../pro/field-template/product-data.vue";
import field_price from "../pro/field-template/price.vue";
import field_payment_method from "../pro/field-template/payment-method.vue";
import field_gmap_position from "../pro/field-template/gmap-position.vue";
import field_repeatsettings from "../pro/field-template/repeatsettings.vue";
import field_range from "../pro/field-template/range.vue";

import field_row_data from "../pro/field-template/row-data.vue";
import field_column_data from "../pro/field-template/column-data.vue";

import field_coupon from '../pro/field-template/coupon.vue'
import field_subscription from '../pro/field-template/subscription.vue'
import field_plan from '../pro/field-template/plan.vue'

import field_calculation_options from '../field-template/calculation_options.vue'
import field_range_slider from '../field-template/range-slider.vue'

export default {
  name: "field_options",
  components: {
    field_text,
    field_text_meta,
    field_textarea,
    field_select,
    field_radio,
    field_name,
    field_multiselect,
    field_checkbox,
    field_option_data,
    field_conditional_logic,
    field_dynamic,
    field_address,
    field_country_list,
    field_multiple_product,
    field_linear,
    field_quantity,
    field_product_data,
    field_price,
    field_payment_method,
    field_gmap_position,
    field_text_with_tag,
    field_required,
    field_html_help_text,
    field_color,
    field_selectbtnstyle,
    field_repeatsettings,
    field_range,
    field_editor,
    field_row_data,
    field_column_data,
    field_coupon,
    field_subscription,
    field_plan,
    field_calculation_options,
    field_range_slider
  },

  data: function data() {
    return {
        show_basic_settings: true,
        show_advanced_settings: false,
    };
  },
  computed: {
    field_settings: function() {
      return this.$store.getters.field_settings;
    },
    current_panel: function() {
      return this.$store.getters.current_panel;
    },
    editfield_id: function() {
      return this.$store.getters.editfield;
    },
    editfield: function() {
      var self = this;
      // let field = this.$store.getters.form_fields.find((item) => this.editfield_id === item.id )

        var form_fields = this.$store.getters.form_fields;

        for (let i = 0; i < form_fields.length; i++) {
        
          if( this.editfield_id === form_fields[i].id  ) {
            return form_fields[i]
          }
        
        
          if (form_fields[i].template === 'column_field') {
                var innerColumnFields = form_fields[i].inner_fields;
                for (const columnFields in innerColumnFields) {
                    if (innerColumnFields.hasOwnProperty(columnFields)) {
                        var columnFieldIndex = 0;
                        while (columnFieldIndex < innerColumnFields[columnFields].length) {
                            if (innerColumnFields[columnFields][columnFieldIndex].id === self.editfield_id) {
                                return innerColumnFields[columnFields][columnFieldIndex];
                            }
                            columnFieldIndex++;
                        }
                    }
                }
          }
          

        }
      // return field[0];
    },
    editfieldsetiing: function() {
        return this.field_settings[this.editfield.template].settings.sort((a,b) =>  a.priority - b.priority );
    },

    basic_settings: function() {
      return this.editfieldsetiing.filter( item => item.section == "basic" );
    },

    advanced_settings: function() {
      return this.editfieldsetiing.filter( item => item.section == "advanced" );
    }
  },
  methods: {
     toggleBasic() {
      this.show_basic_settings = !this.show_basic_settings;
      if (this.show_basic_settings) this.show_advanced_settings = false;
    },
    toggleAdvanced() {
      this.show_advanced_settings = !this.show_advanced_settings;
      if (this.show_advanced_settings) this.show_basic_settings = false;
    },
    close: function() {
      this.$store.dispatch("close_field_options", this.field);
    }
  }
};
</script>

<style lang="scss">


.field-settings-wrapper {
  padding-top: 20px;
}

.field-control-item {
  margin-bottom: 20px;

  .contactum-label {
    font-weight: 600;
    font-size: 14px;
    color: #303133;
  }
}

.panel-field {
    margin-bottom: 14px;
    position: relative;
}


.panel-field-group {
  background: #fff;
  margin-bottom: 30px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  transition: box-shadow 0.2s ease, transform 0.2s ease;  
  
  & h2 {
    margin: 0;
    padding: 12px 16px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    background: #f9fafc;
    border-bottom: 1px solid #ebeef5;
    transition: background 0.2s ease;

    &:hover {
      background: #f0f2f5;
    }

    i {
      font-size: 14px;
      color: #909399;
      transition: transform 0.2s ease;
      float: right;
    }
  }

  .group-content {
    padding: 15px;
  }

}


/* Loader */
.options-fileds-section {
  padding: 30px 0;
}

/* Animation */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: max-height 0.3s ease, opacity 0.2s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
}

.slide-fade-enter-to,
.slide-fade-leave-from {
  max-height: 600px;
  opacity: 1;
}


.toggle-icon {
  transition: transform 0.25s ease;
}

</style>
