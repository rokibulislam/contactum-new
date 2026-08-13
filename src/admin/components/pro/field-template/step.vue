<template>
  <div class="step-settings">
    <label class="contactum-label">Step Title</label>
    <el-input
        v-model="value"
        placeholder="e.g. Contact Details"
    />
    <p class="step-settings__hint">
      Shown in the progress indicator for the step that starts here. Everything from this field
      to the next Step field (or the end of the form) belongs to this step.
    </p>
  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";

export default {
  name: "field_step",
  mixins: [option_field],
};
</script>

<style scoped>
.step-settings__hint {
  margin: 6px 0 0;
  font-size: 12px;
  color: #909399;
  line-height: 1.5;
}
</style>
