<template>
  <div class="ctm-multi-product-preview">

    <!-- Card grid (radio / checkbox) -->
    <div
      v-if="field.select_type !== 'select'"
      :class="['ctm-multi-product', 'ctm-multi-product--' + (field.select_type || 'radio')]"
    >
      <div
        v-for="(option, idx) in field.options"
        :key="idx"
        :class="['ctm-product-card', isSelected(option.name) ? 'ctm-product-card--selected' : '']"
      >
        <!-- Photo -->
        <div v-if="field.photo_value && option.photo" class="ctm-product-card__img">
          <img :src="option.photo" :alt="option.name" />
        </div>
        <div v-else-if="field.photo_value" class="ctm-product-card__img ctm-product-card__img--placeholder">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <path d="M21 15l-5-5L5 21"/>
          </svg>
        </div>

        <div class="ctm-product-card__body">
          <span class="ctm-product-card__name">{{ option.name }}</span>
          <span class="ctm-product-card__price">${{ formatPrice(option.price) }}</span>
        </div>

        <span class="ctm-product-card__check" aria-hidden="true">
          {{ field.select_type === 'checkbox' ? '✓' : '●' }}
        </span>
      </div>
    </div>

    <!-- Select dropdown (when select_type === 'select') -->
    <el-select
      v-else
      style="width:100%;max-width:320px"
      disabled
      placeholder="— Select a product —"
    >
      <el-option
        v-for="(option, idx) in field.options"
        :key="idx"
        :label="option.name + '  —  $' + formatPrice(option.price)"
        :value="option.name"
      />
    </el-select>

    <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_multiple_product",
  mixins: [form_field],
  methods: {
    isSelected(name) {
      const sel = this.field.selected;
      if (Array.isArray(sel)) return sel.includes(name);
      return sel === name;
    },
    formatPrice(p) {
      return p ? parseFloat(p).toFixed(2) : '0.00';
    },
  },
};
</script>

<style lang="scss" scoped>
.ctm-multi-product-preview {
  pointer-events: none;
}

.ctm-multi-product {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.ctm-product-card {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 140px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
  transition: border-color .18s;

  &--selected {
    border-color: #6366f1;
    background: #fafafa;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
  }
}

.ctm-product-card__img {
  width: 100%;
  height: 90px;
  background: #f3f4f6;
  overflow: hidden;
  flex-shrink: 0;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  &--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;

    svg {
      width: 32px;
      height: 32px;
      color: #d1d5db;
    }
  }
}

.ctm-product-card__body {
  padding: 9px 11px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.ctm-product-card__name {
  font-size: 12px;
  font-weight: 600;
  color: #111827;
  line-height: 1.3;
  word-break: break-word;
}

.ctm-product-card__price {
  font-size: 12px;
  font-weight: 700;
  color: #6366f1;
}

.ctm-product-card__check {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #6366f1;
  color: #fff;
  font-size: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transform: scale(.5);
  transition: opacity .15s, transform .15s;

  .ctm-product-card--selected & {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
