<template>
  <div class="pln-wrap">

    <div class="pln-section">
      <label class="pln-label">Subscription Type</label>
      <el-radio-group
        size="small"
        :value="planType"
        @input="setPlanType"
      >
        <el-radio-button label="single">Single Recurring Plan</el-radio-button>
        <el-radio-button label="multiple">Multiple Pricing Plans</el-radio-button>
      </el-radio-group>
    </div>

    <div class="pln-section" v-if="planType === 'multiple'">
      <label class="pln-label">Plan Display Type</label>
      <el-radio-group
        size="small"
        :value="displayType"
        @input="setDisplayType"
      >
        <el-radio-button label="radio">Radio input field</el-radio-button>
        <el-radio-button label="select">Select input field</el-radio-button>
      </el-radio-group>
    </div>

    <div class="pln-section">
      <label class="pln-label">Pricing Plans</label>

      <div v-if="!plans.length" class="pln-empty">
        No plans yet — add at least one plan below.
      </div>

      <div
        v-for="(plan, index) in plans"
        :key="plan.id"
        class="pln-card"
      >
        <div class="pln-card__head">
          <span class="pln-card__title">Plan {{ index + 1 }}</span>
          <button
            type="button"
            class="pln-card__remove"
            title="Remove plan"
            @click="removePlan(index)"
          >
            <i class="el-icon-delete"></i>
          </button>
        </div>

        <div class="pln-row">
          <div class="pln-field pln-field--grow">
            <label class="pln-field__label">Plan Name</label>
            <el-input
              size="small"
              :value="plan.name"
              placeholder="e.g. Monthly"
              @input="updatePlan(index, 'name', $event)"
            />
          </div>
          <div class="pln-field">
            <label class="pln-field__label">Amount</label>
            <el-input-number
              size="small"
              :min="0"
              :value="plan.amount"
              controls-position="right"
              style="width: 100%"
              @input="updatePlan(index, 'amount', $event)"
            />
          </div>
        </div>

        <div class="pln-row">
          <div class="pln-field">
            <label class="pln-field__label">Bill Every</label>
            <el-input-number
              size="small"
              :min="1"
              :value="plan.interval_count"
              controls-position="right"
              style="width: 100%"
              @input="updatePlan(index, 'interval_count', $event)"
            />
          </div>
          <div class="pln-field pln-field--grow">
            <label class="pln-field__label">Interval</label>
            <el-select
              size="small"
              :value="plan.interval"
              style="width: 100%"
              @input="updatePlan(index, 'interval', $event)"
            >
              <el-option label="Day(s)"   value="day" />
              <el-option label="Week(s)"  value="week" />
              <el-option label="Month(s)" value="month" />
              <el-option label="Year(s)"  value="year" />
            </el-select>
          </div>
        </div>

        <div class="pln-row">
          <div class="pln-field">
            <label class="pln-field__label">Trial Days</label>
            <el-input-number
              size="small"
              :min="0"
              :value="plan.trial_days"
              controls-position="right"
              style="width: 100%"
              @input="updatePlan(index, 'trial_days', $event)"
            />
          </div>
          <div class="pln-field">
            <label class="pln-field__label">Signup Fee</label>
            <el-input-number
              size="small"
              :min="0"
              :value="plan.signup_fee"
              controls-position="right"
              style="width: 100%"
              @input="updatePlan(index, 'signup_fee', $event)"
            />
          </div>
        </div>

        <div class="pln-row">
          <div class="pln-field pln-field--grow">
            <label class="pln-field__label">
              Billing Times
              <span class="pln-field__hint">(0 = bill until canceled)</span>
            </label>
            <el-input-number
              size="small"
              :min="0"
              :value="plan.billing_times"
              controls-position="right"
              style="width: 100%"
              @input="updatePlan(index, 'billing_times', $event)"
            />
          </div>
        </div>
      </div>

      <div class="pln-actions">
        <el-button size="small" @click="addPlan">+ Add New Plan</el-button>
      </div>
    </div>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";

function generatePlanId() {
  return 'plan_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
}

function defaultPlan() {
  return {
    id:             generatePlanId(),
    name:           '',
    amount:         0,
    interval:       'month',
    interval_count: 1,
    trial_days:     0,
    signup_fee:     0,
    billing_times:  0,
  };
}

export default {
  name: "plan",
  mixins: [option_field],
  computed: {
    // `value` (from the option_field mixin) reads/writes editfield.plan,
    // since this settings row is registered with name: 'plan'.
    planConfig() {
      const cfg = this.value && typeof this.value === 'object' ? this.value : {};
      return {
        plan_type:    cfg.plan_type === 'multiple' ? 'multiple' : 'single',
        display_type: cfg.display_type === 'select' ? 'select' : 'radio',
        plans:        Array.isArray(cfg.plans) ? cfg.plans : [],
      };
    },
    planType() {
      return this.planConfig.plan_type;
    },
    displayType() {
      return this.planConfig.display_type;
    },
    plans() {
      return this.planConfig.plans;
    },
  },
  methods: {
    save(next) {
      this.value = Object.assign({}, this.planConfig, next);
    },

    setPlanType(val) {
      this.save({ plan_type: val });
    },

    setDisplayType(val) {
      this.save({ display_type: val });
    },

    addPlan() {
      this.save({ plans: this.plans.concat([defaultPlan()]) });
    },

    removePlan(index) {
      const plans = this.plans.slice();
      plans.splice(index, 1);
      this.save({ plans });
    },

    updatePlan(index, key, val) {
      const plans = this.plans.map((p, i) => i === index ? Object.assign({}, p, { [key]: val }) : p);
      this.save({ plans });
    },
  },
};
</script>

<style scoped>
.pln-wrap { font-size: 13px; }

.pln-section {
  margin-bottom: 18px;
}
.pln-section:last-child { margin-bottom: 0; }

.pln-label {
  display: block;
  font-size: 11.5px;
  font-weight: 700;
  color: #606266;
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-bottom: 8px;
}

.pln-empty {
  padding: 12px;
  border: 1px dashed #dcdfe6;
  border-radius: 6px;
  font-size: 12.5px;
  color: #909399;
  text-align: center;
  margin-bottom: 10px;
}

.pln-card {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
  background: #fafafc;
}

.pln-card__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.pln-card__title {
  font-size: 12.5px;
  font-weight: 700;
  color: #303133;
}
.pln-card__remove {
  background: none;
  border: none;
  cursor: pointer;
  color: #c0c4cc;
  padding: 2px;
  transition: color .15s;
}
.pln-card__remove:hover { color: #f56c6c; }

.pln-row {
  display: flex;
  gap: 10px;
  margin-bottom: 10px;
}
.pln-row:last-child { margin-bottom: 0; }

.pln-field {
  flex: 0 0 auto;
  width: 110px;
}
.pln-field--grow {
  flex: 1 1 auto;
  width: auto;
}
.pln-field__label {
  display: block;
  font-size: 11px;
  color: #909399;
  margin-bottom: 4px;
}
.pln-field__hint {
  font-weight: 400;
  text-transform: none;
  letter-spacing: 0;
  color: #c0c4cc;
}

.pln-actions {
  margin-top: 4px;
}
</style>
