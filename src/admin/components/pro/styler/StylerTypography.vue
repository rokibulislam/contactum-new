<template>
  <div class="styler-typo">
    <label v-if="label" class="styler-typo__label">{{ label }}</label>

    <div class="styler-typo__grid">
      <el-input-number size="small" :value="model.font_size" :min="0" controls-position="right" placeholder="Size (px)" @input="v => set('font_size', v)" />

      <el-select size="small" :value="model.font_weight" placeholder="Weight" clearable @input="v => set('font_weight', v)">
        <el-option v-for="w in weights" :key="w" :label="w" :value="w" />
      </el-select>

      <el-select size="small" :value="model.font_style" placeholder="Style" clearable @input="v => set('font_style', v)">
        <el-option label="Normal" value="normal" />
        <el-option label="Italic" value="italic" />
      </el-select>

      <el-select size="small" :value="model.text_transform" placeholder="Transform" clearable @input="v => set('text_transform', v)">
        <el-option label="None" value="none" />
        <el-option label="Uppercase" value="uppercase" />
        <el-option label="Lowercase" value="lowercase" />
        <el-option label="Capitalize" value="capitalize" />
      </el-select>

      <el-select size="small" :value="model.text_decoration" placeholder="Decoration" clearable @input="v => set('text_decoration', v)">
        <el-option label="None" value="none" />
        <el-option label="Underline" value="underline" />
        <el-option label="Line-through" value="line-through" />
      </el-select>

      <el-input-number size="small" :value="model.line_height" :min="0" :step="0.1" controls-position="right" placeholder="Line height" @input="v => set('line_height', v)" />

      <el-input-number size="small" :value="model.letter_spacing" controls-position="right" placeholder="Letter spacing" @input="v => set('letter_spacing', v)" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'StylerTypography',
  props: {
    value: { type: Object, default: () => ({}) },
    label: { type: String, default: '' },
  },
  data() {
    return {
      weights: ['300', '400', '500', '600', '700', '800'],
    };
  },
  computed: {
    model() {
      return Object.assign(
        { font_size: '', font_weight: '', font_style: '', text_transform: '', text_decoration: '', line_height: '', letter_spacing: '' },
        this.value
      );
    },
  },
  methods: {
    set(key, val) {
      this.$emit('input', Object.assign({}, this.model, { [key]: val }));
    },
  },
};
</script>

<style scoped>
.styler-typo { margin-bottom: 14px; }
.styler-typo__label {
  display: block;
  font-size: 12px;
  color: #606266;
  margin-bottom: 6px;
}
.styler-typo__grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}
.styler-typo__grid ::v-deep .el-input-number,
.styler-typo__grid ::v-deep .el-select {
  width: 100%;
}
</style>
