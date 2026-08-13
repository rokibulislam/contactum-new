<template>
  <div class="styler-box-group">
    <StylerColor v-if="withBackground" label="Background" :value="model.background" @input="v => set('background', v)" />
    <StylerColor v-if="withText" label="Text Color" :value="model.color" @input="v => set('color', v)" />
    <StylerTypography v-if="withText" label="Typography" :value="model.typography" @input="v => set('typography', v)" />
    <StylerDimension v-if="withSpacing" label="Padding" :value="model.padding" @input="v => set('padding', v)" />
    <StylerDimension v-if="withSpacing" label="Margin" :value="model.margin" @input="v => set('margin', v)" />
    <StylerBorder v-if="withBorder" :value="model.border" @input="v => set('border', v)" />
    <StylerBoxShadow v-if="withShadow" :value="model.boxshadow" @input="v => set('boxshadow', v)" />
  </div>
</template>

<script>
import StylerColor from './StylerColor.vue';
import StylerDimension from './StylerDimension.vue';
import StylerTypography from './StylerTypography.vue';
import StylerBorder from './StylerBorder.vue';
import StylerBoxShadow from './StylerBoxShadow.vue';

export default {
  name: 'StylerBoxGroup',
  components: { StylerColor, StylerDimension, StylerTypography, StylerBorder, StylerBoxShadow },
  props: {
    value: { type: Object, default: () => ({}) },
    withBackground: { type: Boolean, default: true },
    withText: { type: Boolean, default: false },
    withSpacing: { type: Boolean, default: true },
    withBorder: { type: Boolean, default: true },
    withShadow: { type: Boolean, default: true },
  },
  computed: {
    model() {
      return this.value || {};
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
.styler-box-group { padding: 4px 0; }
</style>
