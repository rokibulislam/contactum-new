<template>
  <div class="ctm-tool-section">
    <div class="ctm-tool-section__head">
      <h3 class="ctm-tool-section__title">Activity Log</h3>
      <p class="ctm-tool-section__desc">
        A running record of what Contactum did behind the scenes — submissions, email notifications, and integration
        deliveries — so you can trace exactly what happened for any entry.
      </p>
    </div>

    <div class="ctm-log">
      <div class="ctm-log__filters">
        <el-input
          v-model="filters.search"
          placeholder="Search title or description…"
          prefix-icon="el-icon-search"
          size="small"
          class="ctm-log__search"
          clearable
          @keyup.enter.native="applyFilters"
          @clear="applyFilters"
        />

        <el-select v-model="filters.form_id" placeholder="All Forms" size="small" clearable class="ctm-log__filter">
          <el-option
            v-for="form in filterOptions.forms"
            :key="form.form_id"
            :label="form.title"
            :value="form.form_id"
          />
        </el-select>

        <el-select v-model="filters.component" placeholder="All Components" size="small" clearable class="ctm-log__filter">
          <el-option
            v-for="component in filterOptions.components"
            :key="component"
            :label="componentLabel(component)"
            :value="component"
          />
        </el-select>

        <el-select v-model="filters.status" placeholder="All Statuses" size="small" clearable class="ctm-log__filter">
          <el-option
            v-for="status in filterOptions.statuses"
            :key="status"
            :label="statusLabel(status)"
            :value="status"
          />
        </el-select>

        <el-date-picker
          v-model="dateRange"
          type="daterange"
          size="small"
          range-separator="to"
          start-placeholder="Start date"
          end-placeholder="End date"
          value-format="yyyy-MM-dd"
          class="ctm-log__filter ctm-log__filter--date"
        />

        <el-button size="small" type="primary" plain @click="applyFilters">Filter</el-button>
        <el-button size="small" @click="resetFilters">Reset</el-button>

        <el-button
          size="small"
          type="danger"
          plain
          icon="el-icon-delete"
          :disabled="!selected.length"
          @click="deleteSelected"
        >
          Delete Selected ({{ selected.length }})
        </el-button>

        <el-button size="small" icon="el-icon-refresh" circle @click="fetchLogs" />
      </div>

      <el-table
        v-loading="loading"
        :data="logs"
        style="width: 100%"
        @selection-change="selected = $event"
        empty-text="No activity logged yet."
      >
        <el-table-column type="selection" width="40" />

        <el-table-column label="Date" width="160">
          <template slot-scope="{ row }">
            <span class="ctm-log__date">{{ row.created_at }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Form" width="160">
          <template slot-scope="{ row }">
            <span>{{ row.form_title }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Component" width="150">
          <template slot-scope="{ row }">
            <el-tag size="mini" type="info">{{ componentLabel(row.component) }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Status" width="110">
          <template slot-scope="{ row }">
            <el-tag size="mini" :type="statusType(row.status)">{{ statusLabel(row.status) }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Details">
          <template slot-scope="{ row }">
            <div class="ctm-log__title">{{ row.title }}</div>
            <div v-if="row.description" class="ctm-log__desc" v-html="row.description"></div>
          </template>
        </el-table-column>

        <el-table-column label="" width="60">
          <template slot-scope="{ row }">
            <el-button
              size="mini"
              type="text"
              icon="el-icon-delete"
              class="ctm-log__row-delete"
              @click="deleteLogs([row.id])"
            />
          </template>
        </el-table-column>
      </el-table>

      <div class="ctm-log__pagination">
        <el-pagination
          background
          layout="total, prev, pager, next"
          :total="total"
          :page-size="perPage"
          :current-page.sync="page"
          @current-change="fetchLogs"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ActivityLog",
  data() {
    return {
      loading: false,
      logs: [],
      selected: [],
      total: 0,
      page: 1,
      perPage: 20,
      dateRange: [],
      filters: {
        search: '',
        form_id: '',
        component: '',
        status: '',
      },
      filterOptions: {
        statuses: [],
        components: [],
        forms: [],
      },
    };
  },
  mounted() {
    this.fetchFilters();
    this.fetchLogs();
  },
  methods: {
    componentLabel(component) {
      const labels = {
        form_submission: 'Form Submission',
        email_notification: 'Email Notification',
        mailchimp: 'Mailchimp',
        webhook: 'Webhook / Zapier',
        cleantalk: 'CleanTalk',
      };
      return labels[component] || component;
    },

    statusLabel(status) {
      const labels = {
        success: 'Success',
        failed: 'Failed',
        blocked: 'Blocked',
        info: 'Info',
      };
      return labels[status] || status;
    },

    statusType(status) {
      const types = {
        success: 'success',
        failed: 'danger',
        blocked: 'warning',
        info: '',
      };
      return types[status] !== undefined ? types[status] : 'info';
    },

    applyFilters() {
      this.page = 1;
      this.fetchLogs();
    },

    resetFilters() {
      this.filters = { search: '', form_id: '', component: '', status: '' };
      this.dateRange = [];
      this.page = 1;
      this.fetchLogs();
    },

    fetchFilters() {
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: { action: 'contactum_get_log_filters', _ajax_nonce: contactum.nonce },
        success: (response) => {
          if (response.success) {
            this.filterOptions = response.data;
          }
        }
      });
    },

    fetchLogs() {
      this.loading = true;

      const data = {
        action: 'contactum_get_logs',
        _ajax_nonce: contactum.nonce,
        page: this.page,
        per_page: this.perPage,
        search: this.filters.search,
        form_id: this.filters.form_id,
        component: this.filters.component,
        status: this.filters.status,
        date_from: this.dateRange && this.dateRange[0] ? this.dateRange[0] : '',
        date_to: this.dateRange && this.dateRange[1] ? this.dateRange[1] : '',
      };

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data,
        success: (response) => {
          if (response.success) {
            this.logs = response.data.logs;
            this.total = response.data.total;
          }
        },
        complete: () => {
          this.loading = false;
        }
      });
    },

    deleteLogs(ids) {
      this.$confirm(
        `Delete ${ids.length} log entr${ids.length > 1 ? 'ies' : 'y'}? This cannot be undone.`,
        'Confirm Delete',
        { confirmButtonText: 'Delete', cancelButtonText: 'Cancel', type: 'warning' }
      ).then(() => {
        jQuery.ajax({
          url: contactum.ajaxurl,
          type: 'POST',
          data: {
            action: 'contactum_delete_logs',
            _ajax_nonce: contactum.nonce,
            log_ids: ids,
          },
          success: (response) => {
            if (response.success) {
              this.$message.success(response.data.message);
              this.selected = [];
              this.fetchLogs();
            } else {
              this.$message.error(response.data || 'Something went wrong.');
            }
          }
        });
      }).catch(() => {});
    },

    deleteSelected() {
      if (!this.selected.length) return;
      this.deleteLogs(this.selected.map(row => row.id));
    },
  }
};
</script>

<style scoped lang="scss">
.ctm-log {
  &__filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
  }

  &__search {
    width: 240px;
  }

  &__filter {
    width: 160px;

    &--date {
      width: 260px;
    }
  }

  &__date {
    font-size: 12px;
    color: #6b7280;
  }

  &__title {
    font-weight: 600;
    font-size: 13px;
    color: #1e1f21;
  }

  &__desc {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
    line-height: 1.5;
    word-break: break-word;
  }

  &__row-delete {
    color: #dc2626;
  }

  &__pagination {
    display: flex;
    justify-content: flex-end;
    margin-top: 16px;
  }
}
</style>
