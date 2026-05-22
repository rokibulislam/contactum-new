<template>
  <div class="pmf-wrap">

    <!-- Gateways ─────────────────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-money-alt"></span>
        <span class="pmf-section__head-label">Accepted Gateways</span>
      </div>
      <div class="pmf-gateway-list">
        <label
          v-for="gw in availableGateways"
          :key="gw.key"
          class="pmf-gateway-item"
          :class="{ 'pmf-gateway-item--active': isGatewayChecked(gw.key) }"
        >
          <input
            type="checkbox"
            class="pmf-gateway-item__cb"
            :value="gw.key"
            :checked="isGatewayChecked(gw.key)"
            @change="toggleGateway(gw.key, $event.target.checked)"
          />
          <span class="pmf-gw-badge" :class="'pmf-gw-badge--' + gw.key">{{ gw.label }}</span>
          <span class="pmf-gateway-item__desc">{{ gw.desc }}</span>
          <span
            v-if="!isConfigured(gw.key)"
            class="pmf-gateway-item__warn"
            title="Gateway not configured — add API keys in Payment Settings"
          >
            <span class="dashicons dashicons-warning"></span>
          </span>
          <span class="pmf-gateway-item__check dashicons dashicons-yes-alt"></span>
        </label>
      </div>
      <p v-if="enabledGateways.length === 0" class="pmf-notice pmf-notice--warn">
        <span class="dashicons dashicons-info"></span>
        Enable at least one gateway so this field renders.
      </p>
    </div>

    <!-- Gateway Labels ───────────────────────────────────────────────────── -->
    <template v-if="enabledGateways.length > 0">
      <div class="pmf-section">
        <div class="pmf-section__head">
          <span class="pmf-section__head-icon dashicons dashicons-tag"></span>
          <span class="pmf-section__head-label">Gateway Labels</span>
        </div>
        <p class="pmf-hint">Customize the label shown to customers for each method.</p>
        <div
          v-for="gw in enabledGatewaysData"
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

    <!-- Currency ─────────────────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-admin-generic"></span>
        <span class="pmf-section__head-label">Currency</span>
      </div>
      <el-select
        :value="fieldSettings.currency || 'USD'"
        size="small"
        style="width:100%"
        @change="updateField('currency', $event)"
      >
        <el-option v-for="c in currencies" :key="c.code" :label="`${c.name} (${c.code})`" :value="c.code" />
      </el-select>
    </div>

    <!-- Amount ───────────────────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-chart-bar"></span>
        <span class="pmf-section__head-label">Amount</span>
      </div>

      <div class="pmf-inline-row">
        <label class="pmf-inline-label">Source</label>
        <el-select
          :value="fieldSettings.amount_type || 'fixed'"
          size="small"
          style="width:160px"
          @change="updateField('amount_type', $event)"
        >
          <el-option label="Fixed Amount" value="fixed" />
          <el-option label="From a Field" value="field" />
        </el-select>
      </div>

      <div v-if="(fieldSettings.amount_type || 'fixed') === 'fixed'" class="pmf-inline-row">
        <label class="pmf-inline-label">Amount</label>
        <el-input
          :value="fieldSettings.fixed_amount || ''"
          size="small"
          type="number"
          min="0"
          step="0.01"
          placeholder="e.g. 49.99"
          style="width:160px"
          @input="updateField('fixed_amount', $event)"
        />
      </div>

      <div v-if="fieldSettings.amount_type === 'field'" class="pmf-inline-row">
        <label class="pmf-inline-label">Field</label>
        <el-select
          :value="fieldSettings.amount_field || ''"
          size="small"
          style="width:160px"
          @change="updateField('amount_field', $event)"
        >
          <el-option
            v-for="f in numericFields"
            :key="f.name"
            :label="f.admin_label || f.label || f.name"
            :value="f.name"
          />
        </el-select>
      </div>
    </div>

    <!-- Appearance ───────────────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-art"></span>
        <span class="pmf-section__head-label">Appearance</span>
      </div>

      <div class="pmf-inline-row">
        <label class="pmf-inline-label">Button Label</label>
        <el-input
          :value="fieldSettings.button_label || ''"
          size="small"
          placeholder="Pay Now"
          @input="updateField('button_label', $event)"
        />
      </div>

      <div class="pmf-field">
        <label class="pmf-label">Instructions / Description</label>
        <el-input
          :value="fieldSettings.description || ''"
          type="textarea"
          :rows="2"
          size="small"
          placeholder="Optional text shown above the payment options…"
          @input="updateField('description', $event)"
        />
      </div>
    </div>

    <!-- Behavior ─────────────────────────────────────────────────────────── -->
    <div class="pmf-section">
      <div class="pmf-section__head">
        <span class="pmf-section__head-icon dashicons dashicons-controls-forward"></span>
        <span class="pmf-section__head-label">After Payment</span>
      </div>

      <div class="pmf-inline-row">
        <label class="pmf-inline-label">On Success</label>
        <el-select
          :value="fieldSettings.on_success || 'message'"
          size="small"
          style="width:160px"
          @change="updateField('on_success', $event)"
        >
          <el-option label="Show message" value="message" />
          <el-option label="Redirect to URL" value="redirect" />
        </el-select>
      </div>

      <div v-if="(fieldSettings.on_success || 'message') === 'message'" class="pmf-field">
        <label class="pmf-label">Success Message</label>
        <el-input
          :value="fieldSettings.success_message || ''"
          type="textarea"
          :rows="2"
          size="small"
          placeholder="Thank you! Your payment was successful."
          @input="updateField('success_message', $event)"
        />
      </div>

      <div v-if="fieldSettings.on_success === 'redirect'" class="pmf-field">
        <label class="pmf-label">Redirect URL</label>
        <el-input
          :value="fieldSettings.success_redirect || ''"
          size="small"
          placeholder="https://yoursite.com/thank-you"
          @input="updateField('success_redirect', $event)"
        />
      </div>
    </div>

  </div>
</template>

<script>
import option_field from '../../../mixin/option-field.js';

export default {
  name: 'field_payment_method',
  mixins: [option_field],
  computed: {
    availableGateways() {
      return [
        { key: 'stripe',   label: 'Stripe',   desc: 'Cards, Apple Pay, Google Pay' },
        { key: 'paypal',   label: 'PayPal',   desc: 'PayPal balance & cards' },
        { key: 'razorpay', label: 'Razorpay', desc: 'UPI, cards, wallets' },
      ];
    },

    fieldSettings() {
      return this.field.payment_settings || {};
    },

    enabledGateways() {
      return this.fieldSettings.gateways || [];
    },

    enabledGatewaysData() {
      return this.availableGateways.filter(gw => this.enabledGateways.includes(gw.key));
    },

    numericFields() {
      const all = window.contactum && window.contactum.form_fields
        ? Object.values(window.contactum.form_fields)
        : [];
      const numericTypes = ['number', 'total', 'single_product', 'multiple_product', 'range_slider'];
      return all.filter(f => numericTypes.includes(f.element) && f.name !== this.field.name);
    },
  },
  data() {
    return {
      currencies: [
        { code: 'USD', name: 'US Dollar' },
        { code: 'EUR', name: 'Euro' },
        { code: 'GBP', name: 'British Pound' },
        { code: 'INR', name: 'Indian Rupee' },
        { code: 'AUD', name: 'Australian Dollar' },
        { code: 'CAD', name: 'Canadian Dollar' },
        { code: 'SGD', name: 'Singapore Dollar' },
        { code: 'JPY', name: 'Japanese Yen' },
        { code: 'AED', name: 'UAE Dirham' },
        { code: 'BDT', name: 'Bangladeshi Taka' },
      ],
    };
  },
  methods: {
    isGatewayChecked(key) {
      return (this.fieldSettings.gateways || []).includes(key);
    },

    isConfigured(key) {
      const gws = window.contactum && window.contactum.payment_gateways
        ? window.contactum.payment_gateways
        : {};
      return gws[key] && gws[key].configured;
    },

    toggleGateway(key, checked) {
      const current = [...(this.fieldSettings.gateways || [])];
      if (checked && !current.includes(key)) {
        current.push(key);
      } else if (!checked) {
        const idx = current.indexOf(key);
        if (idx !== -1) current.splice(idx, 1);
      }
      this.updateField('gateways', current);
    },

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

/* ── Section ───────────────────────────────────────────── */
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

/* ── Gateway list ──────────────────────────────────────── */
.pmf-gateway-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.pmf-gateway-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border: 1.5px solid #dcdfe6;
  border-radius: 8px;
  cursor: pointer;
  transition: border-color .12s, background .12s;
  background: #fff;
}
.pmf-gateway-item--active {
  border-color: #409eff;
  background: #ecf5ff;
}
.pmf-gateway-item__cb { display: none; }

.pmf-gateway-item__desc {
  font-size: 11.5px;
  color: #909399;
  flex: 1;
}
.pmf-gateway-item__warn {
  color: #e6a23c;
  font-size: 14px;
  display: flex;
  align-items: center;
}
.pmf-gateway-item__warn .dashicons { font-size: 14px; width: 14px; height: 14px; }
.pmf-gateway-item__check {
  font-size: 16px;
  width: 16px;
  height: 16px;
  color: #c0c4cc;
  transition: color .12s;
}
.pmf-gateway-item--active .pmf-gateway-item__check { color: #409eff; }

/* ── Gateway badges ────────────────────────────────────── */
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
.pmf-gw-badge--sm { font-size: 10px; padding: 1px 6px; }

/* ── Label row ─────────────────────────────────────────── */
.pmf-label-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.pmf-label-row:last-child { margin-bottom: 0; }

/* ── Inline row ────────────────────────────────────────── */
.pmf-inline-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.pmf-inline-label {
  font-size: 12.5px;
  font-weight: 500;
  color: #606266;
  width: 68px;
  flex-shrink: 0;
}

/* ── Regular field ─────────────────────────────────────── */
.pmf-field { margin-bottom: 10px; }
.pmf-field:last-child { margin-bottom: 0; }
.pmf-label {
  display: block;
  font-size: 12.5px;
  font-weight: 500;
  color: #606266;
  margin-bottom: 5px;
}

/* ── Misc ──────────────────────────────────────────────── */
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
  margin: 8px 0 0;
  padding: 7px 10px;
  border-radius: 6px;
  font-size: 12px;
}
.pmf-notice .dashicons { font-size: 14px; width: 14px; height: 14px; }
.pmf-notice--warn { background: #fdf6ec; color: #e6a23c; border: 1px solid #f5dab1; }
</style>
