<template>
    <div id="custom_js_editor" style="height: 400px; width: 10"></div>
</template>

<script>
    import ace from "ace-builds";
    import "ace-builds/src-noconflict/mode-javascript";
    import "ace-builds/src-noconflict/theme-monokai";

export default {
        name: "AceJSEditor",
        props: ['value', 'mode'],
        data() {
          return {
            editor: null,
          };
        },
        mounted() {
            this.editor = ace.edit("custom_js_editor");
            this.editor.setTheme("ace/theme/monokai");
            this.editor.session.setMode("ace/mode/javascript");
            this.editor.setValue(this.value);

            // Listen for changes
            this.editor.getSession().on("change", () => {
              this.$emit('input', this.editor.getSession().getValue());
            });
        },
        beforeDestroy() {
            if (this.editor) {
              this.editor.destroy();
              this.editor.container.remove();
            }
        }
};
</script>
