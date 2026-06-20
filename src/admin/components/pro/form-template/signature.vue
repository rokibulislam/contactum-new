<template>
  <div class="sig-preview">

    <!-- label -->
    <div v-if="field.label !== false" class="sig-preview__label">
      {{ field.label || 'Signature' }}
      <span v-if="field.required === 'yes'" class="sig-preview__required"> *</span>
    </div>

    <!-- pad area -->
    <div class="sig-preview__pad">
      <div class="sig-preview__hint">
        <svg class="sig-preview__pen-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 20h9" />
          <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
        </svg>
        <span>Sign here</span>
      </div>
      <!-- baseline -->
      <div class="sig-preview__baseline"></div>
    </div>

    <!-- footer -->
    <div class="sig-preview__footer">
      <span class="sig-preview__clear">
        <i class="el-icon-refresh-left"></i> Clear
      </span>
    </div>

    <!-- help text -->
    <div v-if="field.help" class="sig-preview__help" v-html="field.help" />

  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_signature_field",
  mixins: [form_field],
};
</script>

<style scoped>
.sig-preview {
  width: 100%;
}

/* label */
.sig-preview__label {
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}
.sig-preview__required {
  color: #ef4444;
}

/* pad */
.sig-preview__pad {
  position: relative;
  width: 100%;
  min-height: 80px;
  background: #fafafa;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-sizing: border-box;
}

.sig-preview__hint {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  color: #c4c9d4;
  user-select: none;
  padding-bottom: 14px;
}

.sig-preview__pen-icon {
  width: 22px;
  height: 22px;
  opacity: .6;
}

.sig-preview__hint span {
  font-size: 11px;
  letter-spacing: .04em;
}

/* baseline across the bottom of the pad */
.sig-preview__baseline {
  position: absolute;
  bottom: 18px;
  left: 12px;
  right: 12px;
  height: 1px;
  background: #d1d5db;
  border-radius: 1px;
}

/* footer */
.sig-preview__footer {
  display: flex;
  justify-content: flex-end;
  margin-top: 5px;
}

.sig-preview__clear {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 11px;
  color: #9ca3af;
  cursor: default;
  user-select: none;
}

/* help */
.sig-preview__help {
  margin-top: 5px;
  font-size: 11px;
  color: #9ca3af;
}
</style>
