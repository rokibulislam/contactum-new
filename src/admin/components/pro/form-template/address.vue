<template>
  <div class="ctm-address-preview">
    <div class="ctm-address-preview__grid">
      <template v-for="(sub, key) in field.address">
        <div
          v-if="sub.checked"
          :key="key"
          :class="['ctm-address-preview__field', 'ctm-address-preview__field--' + key]"
        >
          <label class="ctm-address-preview__label">
            {{ sub.label }}<span v-if="sub.required" class="required"> *</span>
          </label>
          <div class="ctm-address-preview__input">
            {{ sub.placeholder || '&nbsp;' }}
          </div>
        </div>
      </template>
    </div>
    <span v-if="field.help" v-html="field.help" class="ctm-address-preview__help" />
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_address_field",
  mixins: [form_field],
};
</script>

<style scoped>
.ctm-address-preview {
  width: 100%;
}

.ctm-address-preview__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.ctm-address-preview__field--street_address {
  grid-column: 1 / -1;
}

.ctm-address-preview__label {
  display: block;
  font-size: 12px;
  color: #555;
  margin-bottom: 3px;
  font-weight: 500;
}

.ctm-address-preview__input {
  width: 100%;
  padding: 5px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  background: #f9fafb;
  font-size: 12px;
  color: #bbb;
  box-sizing: border-box;
  min-height: 28px;
  line-height: 18px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.ctm-address-preview__help {
  display: block;
  margin-top: 6px;
  font-size: 12px;
  color: #888;
}

.required {
  color: #ef4444;
}
</style>
