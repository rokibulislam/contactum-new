<template>
  <div>
      <div class="input-item">
        <label class="contactum-label">{{ 'Scale From' }}</label>
        <el-input-number class="full-width" v-model="editfield.scale_range.from" controls-position="right" min="1" />
      </div>

      <div class="input-item">
        <label class="contactum-label">{{ 'Scale To' }}</label>
        <el-input-number class="full-width" v-model="editfield.scale_range.to" controls-position="right" min="editfield.scale_range.from" />
      </div>

      <div class="input-item">
        <label class="contactum-label">{{ 'Scale First Text' }}</label>
        <el-input v-model="editfield.scale_text.first" />
      </div>

      <div class="input-item">
        <label class="contactum-label">{{ 'Scale Last Text' }}</label>
        <el-input v-model="editfield.scale_text.last" />
      </div>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
export default {
  name: "field_linear",
  mixins: [option_field],
  props: {
    field: {
      type: Object,
      default: {}
    }
  },
  methods: {
    onfocusout: function(e) {
      this.$eventHub.$emit("field-text-focusout", this.field, this.value);
    },
    onkeyup: function(e) {
      this.$eventHub.$emit("field-text-keyup", this.field, this.value);
    }
  }
};
</script>

<style lang="css" scoped>

.input-item {
  margin-bottom: 20px;
}


/* Target the component wrapper */
.full-width {
  width: 100% !important;
}

/* Ensure the internal input container also takes full width */
.full-width :deep(.el-input) {
  width: 100%;
}

/* Optional: Align text to the left if preferred when wide */
.full-width :deep(.el-input__inner) {
  text-align: left;
  padding-left: 15px;
}

</style>