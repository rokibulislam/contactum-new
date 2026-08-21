<template>
  <el-dialog
    :visible.sync="visibility"
    :before-close="cancelNewForm"
    custom-class="cnf-dialog"
    width="80%"
    top="5vh"
  >
    <!-- ── Header ── -->
    <template slot="title">
      <div class="cnf-header">
        <div class="cnf-header-left">
          <h4 class="cnf-title">Create a New Form</h4>
          <span class="cnf-subtitle">Choose a template or start from scratch</span>
        </div>
        <div class="cnf-header-right">
          <el-input
            v-model="searchQuery"
            placeholder="Search templates…"
            prefix-icon="el-icon-search"
            clearable
            size="small"
            class="cnf-search"
          />
          <el-button
            size="small"
            :type="showAiGenerate ? 'info' : 'default'"
            @click="showAiGenerate = !showAiGenerate; showFormsImport = false;"
            class="cnf-import-btn"
          >
            <i :class="showAiGenerate ? 'el-icon-circle-close' : 'el-icon-magic-stick'"></i>
            Generate with AI
          </el-button>

          <el-button
            size="small"
            :type="showFormsImport ? 'info' : 'default'"
            @click="showFormsImport = !showFormsImport; showAiGenerate = false;"
            class="cnf-import-btn"
          >
            <i :class="showFormsImport ? 'el-icon-circle-close' : 'el-icon-upload2'"></i>
            Import Form
          </el-button>
        </div>
      </div>

      <transition name="cnf-slide">
        <div v-if="showAiGenerate" class="cnf-ai-section">
          <p class="cnf-ai-desc">Describe the form you need — Contactum will build it for you.</p>

          <el-input
            v-model="aiPrompt"
            type="textarea"
            :rows="3"
            placeholder="e.g. A contact form for a photography business with name, email, phone, and preferred shoot date"
            :disabled="aiGenerating"
          />

          <el-input
            v-model="aiAdditional"
            placeholder="Anything else to include? (optional)"
            :disabled="aiGenerating"
            style="margin-top: 8px;"
          />

          <el-alert
            v-if="aiError"
            :title="aiError"
            type="error"
            :closable="false"
            show-icon
            style="margin-top: 10px;"
          />

          <div class="cnf-ai-actions">
            <el-button
              type="primary"
              size="small"
              :loading="aiGenerating"
              :disabled="!aiPrompt.trim()"
              @click="generateWithAi"
            >
              <i v-if="!aiGenerating" class="el-icon-magic-stick"></i>
              {{ aiGenerating ? 'Generating…' : 'Generate Form' }}
            </el-button>
          </div>
        </div>
      </transition>

      <transition name="cnf-slide">
        <import-forms
          v-if="showFormsImport"
          class="cnf-import-section"
          @forms-imported="updateFormsImported"
          :app="{forms:[]}"
        />
      </transition>
    </template>

    <!-- ── Template Grid ── -->
    <div class="cnf-body">
      <div class="cnf-grid">

        <!-- Blank form card -->
        <div class="cnf-card cnf-card--blank" @click="selectBlank">
          <div class="cnf-card-blank-inner">
            <span class="cnf-card-blank-icon"><i class="el-icon-plus"></i></span>
            <span class="cnf-card-blank-label">Blank Form</span>
            <label class="cnf-card-blank-option" @click.stop>
              <el-checkbox v-model="startConversational"></el-checkbox>
              Start as Conversational Form
            </label>
          </div>
        </div>

        <!-- Template cards -->
        <template v-for="(template, key) in filteredTemplates">
          <div
            :key="key"
            class="cnf-card"
            :class="{ 'cnf-card--disabled': !template.enabled }"
            @click="template.enabled && selectTemplate(key, template)"
          >
            <div class="cnf-card-img-wrap">
              <img :src="template.image" :alt="template.title" class="cnf-card-img" />
              <div class="cnf-card-overlay">
                <span class="cnf-card-use-btn">
                  <i class="el-icon-check"></i> Use Template
                </span>
              </div>
            </div>
            <div class="cnf-card-footer">
              <span class="cnf-card-title">{{ template.title }}</span>
              <span v-if="!template.enabled" class="cnf-card-pro-badge">Pro</span>
            </div>
          </div>
        </template>

      </div>

      <div v-if="filteredTemplates && Object.keys(filteredTemplates).length === 0" class="cnf-empty">
        <i class="el-icon-search cnf-empty-icon"></i>
        <p>No templates match "<strong>{{ searchQuery }}</strong>"</p>
      </div>
    </div>

    <!-- ── Title / Description dialog, shown after picking a template ── -->
    <el-dialog
      :visible.sync="showDetailsModal"
      title="Name Your Form"
      width="600px"
      top="12vh"
      append-to-body
      custom-class="cnf-details-dialog"
      @close="cancelDetailsModal"
    >
      <el-form label-position="top" @submit.native.prevent="confirmCreateFromTemplate">
        <el-form-item label="Form Title">
          <el-input
            v-model="newFormTitle"
            placeholder="e.g. Contact Form"
            ref="titleInput"
            size="medium"
          />
        </el-form-item>
        <el-form-item label="Description (optional)">
          <el-input
            v-model="newFormDescription"
            type="textarea"
            :rows="5"
            placeholder="What is this form for?"
          />
        </el-form-item>
      </el-form>

      <template slot="footer">
        <el-button @click="cancelDetailsModal">Cancel</el-button>
        <el-button
          type="primary"
          :disabled="!newFormTitle.trim()"
          @click="confirmCreateFromTemplate"
        >
          Create Form
        </el-button>
      </template>
    </el-dialog>
  </el-dialog>
</template>

<script>
import ImportForms from '../../pages/tools/ImportForms.vue';

export default {
  name: 'CreateNewFormModal',
  components: { ImportForms },

  props: {
    visibility: Boolean,
  },

  data() {
    return {
      showFormsImport: false,
      formsImported: false,
      searchQuery: '',
      templates: window.contactum.templates,
      showAiGenerate: false,
      aiPrompt: '',
      aiAdditional: '',
      aiGenerating: false,
      aiError: '',
      startConversational: false,
      showDetailsModal: false,
      pendingTemplateKey: null,
      newFormTitle: '',
      newFormDescription: '',
    };
  },

  computed: {
    filteredTemplates() {
      if (!this.searchQuery.trim()) return this.templates;
      const q = this.searchQuery.toLowerCase();
      return Object.fromEntries(
        Object.entries(this.templates).filter(([, t]) =>
          t.title.toLowerCase().includes(q) ||
          (t.description && t.description.toLowerCase().includes(q))
        )
      );
    },

  },

  methods: {
    cancelNewForm() {
      this.searchQuery = '';
      this.$emit('close');
    },

    updateFormsImported(value) {
      this.formsImported = value;
    },

    buildTemplateUrl(templateSlug, title = '', description = '') {
      const url = new URL(contactum.admin_url);
      url.searchParams.set('action', 'create_template');
      url.searchParams.set('template', templateSlug);
      url.searchParams.set('_wpnonce', contactum.nonce);
      if (title.trim()) {
        url.searchParams.set('title', title.trim());
      }
      if (description.trim()) {
        url.searchParams.set('description', description.trim());
      }
      if (templateSlug === 'blank' && this.startConversational) {
        url.searchParams.set('conversational', '1');
      }
      return url.toString();
    },

    selectTemplate(key, template) {
      this.pendingTemplateKey = key;
      this.newFormTitle = template.title || '';
      this.newFormDescription = '';
      this.showDetailsModal = true;

      this.$nextTick(() => {
        this.$refs.titleInput && this.$refs.titleInput.focus();
      });
    },

    selectBlank() {
      this.selectTemplate('blank', { title: 'Blank Form' });
    },

    cancelDetailsModal() {
      this.showDetailsModal = false;
      this.pendingTemplateKey = null;
    },

    confirmCreateFromTemplate() {
      if (!this.newFormTitle.trim() || !this.pendingTemplateKey) return;

      window.location.href = this.buildTemplateUrl(
        this.pendingTemplateKey,
        this.newFormTitle,
        this.newFormDescription
      );
    },

    generateWithAi() {
      if (!this.aiPrompt.trim() || this.aiGenerating) return;

      this.aiGenerating = true;
      this.aiError      = '';

      jQuery.post(contactum.ajaxurl, {
        action:             'contactum_generate_ai_form',
        _ajax_nonce:        contactum.nonce,
        prompt:             this.aiPrompt,
        additional_prompt:  this.aiAdditional,
      }, (response) => {
        this.aiGenerating = false;

        if (response.success) {
          window.location.href = 'admin.php?page=contactum&route=builder#/forms/' + response.data.form_id;
        } else {
          this.aiError = (response.data && response.data.message) || 'Could not generate the form. Please try again.';
        }
      }).fail(() => {
        this.aiGenerating = false;
        this.aiError      = 'Request failed. Please try again.';
      });
    },
  },
};
</script>

<style scoped lang="scss">

/* ── Dialog override ── */
::v-deep .cnf-dialog {
  border-radius: 12px;
  overflow: hidden;

  .el-dialog__header {
    padding: 20px 24px 0;
    border-bottom: none;
  }

  .el-dialog__body {
    padding: 0;
  }

  .el-dialog__headerbtn {
    top: 20px;
    right: 20px;
  }
}

/* ── Header ── */
.cnf-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.cnf-header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.cnf-title {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}

.cnf-subtitle {
  font-size: 13px;
  color: #6b7280;
}

.cnf-header-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.cnf-search {
  width: 220px;
}

.cnf-import-btn {
  white-space: nowrap;
}

/* ── Import section slide ── */
.cnf-import-section {
  margin-top: 16px;
}

/* ── AI generate section ── */
.cnf-ai-section {
  margin-top: 16px;
  padding: 16px;
  background: #f5f3ff;
  border: 1px solid #ddd6fe;
  border-radius: 8px;
}

.cnf-ai-desc {
  margin: 0 0 10px;
  font-size: 13px;
  color: #5b21b6;
}

.cnf-ai-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.cnf-slide-enter-active,
.cnf-slide-leave-active {
  transition: all 0.25s ease;
  overflow: hidden;
}

.cnf-slide-enter,
.cnf-slide-leave-to {
  opacity: 0;
  max-height: 0;
}

.cnf-slide-enter-to,
.cnf-slide-leave {
  opacity: 1;
  max-height: 400px;
}

/* ── Body ── */
.cnf-body {
  padding: 20px 24px 24px;
  max-height: calc(90vh - 140px);
  overflow-y: auto;
}

/* ── Grid ── */
.cnf-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

/* ── Card base ── */
.cnf-card {
  display: flex;
  flex-direction: column;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
  text-decoration: none;
  transition: border-color 0.18s, box-shadow 0.18s, transform 0.18s;
  background: #fff;
  cursor: pointer;

  &:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
    text-decoration: none;
  }

  &--disabled {
    opacity: 0.55;
    pointer-events: none;
  }
}

/* ── Blank card ── */
.cnf-card--blank {
  border: 2px dashed #d1d5db;
  min-height: 180px;
  background: #fafafa;

  &:hover {
    border-color: #3b82f6;
    background: #eff6ff;
  }
}

.cnf-card-blank-inner {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 20px;
}

.cnf-card-blank-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #e0e7ff;
  color: #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  transition: background 0.15s, color 0.15s;

  .cnf-card--blank:hover & {
    background: #3b82f6;
    color: #fff;
  }
}

.cnf-card-blank-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.cnf-card-blank-option {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 4px;
  font-size: 11.5px;
  font-weight: 400;
  color: #6b7280;
  cursor: pointer;
}

/* ── Image wrap ── */
.cnf-card-img-wrap {
  position: relative;
  overflow: hidden;
  aspect-ratio: 4 / 3;
  background: #f3f4f6;
}

.cnf-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.25s ease;

  .cnf-card:hover & {
    transform: scale(1.04);
  }
}

/* ── Hover overlay ── */
.cnf-card-overlay {
  position: absolute;
  inset: 0;
  background: rgba(17, 24, 39, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;

  .cnf-card:hover & {
    opacity: 1;
  }
}

.cnf-card-use-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff;
  color: #111827;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  transition: background 0.15s, color 0.15s;

  i { color: #10b981; font-size: 14px; }
}

/* ── Card footer ── */
.cnf-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  gap: 6px;
  border-top: 1px solid #f3f4f6;
}

.cnf-card-title {
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cnf-card-pro-badge {
  flex-shrink: 0;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  background: #fef3c7;
  color: #92400e;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

/* ── Empty state ── */
.cnf-empty {
  text-align: center;
  padding: 48px 20px;
  color: #9ca3af;

  p {
    margin: 10px 0 0;
    font-size: 14px;
  }

  strong { color: #374151; }
}

.cnf-empty-icon {
  font-size: 36px;
  color: #d1d5db;
}

</style>
