<template>
  <div class="cpm-page">

    <!-- Header ────────────────────────────────────────────────────────────── -->
    <div class="cpm-header">
      <div class="cpm-header__icon">
        <span class="dashicons dashicons-money-alt"></span>
      </div>
      <div class="cpm-header__text">
        <h2 class="cpm-header__title">Payment Transactions</h2>
        <p class="cpm-header__sub">Track and manage all customer payments</p>
      </div>
    </div>

    <!-- Stats ─────────────────────────────────────────────────────────────── -->
    <div class="cpm-stats">
      <div class="cpm-stat cpm-stat--blue">
        <span class="dashicons dashicons-chart-line"></span>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">${{ formatAmount(stats.total_revenue) }}</div>
          <div class="cpm-stat__lbl">Total Revenue</div>
        </div>
      </div>
      <div class="cpm-stat">
        <span class="dashicons dashicons-list-view"></span>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.total }}</div>
          <div class="cpm-stat__lbl">All Payments</div>
        </div>
      </div>
      <div class="cpm-stat cpm-stat--green">
        <span class="dashicons dashicons-yes-alt"></span>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.completed }}</div>
          <div class="cpm-stat__lbl">Completed</div>
        </div>
      </div>
      <div class="cpm-stat cpm-stat--amber">
        <span class="dashicons dashicons-clock"></span>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.pending }}</div>
          <div class="cpm-stat__lbl">Pending</div>
        </div>
      </div>
      <div class="cpm-stat cpm-stat--red">
        <span class="dashicons dashicons-dismiss"></span>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.failed }}</div>
          <div class="cpm-stat__lbl">Failed</div>
        </div>
      </div>
    </div>

    <!-- Toolbar ───────────────────────────────────────────────────────────── -->
    <div class="cpm-toolbar">
      <el-select
        v-model="filterForm"
        clearable
        placeholder="All Forms"
        size="small"
        :loading="formsLoading"
        @change="onFilterChange"
        class="cpm-toolbar__sel"
      >
        <el-option
          v-for="f in availableForms"
          :key="f.id"
          :label="f.name"
          :value="f.id"
        />
      </el-select>

      <el-select
        v-model="filterStatus"
        clearable
        placeholder="All Statuses"
        size="small"
        @change="onFilterChange"
        class="cpm-toolbar__sel"
      >
        <el-option label="Completed" value="completed" />
        <el-option label="Pending"   value="pending" />
        <el-option label="Failed"    value="failed" />
        <el-option label="Refunded"  value="refunded" />
      </el-select>

      <el-select
        v-model="filterGateway"
        clearable
        placeholder="All Gateways"
        size="small"
        @change="onFilterChange"
        class="cpm-toolbar__sel"
      >
        <el-option label="Stripe"   value="stripe" />
        <el-option label="PayPal"   value="paypal" />
        <el-option label="Razorpay" value="razorpay" />
        <el-option label="Mollie"   value="mollie" />
        <el-option label="Square"   value="square" />
      </el-select>
    </div>

    <!-- Table ─────────────────────────────────────────────────────────────── -->
    <div class="cpm-table-wrap" v-loading="loading">
      <el-table :data="payments" class="cpm-table" :stripe="true">

        <el-table-column label="Transaction" width="160">
          <template slot-scope="{ row }">
            <span class="cpm-txn">
              {{ row.transaction_id ? ('#' + row.transaction_id) : ('ID-' + row.id) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Form" min-width="160">
          <template slot-scope="{ row }">
            <span class="cpm-form-name">{{ row.form_title || '—' }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Customer" min-width="130">
          <template slot-scope="{ row }">
            {{ row.customer_name || 'Guest' }}
          </template>
        </el-table-column>

        <el-table-column label="Gateway" width="110">
          <template slot-scope="{ row }">
            <span
              v-if="row.gateway"
              class="cpm-gw"
              :class="'cpm-gw--' + row.gateway.toLowerCase()"
            >{{ capitalize(row.gateway) }}</span>
            <span v-else class="cpm-gw cpm-gw--other">—</span>
          </template>
        </el-table-column>

        <el-table-column label="Amount" width="110">
          <template slot-scope="{ row }">
            <strong class="cpm-amount">${{ formatAmount(row.total) }}</strong>
          </template>
        </el-table-column>

        <el-table-column label="Status" width="120">
          <template slot-scope="{ row }">
            <span class="cpm-status" :class="'cpm-status--' + (row.status || 'unknown')">
              {{ capitalize(row.status || 'unknown') }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Date" width="150">
          <template slot-scope="{ row }">
            <span class="cpm-date">{{ formatDate(row.created_at) }}</span>
          </template>
        </el-table-column>

        <el-table-column width="60" align="center">
          <template slot-scope="{ row }">
            <el-tooltip content="Delete" placement="top">
              <button class="cpm-del" @click="confirmDelete(row)">
                <span class="dashicons dashicons-trash"></span>
              </button>
            </el-tooltip>
          </template>
        </el-table-column>

      </el-table>

      <!-- Empty state -->
      <div class="cpm-empty" v-if="!loading && payments.length === 0">
        <span class="dashicons dashicons-money-alt cpm-empty__icon"></span>
        <p class="cpm-empty__title">No payments found</p>
        <p class="cpm-empty__sub">
          {{ filterForm || filterStatus || filterGateway
            ? 'No payments match the current filters.'
            : 'Payment records will appear here once customers complete transactions.' }}
        </p>
        <button
          v-if="filterForm || filterStatus || filterGateway"
          class="cpm-btn-clear"
          @click="clearFilters"
        >Clear Filters</button>
      </div>
    </div>

    <!-- Pagination ────────────────────────────────────────────────────────── -->
    <div class="cpm-pagination" v-if="totalPages > 1">
      <el-pagination
        background
        layout="prev, pager, next, ->, total"
        :total="total"
        :page-size="perPage"
        :current-page.sync="page"
        @current-change="fetchPayments"
      />
    </div>

  </div>
</template>

<script>
/* global jQuery */
const $ = window.jQuery;
const cpm = window.contactum || {};

export default {
  name: 'Payment',

  data() {
    return {
      payments:       [],
      loading:        false,
      stats: {
        total_revenue: 0,
        total:         0,
        completed:     0,
        pending:       0,
        failed:        0,
        refunded:      0,
      },
      availableForms: [],
      formsLoading:   false,
      filterForm:     '',
      filterStatus:   '',
      filterGateway:  '',
      page:           1,
      perPage:        20,
      total:          0,
      totalPages:     0,
    };
  },

  mounted() {
    this.loadForms();
    this.fetchStats();
    this.fetchPayments();
  },

  methods: {
    loadForms() {
      this.formsLoading = true;
      $.post(cpm.ajaxurl, {
        action:      'contactum_get_forms',
        _ajax_nonce: cpm.nonce,
      }, (res) => {
        this.formsLoading = false;
        if (res.success) {
          this.availableForms = Object.values(res.data.forms || {});
        }
      });
    },

    fetchStats() {
      $.post(cpm.ajaxurl, {
        action: 'contactum_get_payment_stats',
        nonce:  cpm.nonce,
      }, (res) => {
        if (res.success) this.stats = res.data;
      });
    },

    fetchPayments() {
      this.loading = true;
      $.post(cpm.ajaxurl, {
        action:   'contactum_get_payments',
        nonce:    cpm.nonce,
        page:     this.page,
        per_page: this.perPage,
        form_id:  this.filterForm,
        status:   this.filterStatus,
        gateway:  this.filterGateway,
      }, (res) => {
        this.loading = false;
        if (res.success) {
          this.payments   = res.data.payments;
          this.total      = res.data.total;
          this.totalPages = res.data.pages;
        }
      });
    },

    onFilterChange() {
      this.page = 1;
      this.fetchPayments();
    },

    clearFilters() {
      this.filterForm    = '';
      this.filterStatus  = '';
      this.filterGateway = '';
      this.page          = 1;
      this.fetchPayments();
    },

    confirmDelete(row) {
      const label = row.transaction_id ? ('#' + row.transaction_id) : ('ID-' + row.id);
      this.$confirm(
        `Delete payment <strong>${label}</strong>? This cannot be undone.`,
        'Delete Payment',
        {
          confirmButtonText:        'Delete',
          cancelButtonText:         'Cancel',
          type:                     'warning',
          dangerouslyUseHTMLString: true,
          confirmButtonClass:       'el-button--danger',
        }
      ).then(() => {
        $.post(cpm.ajaxurl, {
          action: 'contactum_delete_payment',
          nonce:  cpm.nonce,
          id:     row.id,
        }, (res) => {
          if (res.success) {
            this.$message.success('Payment deleted');
            this.fetchPayments();
            this.fetchStats();
          } else {
            this.$message.error((res.data && res.data.message) || 'Failed to delete payment');
          }
        });
      }).catch(() => {});
    },

    formatAmount(val) {
      return parseFloat(val || 0).toFixed(2);
    },

    formatDate(val) {
      if (!val) return '—';
      return new Date(val).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
      });
    },

    capitalize(str) {
      if (!str) return '';
      return str.charAt(0).toUpperCase() + str.slice(1);
    },
  },
};
</script>

<style scoped>
/* ── Page ────────────────────────────────────────────── */
.cpm-page {
  padding: 24px 28px;
  max-width: 1200px;
}

/* ── Header ──────────────────────────────────────────── */
.cpm-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}
.cpm-header__icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.cpm-header__icon .dashicons {
  font-size: 22px;
  width: 22px;
  height: 22px;
  color: #fff;
}
.cpm-header__title {
  margin: 0 0 2px;
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  line-height: 1.2;
}
.cpm-header__sub {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
}

/* ── Stats ───────────────────────────────────────────── */
.cpm-stats {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.cpm-stat {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-left: 3px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px 18px;
  flex: 1;
  min-width: 130px;
}
.cpm-stat .dashicons {
  font-size: 22px;
  width: 22px;
  height: 22px;
  color: #9ca3af;
  flex-shrink: 0;
}
.cpm-stat__val {
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  line-height: 1;
}
.cpm-stat__lbl {
  font-size: 12px;
  color: #6b7280;
  margin-top: 3px;
}
.cpm-stat--blue  { border-left-color: #3b82f6; }
.cpm-stat--blue  .dashicons { color: #3b82f6; }
.cpm-stat--green { border-left-color: #22c55e; }
.cpm-stat--green .dashicons { color: #22c55e; }
.cpm-stat--amber { border-left-color: #f59e0b; }
.cpm-stat--amber .dashicons { color: #f59e0b; }
.cpm-stat--red   { border-left-color: #ef4444; }
.cpm-stat--red   .dashicons { color: #ef4444; }

/* ── Toolbar ─────────────────────────────────────────── */
.cpm-toolbar {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}
.cpm-toolbar__sel { width: 180px; }

/* ── Table wrap ──────────────────────────────────────── */
.cpm-table-wrap {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
}
.cpm-table { width: 100%; }

/* ── Cells ───────────────────────────────────────────── */
.cpm-txn {
  font-size: 12px;
  font-family: 'SFMono-Regular', Consolas, monospace;
  background: #f3f4f6;
  color: #374151;
  padding: 2px 7px;
  border-radius: 4px;
}
.cpm-form-name {
  font-weight: 500;
  color: #374151;
}
.cpm-amount {
  font-size: 14px;
  color: #111827;
}

/* Gateway badges */
.cpm-gw {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
  text-transform: capitalize;
  background: #f3f4f6;
  color: #6b7280;
}
.cpm-gw--stripe   { background: #ede9fe; color: #7c3aed; }
.cpm-gw--paypal   { background: #fef9c3; color: #854d0e; }
.cpm-gw--razorpay { background: #dbeafe; color: #1d4ed8; }
.cpm-gw--mollie   { background: #fce7f3; color: #9d174d; }
.cpm-gw--square   { background: #dcfce7; color: #166534; }

/* Status pills */
.cpm-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 20px;
  text-transform: capitalize;
}
.cpm-status::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}
.cpm-status--completed { background: #dcfce7; color: #16a34a; }
.cpm-status--pending   { background: #fef3c7; color: #d97706; }
.cpm-status--failed    { background: #fee2e2; color: #dc2626; }
.cpm-status--refunded  { background: #e0e7ff; color: #4338ca; }
.cpm-status--unknown   { background: #f3f4f6; color: #6b7280; }

.cpm-date {
  font-size: 12.5px;
  color: #6b7280;
}

/* Delete button */
.cpm-del {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #d1d5db;
  transition: color .15s;
  line-height: 1;
}
.cpm-del:hover { color: #ef4444; }
.cpm-del .dashicons { font-size: 16px; width: 16px; height: 16px; }

/* ── Empty state ─────────────────────────────────────── */
.cpm-empty {
  text-align: center;
  padding: 56px 24px;
}
.cpm-empty__icon {
  font-size: 48px !important;
  width: 48px !important;
  height: 48px !important;
  color: #d1d5db;
  margin-bottom: 12px;
}
.cpm-empty__title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 6px;
}
.cpm-empty__sub {
  font-size: 13px;
  color: #9ca3af;
  margin: 0 0 16px;
}
.cpm-btn-clear {
  background: none;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 7px 16px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  transition: border-color .15s, color .15s;
}
.cpm-btn-clear:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

/* ── Pagination ──────────────────────────────────────── */
.cpm-pagination {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
}
</style>
