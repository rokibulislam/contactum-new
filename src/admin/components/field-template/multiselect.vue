<template>
  <div class="panel-field">
    <el-select v-model="value" multiple>
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
  name: "field_multiselect",
  mixins: [option_field],
};
</script>
