<template>
  <div class="styler-shadow">
    <label class="styler-shadow__label">Box Shadow</label>

    <div class="styler-shadow__grid">
      <el-input-number size="small" :value="model.x" controls-position="right" placeholder="X" @input="v => set('x', v)" />
      <el-input-number size="small" :value="model.y" controls-position="right" placeholder="Y" @input="v => set('y', v)" />
      <el-input-number size="small" :value="model.blur" :min="0" controls-position="right" placeholder="Blur" @input="v => set('blur', v)" />
      <el-input-number size="small" :value="model.spread" controls-position="right" placeholder="Spread" @input="v => set('spread', v)" />
    </div>

    <div class="styler-shadow__row">
      <StylerColor :value="model.color" @input="v => set('color', v)" />
      <label class="styler-shadow__inset">
        <el-checkbox :value="model.inset" @input="v => set('inset', v)" /> Inset
      </label>
    </div>
  </div>
</template>

<script>
import StylerColor from './StylerColor.vue';

export default {
  name: 'StylerBoxShadow',
  components: { StylerColor },
  props: {
    value: { type: Object, default: () => ({}) },
  },
  computed: {
    model() {
      return Object.assign( { x: 0, y: 0, blur: 0, spread: 0, color: '', inset: false }, this.value );
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
.styler-shadow { margin-bottom: 14px; }
.styler-shadow__label {
  display: block;
  font-size: 12px;
  color: #606266;
  margin-bottom: 6px;
}
.styler-shadow__grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
  margin-bottom: 8px;
}
.styler-shadow__grid ::v-deep .el-input-number {
  width: 100%;
}
.styler-shadow__row {
  display: flex;
  align-items: center;
  gap: 16px;
}
.styler-shadow__inset {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: #606266;
  cursor: pointer;
}
</style>
