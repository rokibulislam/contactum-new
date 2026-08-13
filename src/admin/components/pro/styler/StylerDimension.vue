<template>
  <div class="styler-dim">
    <div class="styler-dim__head">
      <label class="styler-dim__label">{{ label }}</label>
      <button
        type="button"
        class="styler-dim__link"
        :class="{ 'is-active': model.linked }"
        title="Link all sides"
        @click="toggleLinked"
      >
        <i class="el-icon-link"></i>
      </button>
    </div>

    <div class="styler-dim__inputs" v-if="model.linked">
      <el-input-number
        size="small"
        :value="model.top"
        :min="0"
        controls-position="right"
        placeholder="All sides"
        @input="setAll"
      />
    </div>

    <div class="styler-dim__grid" v-else>
      <el-input-number size="small" :value="model.top" :min="0" controls-position="right" placeholder="Top" @input="v => set('top', v)" />
      <el-input-number size="small" :value="model.right" :min="0" controls-position="right" placeholder="Right" @input="v => set('right', v)" />
      <el-input-number size="small" :value="model.bottom" :min="0" controls-position="right" placeholder="Bottom" @input="v => set('bottom', v)" />
      <el-input-number size="small" :value="model.left" :min="0" controls-position="right" placeholder="Left" @input="v => set('left', v)" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'StylerDimension',
  props: {
    value: { type: Object, default: () => ({}) },
    label: { type: String, default: '' },
  },
  computed: {
    model() {
      return Object.assign( { top: '', right: '', bottom: '', left: '', linked: true }, this.value );
    },
  },
  methods: {
    emit(next) {
      this.$emit('input', Object.assign({}, this.model, next));
    },
    toggleLinked() {
      this.emit({ linked: !this.model.linked });
    },
    set(side, val) {
      this.emit({ [side]: val });
    },
    setAll(val) {
      this.emit({ top: val, right: val, bottom: val, left: val });
    },
  },
};
</script>

<style scoped>
.styler-dim { margin-bottom: 14px; }
.styler-dim__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}
.styler-dim__label {
  font-size: 12px;
  color: #606266;
}
.styler-dim__link {
  background: #f4f4f5;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #909399;
  font-size: 11px;
}
.styler-dim__link.is-active {
  background: #ecf5ff;
  border-color: #b3d8ff;
  color: #409eff;
}
.styler-dim__grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}
.styler-dim__inputs ::v-deep .el-input-number,
.styler-dim__grid ::v-deep .el-input-number {
  width: 100%;
}
</style>
