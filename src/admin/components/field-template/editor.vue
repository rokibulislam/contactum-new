<template>
  <div>
    <label class="contactum-label"> {{ field.title }} test </label>
    <wp_editor :value="value" @content-changed="onEditorContentChange" />
<!--   <textarea-->
<!--        class="wp_vue_editor"-->
<!--        :id="editor_id"-->
<!--        v-model="value"-->
<!--    ></textarea>-->
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
import wp_editor from '../common/wp-editor.vue'
export default {
  name: "field_editor",
  mixins: [option_field],
  components: {
    wp_editor
  },
  /*
data() {
  return {
    showButtonDesigner: false,
    buttonInitiated: false,
    hasWpEditor: true,
    hasMedia: false,
  };
},

props: {
  editor_id: {
    type: String,
    default() {
      return "wp_editor_" + Date.now() + parseInt(Math.random() * 1000);
    },
  },
},
*/

  computed: {
    value: {
      get: function () {
        let property = this.field.name;
        return this.editfield[property];
      }
    },
  },
  methods: {
    onEditorContentChange( content ) {
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: this.field.name,
        value: content,
      });
    }
  }
  /*
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
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: this.field.name,
        value: content,
      });
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
   */
};
</script>


