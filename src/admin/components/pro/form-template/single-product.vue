<template>
  <div class="ctm-single-product ctm-single-product--preview">

    <div class="ctm-single-product__card">

      <div class="ctm-single-product__body">
        <p class="ctm-single-product__name">{{ field.label || 'Product' }}</p>

        <!-- Flexible / donation price -->
        <template v-if="field.price && field.price.is_flexible">
          <span class="ctm-single-product__price-lbl">
            {{ isDonation ? 'Amount' : 'Price' }}
          </span>
          <div class="ctm-single-product__price-wrap">
            <span class="ctm-single-product__symbol">$</span>
            <input
              class="ctm-single-product__price-input"
              type="number"
              :value="field.price.min || 0"
              disabled
            />
          </div>
        </template>

        <!-- Fixed price -->
        <template v-else>
          <span class="ctm-single-product__price-badge">
            ${{ fixedPrice }}
          </span>
        </template>
      </div>

      <!-- Quantity stepper -->
      <div v-if="field.quantity && field.quantity.status" class="ctm-single-product__qty-wrap">
        <span class="ctm-single-product__qty-lbl">Qty</span>
        <div class="ctm-qty-stepper">
          <button type="button" class="ctm-qty-btn ctm-qty-btn--minus" disabled>&#8722;</button>
          <input class="ctm-qty-input" type="number" :value="field.quantity.min || 1" disabled />
          <button type="button" class="ctm-qty-btn ctm-qty-btn--plus" disabled>&#43;</button>
        </div>
      </div>

    </div>

    <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_single_product",
  mixins: [form_field],
  computed: {
    isDonation() {
      return this.field.price && this.field.price.type === 'donation';
    },
    fixedPrice() {
      const p = this.field.price && this.field.price.price;
      return p ? parseFloat(p).toFixed(2) : '0.00';
    },
  },
};
</script>

<style lang="scss" scoped>
.ctm-single-product--preview {
  pointer-events: none;
}

.ctm-single-product__card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px 18px;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  max-width: 440px;
}

.ctm-single-product__body { flex: 1; min-width: 0; }

.ctm-single-product__name {
  margin: 0 0 6px;
  font-size: 14px;
  font-weight: 600;
  color: #111827;
}

.ctm-single-product__price-badge {
  display: inline-block;
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #bbf7d0;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  padding: 3px 10px;
}

.ctm-single-product__price-lbl {
  display: block;
  font-size: 11px;
  font-weight: 500;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 4px;
}

.ctm-single-product__price-wrap {
  display: flex;
  align-items: center;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  overflow: hidden;
  width: fit-content;
}

.ctm-single-product__symbol {
  padding: 0 8px;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  background: #f9fafb;
  border-right: 1px solid #d1d5db;
  line-height: 32px;
}

.ctm-single-product__price-input {
  border: none;
  padding: 0 10px;
  font-size: 13px;
  color: #111827;
  width: 90px;
  height: 32px;
  background: #fff;
  -moz-appearance: textfield;
  &::-webkit-inner-spin-button,
  &::-webkit-outer-spin-button { -webkit-appearance: none; }
}

.ctm-single-product__qty-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

.ctm-single-product__qty-lbl {
  font-size: 11px;
  font-weight: 500;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: .04em;
}

.ctm-qty-stepper {
  display: flex;
  align-items: center;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
}

.ctm-qty-btn {
  width: 30px;
  height: 32px;
  border: none;
  background: #f9fafb;
  color: #374151;
  font-size: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  flex-shrink: 0;
  cursor: default;
}

.ctm-qty-input {
  width: 38px;
  height: 32px;
  border: none;
  border-left: 1px solid #e5e7eb;
  border-right: 1px solid #e5e7eb;
  text-align: center;
  font-size: 13px;
  font-weight: 500;
  color: #111827;
  background: #fff;
  -moz-appearance: textfield;
  &::-webkit-inner-spin-button,
  &::-webkit-outer-spin-button { -webkit-appearance: none; }
}
</style>
