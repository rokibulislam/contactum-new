<template>
  
  <div class="price-field">


      <div class="price-header">
        <el-checkbox 
          v-model="price.is_flexible"
          active-text="Flexible"
          inactive-text="Fixed"
        ></el-checkbox>
        <label class="contactum-label"> {{ ' Use flexible price' }} </label>
      </div>

      <template v-if="price.is_flexible">
        
        <div class="price-range">
          
          <div class="price-input-item">
            
            <label class="contactum-label">  {{ 'Minimum Price' }} </label>

            <el-input-number
              v-model="price.min"
              :min="0"
              placeholder="Min"
              controls-position="right"
              style="width: 100%"
            />

          </div>

          <div class="price-input-item">
        
            <label class="contactum-label"> {{ 'Maximum Price' }} </label>

            <el-input-number
              v-model="price.max"
              :min="price.min || 0"
              placeholder="Max"
              controls-position="right"
              style="width: 100%"
            />

          </div>
        
        </div>

      </template>

      <template v-else>
        <div class="panel">
        <label class="contactum-label">Price</label>
          <el-input-number
            v-model="price.price"
            :min="0"
            controls-position="right"
            placeholder="Enter price"
            style="width: 100%"
          />
        </div>
      </template>


  </div>

</template>

<script>

import option_field from "../../../mixin/option-field.js";

export default {
  name: "field_price",
  mixins: [option_field],
  data: function() {
    return {
      price: {
        min: "",
        max: "",
        price: "",
        is_flexible: false
      }
    };
  },
  
  computed: {
      price: function price() {
          return this.editfield.price;
      }
  },
  created: function created() {
      this.price = jQuery.extend(false, this.price, this.editfield.price);
  },
  watch: {
      price: function price() {
        this.$store.dispatch("update_editing_form_field", {
            id: this.editfield.id,
            property: "price",
            value: this.price
        });
      }
  }
};
</script>



<style>

.price-header {
  display:flex;
}

.price-header .el-checkbox {
    margin-right: 10px !important;
}

.price-input-item {
  margin-bottom: 20px;
}

</style>