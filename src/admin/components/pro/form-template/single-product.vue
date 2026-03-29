<template>
  <div>
    <template v-if="field.price.is_flexible">
      <div class="contactum-single-product-price-warp">
        <input type="text" :value="field.price.min ? field.price.min : 0">
      </div>
	  </template>
    
    <template v-else>
        <template v-if = "field.label == 'Donation Amount'">
            <label>Amount:</label>
        </template>

        <template v-else>
            <label>Price:</label>
        </template>
        
        {{ field.price.price ? field.price.price : 0 }}
      </template>


	<div v-if="field.quantity.status">
		<label> Quantity: </label>
    <input
      type="text"
      :class="class_names('textfield contactum-product-qty')"
      placeholder="1"
      :value="field.quantity.min"
      style="width: 50px; float: none !important"
    >
	</div>
    <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_single_product",
  mixins: [form_field]
};
</script>
