<template>
  <div class="pmf-wrap">

    <!-- Gateway status notice ──────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-money-alt"></span>
        <span class="pmf-section__head-label">Active Gateways</span>
      </div>

      <div v-if="activeGateways.length" class="pmf-gateway-status-list">
        <div v-for="gw in activeGateways" :key="gw.key" class="pmf-gateway-status-item">
          <span class="pmf-gw-badge" :class="'pmf-gw-badge--' + gw.key">{{ gw.label }}</span>
          <span class="pmf-gateway-status-item__on dashicons dashicons-yes-alt"></span>
        </div>
      </div>

      <p v-else class="pmf-notice pmf-notice--warn">
        <span class="dashicons dashicons-info"></span>
        No gateways enabled. Go to
        <a :href="paymentSettingsUrl" target="_blank">Payment Settings</a>
        to enable a gateway.
      </p>
    </div>

    <!-- Custom Labels ──────────────────────────────────────────────────────── -->
    <template v-if="activeGateways.length">
      <div class="pmf-section">
        <div class="pmf-section__head">
          <span class="pmf-section__head-icon dashicons dashicons-tag"></span>
          <span class="pmf-section__head-label">Custom Labels</span>
        </div>
        <p class="pmf-hint">Override the default label shown to customers for each gateway.</p>
        <div
          v-for="gw in activeGateways"
          :key="'lbl_' + gw.key"
          class="pmf-label-row"
        >
          <span class="pmf-gw-badge pmf-gw-badge--sm" :class="'pmf-gw-badge--' + gw.key">{{ gw.label }}</span>
          <el-input
            :value="getLabel(gw.key)"
            :placeholder="gw.label"
            size="small"
            @input="setLabel(gw.key, $event)"
          />
        </div>
      </div>
    </template>

    <!-- Description ────────────────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-editor-paragraph"></span>
        <span class="pmf-section__head-label">Description</span>
      </div>
      <el-input
        :value="fieldSettings.description || ''"
        type="textarea"
        :rows="2"
        size="small"
        placeholder="Optional instructions shown above payment options…"
        @input="updateField('description', $event)"
      />
    </div>

  </div>
</template>

<script>
import option_field from '../../../mixin/option-field.js';

export default {
  name: 'field_payment_method',
  mixins: [option_field],
  computed: {
    fieldSettings() {
      return this.field.payment_settings || {};
    },

    allGatewayMeta() {
      return [
        { key: 'stripe',   label: 'Stripe / Card' },
        { key: 'paypal',   label: 'PayPal' },
        { key: 'razorpay', label: 'Razorpay' },
        { key: 'mollie',   label: 'Mollie' },
      ];
    },

    enabledMap() {
      return window.contactum && window.contactum.payment_gateways
        ? window.contactum.payment_gateways
        : {};
    },

    activeGateways() {
      return this.allGatewayMeta.filter(gw => this.enabledMap[gw.key] === true);
    },

    paymentSettingsUrl() {
      return window.contactum && window.contactum.admin_url
        ? window.contactum.admin_url + '?page=contactum-settings#payment_settings'
        : '#';
    },
  },

  methods: {
    getLabel(key) {
      return (this.fieldSettings.labels || {})[key] || '';
    },

    setLabel(key, value) {
      const labels = Object.assign({}, this.fieldSettings.labels || {});
      labels[key] = value;
      this.updateField('labels', labels);
    },

    updateField(key, value) {
      const settings = Object.assign({}, this.fieldSettings, { [key]: value });
      this.$store.dispatch('updateFieldSettings', {
        field: this.field,
        key:   'payment_settings',
        value: settings,
      });
    },
  },
};
</script>

<style scoped>
.pmf-wrap { font-size: 13px; }

.pmf-section {
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ebeef5;
}
.pmf-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

.pmf-section__head {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-bottom: 12px;
}
.pmf-section__head-icon {
  font-size: 14px;
  width: 14px;
  height: 14px;
  color: #909399;
}
.pmf-section__head-label {
  font-size: 11.5px;
  font-weight: 700;
  color: #606266;
  text-transform: uppercase;
  letter-spacing: .5px;
}

/* Gateway status list */
.pmf-gateway-status-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.pmf-gateway-status-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.pmf-gateway-status-item__on {
  font-size: 16px;
  width: 16px;
  height: 16px;
  color: #67c23a;
}

/* Gateway badges */
.pmf-gw-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
  background: #f5f7fa;
  color: #909399;
  white-space: nowrap;
}
.pmf-gw-badge--stripe   { background: #f0eeff; color: #6e4bcc; }
.pmf-gw-badge--paypal   { background: #fdf6ec; color: #e6a23c; }
.pmf-gw-badge--razorpay { background: #ecf5ff; color: #409eff; }
.pmf-gw-badge--mollie   { background: #f0f9eb; color: #67c23a; }
.pmf-gw-badge--sm { font-size: 10px; padding: 1px 6px; }

/* Label row */
.pmf-label-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.pmf-label-row:last-child { margin-bottom: 0; }

/* Misc */
.pmf-hint {
  margin: 0 0 10px;
  font-size: 11.5px;
  color: #909399;
  line-height: 1.4;
}
.pmf-notice {
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 0;
  padding: 8px 10px;
  border-radius: 6px;
  font-size: 12px;
  line-height: 1.5;
}
.pmf-notice .dashicons { font-size: 14px; width: 14px; height: 14px; flex-shrink: 0; }
.pmf-notice--warn { background: #fdf6ec; color: #e6a23c; border: 1px solid #f5dab1; }
.pmf-notice a { color: inherit; font-weight: 600; }
</style>
