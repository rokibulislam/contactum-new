<template>
<div>
  <el-table
    :data="paginatedData"
    v-loading="loading"
    stripe
    empty-text="No entries found."
    class="ctm-entries-table"
  >
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
      admin_url: window.contactum.admin_url
    }
  },
  mounted() {
    this.form_id = this.$route.params.form_id;
    if (this.$route.params.form_id) {
      this.hasForm = true;
    }
  },
  methods: {
    statusLabel(status) {
      const map = { publish: 'Published', read: 'Read', unread: 'Unread', trash: 'Trash' };
      return map[status] || (status ? status : 'Published');
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
    }
  }
}
</script>

<style scoped>

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
