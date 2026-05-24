<template>
  <div class="cfi-page">

    <!-- ── Page header ───────────────────────────── -->
    <div class="cfi-header">
      <div class="cfi-header-text">
        <h2 class="cfi-title">Form Integrations</h2>
        <p class="cfi-subtitle">Connect this form to third-party services. Toggle an integration to activate it, then configure the mapping.</p>
      </div>
      {{ integrationList }
      <div class="cfi-header-meta" v-if="integrationCount > 0">
        <span class="cfi-enabled-badge">
          <i class="el-icon-connection"></i>
          {{ enabledCount }} / {{ integrationCount }} active
        </span>
      </div>
    </div>

    <!-- ── Empty state ────────────────────────────── -->
    <div class="cfi-empty" v-if="integrationCount === 0">
      <i class="el-icon-connection cfi-empty-icon"></i>
      <p>No integrations available for this form.</p>
    </div>

    <!-- ── Integration grid ──────────────────────── -->
    <div class="cfi-grid" v-else>
      <div
        class="cfi-card"
        :class="{
          'cfi-card--enabled': integration.formenable,
          'cfi-card--disconnected': !integration.value || !integration.value.status
        }"
        v-for="integration in integrationList"
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
            <span
              v-if="!integration.value || !integration.value.status"
              class="cfi-status-badge cfi-status-badge--disconnected"
            >Not Connected</span>
            <span v-else class="cfi-status-badge" :class="integration.formenable ? 'cfi-status-badge--on' : 'cfi-status-badge--off'">
              {{ integration.formenable ? 'Enabled' : 'Disabled' }}
            </span>
          </div>
        </div>

        <!-- Card footer -->
        <div class="cfi-card-footer">
          <template v-if="integration.value && integration.value.status">
            <label class="cfi-toggle-row">
              <el-switch
                v-model="integration.formenable"
                @change="changeStatus(integration, $event, integration.id)"
              />
              <span class="cfi-toggle-label">{{ integration.formenable ? 'Active' : 'Inactive' }}</span>
            </label>
            <el-button
              v-if="integration.formenable"
              size="small"
              icon="el-icon-setting"
              @click.prevent="openDialog(integration)"
            >Configure</el-button>
          </template>
          <span v-else class="cfi-not-connected-hint">
            <i class="el-icon-warning-outline"></i> Configure in global settings first
          </span>
        </div>
      </div>
    </div>

    <!-- ── Settings dialog ───────────────────────── -->
    <el-dialog
      :visible.sync="dialogVisible"
      width="580px"
      custom-class="cfi-dialog"
      :show-close="false"
    >
      <!-- Custom header -->
      <template #title>
        <div class="cfi-dlg-header" v-if="dialogIntegration">
          <div class="cfi-dlg-header-icon">
            <img v-if="dialogIntegration.icon" :src="dialogIntegration.icon" :alt="dialogIntegration.title" />
            <i v-else class="el-icon-connection"></i>
          </div>
          <div class="cfi-dlg-header-text">
            <h3 class="cfi-dlg-title">{{ dialogIntegration.title }}</h3>
            <p class="cfi-dlg-subtitle">Configure field mapping for this integration</p>
          </div>
          <button class="cfi-dlg-close" @click="dialogVisible = false">
            <i class="el-icon-close"></i>
          </button>
        </div>
      </template>

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
              type="text"
              icon="el-icon-refresh"
              @click="fetchlists(dialogIntegration, index)"
            >Refresh</el-button>
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
          <el-button plain @click="dialogVisible = false">Cancel</el-button>
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
    integrationList() {
      return Object.values(this.integrations || {});
    },
    integrationCount() {
      return this.integrationList.length;
    },
    enabledCount() {
      return this.integrationList.filter(i => i.formenable).length;
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

    changeStatus(_integration, value, index) {
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

  &--disconnected {
    opacity: 0.7;
    border-color: #e5e7eb;
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

  &--disconnected {
    background: #fff7ed;
    color: #b45309;
    border: 1px solid #fcd34d;
  }
}

/* ── Not-connected hint ──────────────────────── */
.cfi-not-connected-hint {
  font-size: 12px;
  color: #b45309;
  display: inline-flex;
  align-items: center;
  gap: 4px;
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

/* ── Dialog global overrides ─────────────────── */
:deep(.cfi-dialog) {
  border-radius: 14px;
  overflow: hidden;

  .el-dialog__header {
    padding: 0;
    border-bottom: none;
  }

  .el-dialog__body {
    padding: 0 24px 8px;
  }

  .el-dialog__footer {
    padding: 16px 24px 20px;
    border-top: 1px solid #f3f4f6;
    background: #fafafa;
    border-radius: 0 0 14px 14px;
  }
}

/* ── Dialog custom header ────────────────────── */
.cfi-dlg-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 20px 24px 18px;
  border-bottom: 1px solid #f3f4f6;
}

.cfi-dlg-header-icon {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;

  img {
    width: 28px;
    height: 28px;
    object-fit: contain;
  }

  i {
    font-size: 20px;
    color: #9ca3af;
  }
}

.cfi-dlg-header-text {
  flex: 1;
  min-width: 0;
}

.cfi-dlg-title {
  margin: 0 0 2px;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}

.cfi-dlg-subtitle {
  margin: 0;
  font-size: 12px;
  color: #9ca3af;
}

.cfi-dlg-close {
  flex-shrink: 0;
  width: 30px;
  height: 30px;
  border: none;
  background: #f3f4f6;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: background 0.15s, color 0.15s;

  &:hover {
    background: #e5e7eb;
    color: #111827;
  }

  i {
    font-size: 14px;
  }
}

/* ── Dialog body ─────────────────────────────── */
.cfi-dialog-body {
  display: flex;
  flex-direction: column;
  gap: 18px;
  max-height: 60vh;
  overflow-y: auto;
  padding: 20px 0 12px;

  &::-webkit-scrollbar {
    width: 4px;
  }

  &::-webkit-scrollbar-track {
    background: transparent;
  }

  &::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 4px;
  }
}

.cfi-dialog-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 16px;
  background: #fafafa;
  border: 1px solid #f3f4f6;
  border-radius: 8px;
}

.cfi-dialog-field-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2px;
}

.cfi-dialog-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.cfi-input-wrap {
  position: relative;
}

/* ── Dialog footer ───────────────────────────── */
.cfi-dialog-footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
