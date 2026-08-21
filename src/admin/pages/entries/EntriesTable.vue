<template>
<div>

  <!-- ── Bulk actions bar ── -->
  <div class="ctm-bulk-bar" v-if="selectedRows.length">
    <span class="ctm-bulk-count">{{ selectedRows.length }} selected</span>
    <el-button size="mini" @click="bulkMarkStatus('read')">
      <i class="el-icon-view"></i> Mark as Read
    </el-button>
    <el-button size="mini" @click="bulkMarkStatus('unread')">
      <i class="el-icon-message"></i> Mark as Unread
    </el-button>
    <el-button size="mini" type="danger" plain @click="bulkDeleteConfirmation">
      <i class="el-icon-delete"></i> Delete
    </el-button>
    <el-button size="mini" class="ctm-bulk-clear" @click="clearSelection">
      <i class="el-icon-close"></i> Clear
    </el-button>
  </div>

  <el-table
    :data="paginatedData"
    v-loading="loading"
    stripe
    empty-text="No entries found."
    class="ctm-entries-table"
    ref="table"
    @selection-change="onSelectionChange"
  >
    <el-table-column type="selection" width="44"> </el-table-column>

    <el-table-column prop="id" label="#" width="64" align="center"> </el-table-column>

    <el-table-column prop="post_title" label="Form" min-width="160" show-overflow-tooltip> </el-table-column>

    <el-table-column label="User" width="140">
      <template slot-scope="scope">
        <span class="ctm-user-cell">
          <i class="el-icon-user"></i>
          {{ scope.row.user_id > 0 ? 'User #' + scope.row.user_id : 'Guest' }}
        </span>
      </template>
    </el-table-column>

    <el-table-column label="Status" width="120" align="center">
      <template slot-scope="scope">
        <span :class="'ctm-status ctm-status--' + (scope.row.status || 'publish')">
          {{ statusLabel(scope.row.status) }}
        </span>
      </template>
    </el-table-column>

    <el-table-column prop="created_at" label="Submitted" width="190">
      <template slot-scope="scope">
        <span class="ctm-date-cell">
          <i class="el-icon-time"></i>
          {{ scope.row.created_at }}
        </span>
      </template>
    </el-table-column>

    <el-table-column label="Actions" width="155" align="right">
      <template slot-scope="scope">
        <div class="ctm-actions">
          <a v-if="hasForm"
              :href="`${admin_url}?page=contactum-entries#/forms/${form_id}/entries/${scope.row.id}`"
          >
            <el-button type="primary" size="mini" icon="el-icon-view">View</el-button>
          </a>
          <router-link v-else
              :to="{
                  name: 'form-entry',
                  params: { form_id: scope.row.form_id, entry_id: scope.row.id }
              }"
          >
            <el-button type="primary" size="mini" icon="el-icon-view">View</el-button>
          </router-link>

          <el-button
            type="danger"
            size="mini"
            icon="el-icon-delete"
            plain
            @click="confirmDelete(scope.row)"
          ></el-button>
        </div>
      </template>
    </el-table-column>
  </el-table>
</div>
</template>

<script>

export default {
  name: "EntriesTable",
  props: {
    paginatedData: {
      type: Array,
      required: true
    },
    form: {
      type: [String, Number],
      required: false,
      default: ''
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      hasForm: false,
      admin_url: window.contactum.admin_url,
      selectedRows: []
    }
  },
  mounted() {
    this.form_id = this.$route.params.form_id;
    if (this.$route.params.form_id) {
      this.hasForm = true;
    }
  },
  watch: {
    paginatedData() {
      this.clearSelection();
    }
  },
  methods: {
    statusLabel(status) {
      const map = { publish: 'Published', read: 'Read', unread: 'Unread', trash: 'Trash' };
      return map[status] || (status ? status : 'Published');
    },

    onSelectionChange(rows) {
      this.selectedRows = rows;
    },

    clearSelection() {
      this.selectedRows = [];
      this.$refs.table && this.$refs.table.clearSelection();
    },

    confirmDelete(row) {
      this.$confirm(`Delete entry #${row.id}? This cannot be undone.`, 'Delete Entry', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
        confirmButtonClass: 'el-button--danger'
      }).then(() => {
        this.deleteEntry(row.id);
      }).catch(() => {});
    },

    deleteEntry(entryId) {
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'contactum_delete_entry',
          _ajax_nonce: contactum.nonce,
          entry_id: entryId
        },
        success: (response) => {
          if (response.success) {
            this.$message({ type: 'success', message: 'Entry deleted.' });
            this.$emit('entry-deleted', entryId);
          } else {
            this.$message({ type: 'error', message: response.data || 'Could not delete entry.' });
          }
        },
        error: () => {
          this.$message({ type: 'error', message: 'Request failed.' });
        }
      });
    },

    bulkMarkStatus(status) {
      const ids = this.selectedRows.map((row) => row.id);
      if (!ids.length) return;

      Promise.all(ids.map((id) => jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'contactum_update_entry_status',
          _ajax_nonce: contactum.nonce,
          entry_id: id,
          status,
        },
      }))).then((responses) => {
        const failed = responses.filter((r) => !r.success).length;
        const succeededIds = ids.filter((id, index) => responses[index].success);

        this.$emit('entries-status-changed', { ids: succeededIds, status });
        this.clearSelection();

        if (failed) {
          this.$message({ type: 'error', message: `${failed} of ${ids.length} entries could not be updated.` });
        } else {
          this.$message({ type: 'success', message: `${ids.length} entrie(s) marked as ${this.statusLabel(status)}.` });
        }
      }).catch(() => {
        this.$message({ type: 'error', message: 'Failed to update the selected entries.' });
      });
    },

    bulkDeleteConfirmation() {
      const ids = this.selectedRows.map((row) => row.id);
      if (!ids.length) return;

      this.$confirm(`Delete ${ids.length} entrie(s)? This cannot be undone.`, 'Delete Entries', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
        confirmButtonClass: 'el-button--danger'
      }).then(() => {
        this.bulkDelete(ids);
      }).catch(() => {});
    },

    bulkDelete(ids) {
      Promise.all(ids.map((id) => jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'contactum_delete_entry',
          _ajax_nonce: contactum.nonce,
          entry_id: id,
        },
      }))).then((responses) => {
        const failed = responses.filter((r) => !r.success).length;
        const succeededIds = ids.filter((id, index) => responses[index].success);

        this.$emit('entries-deleted', succeededIds);
        this.clearSelection();

        if (failed) {
          this.$message({ type: 'error', message: `${failed} of ${ids.length} entries could not be deleted.` });
        } else {
          this.$message({ type: 'success', message: `${ids.length} entrie(s) deleted.` });
        }
      }).catch(() => {
        this.$message({ type: 'error', message: 'Failed to delete the selected entries.' });
      });
    }
  }
}
</script>

<style scoped>

/* ── Bulk actions bar ── */
.ctm-bulk-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  border-bottom: 1px solid #f0f0f0;
  background: #eff6ff;
}

.ctm-bulk-count {
  font-size: 13px;
  font-weight: 600;
  color: #1d4ed8;
  margin-right: 4px;
}

.ctm-bulk-clear {
  margin-left: auto;
  border: none;
  background: transparent;
  color: #6b7280;
}

.ctm-bulk-clear:hover {
  color: #374151;
  background: transparent;
}

/* ── Actions cell ── */
.ctm-actions {
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: flex-end;
  white-space: nowrap;
}

/* ── Status badge ── */
.ctm-status {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
}
.ctm-status--publish,
.ctm-status--read {
  background: #ecfdf5;
  color: #059669;
}
.ctm-status--unread {
  background: #eff6ff;
  color: #2563eb;
}
.ctm-status--trash {
  background: #fef2f2;
  color: #dc2626;
}

/* ── Cell helpers ── */
.ctm-user-cell,
.ctm-date-cell {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #374151;
  font-size: 13px;
  white-space: nowrap;
}
.ctm-user-cell i,
.ctm-date-cell i {
  color: #9ca3af;
  font-size: 13px;
}
</style>
