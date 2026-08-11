<template>
  <div class="sub-preview">
    <div class="sub-preview__head">
      <span class="dashicons dashicons-update"></span>
      <span>{{ field.label || 'Subscription' }}</span>
    </div>

    <div v-if="!plans.length" class="sub-preview__empty">
      No pricing plans configured yet.
    </div>

    <div v-else class="sub-preview__plans">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="sub-preview__plan"
      >
        <span class="sub-preview__plan-name">{{ plan.name || 'Untitled plan' }}</span>
        <span class="sub-preview__plan-price">
          ${{ formatAmount(plan.amount) }} / {{ intervalLabel(plan) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import form_field from "../../../mixin/form-field.js";

export default {
  name: "form_subscription_field",
  mixins: [form_field],
  computed: {
    planConfig() {
      const cfg = this.field.plan;
      return cfg && typeof cfg === 'object' ? cfg : {};
    },
    plans() {
      return Array.isArray(this.planConfig.plans) ? this.planConfig.plans : [];
    },
  },
  methods: {
    formatAmount(val) {
      return parseFloat(val || 0).toFixed(2);
    },
    intervalLabel(plan) {
      const count = parseInt(plan.interval_count, 10) || 1;
      const unit  = plan.interval || 'month';
      return count === 1 ? unit : (count + ' ' + unit + 's');
    },
  },
};
</script>

<style scoped>
.sub-preview {
  font-size: 13px;
}
.sub-preview__head {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}
.sub-preview__head .dashicons {
  font-size: 15px;
  width: 15px;
  height: 15px;
  color: #6366f1;
}
.sub-preview__empty {
  font-size: 12.5px;
  color: #9ca3af;
  font-style: italic;
}
.sub-preview__plans {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.sub-preview__plan {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 7px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: #fafafe;
}
.sub-preview__plan-name {
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
}
.sub-preview__plan-price {
  font-size: 12px;
  color: #6366f1;
  font-weight: 600;
  white-space: nowrap;
}
</style>
