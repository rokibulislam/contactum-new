<template>
	<div class="contactum-fields">
		<div class="form-captcha-img-wrapper"> 
			<img :src=field.image /> 
		</div>
	</div>
</template>

<style>
	.form-captcha-img-wrapper {
		max-height: 60px;
    max-width: 222px;
	}

	.form-captcha-img-wrapper img {
		max-width: 100%
	}
</style>

<script>
import form_field from "../../mixin/form-field.js";
export default {
  name: "form_hcaptcha_field",
  mixins: [form_field]
};
</script>
