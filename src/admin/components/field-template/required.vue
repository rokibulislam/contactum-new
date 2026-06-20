<template>
    <div v-if="hasvalidate(field)">
      <label class="contactum-label">{{ field.title }}</label>
      <ul class="list-inline">
        <li v-for="(option, key) in field.options">
            <el-radio :label="key" v-model="value"> {{  option }} </el-radio>
        </li>
      </ul>
      <div v-if=" value === 'yes' "> 
        <label class="contactum-label"> Error Message: </label>
        <el-input v-model="editfield.message"></el-input>
      </div>
    </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_required",
  mixins: [option_field],
  computed: {
    message: {
      get: function() {
        return this.editfield;
      },
      set: function(value) {
        
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

<style lang="css" scoped>

.list-inline li {
    margin-right: 30px;
}

</style>