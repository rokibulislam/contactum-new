<template>
  <div class="panel-field-opt panel-field-opt-select">
    <label class="contactum-label"> {{ field.title }} </label>
    <el-select v-model="value" :style="{width: '100%'}">
      <el-option
        v-for="(option, key) in field.options"
        :key="key"
        :label="option"
        :value="key">
      </el-option>
    </el-select>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_select",
  mixins: [option_field],
};
</script>
