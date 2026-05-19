<template>
  <el-dialog
    :visible.sync="dialogVisible"
    width="860px"
    :before-close="handleClose"
    :close-on-click-modal="false"
    custom-class="ccd-dialog"
    top="4vh"
  >
    <!-- ── Header ──────────────────────────────────────────────────────── -->
    <div slot="title" class="ccd-header">
      <div class="ccd-header__icon" :class="form.coupon_type === 'percent' ? 'ccd-icon--purple' : 'ccd-icon--teal'">
        <i class="el-icon-s-ticket" />
      </div>
      <div>
        <div class="ccd-header__title">{{ isEditMode ? 'Edit Coupon' : 'New Coupon' }}</div>
        <div class="ccd-header__sub">{{ isEditMode ? 'Update this discount code' : 'Create a discount code for your payment forms' }}</div>
      </div>
    </div>

    <!-- ── Body ────────────────────────────────────────────────────────── -->
    <div class="ccd-body">

      <!-- Left: Live Preview -->
      <div class="ccd-sidebar">

        <!-- Ticket card -->
        <div class="ccd-ticket" :class="form.coupon_type === 'percent' ? 'ccd-ticket--purple' : 'ccd-ticket--teal'">
          <div class="ccd-ticket__top">
            <div class="ccd-ticket__eyebrow">DISCOUNT CODE</div>
            <div class="ccd-ticket__amount">
              <template v-if="form.amount > 0">
                <template v-if="form.coupon_type === 'percent'">
                  <span class="ccd-ticket__value">{{ form.amount }}</span><span class="ccd-ticket__unit">%</span>
                </template>
                <template v-else>
                  <span class="ccd-ticket__unit ccd-ticket__unit--pre">$</span><span class="ccd-ticket__value">{{ parseFloat(form.amount).toFixed(2) }}</span>
                </template>
              </template>
              <span v-else class="ccd-ticket__empty">—</span>
            </div>
            <div class="ccd-ticket__off">off your order</div>
          </div>

          <!-- Perforated tear -->
          <div class="ccd-tear">
            <div class="ccd-tear__notch ccd-tear__notch--l" />
            <div class="ccd-tear__line" />
            <div class="ccd-tear__notch ccd-tear__notch--r" />
          </div>

          <div class="ccd-ticket__bottom">
            <div class="ccd-ticket__code">{{ form.code || 'YOURCODE' }}</div>
            <div class="ccd-ticket__expiry">
              <i class="el-icon-time" />
              <template v-if="form.expire_date">{{ form.expire_date.substring(0, 10) }}</template>
              <template v-else>No expiry</template>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="ccd-stats">
          <div class="ccd-stat">
            <div class="ccd-stat__val">{{ form.max_use > 0 ? form.max_use : '∞' }}</div>
            <div class="ccd-stat__key">Uses</div>
          </div>
          <div class="ccd-stat">
            <div class="ccd-stat__val">{{ form.min_amount > 0 ? '$' + form.min_amount : '—' }}</div>
            <div class="ccd-stat__key">Min. Order</div>
          </div>
          <div class="ccd-stat">
            <div class="ccd-stat__val">{{ form.settings.form_ids.length || 'All' }}</div>
            <div class="ccd-stat__key">Forms</div>
          </div>
        </div>

        <!-- Indicators -->
        <div class="ccd-indicators">
          <div class="ccd-indicator" :class="form.status === 'active' ? 'ccd-indicator--green' : 'ccd-indicator--gray'">
            <span class="ccd-dot" />
            {{ form.status === 'active' ? 'Active' : 'Inactive' }}
          </div>
          <div v-if="form.stackable === 'yes'" class="ccd-indicator ccd-indicator--blue">
            <i class="el-icon-connection" />
            Stackable
          </div>
        </div>
      </div>

      <!-- Right: Form -->
      <el-form
        :model="form"
        :rules="rules"
        ref="couponForm"
        label-position="top"
        class="ccd-form"
      >
        <!-- ─ Basic ─ -->
        <div class="ccd-section-label">Basic Info</div>
        <div class="ccd-row ccd-row--2">
          <el-form-item prop="title">
            <span slot="label" class="ccd-label">Title</span>
            <el-input v-model="form.title" placeholder="e.g. Summer Sale" size="small" />
          </el-form-item>
          <el-form-item prop="code">
            <span slot="label" class="ccd-label">
              Coupon Code
              <button type="button" class="ccd-gen" @click="generateCode">
                <i class="el-icon-refresh" /> Generate
              </button>
            </span>
            <el-input
              v-model="form.code"
              placeholder="SAVE20"
              size="small"
              @input="form.code = form.code.toUpperCase()"
            />
          </el-form-item>
        </div>

        <!-- ─ Discount ─ -->
        <div class="ccd-section-label">Discount</div>
        <div class="ccd-row ccd-row--2">
          <el-form-item prop="coupon_type">
            <span slot="label" class="ccd-label">Type</span>
            <div class="ccd-pills">
              <button
                type="button"
                class="ccd-pill"
                :class="{ 'ccd-pill--teal': form.coupon_type === 'fixed' }"
                @click="form.coupon_type = 'fixed'"
              ><span class="ccd-pill__icon">$</span> Fixed</button>
              <button
                type="button"
                class="ccd-pill"
                :class="{ 'ccd-pill--purple': form.coupon_type === 'percent' }"
                @click="form.coupon_type = 'percent'"
              ><span class="ccd-pill__icon">%</span> Percent</button>
            </div>
          </el-form-item>

          <el-form-item prop="amount">
            <span slot="label" class="ccd-label">{{ form.coupon_type === 'percent' ? 'Percent Off' : 'Amount Off' }}</span>
            <el-input v-model.number="form.amount" type="number" min="0" step="0.01" placeholder="0" size="small">
              <template slot="append">{{ form.coupon_type === 'percent' ? '%' : '$' }}</template>
            </el-input>
          </el-form-item>
        </div>

        <!-- ─ Restrictions ─ -->
        <div class="ccd-section-label">Restrictions</div>
        <div class="ccd-row ccd-row--2">
          <el-form-item>
            <span slot="label" class="ccd-label">Start Date</span>
            <el-date-picker
              v-model="form.start_date"
              type="datetime"
              placeholder="No start date"
              value-format="yyyy-MM-dd HH:mm:ss"
              size="small"
              style="width:100%"
            />
          </el-form-item>
          <el-form-item>
            <span slot="label" class="ccd-label">Expiry Date</span>
            <el-date-picker
              v-model="form.expire_date"
              type="datetime"
              placeholder="Never expires"
              value-format="yyyy-MM-dd HH:mm:ss"
              size="small"
              style="width:100%"
            />
          </el-form-item>
        </div>

        <div class="ccd-row ccd-row--3">
          <el-form-item>
            <span slot="label" class="ccd-label">Min. Order</span>
            <el-input v-model.number="form.min_amount" type="number" min="0" placeholder="0" size="small">
              <template slot="prepend">$</template>
            </el-input>
          </el-form-item>
          <el-form-item>
            <span slot="label" class="ccd-label">Total Uses</span>
            <el-input v-model.number="form.max_use" type="number" min="0" placeholder="Unlimited" size="small" />
          </el-form-item>
          <el-form-item>
            <span slot="label" class="ccd-label">Per User</span>
            <el-input v-model.number="form.per_user_limit" type="number" min="0" placeholder="Unlimited" size="small" />
          </el-form-item>
        </div>

        <el-form-item>
          <span slot="label" class="ccd-label">
            Applicable Forms
            <span class="ccd-hint">· leave empty for all</span>
          </span>
          <el-select
            v-model="form.settings.form_ids"
            multiple
            placeholder="All payment forms"
            style="width:100%"
            size="small"
            :loading="formsLoading"
          >
            <el-option v-for="f in availableForms" :key="f.id" :label="f.name" :value="f.id" />
          </el-select>
        </el-form-item>

        <!-- ─ Options ─ -->
        <div class="ccd-section-label">Options</div>
        <div class="ccd-switches">
          <div class="ccd-switch-row">
            <div>
              <div class="ccd-switch-row__label">Stackable</div>
              <div class="ccd-switch-row__desc">Allow combining with other coupons</div>
            </div>
            <el-switch
              :value="form.stackable === 'yes'"
              @change="form.stackable = $event ? 'yes' : 'no'"
              active-color="#409EFF"
            />
          </div>
          <div class="ccd-switch-row">
            <div>
              <div class="ccd-switch-row__label">Active</div>
              <div class="ccd-switch-row__desc">Coupon is usable immediately when enabled</div>
            </div>
            <el-switch
              :value="form.status === 'active'"
              @change="form.status = $event ? 'active' : 'inactive'"
              active-color="#67C23A"
            />
          </div>
        </div>
      </el-form>
    </div>

    <!-- ── Footer ──────────────────────────────────────────────────────── -->
    <div slot="footer" class="ccd-footer">
      <el-button size="small" @click="handleClose">Cancel</el-button>
      <el-button size="small" type="primary" :loading="saving" @click="saveCoupon" icon="el-icon-check">
        {{ isEditMode ? 'Update Coupon' : 'Create Coupon' }}
      </el-button>
    </div>
  </el-dialog>
</template>

<script>
export default {
  name: 'CreateCoupon',

  props: {
    visible:    { type: Boolean, required: true },
    couponData: { type: Object,  default: () => ({}) },
  },

  data() {
    return {
      saving:         false,
      formsLoading:   false,
      availableForms: [],
      form:           this.blankForm(),
      rules: {
        title:       [{ required: true, message: 'Title is required', trigger: 'blur' }],
        code:        [{ required: true, message: 'Code is required',  trigger: 'blur' }],
        coupon_type: [{ required: true, message: 'Select a type',     trigger: 'change' }],
        amount:      [{ required: true, message: 'Enter an amount',   trigger: 'blur' }],
      },
    };
  },

  computed: {
    dialogVisible: {
      get() { return this.visible; },
      set(val) { if (!val) this.$emit('close'); },
    },
    isEditMode() {
      return !!this.couponData?.id;
    },
  },

  watch: {
    visible(val) {
      if (val) {
        this.loadForms();
        this.form = this.isEditMode ? this.mapFromServer(this.couponData) : this.blankForm();
        this.$nextTick(() => this.$refs.couponForm?.clearValidate());
      }
    },
  },

  methods: {
    blankForm() {
      return {
        title:          '',
        code:           '',
        coupon_type:    'fixed',
        amount:         0,
        status:         'active',
        stackable:      'no',
        start_date:     '',
        expire_date:    '',
        min_amount:     0,
        max_use:        0,
        per_user_limit: 0,
        settings:       { form_ids: [] },
      };
    },

    mapFromServer(data) {
      const settings = typeof data.settings === 'string'
        ? JSON.parse(data.settings || '{}')
        : (data.settings || {});
      return {
        title:          data.title              || '',
        code:           data.code               || '',
        coupon_type:    data.coupon_type        || 'fixed',
        amount:         parseFloat(data.amount)          || 0,
        status:         data.status             || 'active',
        stackable:      data.stackable          || 'no',
        start_date:     data.start_date         || '',
        expire_date:    data.expire_date        || '',
        min_amount:     parseFloat(data.min_amount)      || 0,
        max_use:        parseInt(data.max_use)           || 0,
        per_user_limit: parseInt(data.per_user_limit)    || 0,
        settings:       { form_ids: settings.form_ids   || [] },
      };
    },

    generateCode() {
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      this.form.code = Array.from(
        { length: 8 },
        () => chars[Math.floor(Math.random() * chars.length)]
      ).join('');
    },

    loadForms() {
      this.formsLoading = true;
      jQuery.post(contactum.ajaxurl, {
        action:      'contactum_get_forms',
        _ajax_nonce: contactum.nonce,
      }, (res) => {
        this.formsLoading = false;
        if (res.success) {
          this.availableForms = Object.values(res.data.forms || {});
        }
      });
    },

    handleClose() {
      this.$emit('close');
    },

    saveCoupon() {
      this.$refs.couponForm.validate((valid) => {
        if (!valid) return;
        this.saving = true;
        const action  = this.isEditMode ? 'update_contactum_coupon' : 'save_contactum_coupon';
        const payload = { action, nonce: contactum.nonce, data: { ...this.form } };
        if (this.isEditMode) payload.id = this.couponData.id;

        jQuery.post(contactum.ajaxurl, payload, (res) => {
          this.saving = false;
          if (res.success) {
            this.$message.success(res.data?.message || 'Coupon saved!');
            this.$emit('saved');
            this.$emit('close');
          } else {
            this.$message.error(res.data?.message || 'Failed to save coupon.');
          }
        });
      });
    },
  },
};
</script>

<!-- Unscoped: override el-dialog wrapper elements -->
<style>
.ccd-dialog .el-dialog__header {
  padding: 0;
  border-bottom: 1px solid #ebeef5;
}
.ccd-dialog .el-dialog__body {
  padding: 0;
}
.ccd-dialog .el-dialog__footer {
  padding: 12px 20px;
  border-top: 1px solid #ebeef5;
}
.ccd-dialog .el-form-item {
  margin-bottom: 14px;
}
.ccd-dialog .el-form-item__label {
  padding-bottom: 4px;
  line-height: 1.4;
}
</style>

<style scoped>
/* ── Header ───────────────────────────────────────────────────────────── */
.ccd-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
}
.ccd-header__icon {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  flex-shrink: 0;
}
.ccd-icon--teal   { background: #d1fae5; color: #059669; }
.ccd-icon--purple { background: #ede9fe; color: #7c3aed; }
.ccd-header__title {
  font-size: 14px;
  font-weight: 600;
  color: #1a1a2e;
  line-height: 1.3;
}
.ccd-header__sub {
  font-size: 12px;
  color: #909399;
  margin-top: 1px;
}

/* ── Body: two-column ─────────────────────────────────────────────────── */
.ccd-body {
  display: flex;
}

/* ── Sidebar (preview) ────────────────────────────────────────────────── */
.ccd-sidebar {
  width: 232px;
  flex-shrink: 0;
  background: #f6f7fb;
  border-right: 1px solid #ebeef5;
  padding: 20px 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Ticket */
.ccd-ticket {
  border-radius: 11px;
  box-shadow: 0 3px 16px rgba(0,0,0,.12);
}
.ccd-ticket__top {
  padding: 16px 15px 12px;
  border-radius: 11px 11px 0 0;
  color: #fff;
}
.ccd-ticket--teal   .ccd-ticket__top { background: linear-gradient(135deg, #0d9488 0%, #059669 100%); }
.ccd-ticket--purple .ccd-ticket__top { background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); }

.ccd-ticket__eyebrow {
  font-size: 8.5px;
  font-weight: 700;
  letter-spacing: 1.8px;
  opacity: .7;
  margin-bottom: 8px;
}
.ccd-ticket__amount {
  font-size: 34px;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -1px;
}
.ccd-ticket__value { display: inline; }
.ccd-ticket__unit {
  font-size: 16px;
  font-weight: 700;
  vertical-align: super;
  line-height: 1;
}
.ccd-ticket__unit--pre { margin-right: 1px; }
.ccd-ticket__empty { opacity: .5; }
.ccd-ticket__off {
  font-size: 10px;
  opacity: .75;
  margin-top: 4px;
}

/* Tear */
.ccd-tear {
  display: flex;
  align-items: center;
  position: relative;
}
.ccd-tear__notch {
  width: 13px;
  height: 13px;
  border-radius: 50%;
  background: #f6f7fb;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}
.ccd-tear__notch--l { margin-left: -6.5px; }
.ccd-tear__notch--r { margin-right: -6.5px; }
.ccd-tear__line {
  flex: 1;
  height: 0;
  border-top: 1.5px dashed #d5d9e8;
}

/* Ticket bottom */
.ccd-ticket__bottom {
  padding: 11px 15px 13px;
  background: #fff;
  border-radius: 0 0 11px 11px;
}
.ccd-ticket__code {
  font-family: 'Courier New', monospace;
  font-size: 15px;
  font-weight: 800;
  letter-spacing: 2.5px;
  color: #1a1a2e;
}
.ccd-ticket__expiry {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  color: #adb5bd;
  margin-top: 3px;
}

/* Stats */
.ccd-stats {
  display: flex;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  overflow: hidden;
}
.ccd-stat {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 9px 4px;
}
.ccd-stat + .ccd-stat { border-left: 1px solid #f0f2f5; }
.ccd-stat__val {
  font-size: 13px;
  font-weight: 700;
  color: #2c3e50;
  line-height: 1;
}
.ccd-stat__key {
  font-size: 9px;
  color: #bdc3c7;
  text-transform: uppercase;
  letter-spacing: .5px;
  margin-top: 3px;
}

/* Indicators */
.ccd-indicators {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.ccd-indicator {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 500;
  padding: 5px 10px;
  border-radius: 6px;
}
.ccd-indicator--green { background: #ecfdf5; color: #059669; }
.ccd-indicator--gray  { background: #f3f4f6; color: #9ca3af; }
.ccd-indicator--blue  { background: #eff6ff; color: #2563eb; }
.ccd-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

/* ── Form panel ───────────────────────────────────────────────────────── */
.ccd-form {
  flex: 1;
  padding: 18px 20px 12px;
  overflow-y: auto;
  max-height: 66vh;
}

/* Section label */
.ccd-section-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .9px;
  text-transform: uppercase;
  color: #bdc3c7;
  padding-bottom: 8px;
  border-bottom: 1px solid #f0f2f5;
  margin-bottom: 12px;
}
.ccd-form > .ccd-section-label + *,
.ccd-form > .ccd-row,
.ccd-form > .el-form-item {
  margin-bottom: 14px;
}
.ccd-section-label:not(:first-child) {
  margin-top: 20px;
}

/* Grid rows */
.ccd-row { display: grid; gap: 12px; }
.ccd-row--2 { grid-template-columns: 1fr 1fr; }
.ccd-row--3 { grid-template-columns: 1fr 1fr 1fr; }

/* Label */
.ccd-label {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
}
.ccd-hint {
  font-weight: 400;
  color: #c0c4cc;
  font-size: 11px;
}

/* Generate button */
.ccd-gen {
  margin-left: auto;
  background: none;
  border: none;
  padding: 0;
  font-size: 10.5px;
  color: #409eff;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  line-height: 1;
  font-family: inherit;
}
.ccd-gen:hover { color: #66b1ff; }

/* Pill type buttons */
.ccd-pills {
  display: flex;
  gap: 8px;
}
.ccd-pill {
  flex: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  padding: 7px 10px;
  border: 1.5px solid #dcdfe6;
  border-radius: 6px;
  background: #fff;
  font-size: 12px;
  font-weight: 500;
  color: #606266;
  cursor: pointer;
  transition: all .15s;
  font-family: inherit;
}
.ccd-pill:hover { border-color: #c6d8f7; color: #409eff; }
.ccd-pill__icon {
  font-size: 13px;
  font-weight: 700;
}
.ccd-pill--teal   { border-color: #10b981; background: #ecfdf5; color: #059669; }
.ccd-pill--purple { border-color: #7c3aed; background: #f5f3ff; color: #7c3aed; }

/* Switch rows */
.ccd-switches {
  border: 1px solid #ebeef5;
  border-radius: 8px;
  overflow: hidden;
}
.ccd-switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 11px 14px;
  background: #fff;
}
.ccd-switch-row + .ccd-switch-row { border-top: 1px solid #f5f7fa; }
.ccd-switch-row__label {
  font-size: 12.5px;
  font-weight: 500;
  color: #374151;
  line-height: 1.3;
}
.ccd-switch-row__desc {
  font-size: 11px;
  color: #9ca3af;
  margin-top: 2px;
}

/* ── Footer ───────────────────────────────────────────────────────────── */
.ccd-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}
</style>
