<template>
     <textarea
         class="wp_vue_editor"
         :id="editor_id"
         v-model="value"
     ></textarea>
</template>


<script>

export default {
  name: "wp_editor",
  data() {
    return {
      showButtonDesigner: false,
      buttonInitiated: false,
      hasWpEditor: true,
      hasMedia: false,
    };
  },
  props: {
    value :  { type: String },
    editor_id: {
      type: String,
      default() {
          return "wp_editor_" + Date.now() + parseInt(Math.random() * 1000);
      }
    }
  },

  mounted() {
    this.initEditor();
  },

  methods: {
    initEditor() {
      wp.editor.remove(this.editor_id);
      const that = this;
      wp.editor.initialize(this.editor_id, {
        mediaButtons: that.hasMedia,
        tinymce: {
          height: that.height,
          toolbar1:
              "formatselect,customInsertButton,table,bold,italic,bullist,numlist,link,blockquote,alignleft,aligncenter,alignright,underline,strikethrough,forecolor,removeformat,codeformat,outdent,indent,undo,redo",
          setup(ed) {
            ed.on("change", function (ed, l) {
              that.changeContentEvent();
            });
          },
        },
        quicktags: true,
      });

      jQuery("#" + this.editor_id).on("change", function (e) {
        that.changeContentEvent();
      });
    },

    changeContentEvent() {
      let content = wp.editor.getContent(this.editor_id);

      this.$emit("content-changed", content);
    },

    handleCommand(command) {
      if (this.hasWpEditor) {
        tinymce.activeEditor.insertContent(command);
      } else {
        var part1 = this.plain_content.slice(0, this.cursorPos);
        var part2 = this.plain_content.slice(
            this.cursorPos,
            this.plain_content.length
        );
        this.plain_content = part1 + command + part2;
        this.cursorPos += command.length;
      }
    },
    showInsertButtonModal(editor) {
      this.currentEditor = editor;
      this.showButtonDesigner = true;
    },
    insertHtml(content) {
      this.currentEditor.insertContent(content);
    },
    customSanitize(input) {
      // Remove potential event handlers
      let sanitized = input.replace(
          /\s*on\w+\s*=\s*("[^"]*"|'[^']*'|[^"'\s>]+)/gi,
          ""
      );
      // Remove http-equiv attributes
      sanitized = sanitized.replace(
          /\s*http-equiv\s*=\s*("[^"]*"|'[^']*'|[^"'\s>]+)/gi,
          ""
      );
      return sanitized;
    },
  },
}
</script>

<style scoped lang="scss">
.wp_vue_editor {
  width: 100%;
  min-height: 100px;
}

.wp_vue_editor_wrapper {
  position: relative;
  .popover-wrapper {
    z-index: 2;
    position: absolute;
    top: 0;
    right: 0;
    &-plaintext {
      left: auto;
      right: 0;
      top: -32px;
    }
  }
  .wp-editor-tabs {
    float: left;
  }
}
</style>