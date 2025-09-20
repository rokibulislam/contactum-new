<template>
  <div>
  <div v-html="sanitizedHtml"></div>
  </div>
</template>
<script>
import DOMPurify from "dompurify";

export default {
  name: "form_html_field",
  props: {
    field: {
      type: Object,
      default() {
        return {};
      },
    },
  },
  data() {
    return {
      sanitizedHtml: "",
    };
  },
  watch: {
    "field.html": {
      immediate: true,
      handler(newVal) {
        this.sanitizedHtml = DOMPurify.sanitize(newVal || "");
      },
    },
  },
};
</script>
