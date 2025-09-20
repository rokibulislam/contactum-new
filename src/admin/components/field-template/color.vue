<template>
    <div class="panel-field">
        <label>{{ field.title }}</label>
        <el-color-picker v-model="value"></el-color-picker>
    </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
    name: "field_color",
    mixins: [option_field]
}
</script>