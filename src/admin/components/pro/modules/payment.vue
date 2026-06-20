<template>
  <div class="pms-wrap">

    <!-- Header ─────────────────────────────────────────────────────────────── -->
    <div class="pms-header">
      <div class="pms-header__left">
        <div class="pms-header__icon">
          <span class="dashicons dashicons-money-alt"></span>
        </div>
        <div>
          <h2 class="pms-header__title">Payment Gateways</h2>
          <p class="pms-header__sub">Configure payment methods for your forms</p>
        </div>
      </div>
    </div>

    <!-- Tabs ────────────────────────────────────────────────────────────────── -->
    <div class="pms-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        class="pms-tab"
        :class="{ 'pms-tab--active': activeTab === tab.key }"
        @click="activeTab = tab.key"
      >
        <span class="dashicons" :class="tab.icon"></span>
        {{ tab.label }}
        <span v-if="isGatewayEnabled(tab.key)" class="pms-tab__dot"></span>
      </button>
    </div>

    <div v-if="loading" class="pms-loading">
      <el-skeleton :rows="6" animated />
    </div>

    <template v-else>

      <!-- ── General ───────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'general'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <h3 class="pms-card__title">General Settings</h3>
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Default Currency</label>
              <el-select v-model="settings.general.currency" style="width:200px">
                <el-option v-for="c in currencies" :key="c.code" :label="`${c.name} (${c.code})`" :value="c.code" />
              </el-select>
              <p class="pms-hint">Currency used for payment transactions.</p>
            </div>
            <div class="pms-field">
              <label class="pms-label">Success Redirect URL</label>
              <el-input v-model="settings.general.success_url" placeholder="https://yoursite.com/thank-you" />
              <p class="pms-hint">Redirect here after a successful payment. Leave empty to stay on page.</p>
            </div>
            <div class="pms-field">
              <label class="pms-label">Cancel / Failure Redirect URL</label>
              <el-input v-model="settings.general.cancel_url" placeholder="https://yoursite.com/payment-failed" />
              <p class="pms-hint">Redirect here when a payment is cancelled or fails.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Stripe ────────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'stripe'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--stripe">Stripe</span>
              <span class="pms-status" :class="settings.stripe.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.stripe.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.stripe.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.stripe.test_mode }"
                  @click="settings.stripe.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.stripe.test_mode }"
                  @click="settings.stripe.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.stripe.test_mode">
              <div class="pms-field">
                <label class="pms-label">Test Publishable Key</label>
                <el-input v-model="settings.stripe.test_publishable_key" placeholder="pk_test_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Test Secret Key</label>
                <el-input v-model="settings.stripe.test_secret_key" type="password" show-password placeholder="sk_test_..." />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Publishable Key</label>
                <el-input v-model="settings.stripe.live_publishable_key" placeholder="pk_live_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Secret Key</label>
                <el-input v-model="settings.stripe.live_secret_key" type="password" show-password placeholder="sk_live_..." />
              </div>
            </template>

            <div class="pms-field">
              <label class="pms-label">Webhook Secret</label>
              <el-input v-model="settings.stripe.webhook_secret" type="password" show-password placeholder="whsec_..." />
              <p class="pms-hint">
                Add this endpoint URL to your Stripe dashboard:
                <code class="pms-code">{{ ajaxUrl }}?action=contactum_stripe_webhook</code>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── PayPal ────────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'paypal'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--paypal">PayPal</span>
              <span class="pms-status" :class="settings.paypal.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.paypal.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.paypal.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.paypal.test_mode }"
                  @click="settings.paypal.test_mode = true"
                >Sandbox</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.paypal.test_mode }"
                  @click="settings.paypal.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.paypal.test_mode">
              <div class="pms-field">
                <label class="pms-label">Sandbox Client ID</label>
                <el-input v-model="settings.paypal.sandbox_client_id" placeholder="Sandbox Client ID" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Sandbox Client Secret</label>
                <el-input v-model="settings.paypal.sandbox_client_secret" type="password" show-password placeholder="Sandbox Client Secret" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Client ID</label>
                <el-input v-model="settings.paypal.live_client_id" placeholder="Live Client ID" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Client Secret</label>
                <el-input v-model="settings.paypal.live_client_secret" type="password" show-password placeholder="Live Client Secret" />
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- ── Razorpay ──────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'razorpay'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--razorpay">Razorpay</span>
              <span class="pms-status" :class="settings.razorpay.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.razorpay.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.razorpay.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.razorpay.test_mode }"
                  @click="settings.razorpay.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.razorpay.test_mode }"
                  @click="settings.razorpay.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.razorpay.test_mode">
              <div class="pms-field">
                <label class="pms-label">Test Key ID</label>
                <el-input v-model="settings.razorpay.test_key_id" placeholder="rzp_test_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Test Key Secret</label>
                <el-input v-model="settings.razorpay.test_key_secret" type="password" show-password placeholder="Test Key Secret" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Key ID</label>
                <el-input v-model="settings.razorpay.live_key_id" placeholder="rzp_live_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Key Secret</label>
                <el-input v-model="settings.razorpay.live_key_secret" type="password" show-password placeholder="Live Key Secret" />
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- ── Mollie ────────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'mollie'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--mollie">Mollie</span>
              <span class="pms-status" :class="settings.mollie.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.mollie.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.mollie.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.mollie.test_mode }"
                  @click="settings.mollie.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.mollie.test_mode }"
                  @click="settings.mollie.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.mollie.test_mode">
              <div class="pms-field">
                <label class="pms-label">Test API Key</label>
                <el-input v-model="settings.mollie.test_api_key" type="password" show-password placeholder="test_..." />
                <p class="pms-hint">Found in your Mollie dashboard under Developers → API keys.</p>
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live API Key</label>
                <el-input v-model="settings.mollie.live_api_key" type="password" show-password placeholder="live_..." />
                <p class="pms-hint">Found in your Mollie dashboard under Developers → API keys.</p>
              </div>
            </template>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_mollie_webhook</code>
              <p class="pms-hint">Mollie calls this URL automatically — no manual configuration needed in your Mollie dashboard.</p>
            </div>
          </div>
        </div>
      </div>

    </template>

    <!-- Save button ─────────────────────────────────────────────────────────── -->
    <div class="pms-footer">
      <el-button type="primary" :loading="saving" icon="el-icon-check" @click="saveSettings">
        Save Settings
      </el-button>
    </div>

  </div>
</template>

<script>
export default {
  name: 'PaymentSettings',
  data() {
    return {
      loading:   true,
      saving:    false,
      activeTab: 'general',
      ajaxUrl:   window.contactum ? window.contactum.ajaxurl : '',
      tabs: [
        { key: 'general',  label: 'General',  icon: 'dashicons-admin-settings' },
        { key: 'stripe',   label: 'Stripe',   icon: 'dashicons-money-alt' },
        { key: 'paypal',   label: 'PayPal',   icon: 'dashicons-cart' },
        { key: 'razorpay', label: 'Razorpay', icon: 'dashicons-controls-repeat' },
        { key: 'mollie',   label: 'Mollie',   icon: 'dashicons-awards' },
      ],
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
      settings: {
        general: {
          currency:    'USD',
          success_url: '',
          cancel_url:  '',
        },
        stripe: {
          enabled:              false,
          test_mode:            true,
          test_publishable_key: '',
          test_secret_key:      '',
          live_publishable_key: '',
          live_secret_key:      '',
          webhook_secret:       '',
        },
        paypal: {
          enabled:               false,
          test_mode:             true,
          sandbox_client_id:     '',
          sandbox_client_secret: '',
          live_client_id:        '',
          live_client_secret:    '',
        },
        razorpay: {
          enabled:          false,
          test_mode:        true,
          test_key_id:      '',
          test_key_secret:  '',
          live_key_id:      '',
          live_key_secret:  '',
        },
        mollie: {
          enabled:      false,
          test_mode:    true,
          test_api_key: '',
          live_api_key: '',
        },
      },
    };
  },
  mounted() {
    this.loadSettings();
  },
  methods: {
    isGatewayEnabled(tab) {
      return tab !== 'general' && this.settings[tab] && this.settings[tab].enabled;
    },

    loadSettings() {
      this.loading = true;
      jQuery.post(window.contactum.ajaxurl, {
        action: 'contactum_get_payment_settings',
        nonce:  window.contactum.nonce,
      }, (res) => {
        this.loading = false;
        if (res.success) {
          this.settings = Object.assign({}, this.settings, res.data);
        }
      });
    },

    saveSettings() {
      this.saving = true;
      jQuery.post(window.contactum.ajaxurl, {
        action:   'contactum_save_payment_settings',
        nonce:    window.contactum.nonce,
        settings: this.settings,
      }, (res) => {
        this.saving = false;
        if (res.success) {
          this.$notify({
            title:    'Saved',
            message:  res.data.message || 'Payment settings saved.',
            type:     'success',
            position: 'bottom-right',
          });
        } else {
          this.$notify({
            title:    'Error',
            message:  (res.data && res.data.message) || 'Failed to save settings.',
            type:     'error',
            position: 'bottom-right',
          });
        }
      });
    },
  },
};
</script>

<style scoped>
/* ── Wrap ────────────────────────────────────────────── */
.pms-wrap { max-width: 720px; }

/* ── Header ──────────────────────────────────────────── */
.pms-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}
.pms-header__left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.pms-header__icon {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  background: linear-gradient(135deg, #409eff 0%, #337ecc 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}
.pms-header__icon .dashicons {
  font-size: 22px;
  width: 22px;
  height: 22px;
  color: #fff;
}
.pms-header__title {
  margin: 0 0 2px;
  font-size: 18px;
  font-weight: 700;
  color: #303133;
}
.pms-header__sub { margin: 0; font-size: 13px; color: #909399; }

/* ── Tabs ────────────────────────────────────────────── */
.pms-tabs {
  display: flex;
  gap: 4px;
  border-bottom: 2px solid #dcdfe6;
  margin-bottom: 20px;
  padding-bottom: 0;
}
.pms-tab {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 500;
  color: #909399;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  cursor: pointer;
  transition: color .15s, border-color .15s;
  position: relative;
}
.pms-tab .dashicons { font-size: 15px; width: 15px; height: 15px; }
.pms-tab:hover { color: #606266; }
.pms-tab--active { color: #409eff; border-bottom-color: #409eff; }
.pms-tab__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #67c23a;
  position: absolute;
  top: 6px;
  right: 6px;
}

.pms-loading { padding: 20px 0; }

/* ── Card ────────────────────────────────────────────── */
.pms-card {
  background: #fff;
  border: 1px solid #dcdfe6;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 16px;
}
.pms-card__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  border-bottom: 1px solid #ebeef5;
  background: #f5f7fa;
}
.pms-card__head-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.pms-card__title {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #303133;
}
.pms-card__body { padding: 20px; }

/* Gateway badges */
.pms-gw-badge {
  display: inline-block;
  font-size: 12px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 4px;
}
.pms-gw-badge--stripe   { background: #f0eeff; color: #6e4bcc; }
.pms-gw-badge--paypal   { background: #fdf6ec; color: #e6a23c; }
.pms-gw-badge--razorpay { background: #ecf5ff; color: #409eff; }
.pms-gw-badge--mollie   { background: #f0f9eb; color: #67c23a; }

/* Status label */
.pms-status {
  font-size: 12px;
  font-weight: 600;
}
.pms-status--on  { color: #67c23a; }
.pms-status--off { color: #909399; }

/* ── Form fields ─────────────────────────────────────── */
.pms-field { margin-bottom: 18px; }
.pms-field:last-child { margin-bottom: 0; }
.pms-label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #606266;
  margin-bottom: 6px;
}
.pms-hint {
  margin: 6px 0 0;
  font-size: 12px;
  color: #909399;
  line-height: 1.4;
}
.pms-code {
  display: inline-block;
  font-size: 11px;
  font-family: 'SFMono-Regular', Consolas, monospace;
  background: #f5f7fa;
  color: #606266;
  padding: 2px 6px;
  border-radius: 3px;
  word-break: break-all;
}

/* ── Mode toggle ─────────────────────────────────────── */
.pms-mode-toggle {
  display: inline-flex;
  border: 1px solid #dcdfe6;
  border-radius: 7px;
  overflow: hidden;
}
.pms-mode-btn {
  padding: 6px 16px;
  font-size: 13px;
  font-weight: 500;
  background: #fff;
  border: none;
  color: #606266;
  cursor: pointer;
  transition: background .12s, color .12s;
}
.pms-mode-btn + .pms-mode-btn { border-left: 1px solid #dcdfe6; }
.pms-mode-btn--active {
  background: #409eff;
  color: #fff;
}

/* ── Footer ──────────────────────────────────────────── */
.pms-footer {
  padding-top: 4px;
}
</style>
