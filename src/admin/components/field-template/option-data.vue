<template>
  <div>
    <div>
      <label class="contactum-label">{{ field.name }}</label>
      <ul class="field-option-actions">
        <li>
          <el-checkbox v-model="editfield.show_value" > {{ "Show values" }} </el-checkbox>
        </li>
        <li>
          <el-checkbox v-model="editfield.sync_value"> {{ "Sync values" }} </el-checkbox>
        </li>
        <li>
          <el-checkbox v-model="editfield.calc_value"> {{ "Calc values" }} </el-checkbox>
        </li>
        <li v-if="editfield.image">
            <el-checkbox v-model="editfield.photo_value"> {{ "Photo" }} </el-checkbox>
        </li>
      </ul>
    </div>

    <ul :class="['option-manager__list', 'option-field-swapper']">
      <li
        v-for="(option, index) in options"
        :key="index"
        class="option-manager__row"
      >
        <div class="option-manager__selector">
          <input
            v-if="field.is_multiple"
            type="checkbox"
            :value="option.value"
            v-model="selected"
          />
          <input
            v-else
            type="radio"
            :value="option.value"
            v-model="selected"
            class="option-chooser-radio"
          />
        </div>

        <div class="option-manager__handle">
          <i class="el-icon-s-fold"></i>
        </div>

        <div v-if="editfield.photo_value == true ">
          <div class="option-manager__photo-card">
            <div>
              <img
                style="max-width: 100%"
                v-if="option.photo"
                :src="option.photo"
                width="50px"
                class="option-manager__image"
              />
              <div class="option-manager__photo-actions">
                <i class="el-icon-upload" @click="initUploader(option)"> </i>
                <i class="el-icon-delete" v-if="option.photo" @click="option.photo = ''"> </i>
              </div>
            </div>
          </div>
        </div>

        <div class="option_label">
          <el-input type="text" v-model="option.label" @input="set_option_value(index, option.label)"> </el-input>
        </div>

        <div v-if="editfield.show_value" class="option_value">
          <el-input type="text" v-model="option.value"> </el-input>
        </div>

        <div v-if="editfield.calc_value" class="option_value">
          <el-input type="text" v-model="option.calc_value"> </el-input>
        </div>

          <i class="el-icon-circle-plus-outline" @click.prevent="add_option"></i>
          <i class="el-icon-remove-outline"  v-if="options.length > 1" @click="delete_option(index)"></i>

      </li>
    </ul>

    <div class="option-manager__footer">
       <el-button @click="initBulkEdit()" class="bulkedit"> Bulk Edit </el-button>
      <el-button
        type="danger"
        v-if="!field.is_multiple && selected.length > 0"
        @click.prevent="clear_selection"
      >
        {{ "Clear Selection" }}
      </el-button>
    </div>

    <el-dialog :visible.sync="bulkEditVisible" custom-class="bulk-edit-dialog">

      <div slot="dialog-header">
          <h4 class="mb-2"> Edit your options </h4>
          <p> Please provide the value as LABEL:VALUE as each line or select from predefined data sets </p>
      </div>

      <div class="dialog-body">
        <div class="data-set-tags mb-4">
        <el-tag 
          v-for="(options, optionGroup) in editor_options" 
          :key="optionGroup"
          :class="['set-tag', { 'is-active': options === activeClass }]"
          @click="setOptions(options)"
          effect="plain">
          {{ optionGroup }}
        </el-tag>
      </div>

        <el-input type="textarea" v-model="value_key_pair_text"> {{ value_key_pair_text }} </el-input>
      
      </div>
      
      <span slot="footer" class="dialog-footer">
        <el-button @click="bulkEditVisible = false" class="btn-cancel">Cancel</el-button>
        <el-button type="primary" @click="confirmBulkEdit()" class="btn-confirm">Confirm</el-button>
      </span>

    </el-dialog>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
import media from "../../mixin/media.js";
export default {
  name: "field_option_data",
  mixins: [option_field, media],
  data: function () {
    return {
      options: [],
      selected: [],
      bulkEditVisible: false,
      value_key_pair_text: "",
      editor_options: JSON.parse(window.contactum.bulk_options_json),
    };
  },
  computed: {
    field_selected: function () {
      return this.editfield.selected;
    },
  },

  methods: {
    initBulkEdit: function () {
      let astext = "";

      this.options.map((item, index) => {
        astext += item.label;
        astext += ":" + item.value;
        astext += String.fromCharCode(13, 10);
      });

      this.value_key_pair_text = astext;

      this.bulkEditVisible = true;
    },

    setOptions(options) {
        this.value_key_pair_text = options.join('\n');
    },

    confirmBulkEdit() {
      let lines = this.value_key_pair_text.split("\n");
      let values = [];

      lines.filter((line) => {
        let lineItem = line.split(":");
        let label = lineItem[0];
        let value = lineItem[1];
        if (!value) {
          value = label;
        }
        if (label && value) {
          values.push({
            label: label,
            value: value,
            photo: "",
          });
        }
      });

      this.options = values;
      this.bulkEditVisible = false;
    },

    set_options: function () {
      this.options = this.editfield.options;
      if (this.editfield.is_multiple && !Array.isArray(this.field_selected)) {
        this.selected = [this.field_selected];
      } else {
        this.selected = this.field_selected;
      }
    },

    add_option: function () {
      
      const numbers = this.options
      .map(opt => {
        const match = opt.label.match(/Option-(\d+)/);
        return match ? parseInt(match[1]) : 0;
      });

      const nextNumber = numbers.length ? Math.max(...numbers) + 1 : 1;

      const label = `Option-${nextNumber}`;
      const value = `option-${nextNumber}`;

      this.options.push({
        label: label,
        value: value,
        photo: "",
      });
      
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "options",
        value: this.options,
      });

    },


    set_option_value: function (index, label) {
      if (this.sync_value) {
        this.options[index].value = label
          .toLocaleLowerCase()
          .replace(/\s/g, "_");
      }
    }
  }
};
</script>

<style lang="scss" scoped>

.field-option-actions {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-top: 6px;
  padding: 8px 12px;
  background: #f9fafb;
  border-radius: 6px;
}

.option-manager__row i {
  align-items: center;
}

.option-manager__list {
  margin-bottom: 5px;
  margin-top: 15px;
  max-height: 300px;
    overflow-y: auto;
  & li {
    display: flex;
    justify-content: flex-start;
    gap: 20px;

    & div.option_label, & div.option_value {
      margin-right: 10px;
    }
  }
}


.option-manager__row {
  display: grid;
  grid-template-columns: 30px 24px auto 1fr 1fr 32px;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: #ffffff;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  margin-bottom: 8px;
}


.option-manager__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px;
  background: #f9fafb;
  border-radius: 8px;


  & button {
    padding: 6px 10px;
  }
}

.option-field-swapper .option-manager__handle {
  width: 22px;
  cursor: grab;
  color: #888;
}

.option-manager__photo-card {
  width: 64px;
  height: 48px;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  background: #f8fafc;
  position: relative;

  & img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.option-manager__photo-actions {
  position: absolute;
    bottom: 25%;
    left: 25%;
  display: flex;
  gap: 4px;

  & i {
    font-size: 16px;
    cursor: pointer;
    color: #007bff;
    background: #fff;
    border-radius: 50%;
    padding: 2px;

    &:hover {
      color: #0056b3;
    }
  }
}

.photo_widget_btn {
  opacity: 0;
  transition: opacity 0.2s;
}

.option-manager__photo-card:hover .photo_widget_btn {
  opacity: 1;
}



.el-icon-remove-outline {
  opacity: 0.4;
  transition: 0.2s;
}

.option-manager__row:hover .el-icon-remove-outline {
  opacity: 1;
}


.option-manager__selector input[type="checkbox"],
.option-manager__selector input[type="radio"] {
  transform: scale(1.2);
}

.option-manager__handle {
  cursor: grab;
  color: #9ca3af;

  &:hover {
    color: #2563eb;
  }
}



.bulk-edit-dialog {
  border-radius: 8px; // Rounded corners

  .data-set-tags {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    overflow-x: auto;
    white-space: nowrap;

    .set-tag {
      background-color: #f0f2f5;
      border: none;
      color: #606266;
      border-radius: 20px; // Pill shape
      padding: 6px 15px;
      cursor: pointer;
      transition: all 0.2s;
      
      &.is-active, &:hover {
        background-color: #4086f4; // Brand blue
        color: #ffffff;
      }
    }
  }

  .dialog-body {
    padding: 10px 25px; // Standard spacing
  }

  .dialog-footer {
    margin-top: 10px;
    padding: 20px 25px 25px;
    text-align: left; // Aligns buttons to the left as seen in your image
  }

  .btn-cancel {
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    font-weight: 500;
    color: #606266;
  }

  .btn-confirm {
    background-color: #3b82f6; // High-contrast blue
    border-color: #3b82f6;
    border-radius: 4px;
    font-weight: 500;
    padding: 10px 25px;
  }
}

</style>
