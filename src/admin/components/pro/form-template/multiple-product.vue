<template>
  <div class="contactum-fields">
        <template v-if="field.select_type == 'select'">
          <el-select :class="class_names('select')" disabled :style="{  'width': '100%' }" :placeholder="field.first">
            <el-option
              v-for="(option, key) in field.options"
              :key="key"
              :label="option.name"
              :value="option.price">
            </el-option>
        </el-select>

        </template>

        <template v-else> 

      <ul :class="['contactum-fields-list', ('yes' === field.inline) ? 'contactum-list-inline' : '']">


          <li  v-for="option in field.options">

            <template v-if="field.select_type == 'radio' && field.select_type == 'radio'">
              <input type="radio" :checked="is_selected(option.name)">
            </template>

            <template v-if="field.select_type == 'checkbox' && field.select_type == 'checkbox'">
              <input type="checkbox" :checked="is_selected(option.name)">
            </template>

            <label>
              <img v-if="option.photo != ''" :src="option.photo" width="200px" />
              <span v-else-if="option.photo == ''">{{ option.name }} {{ option.price }}</span>
            </label>
          </li>

        
      </ul>

        </template>


      <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_multiple_product",
  mixins: [form_field],
};
</script>
