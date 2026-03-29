<template>
<div class="panel-field-opt panel-field-opt-text">
  <div class="field-option-actions">
    <label class="contactum-label">
        {{ field.title }}  <help-text v-if="field.help_text" :text="field.help_text"></help-text>
    </label>

    <el-checkbox v-model="show_value" :label="show_value"> {{ "Show values" }} </el-checkbox>

  </div>

    <ul :class="['field-options', 'option-field-swapper', show_value ? 'show-value' : '']">
        <li v-for="(option, index) in options" :key="index" class="option-field-option">
            <div class="selector">
              <input
                  type="radio"
                  :value="option.value"
                  v-model="selected"
                  class="option-chooser-radio"
              />
            </div>

            <div class="sort-handler">
                <i class="fa fa-bars"></i>
            </div>

            <div class="label">
                <el-input type="text" v-model="option.label" @input="set_option_value(index, option.label)"> </el-input>
            </div>

            <div v-if="show_value" class="value">
              <el-input type="text" v-model="option.value"> </el-input>
            </div>

            <div class="action-buttons clearfix">
              <i class="el-icon-remove-outline"  v-if="options.length > 1" @click="delete_option(index)"></i>
            </div>
        </li>
        <li>
          <i class="el-icon-circle-plus-outline" @click.prevent="add_option"></i>
        </li>
    </ul>
</div>

</template>

<script>
import option_field from "../../../mixin/option-field.js";
export default {
    name: "field_row_data",
    mixins: [option_field],
    
    data: function () {
        return {
            show_value: false,
            options: [],
            selected: []
        };
    },

    computed: {
        field_selected: function () {
            return this.editfield.selected;
        },
    },

    mounted: function () {
        if (!window.wpActiveEditor) {
            window.wpActiveEditor = null;
        }

        var self = this;

        this.set_options();
    },

    methods: {

        set_options: function () {
          this.options = this.editfield.grid_rows;
        },

        add_option: function () {
          let length = this.options.length + 1;
          let label = `Grid-${length}`;
          let value = `Grid-${length}`;

          this.options.push({
            label: label,
            value: value,
          });

          this.$store.dispatch("update_editing_form_field", {
            id: this.editfield.id,
            // property: this.field.name,
            property: "grid_rows",
            value: this.options,
          });

        },

        delete_option: function (index) {
          this.options.splice(index, 1);

          this.$store.dispatch("update_editing_form_field", {
            id: this.editfield.id,
            // property: "grid_rows",
            property: this.field.name,
            value: this.options,
          });
        },

        clear_selection: function () {
          this.selected = [];
        },
    },

    watch: {
      options: {
        deep: true,
        handler: function (new_option) {
          this.$store.dispatch("update_editing_form_field", {
            id: this.editfield.id,
            // property: "grid_rows",
            property: this.field.name,
            value: new_option,
          });
        },
      },

      selected: function (new_val) {
        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: "selected",
          value: new_val,
        });
      },
    },
}
</script>


<style lang="scss" scoped>
  .field-option-actions {
    display: flex;
    justify-content: space-between;
  }

  .field-options {
    margin-bottom: 5px;
    margin-top: 15px;
    & li {
      display: flex;
      justify-content: flex-start;
      gap: 20px;

      & div.option_label, & div.option_value {
        margin-right: 10px;
      }
    }
  }

  .el-icon-circle-plus-outline {
    font-size: 24px;
    color: #5cb85c;
    cursor: pointer;

    &:hover {
      color: #449d44;
    }
  }

  .el-icon-remove-outline {
    color: #d9534f;
    font-size: 20px;
    cursor: pointer;

    & :hover {
      color: #c9302c;
    }
  }

</style>