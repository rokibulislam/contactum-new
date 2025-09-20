<template>
  <div class="contactum-fields">
      <ul :class="['contactum-fields-list', ('yes' === field.inline) ? 'contactum-list-inline' : '']">

          <li  v-for="option in field.options">

            <template v-if="field.select_type == 'radio'">
              <input type="radio" :checked="is_selected(val)"> {{ val }} - {{ label }}
            </template>

            <template v-if="field.select_type == 'checkbox'">
              <input type="checkbox" :checked="is_selected(val)"> {{ val }} - {{ label }}
            </template>

            <label>
              <img v-if="option.photo != ''" :src="option.photo" width="200px" />
              <span v-else-if="option.photo == ''">{{ option.name }} {{ option.price }}</span>
            </label>
          </li>
      </ul>

      <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_multiple_product",
  mixins: [form_field],
};
</script>
