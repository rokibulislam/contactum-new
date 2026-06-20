<template>
  <div class="linear-scale-container">

    <div class="scale-labels">
      <span class="label-start">{{ field.scale_text.first }}</span>
      <span class="label-end">{{ field.scale_text.last }}</span>
    </div>

    <div class="scale-button-group">
      <div 
        v-for="i in linearRange(field.scale_range.from, field.scale_range.to)" 
        :key="i"
        class="scale-item"
      >
        <button type="button" disabled class="scale-button">{{ i }}</button>
      </div>
    </div>

    <span v-if="field.help" class="help-text">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
import _ from 'lodash';
export default {
  name: "form_linear_scale",
  mixins: [form_field],
  methods: {
    linearRange: function (from, to) {
      let start = parseInt(from);
      let end = parseInt(to);
      return Array.from({ length: end - start + 1 }, (_, index) => start + index);
    },
  }
};
</script>


<style scoped lang="scss">


$border-color: #dcdfe6;
$text-muted: #909399;
$header-bg: #444;

.linear-scale-container {
  background: #fff;
  font-family: inherit;

  .scale-labels {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    color: $text-muted;
    font-size: 14px;
  }

  .scale-button-group {
    display: flex;
    border: 1px solid $border-color;
    border-radius: 4px;
    overflow: hidden;

    .scale-item {
      flex: 1;
      border-right: 1px solid $border-color;

      &:last-child {
        border-right: none;
      }
    }

    .scale-button {
      width: 100%;
      height: 45px;
      background: #fff;
      border: none;
      color: #303133;
      font-size: 15px;
      font-weight: 600;
      cursor: default;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  }

  .help-text {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: $text-muted;
    font-style: italic;
  }
}
</style>