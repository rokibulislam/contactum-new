<template>
  <div class="ctm-ps-placeholder">
    <span class="ctm-ps-placeholder__icon dashicons dashicons-list-view"></span>
    <span class="ctm-ps-placeholder__text">Payment Summary will show here</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_payment_summary",
  mixins: [form_field],
};
</script>

<style scoped>
.ctm-ps-placeholder {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px 14px;
  border: 1px dashed #d1d5db;
  border-radius: 6px;
  background: #f9fafb;
  color: #9ca3af;
  font-size: 12px;
  pointer-events: none;
}
.ctm-ps-placeholder__icon {
  font-size: 15px;
  width: 15px;
  height: 15px;
  flex-shrink: 0;
}
</style>
