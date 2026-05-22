<template>
  <div class="ccl-wrap">

    <!-- ── Page Header ─────────────────────────────────────────────────── -->
    <div class="ccl-header">
      <div class="ccl-header__left">
        <div class="ccl-header__icon">
          <i class="el-icon-s-ticket" />
        </div>
        <div>
          <h2 class="ccl-header__title">Discount Coupons</h2>
          <p class="ccl-header__sub">Manage discount codes for your payment forms</p>
        </div>
      </div>
      <el-button type="primary" size="small" icon="el-icon-plus" @click="openCreate">
        New Coupon
      </el-button>
    </div>

    <!-- ── Stat chips ──────────────────────────────────────────────────── -->
    <div class="ccl-stats">
      <div class="ccl-stat">
        <span class="ccl-stat__val">{{ coupons.length }}</span>
        <span class="ccl-stat__key">Total</span>
      </div>
      <div class="ccl-stat ccl-stat--green">
        <span class="ccl-stat__val">{{ activeCoupons }}</span>
        <span class="ccl-stat__key">Active</span>
      </div>
      <div class="ccl-stat ccl-stat--gray">
        <span class="ccl-stat__val">{{ inactiveCoupons }}</span>
        <span class="ccl-stat__key">Inactive</span>
      </div>
      <div class="ccl-stat ccl-stat--blue">
        <span class="ccl-stat__val">{{ totalUses }}</span>
        <span class="ccl-stat__key">Total Uses</span>
      </div>
    </div>

    <!-- ── Toolbar ─────────────────────────────────────────────────────── -->
    <div class="ccl-toolbar">
      <el-input
        v-model="search"
        placeholder="Search by title or code…"
        size="small"
        prefix-icon="el-icon-search"
        clearable
        class="ccl-search"
      />
      <el-select v-model="filterStatus" size="small" class="ccl-filter" placeholder="All Status">
        <el-option label="All Status" value="" />
        <el-option label="Active"     value="active" />
        <el-option label="Inactive"   value="inactive" />
      </el-select>
    </div>

    <!-- ── Table ───────────────────────────────────────────────────────── -->
    <div class="ccl-table-wrap" v-loading="loading">
      <el-table
        :data="pagedCoupons"
        style="width:100%"
        :show-header="filteredCoupons.length > 0"
      >
        <!-- Coupon: code badge + title -->
        <el-table-column label="Coupon" min-width="200">
          <template slot-scope="{ row }">
            <div class="ccl-coupon-cell">
              <span
                class="ccl-code"
                :class="row.coupon_type === 'percent' ? 'ccl-code--purple' : 'ccl-code--teal'"
              >{{ row.code }}</span>
              <span class="ccl-coupon-title">{{ row.title }}</span>
            </div>
          </template>
        </el-table-column>

        <!-- Discount -->
        <el-table-column label="Discount" width="130">
          <template slot-scope="{ row }">
            <span class="ccl-discount" :class="row.coupon_type === 'percent' ? 'ccl-discount--purple' : 'ccl-discount--teal'">
              {{ row.coupon_type === 'percent'
                ? row.amount + '% OFF'
                : '$' + parseFloat(row.amount).toFixed(2) + ' OFF' }}
            </span>
          </template>
        </el-table-column>

        <!-- Usage -->
        <el-table-column label="Usage" width="130">
          <template slot-scope="{ row }">
            <div class="ccl-usage">
              <div class="ccl-usage__label">
                <span class="ccl-usage__used">{{ row.used_count }}</span>
                <span class="ccl-usage__sep"> / </span>
                <span class="ccl-usage__max">{{ row.max_use > 0 ? row.max_use : '∞' }}</span>
              </div>
              <el-progress
                v-if="row.max_use > 0"
                :percentage="Math.min(100, Math.round((row.used_count / row.max_use) * 100))"
                :show-text="false"
                :stroke-width="3"
                :color="row.used_count >= row.max_use ? '#f56c6c' : '#409eff'"
                style="margin-top:4px"
              />
            </div>
          </template>
        </el-table-column>

        <!-- Expiry -->
        <el-table-column label="Expiry" width="120">
          <template slot-scope="{ row }">
            <span v-if="row.expire_date" class="ccl-expiry" :class="{ 'ccl-expiry--expired': isExpired(row) }">
              {{ row.expire_date.substring(0, 10) }}
            </span>
            <span v-else class="ccl-muted">No expiry</span>
          </template>
        </el-table-column>

        <!-- Status -->
        <el-table-column label="Status" width="110">
          <template slot-scope="{ row }">
            <span class="ccl-status" :class="row.status === 'active' ? 'ccl-status--on' : 'ccl-status--off'">
              <span class="ccl-dot" />
              {{ row.status === 'active' ? 'Active' : 'Inactive' }}
            </span>
          </template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column width="80" align="right">
          <template slot-scope="{ row }">
            <div class="ccl-actions">
              <button class="ccl-btn ccl-btn--edit" title="Edit" @click="openEdit(row)">
                <i class="el-icon-edit" />
              </button>
              <button class="ccl-btn ccl-btn--del" title="Delete" @click="confirmDelete(row)">
                <i class="el-icon-delete" />
              </button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- Empty state -->
      <div v-if="!loading && filteredCoupons.length === 0" class="ccl-empty">
        <div class="ccl-empty__icon-wrap">
          <i class="el-icon-s-ticket" />
        </div>
        <p class="ccl-empty__title">
          {{ search || filterStatus ? 'No coupons match your filters' : 'No coupons yet' }}
        </p>
        <p v-if="!search && !filterStatus" class="ccl-empty__sub">
          Create your first discount code to start offering savings
        </p>
        <el-button
          v-if="!search && !filterStatus"
          type="primary"
          size="small"
          icon="el-icon-plus"
          @click="openCreate"
          style="margin-top:12px"
        >
          Create First Coupon
        </el-button>
      </div>
    </div>

    <!-- ── Pagination ──────────────────────────────────────────────────── -->
    <div v-if="filteredCoupons.length > pageSize" class="ccl-pagination">
      <el-pagination
        background
        layout="total, prev, pager, next"
        :total="filteredCoupons.length"
        :page-size="pageSize"
        :current-page.sync="currentPage"
        @current-change="currentPage = $event"
      />
    </div>

    <CreateCoupon
      :visible="dialogVisible"
      :coupon-data="currentCoupon"
      @close="dialogVisible = false"
      @saved="fetchCoupons"
    />

  </div>
</template>

<script>
import CreateCoupon from '../dialog/CreateCoupon.vue';

export default {
  name: 'CouponSettings',
  components: { CreateCoupon },

  data() {
    return {
      coupons:       [],
      pageSize:      10,
      currentPage:   1,
      loading:       false,
      search:        '',
      filterStatus:  '',
      dialogVisible: false,
      currentCoupon: {},
    };
  },

  computed: {
    filteredCoupons() {
      const q   = this.search.trim().toLowerCase();
      const st  = this.filterStatus;
      return this.coupons.filter(c => {
        const matchSearch = !q || c.code.toLowerCase().includes(q) || (c.title || '').toLowerCase().includes(q);
        const matchStatus = !st || c.status === st;
        return matchSearch && matchStatus;
      });
    },

    pagedCoupons() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredCoupons.slice(start, start + this.pageSize);
    },

    activeCoupons()   { return this.coupons.filter(c => c.status === 'active').length; },
    inactiveCoupons() { return this.coupons.filter(c => c.status !== 'active').length; },
    totalUses()       { return this.coupons.reduce((s, c) => s + (parseInt(c.used_count) || 0), 0); },
  },

  watch: {
    search()       { this.currentPage = 1; },
    filterStatus() { this.currentPage = 1; },
  },

  mounted() {
    this.fetchCoupons();
  },

  methods: {
    fetchCoupons() {
      this.loading = true;
      jQuery.post(contactum.ajaxurl, {
        action: 'get_coupons',
        nonce:  contactum.nonce,
      }, (res) => {
        this.loading = false;
        if (res.success) {
          this.coupons = res.data || [];
        }
      });
    },

    isExpired(row) {
      return row.expire_date && new Date(row.expire_date) < new Date();
    },

    openCreate() {
      this.currentCoupon = {};
      this.dialogVisible = true;
    },

    openEdit(row) {
      this.currentCoupon = { ...row };
      this.dialogVisible = true;
    },

    confirmDelete(row) {
      this.$confirm(
        `Delete coupon <strong>${row.code}</strong>? This cannot be undone.`,
        'Delete Coupon',
        {
          confirmButtonText: 'Delete',
          cancelButtonText:  'Cancel',
          dangerouslyUseHTMLString: true,
          type: 'warning',
        }
      ).then(() => {
        jQuery.post(contactum.ajaxurl, {
          action: 'delete_coupon',
          id:     row.id,
          nonce:  contactum.nonce,
        }, (res) => {
          if (res.success) {
            this.$message.success('Coupon deleted.');
            this.fetchCoupons();
          } else {
            this.$message.error(res.data?.message || 'Failed to delete.');
          }
        });
      }).catch(() => {});
    },
  },
};
</script>

<style scoped>
/* ── Wrapper ──────────────────────────────────────────────────────────── */
.ccl-wrap {
  padding: 24px;
}

/* ── Header ───────────────────────────────────────────────────────────── */
.ccl-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.ccl-header__left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ccl-header__icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #ecf5ff;
  color: #409eff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.ccl-header__title {
  margin: 0 0 2px;
  font-size: 16px;
  font-weight: 600;
  color: #1a1a2e;
  line-height: 1.2;
}
.ccl-header__sub {
  margin: 0;
  font-size: 12px;
  color: #909399;
}

/* ── Stats ────────────────────────────────────────────────────────────── */
.ccl-stats {
  display: flex;
  gap: 10px;
  margin-bottom: 16px;
}
.ccl-stat {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: #fff;
  border: 1px solid #ebeef5;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.ccl-stat__val {
  font-size: 18px;
  font-weight: 700;
  color: #2c3e50;
  line-height: 1;
}
.ccl-stat__key {
  font-size: 11px;
  color: #adb5bd;
  text-transform: uppercase;
  letter-spacing: .4px;
}
.ccl-stat--green .ccl-stat__val { color: #67c23a; }
.ccl-stat--gray  .ccl-stat__val { color: #909399; }
.ccl-stat--blue  .ccl-stat__val { color: #409eff; }

/* ── Toolbar ──────────────────────────────────────────────────────────── */
.ccl-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}
.ccl-search { width: 240px; }
.ccl-filter { width: 130px; }

/* ── Table wrapper ────────────────────────────────────────────────────── */
.ccl-table-wrap {
  border: 1px solid #ebeef5;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
}

/* Coupon cell */
.ccl-coupon-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.ccl-code {
  display: inline-block;
  font-family: 'Courier New', monospace;
  font-size: 11.5px;
  font-weight: 800;
  letter-spacing: 1.5px;
  padding: 2px 8px;
  border-radius: 4px;
  width: fit-content;
}
.ccl-code--teal   { background: #f0f9eb; color: #67c23a; }
.ccl-code--purple { background: #f0eeff; color: #6e4bcc; }
.ccl-coupon-title {
  font-size: 12px;
  color: #909399;
}

/* Discount */
.ccl-discount {
  font-size: 12.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
}
.ccl-discount--teal   { background: #f0f9eb; color: #67c23a; }
.ccl-discount--purple { background: #f0eeff; color: #6e4bcc; }

/* Usage */
.ccl-usage { min-width: 80px; }
.ccl-usage__label { font-size: 12px; color: #606266; }
.ccl-usage__used  { font-weight: 600; }
.ccl-usage__sep   { color: #dcdfe6; }
.ccl-usage__max   { color: #909399; }

/* Expiry */
.ccl-expiry { font-size: 12px; color: #606266; }
.ccl-expiry--expired { color: #f56c6c; }
.ccl-muted { font-size: 12px; color: #c0c4cc; }

/* Status */
.ccl-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 500;
  padding: 3px 9px;
  border-radius: 20px;
}
.ccl-status--on  { background: #f0f9eb; color: #67c23a; }
.ccl-status--off { background: #f5f7fa; color: #909399; }
.ccl-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

/* Actions */
.ccl-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 4px;
}
.ccl-btn {
  width: 28px;
  height: 28px;
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  cursor: pointer;
  transition: all .15s;
  color: #909399;
}
.ccl-btn:hover { border-color: transparent; }
.ccl-btn--edit:hover  { background: #ecf5ff; color: #409eff; border-color: #d9ecff; }
.ccl-btn--del:hover   { background: #fef0f0; color: #f56c6c; border-color: #fde2e2; }

/* ── Empty state ──────────────────────────────────────────────────────── */
.ccl-empty {
  padding: 56px 24px;
  text-align: center;
}
.ccl-empty__icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: #f0f4ff;
  color: #93a8d1;
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}
.ccl-empty__title {
  font-size: 14px;
  font-weight: 600;
  color: #606266;
  margin: 0 0 6px;
}
.ccl-empty__sub {
  font-size: 12px;
  color: #909399;
  margin: 0;
}

/* ── Pagination ───────────────────────────────────────────────────────── */
.ccl-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 16px;
}
</style>
