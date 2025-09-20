<template>
    <div class="contactum-fields">
      <div class="contactum-column-field-inner-columns">
        <ul class="contactum-column">
          <li v-for="(innerfield, index) in field.inner_fields" :key="field.id">
        <span 
          v-if="innerfield.field_properties.required && true === innerfield.field_properties.required" 
          class="required"
        > *</span>
        <label>{{  innerfield.field_properties.title }}  </label>
        <component :is="innerfield.fields" :field="innerfield.field_properties"></component>
          </li>
          </ul>
      </div>


        <!-- <h2> {{ field.label }}</h2> -->
        <span v-if="field.help" class="help" v-html="field.help"></span>
<!--        <span -->
<!--          v-if="field.field_properties.required && true === field.field_properties.required" -->
<!--          class="required"-->
<!--        > *</span>-->
<!--        <label>{{  field.field_properties.title }}  </label>-->
<!--        <component :is="field.fields" :field="field.field_properties"></component>-->
    </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
import form_text_field from "../../form-templates/text.vue";
import form_email_address from '../../form-templates/email.vue';
import form_dropdown_field from "../../form-templates/dropdown.vue";
import form_number_field from '../../form-templates/number.vue';

export default {
  name: "form_rating_field",
  mixins: [form_field],
  components: { 
    form_text_field,
    form_dropdown_field,
    form_number_field,
    form_email_address
  },
};
</script>



<style>

.contactum-column {
    display: flex;
    gap: 15px
}


</style>