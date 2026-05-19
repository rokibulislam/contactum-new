<template>
  <div class="panel-field">
    <label class="contactum-label">{{ field.title }}</label>
    <div class="contactum-range-preview">
      <input
        type="range"
        :min="minValue"
        :max="maxValue"
        :step="stepValue"
        v-model="value"
        class="contactum-range-slider-preview"
      />
      <span class="contactum-range-preview-val">{{ prefix }}{{ value }}{{ suffix }}</span>
    </div>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_range_slider",
  mixins: [option_field],
  computed: {
    minValue() {
      return parseFloat(this.editfield.min_value) || 0;
    },
    maxValue() {
      return parseFloat(this.editfield.max_value) || 100;
    },
    stepValue() {
      return parseFloat(this.editfield.step_value) || 1;
    },
    prefix() {
      return this.editfield.prefix || "";
    },
    suffix() {
      return this.editfield.suffix || "";
    }
  }
};
</script>

<style scoped>
.contactum-range-preview {
  display: flex;
  align-items: center;
  gap: 10px;
}
.contactum-range-slider-preview {
  flex: 1;
}
.contactum-range-preview-val {
  min-width: 40px;
  text-align: right;
  font-weight: 600;
}
</style>
