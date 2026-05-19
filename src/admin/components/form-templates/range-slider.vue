<template>
  <div class="contactum-fields contactum-range-slider-wrap">
    <div v-if="field.show_value === 'yes'" class="contactum-range-output">
      {{ field.prefix }}<span>{{ currentValue }}</span>{{ field.suffix }}
    </div>
    <input
      type="range"
      :class="class_names('contactum-range-slider')"
      :min="field.min_value || 0"
      :max="field.max_value || 100"
      :step="field.step_value || 1"
      v-model="currentValue"
      disabled
    />
    <div class="contactum-range-labels">
      <span>{{ field.prefix }}{{ field.min_value || 0 }}{{ field.suffix }}</span>
      <span>{{ field.prefix }}{{ field.max_value || 100 }}{{ field.suffix }}</span>
    </div>
    <span v-if="field.help" v-html="field.help" />
  </div>
</template>

<script>
import form_field from "../../mixin/form-field.js";
export default {
  name: "form_range_slider_field",
  mixins: [form_field],
  data() {
    return {
      currentValue: this.field.default || 50
    };
  },
  watch: {
    "field.default"(val) {
      this.currentValue = val;
    }
  }
};
</script>

<style scoped lang="scss">
.contactum-range-slider-wrap {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.contactum-range-output {
  font-size: 14px;
  font-weight: 600;
  color: #333;
}
.contactum-range-slider {
  width: 100%;
  cursor: default;
  accent-color: #0076FF;
}
.contactum-range-labels {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #888;
}
</style>
