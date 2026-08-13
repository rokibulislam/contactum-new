<template>
  <div class="styler-color">
    <label v-if="label" class="styler-color__label">{{ label }}</label>
    <div class="styler-color__row">
      <el-color-picker :value="value" show-alpha size="small" @change="onChange" />
      <button v-if="value" type="button" class="styler-color__clear" @click="onChange('')">Clear</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StylerColor',
  props: {
    value: { type: String, default: '' },
    label: { type: String, default: '' },
  },
  methods: {
    onChange(val) {
      this.$emit('input', val || '');
    },
  },
};
</script>

<style scoped>
.styler-color__label {
  display: block;
  font-size: 12px;
  color: #606266;
  margin-bottom: 6px;
}
.styler-color__row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.styler-color__clear {
  background: none;
  border: none;
  color: #909399;
  font-size: 12px;
  cursor: pointer;
  padding: 0;
}
.styler-color__clear:hover {
  color: #f56c6c;
}
</style>
