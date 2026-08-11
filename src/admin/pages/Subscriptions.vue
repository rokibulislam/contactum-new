<template>
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
          <el-option label="Active"    value="active" />
          <el-option label="Trialing"  value="trialing" />
          <el-option label="Past Due"  value="past_due" />
          <el-option label="Canceled"  value="canceled" />
          <el-option label="Pending"   value="pending" />
          <el-option label="Failed"    value="failed" />
        </el-select>
      </div>
    </div>

    <!-- Table -->
    <div v-loading="loading">
      <el-table :data="subscriptions" class="cpm-table" stripe>

        <el-table-column label="Plan" min-width="150" show-overflow-tooltip>
          <template slot-scope="{ row }">
            <span class="cpm-form-name">{{ row.plan_name || '—' }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Form" min-width="160" show-overflow-tooltip>
          <template slot-scope="{ row }">
            <span class="cpm-form-name">{{ row.form_title || '—' }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Customer" min-width="160" show-overflow-tooltip>
          <template slot-scope="{ row }">
            <span class="cpm-customer">
              <i class="el-icon-user"></i>
              {{ row.customer_email || 'Guest' }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Amount" width="140">
          <template slot-scope="{ row }">
            <strong class="cpm-amount">${{ formatAmount(row.amount) }}</strong>
            <span class="csub-interval">/ {{ intervalLabel(row) }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Billing Cycles" width="120" align="center">
          <template slot-scope="{ row }">
            <span class="csub-cycles">
              {{ row.times_billed }}{{ row.billing_times > 0 ? (' / ' + row.billing_times) : '' }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Status" width="120" align="center">
          <template slot-scope="{ row }">
            <span class="cpm-status" :class="'cpm-status--' + statusClass(row.status)">
              {{ capitalize((row.status || 'unknown').replace('_', ' ')) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Started" width="150">
          <template slot-scope="{ row }">
            <span class="cpm-date">
              <i class="el-icon-time"></i>
              {{ formatDate(row.created_at) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column width="90" align="center">
          <template slot-scope="{ row }">
            <el-tooltip v-if="isCancelable(row.status)" content="Cancel subscription" placement="top">
              <button class="cpm-del" @click="confirmCancel(row)">
                <span class="dashicons dashicons-no-alt"></span>
              </button>
            </el-tooltip>
          </template>
        </el-table-column>

      </el-table>

      <!-- Empty state -->
      <div class="cpm-empty" v-if="!loading && subscriptions.length === 0">
        <span class="dashicons dashicons-update cpm-empty__icon"></span>
        <p class="cpm-empty__title">No subscriptions found</p>
        <p class="cpm-empty__sub">
          {{ (filterForm || filterStatus)
            ? 'No subscriptions match the current filters.'
            : 'Subscriptions will appear here once customers sign up for a recurring plan.' }}
        </p>
        <el-button
          v-if="filterForm || filterStatus"
          size="small"
          @click="clearFilters"
        >
          Clear Filters
        </el-button>
      </div>
    </div>

    <!-- Footer / Pagination -->
    <div class="cpm-footer" v-if="total > 0">
      <span class="cpm-footer__count">{{ total }} subscription{{ total !== 1 ? 's' : '' }}</span>
      <el-pagination
        v-if="totalPages > 1"
        background
        layout="prev, pager, next"
        :total="total"
        :page-size="perPage"
        :current-page.sync="page"
        @current-change="fetchSubscriptions"
      />
    </div>

  </div>
</template>

<script>
/* global jQuery */
const $ = window.jQuery;
const cpm = window.contactum || {};

export default {
  name: 'Subscriptions',

  data() {
    return {
      subscriptions:  [],
      loading:        false,
      availableForms: [],
      formsLoading:   false,
      filterForm:     '',
      filterStatus:   '',
      page:           1,
      perPage:        20,
      total:          0,
      totalPages:     0,
    };
  },

  mounted() {
    this.loadForms();
    this.fetchSubscriptions();
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

    fetchSubscriptions() {
      this.loading = true;
      $.post(cpm.ajaxurl, {
        action:   'contactum_get_subscriptions',
        nonce:    cpm.nonce,
        page:     this.page,
        per_page: this.perPage,
        form_id:  this.filterForm,
        status:   this.filterStatus,
      }, (res) => {
        this.loading = false;
        if (res.success) {
          this.subscriptions = res.data.subscriptions;
          this.total         = res.data.total;
          this.totalPages     = res.data.pages;
        }
      });
    },

    onFilterChange() {
      this.page = 1;
      this.fetchSubscriptions();
    },

    clearFilters() {
      this.filterForm   = '';
      this.filterStatus = '';
      this.page         = 1;
      this.fetchSubscriptions();
    },

    isCancelable(status) {
      return ['active', 'trialing', 'past_due'].includes(status);
    },

    statusClass(status) {
      if (status === 'active' || status === 'trialing') return 'completed';
      if (status === 'past_due' || status === 'pending') return 'pending';
      if (status === 'canceled' || status === 'failed') return 'failed';
      return 'unknown';
    },

    confirmCancel(row) {
      this.$confirm(
        `Cancel the <strong>${row.plan_name || 'subscription'}</strong> for <strong>${row.customer_email || 'this customer'}</strong>? This stops future billing immediately and cannot be undone.`,
        'Cancel Subscription',
        {
          confirmButtonText:        'Cancel Subscription',
          cancelButtonText:         'Keep It',
          type:                     'warning',
          dangerouslyUseHTMLString: true,
          confirmButtonClass:       'el-button--danger',
        }
      ).then(() => {
        $.post(cpm.ajaxurl, {
          action: 'contactum_cancel_subscription',
          nonce:  cpm.nonce,
          id:     row.id,
        }, (res) => {
          if (res.success) {
            this.$message.success('Subscription canceled');
            this.fetchSubscriptions();
          } else {
            this.$message.error((res.data && res.data.message) || 'Failed to cancel subscription');
          }
        });
      }).catch(() => {});
    },

    formatAmount(val) {
      return parseFloat(val || 0).toFixed(2);
    },

    intervalLabel(row) {
      const count = parseInt(row.interval_count, 10) || 1;
      const unit  = row.billing_interval || 'month';
      return count === 1 ? unit : (count + ' ' + unit + 's');
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
.cpm-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, .08);
  overflow: hidden;
}
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
.cpm-table { width: 100%; }
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
.csub-interval {
  font-size: 12px;
  color: #9ca3af;
  margin-left: 3px;
}
.csub-cycles {
  font-size: 13px;
  color: #6b7280;
  font-variant-numeric: tabular-nums;
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
.cpm-status--unknown   { background: #f3f4f6; color: #6b7280; }
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
