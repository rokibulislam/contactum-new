<template>
  <div class="contactum-fields">
    <el-select :class="class_names('select')" disabled :style="{  'width': '100%' }">
      <el-option
        v-for="(option, key) in field.options"
        :key="key"
        :label="option.label"
        :value="option.value">
      </el-option>
    </el-select>
    <span v-if="field.help" class="help" v-html="field.help"></span>
  </div>
</template>
<script>
import form_field from "../../mixin/form-field.js";
export default {
  name: "form_multiple_select",
  mixins: [form_field],
};
</script>
