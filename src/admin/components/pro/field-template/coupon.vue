<template>
  <div class="contactum-field-options contactum-coupon-options">

    <div class="contactum-option-row">
      <label class="contactum-label">Placeholder Text</label>
      <el-input
        v-model="editfield.placeholder"
        placeholder="e.g. Enter coupon code"
        size="small"
      />
    </div>

    <div class="contactum-option-row">
      <label class="contactum-label">Button Label</label>
      <el-input
        v-model="editfield.button_label"
        placeholder="Apply"
        size="small"
      />
    </div>

    <div class="contactum-option-row">
      <label class="contactum-label">Success Message</label>
      <el-input
        v-model="editfield.success_message"
        placeholder="Coupon applied successfully!"
        size="small"
      />
    </div>

    <div class="contactum-option-row">
      <label class="contactum-label">Error Message</label>
      <el-input
        v-model="editfield.error_message"
        placeholder="Invalid or expired coupon code."
        size="small"
      />
    </div>

    <div class="contactum-option-row contactum-option-row--switch">
      <label class="contactum-label">Show Discount Line in Total</label>
      <el-switch v-model="editfield.show_discount_line" />
    </div>

    <div class="contactum-option-row contactum-option-row--switch">
      <label class="contactum-label">Auto-apply from URL (?coupon=CODE)</label>
      <el-switch v-model="editfield.auto_apply" />
    </div>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";

export default {
  name: "field_coupon",
  mixins: [option_field],

  created() {
    if ( this.editfield.button_label      === undefined ) this.$set( this.editfield, 'button_label',      'Apply' );
    if ( this.editfield.success_message   === undefined ) this.$set( this.editfield, 'success_message',   '' );
    if ( this.editfield.error_message     === undefined ) this.$set( this.editfield, 'error_message',     '' );
    if ( this.editfield.show_discount_line === undefined ) this.$set( this.editfield, 'show_discount_line', true );
    if ( this.editfield.auto_apply        === undefined ) this.$set( this.editfield, 'auto_apply',        false );
  },
};
</script>

<style scoped>
.contactum-coupon-options {
  padding: 8px 0;
}
.contactum-option-row {
  display: flex;
  flex-direction: column;
  margin-bottom: 14px;
}
.contactum-option-row--switch {
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
}
.contactum-label {
  font-size: 12px;
  color: #606266;
  margin-bottom: 4px;
}
</style>
