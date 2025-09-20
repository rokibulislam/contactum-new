<template>
  <div>
    <label class="contactum-label">{{ 'Scale Range' }}</label>

    <div class="panel-field">
      <div class="name-field-placeholder">
        <label class="contactum-label">{{ 'From' }}</label>
        <el-input-number v-model="editfield.scale_range.from" controls-position="right" min="1" />
      </div>

      <div class="name-field-value">
        <label class="contactum-label">{{ 'To' }}</label>
        <el-input-number v-model="editfield.scale_range.to" controls-position="right" min="editfield.scale_range.from" />
      </div>
    </div>

    <label class="contactum-label">{{ 'Scale Text' }}</label>

    <div class="panel-field-opt panel-field-name clearfix panel-field">

      <div class="name-field-placeholder">
        <label class="contactum-label">{{ 'First Text' }}</label>
        <el-input v-model="editfield.scale_text.first" />
      </div>

      <div class="name-field-value">
        <label class="contactum-label">{{ 'Last Text' }}</label>
        <el-input v-model="editfield.scale_text.last" />
      </div>
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

<style scoped>

.panel-field {
  display: flex;
  justify-content: space-between;
}

</style>