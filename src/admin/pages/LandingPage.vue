<template>
  <div class="lp-page" v-loading="loading">
    <div class="lp-topbar">
      <label class="lp-topbar__enable">
        <el-switch v-model="isLandingPageEnabled" />
        {{ strings.enable_label }}
      </label>

      <div class="lp-topbar__actions">
        <a v-if="isLandingPageEnabled && landingUrl" :href="landingUrl" target="_blank" class="lp-topbar__open" title="Open landing page in a new tab">
          <i class="el-icon-full-screen"></i>
        </a>
      </div>
    </div>

    <div v-if="!isLandingPageEnabled" class="lp-locked">
      <i class="el-icon-info"></i>
      <span v-html="strings.locked_message"></span>
    </div>

    <div v-else class="lp-workspace">
      <div class="lp-controls">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="Design" name="design">
            <div v-for="field in designFields" :key="field.key" class="lp-field">
              <label class="lp-field__label">
                {{ field.label }}
                <el-tooltip v-if="field.tooltip" :content="field.tooltip" placement="top">
                  <i class="el-icon-info"></i>
                </el-tooltip>
              </label>

              <el-radio-group v-if="field.type === 'radio'" v-model="settings[field.key]" size="small">
                <el-radio-button v-for="opt in field.options" :key="opt.value" :label="opt.value">{{ opt.label }}</el-radio-button>
              </el-radio-group>

              <el-color-picker v-else-if="field.type === 'color'" v-model="settings[field.key]" />

              <ImagePicker v-else-if="field.type === 'image'" v-model="settings[field.key]" />

              <el-input
                v-else-if="field.type === 'text'"
                v-model="settings[field.key]"
                :placeholder="field.key === 'landing_page_title' ? formTitle : field.placeholder"
              />

              <wp_editor
                v-else-if="field.type === 'richtext'"
                :value="settings[field.key]"
                @content-changed="val => settings[field.key] = val"
              />
            </div>
          </el-tab-pane>

          <el-tab-pane label="Share" name="share">
            <div class="lp-field">
              <label class="lp-field__label">Landing Page URL</label>
              <div class="lp-share-url">
                <el-input :value="landingUrl" readonly />
                <el-button size="small" @click="copyUrl">Copy</el-button>
              </div>
            </div>

            <div v-for="field in shareFields" :key="field.key" class="lp-field">
              <label class="lp-field__label">
                {{ field.label }}
                <el-tooltip v-if="field.tooltip" :content="field.tooltip" placement="top">
                  <i class="el-icon-info"></i>
                </el-tooltip>
              </label>
              <el-input v-if="field.type === 'text'" v-model="settings[field.key]" :placeholder="field.placeholder" />
            </div>

            <div class="lp-field">
              <label class="lp-field__label">Shortcode</label>
              <el-input :value="shortcode" readonly />
            </div>
          </el-tab-pane>
        </el-tabs>

        <p class="lp-save-note">
          <i class="el-icon-info"></i>
          Saved together with the rest of the form — click <strong>Save Form</strong> above when you're done.
        </p>
      </div>

      <div class="lp-preview">
        <div class="lp-preview__head">
          <span><i class="el-icon-view"></i> Live Preview</span>
        </div>
        <div class="lp-preview__viewport">
          <div class="lp-mock" :class="'lp-mock--' + settings.landing_page_design_style" :style="mockWrapperStyle">
            <div class="lp-mock__card">
              <div class="lp-mock__header" v-if="settings.landing_page_logo || settings.landing_page_title || formTitle || settings.landing_page_description">
                <div class="lp-mock__logo" v-if="settings.landing_page_logo">
                  <img :src="settings.landing_page_logo" alt="">
                </div>
                <h1 class="lp-mock__title">{{ settings.landing_page_title || formTitle }}</h1>
                <div class="lp-mock__desc" v-if="settings.landing_page_description" v-html="settings.landing_page_description"></div>
              </div>
              <div class="lp-mock__body">
                <div v-for="field in formFields" :key="field.name" class="lp-mock__field">
                  <label class="lp-mock__field-label">
                    {{ field.label }}<span v-if="field.required" class="lp-mock__required">*</span>
                  </label>
                  <div class="lp-mock__field-input">{{ field.placeholder || '—' }}</div>
                </div>
                <p v-if="!formFields.length" class="lp-mock__empty">This form has no fields yet.</p>
                <div class="lp-mock__submit">Submit</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const cpm = window.contactum || {};

import wp_editor from '../components/common/wp-editor.vue';
import ImagePicker from '../components/pro/landing-page/ImagePicker.vue';

export default {
  name: 'LandingPage',
  components: { wp_editor, ImagePicker },
  props: {
    id: [String, Number],
    embedded: { type: Boolean, default: false },
  },

  data() {
    return {
      loading: true,
      formTitle: '',
      // Populated from PHP (LandingPage::get_ui_strings()) once load()
      // resolves — no client-side copy, so this plugin has no English
      // string of its own to fall out of sync with the PHP source.
      strings: {
        enable_label: 'Enable Landing Page',
        locked_message: '',
      },
      activeTab: 'design',
    };
  },

  computed: {
    // Landing page settings live inside the SAME shared form-settings
    // object every other tab (Settings/Quiz/Zapier) reads and writes —
    // LandingPage::extend_default_settings() merges landing_page_* keys
    // into contactum_get_default_form_settings() on the PHP side, so this
    // just works without a dedicated fetch/save round-trip. Saved together
    // with everything else on the main "Save Form" click.
    settings() {
      return this.$store.getters.settings;
    },

    // Saved as the string 'true'/'false' via jQuery-encoded requests, but
    // as a real boolean when this same object rides along in the builder's
    // JSON.stringify()'d Save Form payload — el-switch only lights up for a
    // strict boolean, so normalize on read and always write back a real one.
    isLandingPageEnabled: {
      get() {
        return this.settings.landing_page_status === true || this.settings.landing_page_status === 'true';
      },
      set(value) {
        this.$set(this.settings, 'landing_page_status', value);
      },
    },

    shortcode() {
      return '[contactum id="' + this.id + '"]';
    },

    // Matches LandingPage::get_landing_url() on the PHP side — a fixed,
    // predictable shape (home url + ?contactum_landing=<id>), so there's no
    // reason to round-trip to the server just to read it back. When a
    // Private Share Link token is set, LandingPage::render() only grants
    // access if `?form=<token>` is present (see unprefix_settings()'s
    // share_url_salt check) — appended here so the URL shown/copied above
    // that field is the one that actually works, matching its tooltip.
    landingUrl() {
      if (!this.id) return '';
      let url = window.location.origin + '/?contactum_landing=' + this.id;

      if (this.settings.landing_page_share_url_salt) {
        url += '&form=' + encodeURIComponent(this.settings.landing_page_share_url_salt);
      }

      return url;
    },

    // Field definitions (label/type/tooltip/options) come from PHP —
    // LandingPage::get_settings_fields(), localized into the same builder
    // bootstrap payload as `settings`/`field_settings` (see
    // LandingPage::localize_settings_fields()) rather than a dedicated
    // per-form AJAX call — the template below just renders whatever it's
    // given rather than hardcoding labels/field types itself.
    fieldSchema() {
      return this.$store.getters.landing_page_fields || [];
    },

    designFields() {
      return this.fieldSchema.filter(field => field.tab === 'design');
    },

    shareFields() {
      return this.fieldSchema.filter(field => field.tab === 'share');
    },

    // Read live from the same Vuex store the Editor tab edits — reflects
    // fields you've just added/renamed even before hitting Save, unlike a
    // fetch of the saved-in-the-database field list.
    formFields() {
      const fields = this.$store.getters.form_fields || [];

      return fields.map(field => ({
        name: field.name || field.template,
        label: field.label || '',
        required: field.required === 'yes' || field.required === true,
        placeholder: field.placeholder || '',
      }));
    },

    // Reactive, client-side-only mock of the page background — bound
    // directly from `settings`, so the preview updates the instant a
    // field changes, no server round-trip or iframe involved.
    mockWrapperStyle() {
      const style = { backgroundColor: this.settings.landing_page_custom_color || '#4286c4' };

      if (this.settings.landing_page_background_image) {
        style.backgroundImage = 'url(' + this.settings.landing_page_background_image + ')';
        style.backgroundSize = 'cover';
        style.backgroundPosition = 'center';
      }

      return style;
    },
  },

  mounted() {
    this.load();
  },

  methods: {
    // Only the piece that isn't already part of the shared settings/field
    // schema bootstrap or computable client-side: chrome text.
    load() {
      this.loading = true;
      $.post(cpm.ajaxurl, {
        action: 'contactum_get_landing_page_settings',
        _ajax_nonce: cpm.nonce,
      }, (res) => {
        this.loading = false;
        if (!res.success) {
          console.error('contactum_get_landing_page_settings failed', res);
          return;
        }

        this.formTitle = (cpm.post && cpm.post.post_title) || '';
        this.strings = res.data.strings || this.strings;
      }).fail((jqXHR, textStatus, errorThrown) => {
        this.loading = false;
        console.error('contactum_get_landing_page_settings request failed', textStatus, errorThrown, jqXHR.responseText);
      });
    },

    copyUrl() {
      if (!this.landingUrl) return;

      // navigator.clipboard needs a secure context (HTTPS/localhost) and can
      // still reject on permission — fall back to the classic textarea +
      // execCommand trick so the button also works on plain-HTTP local dev
      // and older browsers, and always gives feedback either way.
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(this.landingUrl).then(() => {
          this.$message.success('Landing page URL copied.');
        }).catch(() => {
          this.$message.error('Could not copy the URL — please copy it manually.');
        });
        return;
      }

      const textarea = document.createElement('textarea');
      textarea.value = this.landingUrl;
      textarea.style.position = 'fixed';
      textarea.style.opacity = '0';
      document.body.appendChild(textarea);
      textarea.select();

      try {
        document.execCommand('copy');
        this.$message.success('Landing page URL copied.');
      } catch (e) {
        this.$message.error('Could not copy the URL — please copy it manually.');
      }

      document.body.removeChild(textarea);
    },
  },
};
</script>

<style scoped>
.lp-page {
  padding: 20px 24px;
  min-height: 70vh;
}
.lp-topbar {
  display: flex;
  align-items: center;
  gap: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #ebeef5;
  margin-bottom: 20px;
}
.lp-topbar__enable {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #606266;
  cursor: pointer;
}
.lp-topbar__actions {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 14px;
}
.lp-topbar__open {
  font-size: 16px;
  color: #409eff;
}

.lp-locked {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f4f4f5;
  border-radius: 8px;
  padding: 16px 20px;
  font-size: 13.5px;
  color: #606266;
}
.lp-locked i {
  color: #909399;
  font-size: 16px;
}

.lp-workspace {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}
.lp-controls {
  flex: 1;
  min-width: 0;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 8px 20px 20px;
}
.lp-save-note {
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 4px 0 12px;
  font-size: 12px;
  color: #909399;
}
.lp-preview {
  position: sticky;
  top: 20px;
  width: 420px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  height: calc(100vh - 160px);
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  overflow: hidden;
}
.lp-preview__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  border-bottom: 1px solid #ebeef5;
  font-size: 13px;
  color: #606266;
  flex-shrink: 0;
}
.lp-preview__viewport {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background: #f4f4f5;
}

/* ── Reactive mock — mirrors the real landing page's classes/structure at
   preview scale, driven directly by `settings` (see mockWrapperStyle and
   the template above) — no server round-trip or iframe. ── */
.lp-mock {
  min-height: 100%;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
  box-sizing: border-box;
}
.lp-mock__card {
  width: 100%;
}
.lp-mock--modern .lp-mock__card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 14px 24px rgba(0, 0, 0, 0.16);
  padding: 22px 20px;
}
.lp-mock--classic .lp-mock__card {
  background: transparent;
  padding: 10px 0;
}
.lp-mock__header {
  text-align: center;
  margin-bottom: 16px;
}
.lp-mock__logo img {
  max-height: 32px;
  max-width: 140px;
  margin-bottom: 8px;
}
.lp-mock__title {
  margin: 0 0 6px;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}
.lp-mock__desc {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.5;
}
.lp-mock__field {
  margin-bottom: 10px;
}
.lp-mock__field-label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 4px;
}
.lp-mock__required {
  color: #f56c6c;
  margin-left: 2px;
}
.lp-mock__field-input {
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 7px 10px;
  font-size: 11px;
  color: #c0c4cc;
  background: #fff;
}
.lp-mock__empty {
  font-size: 12px;
  color: #9ca3af;
  text-align: center;
  margin: 10px 0;
}
.lp-mock__submit {
  margin-top: 14px;
  display: inline-block;
  background: var(--primary, #409eff);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  padding: 8px 18px;
  border-radius: 4px;
}
@media (max-width: 1200px) {
  .lp-workspace {
    flex-direction: column;
  }
  .lp-preview {
    position: static;
    width: 100%;
    height: 60vh;
  }
}

.lp-field {
  margin-bottom: 18px;
}
.lp-field__label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #606266;
  margin-bottom: 8px;
}
.lp-share-url {
  display: flex;
  gap: 8px;
}
.lp-share-url .el-input {
  flex: 1;
}
</style>
