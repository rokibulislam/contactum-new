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
      <el-button type="primary" :loading="saving" icon="el-icon-check" @click="saveSettings">
        Save Settings
      </el-button>
    </div>

    <div class="pms-body">

      <!-- Sidebar: tabs ─────────────────────────────────────────────────────── -->
      <div class="pms-sidebar">
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
      </div>

      <div class="pms-content">

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

      <!-- ── Authorize.net ─────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'authorizenet'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--authorizenet">Authorize.net</span>
              <span class="pms-status" :class="settings.authorizenet.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.authorizenet.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.authorizenet.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.authorizenet.test_mode }"
                  @click="settings.authorizenet.test_mode = true"
                >Sandbox</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.authorizenet.test_mode }"
                  @click="settings.authorizenet.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.authorizenet.test_mode">
              <div class="pms-field">
                <label class="pms-label">Sandbox API Login ID</label>
                <el-input v-model="settings.authorizenet.sandbox_login_id" placeholder="Sandbox API Login ID" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Sandbox Transaction Key</label>
                <el-input v-model="settings.authorizenet.sandbox_transaction_key" type="password" show-password placeholder="Sandbox Transaction Key" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live API Login ID</label>
                <el-input v-model="settings.authorizenet.live_login_id" placeholder="Live API Login ID" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Transaction Key</label>
                <el-input v-model="settings.authorizenet.live_transaction_key" type="password" show-password placeholder="Live Transaction Key" />
              </div>
            </template>

            <div class="pms-field">
              <label class="pms-label">Signature Key</label>
              <el-input v-model="settings.authorizenet.signature_key" type="password" show-password placeholder="Signature Key" />
              <p class="pms-hint">
                Found in the Merchant Interface under Account → Settings → Security Settings → General Security Settings → Signature Key.
                Used to verify the webhook below.
              </p>
            </div>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_authorizenet_webhook</code>
              <p class="pms-hint">
                Add this endpoint in the Merchant Interface under Account → Settings → Webhooks, subscribed to the
                authCapture/void/refund transaction events.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Square ────────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'square'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--square">Square</span>
              <span class="pms-status" :class="settings.square.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.square.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.square.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.square.test_mode }"
                  @click="settings.square.test_mode = true"
                >Sandbox</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.square.test_mode }"
                  @click="settings.square.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.square.test_mode">
              <div class="pms-field">
                <label class="pms-label">Sandbox Access Token</label>
                <el-input v-model="settings.square.sandbox_access_token" type="password" show-password placeholder="EAAA..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Sandbox Location ID</label>
                <el-input v-model="settings.square.sandbox_location_id" placeholder="Sandbox Location ID" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Access Token</label>
                <el-input v-model="settings.square.live_access_token" type="password" show-password placeholder="EAAA..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Location ID</label>
                <el-input v-model="settings.square.live_location_id" placeholder="Live Location ID" />
              </div>
            </template>
            <p class="pms-hint">Found in the Square Developer Dashboard under your application's Credentials and Locations pages.</p>

            <div class="pms-field">
              <label class="pms-label">Webhook Signature Key</label>
              <el-input v-model="settings.square.webhook_signature_key" type="password" show-password placeholder="Signature Key" />
            </div>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_square_webhook</code>
              <p class="pms-hint">
                Add this exact URL as a webhook endpoint in the Square Developer Dashboard, subscribed to
                <code class="pms-code">payment.created</code> and <code class="pms-code">payment.updated</code>.
                Square signs each request using the exact URL you register, so it must match this one precisely.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Paystack ──────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'paystack'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--paystack">Paystack</span>
              <span class="pms-status" :class="settings.paystack.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.paystack.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.paystack.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.paystack.test_mode }"
                  @click="settings.paystack.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.paystack.test_mode }"
                  @click="settings.paystack.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.paystack.test_mode">
              <div class="pms-field">
                <label class="pms-label">Test Public Key</label>
                <el-input v-model="settings.paystack.test_public_key" placeholder="pk_test_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Test Secret Key</label>
                <el-input v-model="settings.paystack.test_secret_key" type="password" show-password placeholder="sk_test_..." />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Public Key</label>
                <el-input v-model="settings.paystack.live_public_key" placeholder="pk_live_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Secret Key</label>
                <el-input v-model="settings.paystack.live_secret_key" type="password" show-password placeholder="sk_live_..." />
              </div>
            </template>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_paystack_webhook</code>
              <p class="pms-hint">
                Add this URL in the Paystack Dashboard under Settings → API Keys &amp; Webhooks. Paystack signs
                webhooks with your Secret Key above — no separate signing secret to configure.
              </p>
            </div>

            <div class="pms-field">
              <p class="pms-hint">
                Paystack requires a customer email for every transaction. The first Email field on the form is
                used automatically; if the form has none, the site admin email is used instead.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Payrexx ───────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'payrexx'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--payrexx">Payrexx</span>
              <span class="pms-status" :class="settings.payrexx.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.payrexx.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.payrexx.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Instance</label>
              <el-input v-model="settings.payrexx.instance" placeholder="your-instance-name" />
              <p class="pms-hint">The subdomain of your Payrexx account, e.g. "your-instance-name" from your-instance-name.payrexx.com.</p>
            </div>
            <div class="pms-field">
              <label class="pms-label">API Secret</label>
              <el-input v-model="settings.payrexx.api_secret" type="password" show-password placeholder="API Secret" />
              <p class="pms-hint">Found in the Payrexx Dashboard under Account → API Keys. Payrexx has no separate sandbox environment — test payments are made through this same instance.</p>
            </div>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_payrexx_webhook</code>
              <p class="pms-hint">Add this URL as a webhook in the Payrexx Dashboard under Account → Webhooks.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Moneris ───────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'moneris'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--moneris">Moneris</span>
              <span class="pms-status" :class="settings.moneris.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.moneris.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.moneris.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Region</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.moneris.region === 'ca' }"
                  @click="settings.moneris.region = 'ca'"
                >Canada</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.moneris.region === 'us' }"
                  @click="settings.moneris.region = 'us'"
                >US</button>
              </div>
            </div>

            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.moneris.test_mode }"
                  @click="settings.moneris.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.moneris.test_mode }"
                  @click="settings.moneris.test_mode = false"
                >Live</button>
              </div>
            </div>

            <div class="pms-field">
              <label class="pms-label">Store ID</label>
              <el-input v-model="settings.moneris.store_id" placeholder="Store ID" />
            </div>
            <div class="pms-field">
              <label class="pms-label">Hosted Paypage Key</label>
              <el-input v-model="settings.moneris.hpp_key" type="password" show-password placeholder="Hosted Paypage Key" />
              <p class="pms-hint">Found in the Moneris Merchant Resource Center under Admin → Hosted Config → Hosted Paypage Configuration.</p>
            </div>

            <div class="pms-field">
              <label class="pms-label">Response URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_moneris_webhook</code>
              <p class="pms-hint">
                Set your Hosted Paypage profile's approved/declined redirect URLs to point back to your own site
                (e.g. your Success/Cancel Redirect URLs above) in the Moneris Merchant Resource Center — Moneris
                doesn't accept those as per-transaction parameters the way the other gateways here do.
              </p>
            </div>

            <div class="pms-field">
              <p class="pms-hint">
                <strong>Note:</strong> unlike the other gateways here, Moneris payment confirmation is not
                independently re-verified against the Moneris API — it trusts the result Moneris posts back
                directly. Confirm this meets your requirements before processing real transactions.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Xendit ────────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'xendit'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--xendit">Xendit</span>
              <span class="pms-status" :class="settings.xendit.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.xendit.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.xendit.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.xendit.test_mode }"
                  @click="settings.xendit.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.xendit.test_mode }"
                  @click="settings.xendit.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.xendit.test_mode">
              <div class="pms-field">
                <label class="pms-label">Test Secret Key</label>
                <el-input v-model="settings.xendit.test_secret_key" type="password" show-password placeholder="xnd_development_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Test Webhook Verification Token</label>
                <el-input v-model="settings.xendit.test_webhook_token" type="password" show-password placeholder="Webhook Verification Token" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Secret Key</label>
                <el-input v-model="settings.xendit.live_secret_key" type="password" show-password placeholder="xnd_production_..." />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Webhook Verification Token</label>
                <el-input v-model="settings.xendit.live_webhook_token" type="password" show-password placeholder="Webhook Verification Token" />
              </div>
            </template>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_xendit_webhook</code>
              <p class="pms-hint">
                Add this URL as a Callback URL in the Xendit Dashboard under Settings → Developers → Webhooks,
                for the Invoice "invoice.paid" event, and paste the matching Verification Token above.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Flutterwave ───────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'flutterwave'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--flutterwave">Flutterwave</span>
              <span class="pms-status" :class="settings.flutterwave.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.flutterwave.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.flutterwave.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.flutterwave.test_mode }"
                  @click="settings.flutterwave.test_mode = true"
                >Test</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.flutterwave.test_mode }"
                  @click="settings.flutterwave.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.flutterwave.test_mode">
              <div class="pms-field">
                <label class="pms-label">Test Secret Key</label>
                <el-input v-model="settings.flutterwave.test_secret_key" type="password" show-password placeholder="FLWSECK_TEST-..." />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Secret Key</label>
                <el-input v-model="settings.flutterwave.live_secret_key" type="password" show-password placeholder="FLWSECK-..." />
              </div>
            </template>

            <div class="pms-field">
              <label class="pms-label">Secret Hash</label>
              <el-input v-model="settings.flutterwave.secret_hash" type="password" show-password placeholder="Secret Hash" />
              <p class="pms-hint">Set this same value as your webhook secret hash in the Flutterwave Dashboard under Settings → Webhooks — used for both test and live.</p>
            </div>

            <div class="pms-field">
              <label class="pms-label">Webhook URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_flutterwave_webhook</code>
              <p class="pms-hint">Add this URL as your Webhook URL in the Flutterwave Dashboard under Settings → Webhooks.</p>
            </div>

            <div class="pms-field">
              <p class="pms-hint">
                Flutterwave requires a customer email for every transaction. The first Email field on the form is
                used automatically; if the form has none, the site admin email is used instead.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Billplz ───────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'billplz'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--billplz">Billplz</span>
              <span class="pms-status" :class="settings.billplz.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.billplz.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.billplz.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.billplz.test_mode }"
                  @click="settings.billplz.test_mode = true"
                >Sandbox</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.billplz.test_mode }"
                  @click="settings.billplz.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.billplz.test_mode">
              <div class="pms-field">
                <label class="pms-label">Sandbox API Secret Key</label>
                <el-input v-model="settings.billplz.test_api_key" type="password" show-password placeholder="Sandbox API Secret Key" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Sandbox Collection ID</label>
                <el-input v-model="settings.billplz.test_collection_id" placeholder="Sandbox Collection ID" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live API Secret Key</label>
                <el-input v-model="settings.billplz.live_api_key" type="password" show-password placeholder="Live API Secret Key" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Collection ID</label>
                <el-input v-model="settings.billplz.live_collection_id" placeholder="Live Collection ID" />
              </div>
            </template>
            <p class="pms-hint">Bills must belong to a Collection — create one in the Billplz Dashboard first and paste its ID here.</p>

            <div class="pms-field">
              <label class="pms-label">X-Signature Key</label>
              <el-input v-model="settings.billplz.x_signature_key" type="password" show-password placeholder="X-Signature Key" />
              <p class="pms-hint">Found in the Billplz Dashboard under Settings → API Keys — used to verify the callback below.</p>
            </div>

            <div class="pms-field">
              <label class="pms-label">Callback URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_billplz_webhook</code>
              <p class="pms-hint">Bills are created with this as their callback_url automatically — no manual configuration needed in the Billplz Dashboard.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── SSLCommerz ────────────────────────────────────────────────────── -->
      <div v-show="activeTab === 'sslcommerz'" class="pms-section">
        <div class="pms-card">
          <div class="pms-card__head">
            <div class="pms-card__head-left">
              <span class="pms-gw-badge pms-gw-badge--sslcommerz">SSLCommerz</span>
              <span class="pms-status" :class="settings.sslcommerz.enabled ? 'pms-status--on' : 'pms-status--off'">
                {{ settings.sslcommerz.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </div>
            <el-switch v-model="settings.sslcommerz.enabled" />
          </div>
          <div class="pms-card__body">
            <div class="pms-field">
              <label class="pms-label">Mode</label>
              <div class="pms-mode-toggle">
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': settings.sslcommerz.test_mode }"
                  @click="settings.sslcommerz.test_mode = true"
                >Sandbox</button>
                <button
                  class="pms-mode-btn"
                  :class="{ 'pms-mode-btn--active': !settings.sslcommerz.test_mode }"
                  @click="settings.sslcommerz.test_mode = false"
                >Live</button>
              </div>
            </div>

            <template v-if="settings.sslcommerz.test_mode">
              <div class="pms-field">
                <label class="pms-label">Sandbox Store ID</label>
                <el-input v-model="settings.sslcommerz.test_store_id" placeholder="Sandbox Store ID" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Sandbox Store Password</label>
                <el-input v-model="settings.sslcommerz.test_store_password" type="password" show-password placeholder="Sandbox Store Password" />
              </div>
            </template>
            <template v-else>
              <div class="pms-field">
                <label class="pms-label">Live Store ID</label>
                <el-input v-model="settings.sslcommerz.live_store_id" placeholder="Live Store ID" />
              </div>
              <div class="pms-field">
                <label class="pms-label">Live Store Password</label>
                <el-input v-model="settings.sslcommerz.live_store_password" type="password" show-password placeholder="Live Store Password" />
              </div>
            </template>
            <p class="pms-hint">Sandbox and Live are separate SSLCommerz merchant accounts with their own credentials.</p>

            <div class="pms-field">
              <label class="pms-label">IPN URL</label>
              <code class="pms-code">{{ ajaxUrl }}?action=contactum_sslcommerz_webhook</code>
              <p class="pms-hint">Sessions are created with this as their ipn_url automatically — no manual configuration needed in the SSLCommerz panel.</p>
            </div>

            <div class="pms-field">
              <p class="pms-hint">
                SSLCommerz requires customer name, email, phone, and address details. Name/Email/Phone/Country
                fields on the form are used automatically where present; address details this plugin can't
                reliably detect are sent as placeholder values, which SSLCommerz accepts as long as they're non-empty.
              </p>
            </div>
          </div>
        </div>
      </div>

    </template>

      </div>

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
        { key: 'authorizenet', label: 'Authorize.net', icon: 'dashicons-shield' },
        { key: 'square', label: 'Square', icon: 'dashicons-screenoptions' },
        { key: 'paystack', label: 'Paystack', icon: 'dashicons-tickets-alt' },
        { key: 'payrexx', label: 'Payrexx', icon: 'dashicons-flag' },
        { key: 'moneris', label: 'Moneris', icon: 'dashicons-bank' },
        { key: 'xendit', label: 'Xendit', icon: 'dashicons-admin-site-alt3' },
        { key: 'flutterwave', label: 'Flutterwave', icon: 'dashicons-chart-area' },
        { key: 'billplz', label: 'Billplz', icon: 'dashicons-media-text' },
        { key: 'sslcommerz', label: 'SSLCommerz', icon: 'dashicons-shield-alt' },
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
      // Required credential fields per gateway/mode — kept in sync with
      // get_active_gateways() in class-field-payment-method.php, which is
      // the authoritative definition of "active" on the PHP side. Used so
      // the tab dot reflects enabled AND fully configured, not just enabled.
      credentialFields: {
        stripe:       { test: ['test_publishable_key', 'test_secret_key'], live: ['live_publishable_key', 'live_secret_key'] },
        paypal:       { test: ['sandbox_client_id', 'sandbox_client_secret'], live: ['live_client_id', 'live_client_secret'] },
        razorpay:     { test: ['test_key_id', 'test_key_secret'], live: ['live_key_id', 'live_key_secret'] },
        mollie:       { test: ['test_api_key'], live: ['live_api_key'] },
        authorizenet: { test: ['sandbox_login_id', 'sandbox_transaction_key'], live: ['live_login_id', 'live_transaction_key'] },
        square:       { test: ['sandbox_access_token', 'sandbox_location_id'], live: ['live_access_token', 'live_location_id'] },
        paystack:     { test: ['test_secret_key', 'test_public_key'], live: ['live_secret_key', 'live_public_key'] },
        payrexx:      { test: ['instance', 'api_secret'], live: ['instance', 'api_secret'] },
        moneris:      { test: ['store_id', 'hpp_key'], live: ['store_id', 'hpp_key'] },
        xendit:       { test: ['test_secret_key'], live: ['live_secret_key'] },
        flutterwave:  { test: ['test_secret_key'], live: ['live_secret_key'] },
        billplz:      { test: ['test_api_key', 'test_collection_id'], live: ['live_api_key', 'live_collection_id'] },
        sslcommerz:   { test: ['test_store_id', 'test_store_password'], live: ['live_store_id', 'live_store_password'] },
      },
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
        authorizenet: {
          enabled:                  false,
          test_mode:                true,
          sandbox_login_id:         '',
          sandbox_transaction_key:  '',
          live_login_id:            '',
          live_transaction_key:     '',
          signature_key:            '',
        },
        square: {
          enabled:                false,
          test_mode:              true,
          sandbox_access_token:   '',
          sandbox_location_id:    '',
          live_access_token:      '',
          live_location_id:       '',
          webhook_signature_key:  '',
        },
        paystack: {
          enabled:          false,
          test_mode:        true,
          test_secret_key:  '',
          test_public_key:  '',
          live_secret_key:  '',
          live_public_key:  '',
        },
        payrexx: {
          enabled:    false,
          instance:   '',
          api_secret: '',
        },
        moneris: {
          enabled:    false,
          test_mode:  true,
          region:     'ca',
          store_id:   '',
          hpp_key:    '',
        },
        xendit: {
          enabled:            false,
          test_mode:          true,
          test_secret_key:    '',
          test_webhook_token: '',
          live_secret_key:    '',
          live_webhook_token: '',
        },
        flutterwave: {
          enabled:          false,
          test_mode:        true,
          test_secret_key:  '',
          live_secret_key:  '',
          secret_hash:      '',
        },
        billplz: {
          enabled:             false,
          test_mode:           true,
          test_api_key:        '',
          test_collection_id:  '',
          live_api_key:        '',
          live_collection_id:  '',
          x_signature_key:     '',
        },
        sslcommerz: {
          enabled:              false,
          test_mode:            true,
          test_store_id:        '',
          test_store_password:  '',
          live_store_id:        '',
          live_store_password:  '',
        },
      },
    };
  },
  mounted() {
    this.loadSettings();
  },
  methods: {
    isGatewayEnabled(tab) {
      const gw = this.settings[tab];
      if (tab === 'general' || !gw || !gw.enabled) {
        return false;
      }

      const creds = this.credentialFields[tab];
      if (!creds) {
        return true;
      }

      const mode = gw.test_mode === false ? 'live' : 'test';
      return creds[mode].every(field => !!gw[field]);
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
.pms-wrap { max-width: 900px; }

/* ── Header ──────────────────────────────────────────── */
.pms-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
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

/* ── Body layout (sidebar + content) ──────────────────── */
.pms-body {
  display: flex;
  align-items: flex-start;
  gap: 24px;
}

.pms-content {
  flex: 1;
  min-width: 0;
}

/* ── Sidebar (tabs) ─────────────────────────────────────── */
.pms-sidebar {
  display: flex;
  flex-direction: column;
  flex: 0 0 190px;
  padding-right: 16px;
  border-right: 1px solid #dcdfe6;
}

.pms-tabs {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.pms-tab {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 9px 12px;
  font-size: 13px;
  font-weight: 500;
  color: #909399;
  background: none;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  text-align: left;
  transition: color .15s, background-color .15s;
}
.pms-tab .dashicons { font-size: 15px; width: 15px; height: 15px; flex-shrink: 0; }
.pms-tab:hover { color: #606266; background: #f5f7fa; }
.pms-tab--active { color: #409eff; background: #ecf5ff; }
.pms-tab__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #67c23a;
  margin-left: auto;
  flex-shrink: 0;
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
.pms-gw-badge--authorizenet { background: #fef0f0; color: #f56c6c; }
.pms-gw-badge--square { background: #eafaf1; color: #2f9e44; }
.pms-gw-badge--paystack { background: #e8f5e9; color: #00a86b; }
.pms-gw-badge--payrexx { background: #fff4e6; color: #e8590c; }
.pms-gw-badge--moneris { background: #e7f5ff; color: #1971c2; }
.pms-gw-badge--xendit { background: #edf2ff; color: #4263eb; }
.pms-gw-badge--flutterwave { background: #fff9db; color: #f08c00; }
.pms-gw-badge--billplz { background: #e6fcf5; color: #0ca678; }
.pms-gw-badge--sslcommerz { background: #eef2ff; color: #4338ca; }

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
</style>
