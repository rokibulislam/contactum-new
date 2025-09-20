<template>
  <div class="panel-field">
    <label class="contactum-label"> {{ field.title }}</label>
    <el-input
        type="textarea"
        :rows="field.rows || 5"
        v-model="value"
      >
      </el-input>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_textarea",
  mixins: [option_field],
};
</script>