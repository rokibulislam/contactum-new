<template>
  <div class="cfl-page">

    <!-- ── Toolbar ── -->
    <div class="cfl-toolbar">
      <div class="cfl-toolbar-left">
        <h1 class="cfl-title">Forms</h1>
        <span class="cfl-count" v-if="totalForms">{{ totalForms }}</span>
      </div>
      <div class="cfl-toolbar-right">
        <el-form @submit.native.prevent="searchForms">
          <el-input
            clearable
            @clear="refetchItems"
            v-model="searchFormsKeyWord"
            type="text"
            placeholder="Search forms…"
            class="cfl-search"
            prefix-icon="el-icon-search"
          />
        </el-form>
        <DateFilter :filter_date_range.sync="filter_date_range" @date-filter-changed="onDateFilterChanged" />
        <el-button type="primary" class="cfl-add-btn" @click.prevent="showAddFormModal = true">
          <i class="el-icon-plus"></i> Add New Form
        </el-button>
      </div>
    </div>

    <!-- ── Table ── -->
    <div class="cfl-table-wrap" v-loading="loading">
      <el-table :data="paginatedData" v-if="forms !== null" class="cfl-table" row-class-name="cfl-row">

        <!-- Form Name -->
        <el-table-column label="Form" min-width="260">
          <template slot-scope="scope">
            <div class="cfl-form-cell">
              <span class="cfl-avatar" :style="{ background: avatarColor(scope.row.id) }">
                {{ initials(scope.row.name) }}
              </span>
              <div class="cfl-form-info">
                <a
                  class="cfl-form-name"
                  :href="`${admin_url}?page=contactum&route=builder#/forms/${scope.row.id}`"
                >{{ scope.row.name }}</a>
                <div class="cfl-row-actions">
                  <a :href="`${admin_url}?page=contactum&route=builder#/forms/${scope.row.id}`">
                    <i class="el-icon-edit-outline"></i> Edit
                  </a>
                  <span class="cfl-sep">|</span>
                  <a :href="`${admin_url}?page=contactum&route=settings&form_id=${scope.row.id}#/forms/settings/${scope.row.id}`">
                    <i class="el-icon-setting"></i> Settings
                  </a>
                  <span class="cfl-sep">|</span>
                  <a :href="`${admin_url}?page=contactum&route=notifications#/forms/notifications/${scope.row.id}`">
                    <i class="el-icon-bell"></i> Notifications
                  </a>
                  <span class="cfl-sep">|</span>
                  <a :href="`${admin_url}?page=contactum&route=builder#/forms/entries/${scope.row.id}`">
                    <i class="el-icon-tickets"></i> Entries
                  </a>
                  <span class="cfl-sep">|</span>
                  <a href="#" class="cfl-danger" @click.prevent="removeFormConfirmation(scope.row.id, scope.$index)">
                    <i class="el-icon-delete"></i> Delete
                  </a>
                </div>
              </div>
            </div>
          </template>
        </el-table-column>

        <!-- Shortcode -->
        <el-table-column label="Shortcode" width="220">
          <template slot-scope="scope">
            <code
              class="cfl-shortcode copy"
              :data-clipboard-text='`[contactum id="${scope.row.id}"]`'
              title="Click to copy"
            >
              <i class="el-icon-document-copy"></i>
              [contactum id="{{ scope.row.id }}"]
            </code>
          </template>
        </el-table-column>

        <!-- Date -->
        <el-table-column label="Created" width="160">
          <template slot-scope="scope">
            <span class="cfl-date">{{ formatDate(scope.row.data && scope.row.data.post_date) }}</span>
          </template>
        </el-table-column>

        <!-- Entries -->
        <el-table-column label="Entries" width="100" align="center">
          <template slot-scope="scope">
            <a
              class="cfl-entries-badge"
              :class="scope.row.entries > 0 ? 'cfl-entries-badge--has' : ''"
              :href="`${admin_url}?page=contactum&route=builder#/forms/entries/${scope.row.id}`"
            >{{ scope.row.entries || 0 }}</a>
          </template>
        </el-table-column>

        <!-- Actions -->
        <el-table-column width="60" align="center">
          <template slot-scope="scope">
            <el-dropdown trigger="click" placement="bottom-end">
              <el-button class="cfl-more-btn" size="mini">
                <i class="el-icon-more"></i>
              </el-button>
              <el-dropdown-menu slot="dropdown" class="cfl-dropdown">
                <el-dropdown-item>
                  <a :href="`${admin_url}?page=contactum&route=builder#/forms/${scope.row.id}`">
                    <i class="el-icon-edit-outline"></i> Edit
                  </a>
                </el-dropdown-item>
                <el-dropdown-item>
                  <a :href="`${admin_url}?page=contactum&route=settings&form_id=${scope.row.id}#/forms/settings/${scope.row.id}`">
                    <i class="el-icon-setting"></i> Settings
                  </a>
                </el-dropdown-item>
                <el-dropdown-item>
                  <a :href="`${admin_url}?page=contactum&route=notifications#/forms/notifications/${scope.row.id}`">
                    <i class="el-icon-bell"></i> Notifications
                  </a>
                </el-dropdown-item>
                <el-dropdown-item @click.native="duplicateFormConfirmation(scope.row.id, scope.$index)">
                  <i class="el-icon-copy-document"></i> Duplicate
                </el-dropdown-item>
                <el-dropdown-item @click.native="exportForm(scope.row.id)">
                  <i class="el-icon-download"></i> Export
                </el-dropdown-item>
                <el-dropdown-item class="cfl-dropdown-delete" @click.native="removeFormConfirmation(scope.row.id, scope.$index)">
                  <i class="el-icon-delete"></i> Delete
                </el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>

      </el-table>

      <!-- Empty state -->
      <div class="cfl-empty" v-if="forms && forms.length === 0">
        <i class="el-icon-document cfl-empty-icon"></i>
        <p>No forms found. <a href="#" @click.prevent="showAddFormModal = true">Create your first form</a>.</p>
      </div>
    </div>

    <!-- ── Pagination ── -->
    <el-pagination
      class="cfl-pagination"
      background
      @size-change="handleSizeChange"
      @current-change="goToPage"
      :current-page.sync="paginate.current_page"
      :page-sizes="[5, 10, 20, 50, 100]"
      :page-size="parseInt(paginate.per_page)"
      layout="total, sizes, prev, pager, next, jumper"
      :total="totalForms"
    />

    <CreateNewFormModal
      :templates="templates"
      :visibility.sync="showAddFormModal"
      @close="showAddFormModal = false"
    />
  </div>
</template>

<script>
import CreateNewFormModal from '../components/dialog/CreateNewFormModal.vue';
import DateFilter from '../components/common/filter.vue';

const AVATAR_COLORS = [
  '#6366f1','#8b5cf6','#ec4899','#f59e0b',
  '#10b981','#3b82f6','#ef4444','#14b8a6',
];

export default {
  name: 'AllForms',
  props: ['id'],
  components: { CreateNewFormModal, DateFilter },

  data() {
    return {
      loading: false,
      hasMounted: false,
      paginate: {
        total: 0,
        current_page: +(localStorage.getItem('formItemsCurrentPage') || 1),
        last_page: 2,
        per_page: parseInt(localStorage.getItem('formItemsPerPage')) || 5,
      },
      templates: Object.values(window.contactum.templates),
      forms: null,
      searchFormsKeyWord: '',
      clearingSearchKeyword: false,
      filter_date_range: [],
      showAddFormModal: false,
      admin_url: window.contactum.admin_url,
    };
  },

  computed: {
    totalForms() {
      return this.forms ? this.forms.length : 0;
    },
    paginatedData() {
      if (!this.forms) return [];
      const perPage = parseInt(this.paginate.per_page);
      const start = (this.paginate.current_page - 1) * perPage;
      return this.forms.slice(start, start + perPage);
    },
  },

  methods: {
    avatarColor(id) {
      return AVATAR_COLORS[id % AVATAR_COLORS.length];
    },

    initials(name) {
      if (!name) return '?';
      const words = name.trim().split(/\s+/);
      return words.length === 1
        ? words[0].slice(0, 2).toUpperCase()
        : (words[0][0] + words[1][0]).toUpperCase();
    },

    formatDate(dateStr) {
      if (!dateStr) return '—';
      const d = new Date(dateStr);
      if (isNaN(d)) return dateStr;
      return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
    },

    onDateFilterChanged(range) {
      if (!this.hasMounted) return;
      this.filter_date_range = range;
      this.paginate.current_page = 1;
      this.fetchItems();
    },

    fetchItems() {
      this.loading = true;
      const data = {
        action: 'contactum_get_forms',
        _ajax_nonce: contactum.nonce,
      };
      if (this.filter_date_range && this.filter_date_range.length) {
        data.startdate = this.filter_date_range[0];
        data.enddate = this.filter_date_range[this.filter_date_range.length - 1];
      }
      if (this.searchFormsKeyWord) data.search = this.searchFormsKeyWord;

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data,
        success: (response) => {
          if (response.success) {
            this.forms = Object.values(response.data.forms);
          }
        },
        complete: () => {
          this.loading = false;
        },
      });
    },

    searchForms() {
      this.paginate.current_page = 1;
      this.fetchItems();
    },

    goToPage(pageNumber) {
      localStorage.setItem('formItemsCurrentPage', pageNumber);
      this.paginate.current_page = pageNumber;
    },

    refetchItems() {
      this.paginate.current_page = 1;
      this.clearingSearchKeyword = true;
      this.fetchItems();
      this.$nextTick(() => { this.clearingSearchKeyword = false; });
    },

    handleSizeChange(val) {
      localStorage.setItem('formItemsPerPage', val);
      this.paginate.per_page = val;
      this.paginate.current_page = 1;
    },

    removeFormConfirmation(id, index) {
      this.$confirm('This will permanently delete the form and all its entries. Continue?', 'Delete Form', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
        confirmButtonClass: 'el-button--danger',
      }).then(() => {
        this.$prompt('Type DELETE to confirm', 'Confirm Deletion', {
          confirmButtonText: 'Confirm Delete',
          cancelButtonText: 'Cancel',
          confirmButtonClass: 'el-button--danger',
        }).then(({ value }) => {
          if (value === 'DELETE') {
            this.removeForm(id, index);
          } else {
            this.$notify.error({ title: 'Error', message: 'You must type DELETE to confirm', position: 'bottom-right' });
          }
        });
      }).catch(() => {});
    },

    exportForm(id) {
      const data = { action: 'contactum_export_forms', form_id: [id], format: 'json', nonce: window.contactum.export_nonce };
      location.href = contactum.ajaxurl + '?' + jQuery.param(data);
    },

    duplicateFormConfirmation(id) {
      const self = this;
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: { action: 'duplicate_contactum_form', post_id: id, _ajax_nonce: contactum.nonce },
        success(response) {
          if (response.success) {
            self.$notify.success({ message: 'Form duplicated successfully.', position: 'bottom-right' });
            window.location.href = `${self.admin_url}?page=contactum&route=builder#/forms/${response.data.id}`;
          } else {
            self.$notify.error({ message: 'Failed to duplicate form.', position: 'bottom-right' });
          }
        },
      });
    },

    removeForm(id, index) {
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: { action: 'delete_contactum_form', post_id: id, _ajax_nonce: contactum.nonce },
        success: (response) => {
          if (response.success) {
            this.forms.splice(index, 1);
            // Clamp current page if the deleted item emptied the last page
            const lastPage = Math.max(1, Math.ceil(this.forms.length / parseInt(this.paginate.per_page)));
            if (this.paginate.current_page > lastPage) this.paginate.current_page = lastPage;
            this.$notify.success({ message: 'Form deleted successfully.', position: 'bottom-right' });
          } else {
            this.$notify.error({ title: 'Error', message: 'Failed to delete form.', position: 'bottom-right' });
          }
        },
      });
    },
  },

  mounted() {
    this.hasMounted = true;
    this.fetchItems();
    const clipboard = new ClipboardJS('.copy');
    clipboard.on('success', () => {
      this.$notify.success({ title: 'Copied!', message: 'Shortcode copied to clipboard.', position: 'bottom-right' });
    });
  },

  watch: {
    searchFormsKeyWord(newVal, oldVal) {
      if (oldVal && !newVal && !this.clearingSearchKeyword) {
        this.paginate.current_page = 1;
        this.fetchItems();
      }
    },
  },
};
</script>

<style scoped lang="scss">

/* ── Page ── */
.cfl-page {
  padding: 24px 28px;
}

/* ── Toolbar ── */
.cfl-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
}

.cfl-toolbar-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.cfl-title {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #111827;
  line-height: 1;
}

.cfl-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 20px;
  padding: 0 7px;
  background: #e5e7eb;
  color: #374151;
  font-size: 11px;
  font-weight: 700;
  border-radius: 20px;
}

.cfl-toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.cfl-search {
  width: 240px;
}

.cfl-add-btn {
  white-space: nowrap;
}

/* ── Table wrapper ── */
.cfl-table-wrap {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
}

/* Override Element UI table chrome */
.cfl-table {
  width: 100%;

  ::v-deep .el-table__header-wrapper th {
    background: #f9fafb;
    color: #6b7280;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 0;
  }

  ::v-deep .cfl-row td {
    padding: 14px 0;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
  }

  ::v-deep .cfl-row:last-child td {
    border-bottom: none;
  }

  ::v-deep .cfl-row:hover td {
    background: #fafbff;
  }

  /* Show row actions on hover */
  ::v-deep .cfl-row .cfl-row-actions {
    opacity: 0;
    transition: opacity .15s;
  }

  ::v-deep .cfl-row:hover .cfl-row-actions {
    opacity: 1;
  }
}

/* ── Form cell ── */
.cfl-form-cell {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.cfl-avatar {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: .5px;
}

.cfl-form-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.cfl-form-name {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  text-decoration: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  &:hover {
    color: #3b82f6;
    text-decoration: underline;
  }
}

/* ── Row quick actions ── */
.cfl-row-actions {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 2px;

  a {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 12px;
    color: #4b5563;
    text-decoration: none;
    transition: color .12s;

    &:hover { color: #3b82f6; }

    i { font-size: 11px; }
  }

  .cfl-danger:hover { color: #ef4444 !important; }
}

.cfl-sep {
  color: #d1d5db;
  font-size: 11px;
  user-select: none;
}

/* ── Shortcode ── */
.cfl-shortcode {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 5px;
  padding: 4px 9px;
  font-size: 11.5px;
  color: #374151;
  cursor: pointer;
  transition: background .12s, border-color .12s;
  white-space: nowrap;

  &:hover {
    background: #e0e7ff;
    border-color: #a5b4fc;
    color: #4338ca;
  }
}

/* ── Date ── */
.cfl-date {
  font-size: 13px;
  color: #6b7280;
}

/* ── Entries badge ── */
.cfl-entries-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 22px;
  padding: 0 8px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  background: #f3f4f6;
  color: #6b7280;
  transition: background .12s, color .12s;

  &--has {
    background: #eff6ff;
    color: #1d4ed8;
  }

  &:hover {
    background: #dbeafe;
    color: #1d4ed8;
  }
}

/* ── More button ── */
.cfl-more-btn {
  border: 1px solid #e5e7eb;
  background: transparent;
  color: #6b7280;
  padding: 5px 8px;
  border-radius: 6px;

  &:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
  }
}

/* ── Dropdown delete item ── */
::v-deep .cfl-dropdown-delete * {
  color: #ef4444 !important;
}

/* ── Empty state ── */
.cfl-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 60px 20px;
  color: #9ca3af;

  a { color: #3b82f6; }

  p { margin: 12px 0 0; font-size: 14px; }
}

.cfl-empty-icon {
  font-size: 40px;
  color: #d1d5db;
}

/* ── Pagination ── */
.cfl-pagination {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
}
</style>
