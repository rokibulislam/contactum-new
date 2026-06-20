<template>
  <div class="contactum-fields">
    <div class="ctm-color-preview-wrap">
      <input
        type="color"
        :value="field.default || '#000000'"
        class="ctm-color-input"
        disabled
      />
      <span class="ctm-color-value">{{ field.default || '#000000' }}</span>
    </div>
    <span v-if="field.help" v-html="field.help"></span>
  </div>
</template>

<script>
import form_field from "../../mixin/form-field.js";
export default {
  name: "form_color_picker",
  mixins: [form_field],
};
</script>

<style scoped lang="scss">
.ctm-color-preview-wrap {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 4px 10px 4px 4px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #fff;
  cursor: default;
}
.ctm-color-input {
  width: 32px;
  height: 28px;
  border: none;
  border-radius: 4px;
  padding: 0;
  cursor: default;
  outline: none;
  background: none;
}
.ctm-color-value {
  font-size: 13px;
  color: #374151;
  font-family: monospace;
}
</style>
