<template>
  <div class="cpm-page">

    <!-- Header ─────────────────────────────────────────────────────────── -->
    <div class="cpm-header">
      <div class="cpm-header__left">
        <div class="cpm-header__icon">
          <span class="dashicons dashicons-money-alt"></span>
        </div>
        <div>
          <h1 class="cpm-header__title">Payment Transactions</h1>
          <p class="cpm-header__sub">Track and manage all customer payments</p>
        </div>
      </div>
    </div>

    <!-- Stats ──────────────────────────────────────────────────────────── -->
    <div class="cpm-stats">
      <div class="cpm-stat cpm-stat--blue">
        <div class="cpm-stat__icon-wrap">
          <span class="dashicons dashicons-chart-line"></span>
        </div>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">${{ formatAmount(stats.total_revenue) }}</div>
          <div class="cpm-stat__lbl">Total Revenue</div>
        </div>
      </div>
      <div class="cpm-stat">
        <div class="cpm-stat__icon-wrap">
          <span class="dashicons dashicons-list-view"></span>
        </div>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.total }}</div>
          <div class="cpm-stat__lbl">All Payments</div>
        </div>
      </div>
      <div class="cpm-stat cpm-stat--green">
        <div class="cpm-stat__icon-wrap">
          <span class="dashicons dashicons-yes-alt"></span>
        </div>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.completed }}</div>
          <div class="cpm-stat__lbl">Completed</div>
        </div>
      </div>
      <div class="cpm-stat cpm-stat--amber">
        <div class="cpm-stat__icon-wrap">
          <span class="dashicons dashicons-clock"></span>
        </div>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.pending }}</div>
          <div class="cpm-stat__lbl">Pending</div>
        </div>
      </div>
      <div class="cpm-stat cpm-stat--red">
        <div class="cpm-stat__icon-wrap">
          <span class="dashicons dashicons-dismiss"></span>
        </div>
        <div class="cpm-stat__body">
          <div class="cpm-stat__val">{{ stats.failed }}</div>
          <div class="cpm-stat__lbl">Failed</div>
        </div>
      </div>
    </div>

    <!-- Table card ─────────────────────────────────────────────────────── -->
    <div class="cpm-card">

      <!-- Toolbar -->
      <div class="cpm-toolbar">
        <div class="cpm-toolbar__filters">
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

        <el-input
          v-model="search"
          placeholder="Search transactions…"
          prefix-icon="el-icon-search"
          clearable
          size="small"
          class="cpm-toolbar__search"
          @keyup.enter.native="onFilterChange"
          @clear="onFilterChange"
        />
      </div>

      <!-- Table -->
      <div v-loading="loading">
        <el-table :data="payments" class="cpm-table" stripe>

          <el-table-column label="Transaction" width="160">
            <template slot-scope="{ row }">
              <span class="cpm-txn">
                {{ row.transaction_id ? ('#' + row.transaction_id) : ('ID-' + row.id) }}
              </span>
            </template>
          </el-table-column>

          <el-table-column label="Form" min-width="160" show-overflow-tooltip>
            <template slot-scope="{ row }">
              <span class="cpm-form-name">{{ row.form_title || '—' }}</span>
            </template>
          </el-table-column>

          <el-table-column label="Customer" min-width="130">
            <template slot-scope="{ row }">
              <span class="cpm-customer">
                <i class="el-icon-user"></i>
                {{ row.customer_name || 'Guest' }}
              </span>
            </template>
          </el-table-column>

          <el-table-column label="Gateway" width="110" align="center">
            <template slot-scope="{ row }">
              <span
                v-if="row.gateway"
                class="cpm-gw"
                :class="'cpm-gw--' + row.gateway.toLowerCase()"
              >{{ capitalize(row.gateway) }}</span>
              <span v-else class="cpm-gw">—</span>
            </template>
          </el-table-column>

          <el-table-column label="Amount" width="110">
            <template slot-scope="{ row }">
              <strong class="cpm-amount">${{ formatAmount(row.total) }}</strong>
            </template>
          </el-table-column>

          <el-table-column label="Status" width="120" align="center">
            <template slot-scope="{ row }">
              <span class="cpm-status" :class="'cpm-status--' + (row.status || 'unknown')">
                {{ capitalize(row.status || 'unknown') }}
              </span>
            </template>
          </el-table-column>

          <el-table-column label="Date" width="150">
            <template slot-scope="{ row }">
              <span class="cpm-date">
                <i class="el-icon-time"></i>
                {{ formatDate(row.created_at) }}
              </span>
            </template>
          </el-table-column>

          <el-table-column width="52" align="center">
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
            {{ (filterForm || filterStatus || filterGateway || search)
              ? 'No payments match the current filters.'
              : 'Payment records will appear here once customers complete transactions.' }}
          </p>
          <el-button
            v-if="filterForm || filterStatus || filterGateway || search"
            size="small"
            @click="clearFilters"
          >
            Clear Filters
          </el-button>
        </div>
      </div>

      <!-- Footer / Pagination -->
      <div class="cpm-footer" v-if="total > 0">
        <span class="cpm-footer__count">{{ total }} payment{{ total !== 1 ? 's' : '' }}</span>
        <el-pagination
          v-if="totalPages > 1"
          background
          layout="prev, pager, next"
          :total="total"
          :page-size="perPage"
          :current-page.sync="page"
          @current-change="fetchPayments"
        />
      </div>

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
      search:         '',
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
        search:   this.search,
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
      this.search        = '';
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

/* ── Page ─────────────────────────────────────────────── */
.cpm-page {
  padding: 24px;
  background: #f8f9fa;
  min-height: 100vh;
}

/* ── Header ───────────────────────────────────────────── */
.cpm-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.cpm-header__left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.cpm-header__icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: linear-gradient(135deg, #409eff 0%, #1a7efb 100%);
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
  font-size: 22px;
  font-weight: 600;
  color: #1e1f21;
  line-height: 1.3;
}
.cpm-header__sub {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
}

/* ── Stats ────────────────────────────────────────────── */
.cpm-stats {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.cpm-stat {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-left: 3px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, .06);
  padding: 16px 18px;
  flex: 1;
  min-width: 140px;
}
.cpm-stat__icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.cpm-stat .dashicons {
  font-size: 18px;
  width: 18px;
  height: 18px;
  color: #9ca3af;
}
.cpm-stat__val {
  font-size: 20px;
  font-weight: 600;
  color: #1e1f21;
  line-height: 1;
}
.cpm-stat__lbl {
  font-size: 12px;
  color: #6b7280;
  margin-top: 3px;
}

/* stat variants */
.cpm-stat--blue  { border-left-color: #409eff; }
.cpm-stat--blue  .cpm-stat__icon-wrap { background: #ecf5ff; }
.cpm-stat--blue  .dashicons { color: #409eff; }

.cpm-stat--green { border-left-color: #059669; }
.cpm-stat--green .cpm-stat__icon-wrap { background: #ecfdf5; }
.cpm-stat--green .dashicons { color: #059669; }

.cpm-stat--amber { border-left-color: #d97706; }
.cpm-stat--amber .cpm-stat__icon-wrap { background: #fffbeb; }
.cpm-stat--amber .dashicons { color: #d97706; }

.cpm-stat--red   { border-left-color: #dc2626; }
.cpm-stat--red   .cpm-stat__icon-wrap { background: #fef2f2; }
.cpm-stat--red   .dashicons { color: #dc2626; }

/* ── Table card ───────────────────────────────────────── */
.cpm-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, .08);
  overflow: hidden;
}

/* Toolbar */
.cpm-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f0f0;
  flex-wrap: wrap;
}
.cpm-toolbar__filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  flex: 1;
}
.cpm-toolbar__sel { width: 160px; }
.cpm-toolbar__search {
  width: 240px;
  flex-shrink: 0;
}

/* Table */
.cpm-table { width: 100%; }

/* Footer */
.cpm-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-top: 1px solid #f0f0f0;
}
.cpm-footer__count {
  font-size: 13px;
  color: #9ca3af;
}

/* ── Cells ────────────────────────────────────────────── */
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
.cpm-customer {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
  color: #374151;
}
.cpm-customer .el-icon-user {
  color: #9ca3af;
  font-size: 13px;
}
.cpm-amount {
  font-size: 14px;
  font-weight: 600;
  color: #1e1f21;
}
.cpm-date {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12.5px;
  color: #6b7280;
  white-space: nowrap;
}
.cpm-date .el-icon-time { color: #9ca3af; }

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
  white-space: nowrap;
}
.cpm-status::before {
  content: '';
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}
.cpm-status--completed { background: #ecfdf5; color: #059669; }
.cpm-status--pending   { background: #fef3c7; color: #d97706; }
.cpm-status--failed    { background: #fef2f2; color: #dc2626; }
.cpm-status--refunded  { background: #eff6ff; color: #2563eb; }
.cpm-status--unknown   { background: #f3f4f6; color: #6b7280; }

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
.cpm-del:hover { color: #dc2626; }
.cpm-del .dashicons { font-size: 16px; width: 16px; height: 16px; }

/* ── Empty state ──────────────────────────────────────── */
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
  display: block;
  margin-left: auto;
  margin-right: auto;
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
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}
</style>
