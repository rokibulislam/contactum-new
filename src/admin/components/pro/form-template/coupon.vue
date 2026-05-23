<template>
  <div class="ctm-coupon-preview">

    <div class="ctm-coupon-input-row">
      <div class="ctm-coupon-input-wrap">
        <span class="ctm-coupon-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
            <line x1="7" y1="7" x2="7.01" y2="7"/>
          </svg>
        </span>
        <input
          type="text"
          class="ctm-coupon-code-input"
          :placeholder="field.placeholder || 'Enter coupon code'"
          disabled
        />
      </div>
      <button type="button" class="ctm-coupon-apply-btn" disabled>
        {{ field.button_label || 'Apply' }}
      </button>
    </div>

    <div v-if="field.show_discount_line" class="ctm-coupon-discount-line">
      <span class="ctm-coupon-discount-label">Discount:</span>
      <strong class="ctm-coupon-discount-amount">— —</strong>
    </div>

    <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_coupon_field",
  mixins: [form_field],
};
</script>

<style lang="scss" scoped>
.ctm-coupon-preview {
  pointer-events: none;
  max-width: 480px;
}

.ctm-coupon-input-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ctm-coupon-input-wrap {
  position: relative;
  flex: 1;
  min-width: 0;
}

.ctm-coupon-icon {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  color: #9ca3af;

  svg {
    width: 15px;
    height: 15px;
  }
}

.ctm-coupon-code-input {
  width: 100%;
  height: 38px;
  padding: 0 12px 0 34px;
  font-size: 14px;
  color: #6b7280;
  background: #fff;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  box-sizing: border-box;
  font-family: monospace;
  letter-spacing: .04em;
}

.ctm-coupon-apply-btn {
  display: inline-flex;
  align-items: center;
  height: 38px;
  padding: 0 18px;
  font-size: 13px;
  font-weight: 600;
  color: #fff;
  background: #6366f1;
  border: none;
  border-radius: 8px;
  cursor: default;
  white-space: nowrap;
  flex-shrink: 0;
}

.ctm-coupon-discount-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 10px;
  padding: 8px 12px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  font-size: 13px;
  color: #166534;
}

.ctm-coupon-discount-label {
  font-weight: 500;
}

.ctm-coupon-discount-amount {
  font-weight: 700;
  color: #16a34a;
}
</style>
