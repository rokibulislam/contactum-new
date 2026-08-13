<template>
  <div class="styler-border">
    <div class="styler-border__head">
      <label class="styler-border__label">{{ label }}</label>
      <el-switch :value="model.status" size="small" @input="v => set('status', v)" />
    </div>

    <template v-if="model.status">
      <div class="styler-border__row">
        <el-select size="small" :value="model.type" placeholder="Style" @input="v => set('type', v)">
          <el-option label="Solid" value="solid" />
          <el-option label="Dashed" value="dashed" />
          <el-option label="Dotted" value="dotted" />
        </el-select>
        <StylerColor :value="model.color" @input="v => set('color', v)" />
      </div>

      <StylerDimension label="Width" :value="model.width" @input="v => set('width', v)" />
      <StylerDimension label="Radius" :value="model.radius" @input="v => set('radius', v)" />
    </template>
  </div>
</template>

<script>
import StylerColor from './StylerColor.vue';
import StylerDimension from './StylerDimension.vue';

export default {
  name: 'StylerBorder',
  components: { StylerColor, StylerDimension },
  props: {
    value: { type: Object, default: () => ({}) },
    label: { type: String, default: 'Border' },
  },
  computed: {
    model() {
      return Object.assign( { status: false, type: 'solid', color: '', width: {}, radius: {} }, this.value );
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
.styler-border { margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px dashed #ebeef5; }
.styler-border__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.styler-border__label {
  font-size: 12.5px;
  font-weight: 600;
  color: #303133;
}
.styler-border__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  align-items: end;
  margin-bottom: 10px;
}
.styler-border__row ::v-deep .el-select {
  width: 100%;
}
</style>
