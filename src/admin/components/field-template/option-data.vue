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
        <li v-if="editfield.image">
            <el-checkbox v-model="editfield.photo_value"> {{ "Photo" }} </el-checkbox>
        </li>
      </ul>
    </div>

    <ul :class="['field-options', 'option-field-swapper']">
      <li
        v-for="(option, index) in options"
        :key="index"
        class="option-field-option"
      >
        <div class="selector">
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

        <div class="sort-handler">
          <i class="fa fa-bars"></i>
        </div>

        <div v-if="editfield.photo_value == true ">
          <div class="ff_photo_card">
            <div class="wpf_photo_holder">
              <img
                style="max-width: 100%"
                v-if="option.photo"
                :src="option.photo"
                width="50px"
              />
              <div class="photo_widget_btn">
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

          <i class="el-icon-remove-outline"  v-if="options.length > 1" @click="delete_option(index)"></i>

      </li>
    </ul>
    <div class="option-actions">
      <i class="el-icon-circle-plus-outline" @click.prevent="add_option"></i>
       <el-button @click="initBulkEdit()" class="option-btn  bulkedit"> Bulk Edit </el-button>
      <el-button
        type="danger"
        v-if="!field.is_multiple && selected.length > 0"
        @click.prevent="clear_selection"
      >
        {{ "Clear Selection" }}
      </el-button>
    </div>

    <el-dialog :visible.sync="bulkEditVisible" >
      <el-input type="textarea" v-model="value_key_pair_text"> {{ value_key_pair_text }} </el-input>
      <el-button @click="bulkEditVisible = false">Cancel</el-button>
      <el-button type="primary" @click="confirmBulkEdit()">Confirm</el-button>
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
      let length = this.options.length + 1;
      let label = `Option-${length}`;
      let value = `option-${length}`;
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
  justify-content: space-between;
}

.option-field-option i {
  align-items: center;
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

.option-field-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.option-actions {
  margin-top: 15px;
  display: flex;
  align-items: center;
  gap: 10px;

  & button {
    padding: 6px 10px;
  }
}

.option-field-swapper .sort-handler {
  width: 22px;
  cursor: grab;
  color: #888;
}


.ff_photo_card {
  width: 80px;
  height: 60px;
  position: relative;
  border: 1px solid #ccc;
  border-radius: 6px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;

  & img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
}

.photo_widget_btn {
  position: absolute;
  bottom: 4px;
  left: 4px;
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

.selector input[type="checkbox"],
.selector input[type="radio"] {
  transform: scale(1.2);
}

</style>
