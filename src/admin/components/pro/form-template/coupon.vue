<template>
  <div>
    <el-input
        type="text"
        :placeholder="field.placeholder"
        :value="field.default"
        :size="field.size"
        disabled
    >
    </el-input>
    <!-- <input type="tel" :placeholder="field.placeholder" :value="field.default" :size="field.size" /> -->
    <span v-if="field.help">{{ field.help }}</span>
  </div>
</template>


<script>
import form_field from "../../../mixin/form-field.js";
export default {
  name: "form_coupon_field",
  mixins: [form_field],
  computed: {}
}
</script>

<style scoped>

</style>