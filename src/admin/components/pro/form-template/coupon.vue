<template>
  <div class="contactum-coupon-preview">
    <div class="contactum-coupon-input-wrap">
      <el-input
        type="text"
        :placeholder="field.placeholder || 'Enter coupon code'"
        :value="field.default"
        :size="field.size || 'small'"
        class="contactum-coupon-input"
        disabled
      />
      <el-button type="primary" size="small" disabled>
        {{ field.button_label || 'Apply' }}
      </el-button>
    </div>
    <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
    <p v-if="field.show_discount_line" class="contactum-coupon-discount-preview">
      Discount: <strong>—</strong>
    </p>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";

export default {
  name: "form_coupon_field",
  mixins: [form_field],
};
</script>

<style scoped>
.contactum-coupon-preview {
  width: 100%;
}
.contactum-coupon-input-wrap {
  display: flex;
  gap: 6px;
  align-items: center;
}
.contactum-coupon-input {
  flex: 1;
}
.contactum-coupon-discount-preview {
  margin: 6px 0 0;
  font-size: 12px;
  color: #67c23a;
}
</style>
