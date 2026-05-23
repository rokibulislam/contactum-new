<template>
  <div class="ctm-pm-preview">
    <p v-if="description" class="ctm-pm-preview__desc">{{ description }}</p>

    <div v-if="activeGateways.length" class="ctm-pm-preview__cards">
      <div
        v-for="(gw, idx) in activeGateways"
        :key="gw.key"
        :class="['ctm-pm-preview__card', idx === 0 ? 'ctm-pm-preview__card--selected' : '']"
      >
        <span class="ctm-pm-preview__icon">{{ gw.icon }}</span>
        <span class="ctm-pm-preview__label">{{ customLabel(gw.key) || gw.label }}</span>
        <span class="ctm-pm-preview__check" aria-hidden="true">&#10003;</span>
      </div>
    </div>

    <p v-else class="ctm-pm-preview__empty">
      No payment gateways enabled — go to <strong>Payment Settings</strong> to enable one.
    </p>

    <span v-if="field.help" class="contactum-help">{{ field.help }}</span>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";

export default {
  name: "form_payment_method",
  mixins: [form_field],
  computed: {
    ps() {
      return this.field.payment_settings || {};
    },
    description() {
      return this.ps.description || '';
    },
    gatewayMeta() {
      return [
        { key: 'stripe',   label: 'Credit / Debit Card', icon: '💳' },
        { key: 'paypal',   label: 'PayPal',              icon: '🅿️' },
        { key: 'razorpay', label: 'Razorpay',            icon: '₹' },
        { key: 'mollie',   label: 'Mollie',              icon: '🔵' },
      ];
    },
    enabledGateways() {
      const gws = window.contactum && window.contactum.payment_gateways
        ? window.contactum.payment_gateways
        : {};
      return gws;
    },
    activeGateways() {
      return this.gatewayMeta.filter(gw => this.enabledGateways[gw.key] === true);
    },
  },
  methods: {
    customLabel(key) {
      return (this.ps.labels || {})[key] || '';
    },
  },
};
</script>

<style lang="scss" scoped>
.ctm-pm-preview {
  pointer-events: none;
}

.ctm-pm-preview__desc {
  margin: 0 0 10px;
  font-size: 13px;
  color: #6b7280;
}

.ctm-pm-preview__cards {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.ctm-pm-preview__card {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: #fff;
  min-width: 130px;
  box-shadow: 0 1px 3px rgba(0,0,0,.05);

  &--selected {
    border-color: #6366f1;
    background: #fafafe;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
  }
}

.ctm-pm-preview__icon {
  font-size: 16px;
  line-height: 1;
}

.ctm-pm-preview__label {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
}

.ctm-pm-preview__check {
  position: absolute;
  top: 5px;
  right: 6px;
  width: 15px;
  height: 15px;
  border-radius: 50%;
  background: #6366f1;
  color: #fff;
  font-size: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transform: scale(.5);
  transition: opacity .15s, transform .15s;

  .ctm-pm-preview__card--selected & {
    opacity: 1;
    transform: scale(1);
  }
}

.ctm-pm-preview__empty {
  margin: 0;
  font-size: 12px;
  color: #9ca3af;
  font-style: italic;
  line-height: 1.5;
}
</style>
