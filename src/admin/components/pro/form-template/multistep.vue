<template>
  <div class="step-divider">
    <span class="step-divider__line"></span>
    <span class="step-divider__badge">
      <i class="el-icon-right"></i>
      {{ field.step_title || 'Step' }}
    </span>
    <span class="step-divider__line"></span>
  </div>
</template>

<script type="text/javascript">
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_step_field",
  mixins: [form_field],
};
</script>

<style scoped>
.step-divider {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 0;
}
.step-divider__line {
  flex: 1;
  height: 1px;
  background: repeating-linear-gradient(to right, #c7d2fe 0, #c7d2fe 6px, transparent 6px, transparent 12px);
}
.step-divider__badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  color: #4f46e5;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  border-radius: 20px;
  padding: 4px 12px;
  white-space: nowrap;
}
</style>
