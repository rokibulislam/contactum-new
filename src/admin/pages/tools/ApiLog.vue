<template>
  <div class="ctm-tool-section">
    <div class="ctm-tool-section__head">
      <h3 class="ctm-tool-section__title">API Log</h3>
      <p class="ctm-tool-section__desc">
        Every outbound call Contactum made to a third-party service — webhooks, Mailchimp, and other integrations.
        Failed or unconfirmed deliveries can be retried right here.
      </p>
    </div>

    <div class="ctm-log">
      <div class="ctm-log__filters">
        <el-input
          v-model="filters.search"
          placeholder="Search notes…"
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

        <el-select v-model="filters.api_action" placeholder="All Actions" size="small" clearable class="ctm-log__filter">
          <el-option
            v-for="action in filterOptions.actions"
            :key="action"
            :label="actionLabel(action)"
            :value="action"
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
        empty-text="No API activity logged yet."
      >
        <el-table-column type="selection" width="40" />

        <el-table-column label="Date" width="160">
          <template slot-scope="{ row }">
            <span class="ctm-log__date">{{ row.updated_at || row.created_at }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Form" width="160">
          <template slot-scope="{ row }">
            <span>{{ row.form_title }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Action" width="140">
          <template slot-scope="{ row }">
            <el-tag size="mini" type="info">{{ actionLabel(row.action) }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Status" width="150">
          <template slot-scope="{ row }">
            <el-tag size="mini" :type="statusType(row.status)">{{ statusLabel(row.status) }}</el-tag>
            <span v-if="row.retry_count > 0" class="ctm-log__retry-count">
              · retried {{ row.retry_count }}x
            </span>
          </template>
        </el-table-column>

        <el-table-column label="Note">
          <template slot-scope="{ row }">
            <div class="ctm-log__desc" v-html="row.note"></div>
          </template>
        </el-table-column>

        <el-table-column label="" width="130">
          <template slot-scope="{ row }">
            <el-button
              v-if="row.retryable"
              size="mini"
              type="text"
              icon="el-icon-refresh-right"
              :loading="retrying === row.id"
              @click="retryLog(row)"
            >
              Retry
            </el-button>
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
  name: "ApiLog",
  data() {
    return {
      loading: false,
      retrying: null,
      logs: [],
      selected: [],
      total: 0,
      page: 1,
      perPage: 20,
      dateRange: [],
      filters: {
        search: '',
        form_id: '',
        api_action: '',
        status: '',
      },
      filterOptions: {
        statuses: [],
        actions: [],
        forms: [],
      },
    };
  },
  mounted() {
    this.fetchFilters();
    this.fetchLogs();
  },
  methods: {
    actionLabel(action) {
      const labels = {
        webhook: 'Webhook',
        zapier: 'Zapier',
        n8n: 'n8n',
        mailchimp: 'Mailchimp',
      };
      return labels[action] || action;
    },

    statusLabel(status) {
      const labels = {
        success: 'Success',
        failed: 'Failed',
        pending: 'Pending',
        manual_retry: 'Retrying…',
      };
      return labels[status] || status;
    },

    statusType(status) {
      const types = {
        success: 'success',
        failed: 'danger',
        pending: 'info',
        manual_retry: 'warning',
      };
      return types[status] !== undefined ? types[status] : 'info';
    },

    applyFilters() {
      this.page = 1;
      this.fetchLogs();
    },

    resetFilters() {
      this.filters = { search: '', form_id: '', api_action: '', status: '' };
      this.dateRange = [];
      this.page = 1;
      this.fetchLogs();
    },

    fetchFilters() {
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: { action: 'contactum_get_api_log_filters', _ajax_nonce: contactum.nonce },
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
        action: 'contactum_get_api_logs',
        _ajax_nonce: contactum.nonce,
        page: this.page,
        per_page: this.perPage,
        search: this.filters.search,
        form_id: this.filters.form_id,
        api_action: this.filters.api_action,
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

    retryLog(row) {
      this.retrying = row.id;

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'contactum_retry_api_log',
          _ajax_nonce: contactum.nonce,
          log_id: row.id,
        },
        success: (response) => {
          if (response.success) {
            this.$message.success(response.data.message);
            this.fetchLogs();
          } else {
            this.$message.error(response.data || 'Something went wrong.');
          }
        },
        complete: () => {
          this.retrying = null;
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
            action: 'contactum_delete_api_logs',
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

  &__retry-count {
    font-size: 11px;
    color: #9ca3af;
    margin-left: 4px;
  }

  &__desc {
    font-size: 12px;
    color: #6b7280;
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
