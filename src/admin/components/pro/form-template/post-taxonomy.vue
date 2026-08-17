<template>
  <div class="contactum-fields">
    <select class="contactum-el-form-control" disabled>
      <option>{{ field.placeholder || '— Select —' }}</option>
    </select>
    <span v-if="field.help" v-html="field.help"></span>
  </div>
</template>
<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_post_taxonomy_field",
  mixins: [form_field]
};
</script>
