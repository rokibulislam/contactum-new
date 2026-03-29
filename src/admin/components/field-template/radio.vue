<template>
  <div v-if="hasvalidate(field)">
    <label class="contactum-label">{{ field.title }}  </label>
    <ul class="list-inline">
      <li v-for="(option, key) in field.options">
        <el-radio :label="key" v-model="value"> {{ option }} </el-radio>
      </li>
    </ul>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_radio",
  mixins: [option_field],
  computed: {
    value: {
      get: function() {
        let property = this.field.name;
        return this.editfield[property];
      },
      set: function(value) {
        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: this.field.name,
          value: value
        });
      }
    }
  },
  methods: {
    hasvalidate( field ) {
        if( field.hasOwnProperty('show_if') ) {
            return this[field.show_if]();
        }

        return true;
    }
  }
};
</script>


<style>

.el-radio-button__inner {
    border-radius: 20px !important;
    border: 1px solid #dcdfe6 !important;
    box-shadow: none !important;
    padding: 8px 20px;
  }

  .list-inline li {
    margin-right: 30px;
  }
  
</style>