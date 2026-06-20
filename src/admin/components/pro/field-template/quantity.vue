<template>
  <div>
    
    <div class="quantity-header">
      <el-checkbox v-model="quantity.status" />
      <label class="contactum-label"> {{ 'Use Quantity' }} </label>
    </div>

    <div v-if="quantity.status">
      
      <div class="quantity-input-item">
        <label class="contactum-label"> {{ 'Minimum Quantity' }} </label>
        <el-input-number v-model="quantity.min"
            :min="0"
            placeholder="Max"
            controls-position="right"
            style="width: 100%"
        />
      </div>

      <div class="quantity-input-item">

        <label class="contactum-label"> {{ 'Maximum Quantity' }} </label>
        <el-input-number v-model="quantity.max"
            :min="quantity.min || 0"
            placeholder="Max"
            controls-position="right"
            style="width: 100%"
        />

      </div>

    </div>
  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
export default {
  name: "field_quantity",
  mixins: [option_field],
  data: function() {
    return {
      quantity: {
        status: false,
        min: 0,
        max: ""
      }
    };
  },

  computed: {
      quantity: function quantity() {
          return this.editfield.quantity;
      }
  },

  created: function created() {
      this.quantity = jQuery.extend(false, this.quantity, this.editfield.quantity);
  },

  watch: {
      quantity: function quantity() {
        this.$store.dispatch("update_editing_form_field", {
            id: this.editfield.id,
            property: "quantity",
            value: this.quantity
        });
      }
  }

};
</script>


<style>


.quantity-header .el-checkbox {
    margin-right: 10px !important;
}

.quantity-header {
  display:flex;
}

.quantity-input-item {
  margin-bottom: 20px;
}
</style>