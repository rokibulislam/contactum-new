<template>
  <div class="gis-wrap">

    <!-- ── Connected state ────────────────────────────────────────────────── -->
    <div
      v-if="settings.settings && settings.settings.hide_on_valid && integration.status"
      class="gis-card"
    >
      <div class="gis-card__head">
        <h2 class="gis-card__title">{{ settings.title }}</h2>
        <p class="gis-card__desc" v-html="settings.description"></p>
      </div>

      <div class="gis-connected">
        <div class="gis-connected__icon">
          <span class="dashicons dashicons-yes-alt"></span>
        </div>
        <h3 class="gis-connected__title"
            v-html="settings.settings.discard_settings.section_description">
        </h3>
        <div class="gis-connected__actions">
          <el-button
            v-if="settings.settings.discard_settings.show_verify"
            v-loading="saving"
            type="primary"
            size="small"
            icon="el-icon-refresh"
            @click="save"
          >Verify Again</el-button>
          <el-button
            type="danger"
            size="small"
            plain
            @click="disconnect(settings.settings.discard_settings.data)"
          >{{ settings.settings.discard_settings.button_text }}</el-button>
        </div>
      </div>
    </div>

    <!-- ── Settings form ──────────────────────────────────────────────────── -->
    <div v-else class="gis-card">
      <div class="gis-card__head">
        <div class="gis-card__head-row">
          <div>
            <h2 class="gis-card__title">{{ settings.title }}</h2>
            <p class="gis-card__desc" v-html="settings.description"></p>
          </div>
          <span
            v-if="settings.settings"
            class="gis-status-badge"
            :class="integration.status ? 'gis-status-badge--on' : 'gis-status-badge--off'"
          >
            <span class="dashicons"
                  :class="integration.status ? 'dashicons-yes-alt' : 'dashicons-warning'">
            </span>
            {{ integration.status ? 'Connected' : 'Not Connected' }}
          </span>
        </div>
      </div>

      <el-skeleton :loading="loading" animated :rows="6">
        <el-form label-position="top" class="gis-form">
          <el-form-item
            v-for="(field, fieldKey) in (settings.settings && settings.settings.fields ? settings.settings.fields : [])"
            :key="fieldKey"
            class="gis-form__item"
          >
            <template slot="label" v-if="field.label">
              {{ field.label }}
            </template>

            <el-input
              v-if="field.type === 'text' || field.type === 'number'"
              :type="field.type"
              :placeholder="field.placeholder"
              v-model="integration[field.name]"
            />
            <el-input
              v-else-if="field.type === 'password'"
              type="password"
              :placeholder="field.placeholder"
              v-model="integration[field.name]"
              show-password
            />
            <el-input
              v-else-if="field.type === 'textarea'"
              type="textarea"
              :rows="3"
              :placeholder="field.placeholder"
              v-model="integration[field.name]"
            />
            <el-select
              v-else-if="field.type === 'select'"
              v-model="integration[field.name]"
              style="width:100%"
            >
              <el-option
                v-for="(optionName, optionValue) in field.options"
                :key="optionValue"
                :label="optionName"
                :value="optionValue"
              />
            </el-select>

            <p class="gis-form__tip" v-if="field.tips">{{ field.tips }}</p>
          </el-form-item>

          <!-- Validation message -->
          <div v-if="settings.settings" class="gis-validation">
            <div v-if="integration.status" class="gis-validation--ok">
              <span class="dashicons dashicons-yes-alt"></span>
              {{ settings.settings.valid_message }}
            </div>
            <div v-else class="gis-validation--err">
              <span class="dashicons dashicons-warning"></span>
              {{ settings.settings.invalid_message }}
            </div>
          </div>

          <p v-if="error_message" class="gis-error">{{ error_message }}</p>
        </el-form>
      </el-skeleton>

      <!-- Save button -->
      <div class="gis-card__footer">
        <el-button
          v-loading="saving"
          type="primary"
          icon="el-icon-check"
          @click="save"
        >Save Settings</el-button>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'GeneralIntegrationSettings',
  props: {
    setting_key: {
      type:     String,
      required: true,
    },
  },
  data() {
    return {
      integration:   {},
      settings:      {},
      loading:       false,
      saving:        false,
      error_message: '',
    };
  },
  mounted() {
    this.getIntegrationSettings();
  },
  watch: {
    setting_key() {
      this.getIntegrationSettings();
    },
  },
  methods: {
    getIntegrationSettings() {
      this.loading = true;
      jQuery.post(
        window.contactum.ajaxurl,
        {
          action:       'contactum_get_admin_integrations',
          _ajax_nonce:  window.contactum.nonce,
          settings_key: this.setting_key,
        },
        (res) => {
          this.loading = false;
          if (res.success) {
            this.settings    = res.data;
            this.integration = res.data.value;
          }
        }
      );
    },

    save() {
      this.saving = true;
      jQuery.post(
        window.contactum.ajaxurl,
        {
          action:       'contactum_save_global_integrations',
          _ajax_nonce:  window.contactum.nonce,
          settings_key: this.setting_key,
          integration:  this.integration,
        },
        (res) => {
          this.saving = false;
          if (res.success) {
            const ok = res.data.status === true || res.data.status === '1' || res.data.status === 'success';
            this.$set(this.integration, 'status', ok);
            this.$notify({
              title:    'Success',
              message:  res.data.message,
              type:     'success',
              position: 'bottom-right',
            });
          } else {
            this.$set(this.integration, 'status', false);
            this.$notify({
              title:    'Warning',
              message:  res.data.message,
              type:     'warning',
              position: 'bottom-right',
            });
          }
        }
      );
    },

    disconnect(data) {
      this.integration = data;
      this.save();
    },
  },
};
</script>

<style scoped>
/* ── Wrapper ─────────────────────────────────────────── */
.gis-wrap {
  max-width: 680px;
}

/* ── Card ────────────────────────────────────────────── */
.gis-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}

.gis-card__head {
  padding: 20px 24px 18px;
  border-bottom: 1px solid #f3f4f6;
}
.gis-card__head-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}
.gis-card__title {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}
.gis-card__desc {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.5;
}

/* Status badge */
.gis-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  white-space: nowrap;
  flex-shrink: 0;
}
.gis-status-badge .dashicons {
  font-size: 14px;
  width: 14px;
  height: 14px;
}
.gis-status-badge--on {
  background: #dcfce7;
  color: #16a34a;
}
.gis-status-badge--off {
  background: #fef3c7;
  color: #d97706;
}

/* ── Form ────────────────────────────────────────────── */
.gis-form {
  padding: 20px 24px 4px;
}
.gis-form__item {
  margin-bottom: 18px;
}
.gis-form__tip {
  margin: 6px 0 0;
  font-size: 12px;
  color: #9ca3af;
  line-height: 1.4;
}

/* Validation row */
.gis-validation {
  margin-bottom: 8px;
  font-size: 13px;
}
.gis-validation--ok {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #16a34a;
}
.gis-validation--err {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #d97706;
}
.gis-validation .dashicons {
  font-size: 15px;
  width: 15px;
  height: 15px;
}
.gis-error {
  font-size: 13px;
  color: #dc2626;
  margin: 4px 0 12px;
}

/* Card footer */
.gis-card__footer {
  padding: 14px 24px;
  border-top: 1px solid #f3f4f6;
  background: #fafafa;
}

/* ── Connected state ─────────────────────────────────── */
.gis-connected {
  padding: 40px 24px;
  text-align: center;
}
.gis-connected__icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #dcfce7;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}
.gis-connected__icon .dashicons {
  font-size: 28px;
  width: 28px;
  height: 28px;
  color: #16a34a;
}
.gis-connected__title {
  font-size: 15px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 20px;
  line-height: 1.4;
}
.gis-connected__actions {
  display: flex;
  gap: 10px;
  justify-content: center;
}

/* Override element-ui label color */
:deep(label) {
  color: #374151 !important;
  font-size: 13px !important;
  font-weight: 500 !important;
}
</style>
