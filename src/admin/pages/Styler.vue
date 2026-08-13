<template>
  <div class="styler-page" v-loading="loading">
    <div class="styler-topbar">
      <el-button icon="el-icon-arrow-left" size="small" @click="goBack">Back to Builder</el-button>

      <div class="styler-topbar__preset">
        <label>Style</label>
        <el-select size="small" :value="selectedStyle" @input="onSelectStyle" style="width: 220px">
          <el-option label="Default (no custom styling)" value="" />
          <el-option v-for="(preset, key) in presets" :key="key" :label="preset.label" :value="key" />
          <el-option label="Custom (Advanced Customization)" value="ctm_custom" />
        </el-select>
      </div>

      <div class="styler-topbar__actions">
        <a v-if="previewUrl" :href="previewUrl" target="_blank" class="styler-topbar__preview" title="Open preview in a new tab">
          <i class="el-icon-full-screen"></i>
        </a>
        <el-button type="primary" size="small" :loading="saving" @click="save">Save Styles</el-button>
      </div>
    </div>

    <div class="styler-workspace">
      <div class="styler-controls">
        <div v-if="selectedStyle !== 'ctm_custom'" class="styler-locked">
          <i class="el-icon-info"></i>
          <span v-if="!selectedStyle">Using default styling. Choose <strong>Custom</strong> above to fine-tune every part of the form, or pick a preset — the preview on the right updates either way.</span>
          <span v-else>Using the <strong>{{ (presets[selectedStyle] || {}).label }}</strong> preset. Choose <strong>Custom</strong> above to fine-tune every part of the form yourself.</span>
        </div>

        <div v-else class="styler-body">
          <aside class="styler-sidebar">
            <button
              v-for="cat in categories"
              :key="cat.key"
              type="button"
              class="styler-sidebar__item"
              :class="{ 'is-active': activeCategory === cat.key }"
              @click="activeCategory = cat.key"
            >
              <i :class="cat.icon"></i>
              {{ cat.label }}
            </button>
          </aside>

          <div class="styler-panel">
            <h3 class="styler-panel__title">{{ activeCategoryLabel }}</h3>

        <!-- Container ─────────────────────────────────────────────────── -->
        <StylerBoxGroup v-if="activeCategory === 'container'" :with-text="false" v-model="styles.container" />

        <!-- Label ──────────────────────────────────────────────────────── -->
        <template v-if="activeCategory === 'label'">
          <StylerColor label="Text Color" v-model="styles.label.color" />
          <StylerTypography label="Typography" v-model="styles.label.typography" />
        </template>

        <!-- Placeholder ────────────────────────────────────────────────── -->
        <StylerColor v-if="activeCategory === 'placeholder'" label="Placeholder Color" v-model="styles.placeholder.color" />

        <!-- Asterisk ───────────────────────────────────────────────────── -->
        <StylerColor v-if="activeCategory === 'asterisk'" label="Required Asterisk Color" v-model="styles.asterisk.color" />

        <!-- Input fields ───────────────────────────────────────────────── -->
        <template v-if="activeCategory === 'input'">
          <el-collapse v-model="inputCollapse">
            <el-collapse-item title="Normal State" name="normal">
              <StylerBoxGroup :with-text="true" v-model="styles.input.normal" />
            </el-collapse-item>
            <el-collapse-item title="Focus State" name="focus">
              <StylerBoxGroup :with-text="true" :with-spacing="false" v-model="styles.input.focus" />
            </el-collapse-item>
          </el-collapse>
        </template>

        <!-- Buttons ────────────────────────────────────────────────────── -->
        <template v-if="['submit_button', 'next_button', 'prev_button'].includes(activeCategory)">
          <div v-if="activeCategory === 'submit_button'" class="styler-field">
            <label class="styler-field__label">Alignment</label>
            <el-radio-group :value="styles[activeCategory].alignment" size="small" @input="v => $set(styles[activeCategory], 'alignment', v)">
              <el-radio-button label="left">Left</el-radio-button>
              <el-radio-button label="center">Center</el-radio-button>
              <el-radio-button label="right">Right</el-radio-button>
            </el-radio-group>
          </div>

          <el-collapse v-model="buttonCollapse">
            <el-collapse-item title="Normal State" name="normal">
              <StylerBoxGroup :with-text="true" v-model="styles[activeCategory].normal" />
            </el-collapse-item>
            <el-collapse-item title="Hover State" name="hover">
              <StylerBoxGroup :with-text="true" :with-spacing="false" v-model="styles[activeCategory].hover" />
            </el-collapse-item>
          </el-collapse>
        </template>

        <!-- Section break ──────────────────────────────────────────────── -->
        <template v-if="activeCategory === 'section_break'">
          <el-collapse v-model="sectionCollapse">
            <el-collapse-item title="Title" name="title">
              <StylerColor label="Text Color" v-model="styles.section_break.title.color" />
              <StylerTypography label="Typography" v-model="styles.section_break.title.typography" />
            </el-collapse-item>
            <el-collapse-item title="Description" name="description">
              <StylerColor label="Text Color" v-model="styles.section_break.description.color" />
              <StylerTypography label="Typography" v-model="styles.section_break.description.typography" />
            </el-collapse-item>
          </el-collapse>
        </template>

        <!-- Grid table ─────────────────────────────────────────────────── -->
        <template v-if="activeCategory === 'grid_table'">
          <el-collapse v-model="gridCollapse">
            <el-collapse-item title="Header Row" name="head">
              <StylerBoxGroup :with-text="true" v-model="styles.grid_table.head" />
            </el-collapse-item>
            <el-collapse-item title="Body Cells" name="body">
              <StylerBoxGroup :with-text="true" v-model="styles.grid_table.body" />
            </el-collapse-item>
          </el-collapse>
        </template>

        <!-- Step header / progress bar ─────────────────────────────────── -->
        <template v-if="activeCategory === 'step_header'">
          <StylerColor label="Active Color" v-model="styles.step_header.active_color" />
          <StylerColor label="Inactive Color" v-model="styles.step_header.inactive_color" />
          <StylerColor label="Text Color" v-model="styles.step_header.text_color" />
        </template>

        <!-- Range slider ───────────────────────────────────────────────── -->
        <template v-if="activeCategory === 'range_slider'">
          <StylerColor label="Active Color" v-model="styles.range_slider.active_color" />
          <StylerColor label="Inactive Color" v-model="styles.range_slider.inactive_color" />
          <StylerColor label="Text Color" v-model="styles.range_slider.text_color" />
        </template>

        <!-- Radio / Checkbox ───────────────────────────────────────────── -->
        <template v-if="activeCategory === 'checkable'">
          <StylerColor label="Label Color" v-model="styles.checkable.color" />
          <StylerColor label="Checked Color" v-model="styles.checkable.active_color" />
        </template>

        <!-- Messages ───────────────────────────────────────────────────── -->
        <template v-if="activeCategory === 'error_message'">
          <StylerColor label="Text Color" v-model="styles.error_message.color" />
          <StylerTypography label="Typography" v-model="styles.error_message.typography" />
        </template>
        <template v-if="activeCategory === 'success_message'">
          <StylerColor label="Text Color" v-model="styles.success_message.color" />
          <StylerTypography label="Typography" v-model="styles.success_message.typography" />
        </template>
          </div>
        </div>
      </div>

      <div class="styler-preview">
        <div class="styler-preview__head">
          <span><i class="el-icon-view"></i> Live Preview</span>
          <span v-if="previewLoading" class="styler-preview__status">Updating…</span>
        </div>
        <iframe
          ref="previewFrame"
          class="styler-preview__frame"
          :src="previewFrameUrl"
          @load="onFrameLoad"
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script>
/* global jQuery */
const $ = window.jQuery;
const cpm = window.contactum || {};

import StylerColor from '../components/pro/styler/StylerColor.vue';
import StylerTypography from '../components/pro/styler/StylerTypography.vue';
import StylerBoxGroup from '../components/pro/styler/StylerBoxGroup.vue';

function emptyStyles() {
  return {
    container: {},
    label: { color: '', typography: {} },
    placeholder: { color: '' },
    asterisk: { color: '' },
    input: { normal: {}, focus: {} },
    submit_button: { alignment: 'left', normal: {}, hover: {} },
    next_button: { normal: {}, hover: {} },
    prev_button: { normal: {}, hover: {} },
    section_break: { title: { color: '', typography: {} }, description: { color: '', typography: {} } },
    grid_table: { head: {}, body: {} },
    step_header: { active_color: '', inactive_color: '', text_color: '' },
    range_slider: { active_color: '', inactive_color: '', text_color: '' },
    checkable: { color: '', active_color: '' },
    error_message: { color: '', typography: {} },
    success_message: { color: '', typography: {} },
  };
}

export default {
  name: 'Styler',
  components: { StylerColor, StylerTypography, StylerBoxGroup },
  props: ['id'],

  data() {
    return {
      loading: true,
      saving: false,
      selectedStyle: '',
      presets: {},
      styles: emptyStyles(),
      activeCategory: 'container',
      inputCollapse: ['normal'],
      buttonCollapse: ['normal'],
      sectionCollapse: ['title'],
      gridCollapse: ['head'],
      formFlags: {
        has_step: false,
        has_section_break: false,
        has_grid: false,
        has_range_slider: false,
        has_checkable: false,
      },
      frameReady: false,
      previewLoading: false,
      previewDebounce: null,
      previewBaseUrl: '',
    };
  },

  computed: {
    // `window.contactum.preview_url` is only ever set for whatever form the
    // server happened to render on the last full page load — stale/wrong
    // once you're routed here client-side. The styler's own "get" AJAX call
    // returns the correct per-form URL instead; see load().
    previewUrl() {
      return this.previewBaseUrl ? this.previewBaseUrl + '&new_window=1' : '';
    },

    previewFrameUrl() {
      return this.previewBaseUrl || '';
    },

    categories() {
      const list = [
        { key: 'container', label: 'Container', icon: 'el-icon-picture-outline' },
        { key: 'label', label: 'Field Labels', icon: 'el-icon-price-tag' },
        { key: 'placeholder', label: 'Placeholder', icon: 'el-icon-edit-outline' },
        { key: 'asterisk', label: 'Required Asterisk', icon: 'el-icon-warning-outline' },
        { key: 'input', label: 'Input Fields', icon: 'el-icon-edit' },
        { key: 'submit_button', label: 'Submit Button', icon: 'el-icon-position' },
      ];

      if (this.formFlags.has_step) {
        list.push(
          { key: 'next_button', label: 'Next Button', icon: 'el-icon-right' },
          { key: 'prev_button', label: 'Previous Button', icon: 'el-icon-back' },
          { key: 'step_header', label: 'Step Progress', icon: 'el-icon-data-line' }
        );
      }

      if (this.formFlags.has_section_break) {
        list.push({ key: 'section_break', label: 'Section Break', icon: 'el-icon-minus' });
      }

      if (this.formFlags.has_grid) {
        list.push({ key: 'grid_table', label: 'Grid Table', icon: 'el-icon-menu' });
      }

      if (this.formFlags.has_range_slider) {
        list.push({ key: 'range_slider', label: 'Range Slider', icon: 'el-icon-sort' });
      }

      if (this.formFlags.has_checkable) {
        list.push({ key: 'checkable', label: 'Radio / Checkbox', icon: 'el-icon-circle-check' });
      }

      list.push(
        { key: 'error_message', label: 'Error Message', icon: 'el-icon-circle-close' },
        { key: 'success_message', label: 'Success Message', icon: 'el-icon-success' }
      );

      return list;
    },

    activeCategoryLabel() {
      const found = this.categories.find(c => c.key === this.activeCategory);
      return found ? found.label : '';
    },
  },

  watch: {
    styles: {
      handler() {
        this.schedulePreview();
      },
      deep: true,
    },
    selectedStyle() {
      this.schedulePreview();
    },
  },

  mounted() {
    this.load();
  },

  methods: {
    onFrameLoad() {
      this.frameReady = true;
      this.schedulePreview();
    },

    schedulePreview() {
      if (!this.frameReady || this.loading) return;

      clearTimeout(this.previewDebounce);
      this.previewDebounce = setTimeout(() => {
        this.pushPreview();
      }, 400);
    },

    pushPreview() {
      this.previewLoading = true;
      $.post(cpm.ajaxurl, {
        action: 'contactum_preview_form_styler',
        _ajax_nonce: cpm.nonce,
        form_id: this.id,
        selected_style: this.selectedStyle,
        styles: JSON.stringify(this.styles),
      }, (res) => {
        this.previewLoading = false;
        if (res.success) {
          this.applyPreviewCss(res.data.css, res.data.preset_url);
        }
      }).fail(() => {
        this.previewLoading = false;
      });
    },

    applyPreviewCss(css, presetUrl) {
      const frame = this.$refs.previewFrame;
      const doc = frame && frame.contentDocument;
      if (!doc || !doc.head) return; // cross-origin or not yet loaded — nothing safe to do

      let presetLink = doc.getElementById('contactum-styler-preset');
      if (presetUrl) {
        if (!presetLink) {
          presetLink = doc.createElement('link');
          presetLink.id = 'contactum-styler-preset';
          presetLink.rel = 'stylesheet';
          doc.head.appendChild(presetLink);
        }
        presetLink.href = presetUrl;
      } else if (presetLink) {
        presetLink.remove();
      }

      let styleTag = doc.getElementById('contactum-styler-preview');
      if (!styleTag) {
        styleTag = doc.createElement('style');
        styleTag.id = 'contactum-styler-preview';
        doc.head.appendChild(styleTag);
      }
      styleTag.textContent = css || '';
    },

    goBack() {
      this.$router.push({ name: 'form-edit', params: { id: this.id } });
    },

    load() {
      this.loading = true;
      $.post(cpm.ajaxurl, {
        action: 'contactum_get_form_styler',
        _ajax_nonce: cpm.nonce,
        form_id: this.id,
      }, (res) => {
        this.loading = false;
        if (!res.success) return;

        const data = res.data;
        this.presets = data.presets || {};
        this.selectedStyle = data.selected_style || '';
        this.previewBaseUrl = data.preview_url || '';

        const merged = emptyStyles();
        const saved = (data.styles && typeof data.styles === 'object') ? data.styles : {};
        Object.keys(merged).forEach((key) => {
          if (saved[key]) {
            merged[key] = this.deepMerge(merged[key], saved[key]);
          }
        });
        this.styles = merged;

        this.formFlags = {
          has_step: !!data.has_step,
          has_section_break: !!data.has_section_break,
          has_grid: !!data.has_grid,
          has_range_slider: !!data.has_range_slider,
          has_checkable: !!data.has_checkable,
        };

        this.$nextTick(() => this.schedulePreview());
      });
    },

    deepMerge(base, incoming) {
      if (Array.isArray(base) || typeof base !== 'object' || base === null) {
        return incoming;
      }
      const out = Object.assign({}, base);
      Object.keys(incoming || {}).forEach((key) => {
        out[key] = (incoming[key] && typeof incoming[key] === 'object' && !Array.isArray(incoming[key]) && typeof base[key] === 'object')
          ? this.deepMerge(base[key], incoming[key])
          : incoming[key];
      });
      return out;
    },

    onSelectStyle(val) {
      this.selectedStyle = val;
      this.save();
    },

    save() {
      this.saving = true;
      $.post(cpm.ajaxurl, {
        action: 'contactum_save_form_styler',
        _ajax_nonce: cpm.nonce,
        form_id: this.id,
        selected_style: this.selectedStyle,
        styles: JSON.stringify(this.styles),
      }, (res) => {
        this.saving = false;
        if (res.success) {
          this.$message.success(res.data.message);
        } else {
          this.$message.error((res.data && res.data.message) || 'Could not save styles.');
        }
      });
    },
  },
};
</script>

<style scoped>
.styler-page {
  padding: 20px 24px;
  min-height: 70vh;
}
.styler-topbar {
  display: flex;
  align-items: center;
  gap: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #ebeef5;
  margin-bottom: 20px;
}
.styler-topbar__preset {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #606266;
}
.styler-topbar__actions {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 14px;
}
.styler-topbar__preview {
  font-size: 13px;
  color: #409eff;
  text-decoration: none;
}
.styler-topbar__preview:hover {
  text-decoration: underline;
}

.styler-workspace {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}
.styler-controls {
  flex: 1;
  min-width: 0;
}
.styler-preview {
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
.styler-preview__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  border-bottom: 1px solid #ebeef5;
  font-size: 13px;
  color: #606266;
  flex-shrink: 0;
}
.styler-preview__status {
  font-size: 12px;
  color: #409eff;
}
.styler-preview__frame {
  flex: 1;
  width: 100%;
  border: 0;
  background: #fff;
}
@media (max-width: 1200px) {
  .styler-workspace {
    flex-direction: column;
  }
  .styler-preview {
    position: static;
    width: 100%;
    height: 60vh;
  }
}

.styler-locked {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #f4f4f5;
  border-radius: 8px;
  padding: 16px 20px;
  font-size: 13.5px;
  color: #606266;
}
.styler-locked i {
  color: #909399;
  font-size: 16px;
}

.styler-body {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}
.styler-sidebar {
  width: 200px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 8px;
}
.styler-sidebar__item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  text-align: left;
  padding: 9px 10px;
  border-radius: 6px;
  font-size: 13px;
  color: #606266;
  cursor: pointer;
}
.styler-sidebar__item:hover {
  background: #f5f7fa;
}
.styler-sidebar__item.is-active {
  background: #ecf5ff;
  color: #409eff;
  font-weight: 600;
}

.styler-panel {
  flex: 1;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 20px 24px;
  max-width: 520px;
}
.styler-panel__title {
  margin: 0 0 16px;
  font-size: 15px;
  font-weight: 600;
  color: #303133;
}

.styler-field {
  margin-bottom: 14px;
}
.styler-field__label {
  display: block;
  font-size: 12px;
  color: #606266;
  margin-bottom: 6px;
}
</style>
