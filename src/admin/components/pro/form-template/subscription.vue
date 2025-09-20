<template>
  <div>
    <h2> Subscription Field </h2>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";
import Subscription from "admin/components/pro/field-template/subscription.vue";
export default {
  name: "form_subscription_field",
  components: {Subscription},
  mixins: [form_field],
  computed: {}
}
</script>

<style scoped>

</style>