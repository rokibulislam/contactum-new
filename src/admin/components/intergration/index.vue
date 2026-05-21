<template>
  <div class="cfi-page">

    <!-- ── Page header ───────────────────────────── -->
    <div class="cfi-header">
      <div class="cfi-header-text">
        <h2 class="cfi-title">Form Integrations</h2>
        <p class="cfi-subtitle">Connect this form to third-party services. Toggle an integration to activate it, then configure the mapping.</p>
      </div>
      {{  integrations }}
      <div class="cfi-header-meta" v-if="integrations && integrations.length">
        <span class="cfi-enabled-badge">
          <i class="el-icon-connection"></i>
          {{ enabledCount }} / {{ integrations.length }} active
        </span>
      </div>
    </div>

    <!-- ── Empty state ────────────────────────────── -->
    <div class="cfi-empty" v-if="!integrations || !integrations.length">
      <i class="el-icon-connection cfi-empty-icon"></i>
      <p>No integrations available for this form.</p>
    </div>

    <!-- ── Integration grid ──────────────────────── -->
    <div class="cfi-grid" v-else>
      <div
        class="cfi-card"
        :class="{ 'cfi-card--enabled': integration.formenable }"
        v-for="(integration, index) in integrations"
        :key="integration.id"
      >
        <!-- Card body -->
        <div class="cfi-card-body">
          <div class="cfi-card-icon-wrap">
            <img
              v-if="integration.icon"
              :src="integration.icon"
              :alt="integration.title"
              class="cfi-card-icon"
            />
            <span v-else class="cfi-card-icon-placeholder">
              <i class="el-icon-connection"></i>
            </span>
          </div>
          <div class="cfi-card-info">
            <h4 class="cfi-card-title">{{ integration.title }}</h4>
            <span class="cfi-status-badge" :class="integration.formenable ? 'cfi-status-badge--on' : 'cfi-status-badge--off'">
              {{ integration.formenable ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
        </div>

        <!-- Card footer -->
        <div class="cfi-card-footer">
          <label class="cfi-toggle-row">
            <el-switch
              v-model="integration.formenable"
              @change="changeStatus(integration, $event, index)"
            />
            <span class="cfi-toggle-label">{{ integration.formenable ? 'Active' : 'Inactive' }}</span>
          </label>
          <el-button
            v-if="integration.formenable"
            size="small"
            icon="el-icon-setting"
            @click.prevent="openDialog(integration)"
          >Configure</el-button>
        </div>
      </div>
    </div>

    <!-- ── Settings dialog ───────────────────────── -->
    <el-dialog
      :title="dialogIntegration ? dialogIntegration.title + ' Settings' : 'Integration Settings'"
      :visible.sync="dialogVisible"
      width="560px"
      custom-class="cfi-dialog"
    >
      <div v-if="dialogIntegration && dialogIntegration.formenable" class="cfi-dialog-body">

        <div
          v-for="(field, index) in dialogIntegration.integration_fields"
          :key="field.name || index"
          class="cfi-dialog-field"
        >
          <div class="cfi-dialog-field-header">
            <label class="cfi-dialog-label">{{ field.label }}</label>
            <el-button
              v-if="field.type === 'list_ajax_options'"
              size="mini"
              icon="el-icon-refresh"
              circle
              @click="fetchlists(dialogIntegration, index)"
            />
          </div>

          <template v-if="field.type === 'text'">
            <div class="cfi-input-wrap">
              <el-input
                :placeholder="field.placeholder"
                v-model="dialogIntegration.integration[field.name]"
              />
              <merge_tags
                @insert="insertValue"
                :field="field.name"
                :name="dialogIntegration.id"
              />
            </div>
          </template>

          <template v-if="field.type === 'list_ajax_options' || field.type === 'select'">
            <el-select
              filterable
              clearable
              v-model="dialogIntegration.integration[field.name]"
              :placeholder="field.placeholder || 'Select an option'"
              style="width: 100%"
            >
              <el-option
                v-for="(list_name, list_key) in field.options"
                :key="list_key"
                :value="list_key"
                :label="list_name"
              />
            </el-select>
          </template>

          <template v-if="field.type === 'checkbox-single'">
            <el-checkbox v-model="dialogIntegration.integration[field.name]">
              {{ field.checkbox_label }}
            </el-checkbox>
          </template>
        </div>

      </div>

      <template #footer>
        <div class="cfi-dialog-footer">
          <el-button @click="dialogVisible = false">Cancel</el-button>
          <el-button type="primary" icon="el-icon-check" @click="saveIntegration">Save Settings</el-button>
        </div>
      </template>
    </el-dialog>

  </div>
</template>

<script>
import merge_tags from '../merge-tags/index.vue';

export default {
  name: "Intergrations",
  components: { merge_tags },

  data() {
    return {
      dialogVisible: false,
      dialogIntegration: null,
    };
  },

  computed: {
    integrations() {
      return this.$store.getters.integrations;
    },
    enabledCount() {
      return (this.integrations || []).filter(i => i.formenable).length;
    },
  },

  methods: {
    openDialog(integration) {
      this.dialogIntegration = integration;
      this.dialogVisible = true;
    },

    saveIntegration() {
      this.$emit('save-integration', this.dialogIntegration);
      this.dialogVisible = false;
    },

    changeStatus(integration, value, index) {
      this.$store.commit("updateIntegrationProperty", {
        index,
        property: 'formenable',
        value,
      });
    },

    fetchlists(dialogIntegration, index) {
      jQuery.post(window.contactum.ajaxurl, {
        action: `contactum_${dialogIntegration.id}_update_list`,
        _ajax_nonce: window.contactum.nonce,
      }, (response) => {
        if (response.success) {
          dialogIntegration.integration_fields[index].options = response.data;
        }
      });
    },

    insertValue(type, field, integration, property) {
      const value = field !== undefined ? `{${type}:${field}}` : `{${type}}`;
      this.$store.commit("updateIntegrationNestedProperty", {
        type, field, integration, property, value,
      });
    },
  },
};
</script>

<style scoped lang="scss">

/* ── Page ────────────────────────────────────── */
.cfi-page {
  padding: 24px;
}

/* ── Header ──────────────────────────────────── */
.cfi-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 12px;
}

.cfi-title {
  margin: 0 0 4px;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
}

.cfi-subtitle {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
  max-width: 560px;
}

.cfi-enabled-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ecfdf5;
  color: #059669;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid #a7f3d0;
}

/* ── Empty state ─────────────────────────────── */
.cfi-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #9ca3af;

  p {
    margin: 12px 0 0;
    font-size: 14px;
  }
}

.cfi-empty-icon {
  font-size: 40px;
  color: #d1d5db;
}

/* ── Grid ────────────────────────────────────── */
.cfi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 18px;
}

/* ── Card ────────────────────────────────────── */
.cfi-card {
  background: #fff;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;

  &:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    border-color: #d1d5db;
  }

  &--enabled {
    border-color: var(--primary, #6366f1);
    box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.12);
  }
}

.cfi-card-body {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 20px 18px;
  flex: 1;
}

.cfi-card-icon-wrap {
  flex-shrink: 0;
  width: 46px;
  height: 46px;
  border-radius: 10px;
  background: #f9fafb;
  border: 1px solid #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cfi-card-icon {
  width: 30px;
  height: 30px;
  object-fit: contain;
}

.cfi-card-icon-placeholder {
  font-size: 22px;
  color: #9ca3af;
}

.cfi-card-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.cfi-card-title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── Status badge ────────────────────────────── */
.cfi-status-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
  letter-spacing: 0.3px;

  &--on {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
  }

  &--off {
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #e5e7eb;
  }
}

/* ── Card footer ─────────────────────────────── */
.cfi-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 18px;
  border-top: 1px solid #f3f4f6;
  background: #fafafa;
}

.cfi-toggle-row {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.cfi-toggle-label {
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
}

/* ── Dialog ──────────────────────────────────── */
.cfi-dialog-body {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-height: 65vh;
  overflow-y: auto;
  padding-right: 4px;
}

.cfi-dialog-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.cfi-dialog-field-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.cfi-dialog-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.cfi-input-wrap {
  position: relative;
}

.cfi-dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
</style>
