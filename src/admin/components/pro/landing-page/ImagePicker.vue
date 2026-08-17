<template>
  <div class="ctm-image-picker">
    <div v-if="value" class="ctm-image-picker__preview">
      <img :src="value" alt="" />
      <button type="button" class="ctm-image-picker__remove" title="Remove" @click="$emit('input', '')">
        <i class="el-icon-close"></i>
      </button>
    </div>
    <el-button size="small" @click="openMediaLibrary">
      <i class="el-icon-upload"></i> {{ value ? 'Replace Image' : 'Select Image' }}
    </el-button>
  </div>
</template>

<script>
// Same technique as components/field-template/photoUploader.vue: use
// wp.media.editor — WordPress core's own already-managed singleton — rather
// than building a wp.media({...}) frame by hand. Constructing more than one
// hand-built frame on the same page trips a WP core Backbone-view bug
// ("this.activateMode is not a function"); wp.media.editor sidesteps that
// entirely since core itself owns the single frame instance.
export default {
  name: 'ImagePicker',
  props: {
    value: { type: String, default: '' },
  },
  mounted() {
    // Matches mixin/media.js's guard — wp.media.editor.open() expects
    // window.wpActiveEditor to exist even when no real TinyMCE editor is
    // present on the page.
    if (!window.wpActiveEditor) {
      window.wpActiveEditor = null;
    }
  },
  methods: {

    isLodash: function () {
        let isLodash = false;

        // If _ is defined and the function _.forEach exists then we know underscore OR lodash are in place
        if ('undefined' != typeof (_) && 'function' == typeof (_.forEach)) {

            // A small sample of some of the functions that exist in lodash but not underscore
            const funcs = ['get', 'set', 'at', 'cloneDeep'];

            // Simplest if assume exists to start
            isLodash = true;

            funcs.forEach(function (func) {
                // If just one of the functions do not exist, then not lodash
                isLodash = ('function' != typeof (_[func])) ? false : isLodash;
            });
        }

        if (isLodash) {
            // We know that lodash is loaded in the _ variable
            return true;
        } else {
            // We know that lodash is NOT loaded
            return false;
        }
    },

    openMediaLibrary(e) {
      e.preventDefault();

      if (this.isLodash()) {
          _.noConflict();
      }

      if (typeof wp === 'undefined' || !wp.media || !wp.media.editor) return;

      const sendAttachmentBackup = wp.media.editor.send.attachment;

      wp.media.editor.send.attachment = (props, attachment) => {
        this.$emit('input', attachment.url);
        wp.media.editor.send.attachment = sendAttachmentBackup;
      };

      wp.media.editor.open();
      
    

      return false;
    },
  },
};
</script>

<style scoped>
.ctm-image-picker {
  display: flex;
  align-items: center;
  gap: 10px;
}
.ctm-image-picker__preview {
  position: relative;
  width: 56px;
  height: 56px;
  border-radius: 6px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  flex-shrink: 0;
}
.ctm-image-picker__preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.ctm-image-picker__remove {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: none;
  background: rgba(0, 0, 0, 0.55);
  color: #fff;
  font-size: 11px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ctm-image-picker__remove:hover {
  background: rgba(0, 0, 0, 0.8);
}
</style>
