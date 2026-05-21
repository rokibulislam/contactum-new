<template>
<div class="entry_details">

  <div class="entry_details_topbar">
    <el-button icon="el-icon-arrow-left" size="small" @click="goBack">Back to Entries</el-button>
    <div class="entry_details_actions">
      <el-button icon="el-icon-printer" size="small" @click="printEntry">Print</el-button>
      <el-button type="danger" icon="el-icon-delete" size="small" @click="confirmDelete">Delete Entry</el-button>
    </div>
  </div>

  <el-row :gutter="20" v-if="metadata">
    <el-col :span="16">
      <div class="contactum_card">
        <div class="contactum_card_head">
          <div class="entry_info_box_title">Entry Details</div>
        </div>
        <div class="contactum_card_body">
          <table class="form-table">
            <tr
                v-for="(field, index) in fields"
                :key="index"
                class="contactum_table_row"
            >
              <td class="form-title">{{ field.label }}</td>
              <td>
                <span v-if="Array.isArray(field.value)" class="form-value">
                  {{ field.value.join(' | ') }}
                </span>
                <span v-else class="form-value">{{ field.value }}</span>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </el-col>

    <el-col :span="8">
      <div class="contactum_card">
        <div class="contactum_card_head">
          <div class="entry_info_box_title">Submission Info</div>
        </div>
        <div class="contactum_card_body">
          <ul class="contactum-forms-entry-details-meta contactum_list_border_bottom">
            <li class="contactum-forms-entry-id">
              <span class="lead-title">Entry ID:</span>
              <span class="lead-text"><strong>{{ metadata.id }}</strong></span>
            </li>
            <li class="contactum-forms-entry-date">
              <span class="lead-title">Submitted:</span>
              <span class="lead-text"><strong>{{ metadata.created }}</strong></span>
            </li>
            <li class="contactum-forms-entry-user">
              <span class="lead-title">User:</span>
              <span class="lead-text"><strong>{{ metadata.user || 'Guest' }}</strong></span>
            </li>
            <li class="contactum-forms-entry-ip">
              <span class="lead-title">User IP:</span>
              <span class="lead-text"><strong>{{ metadata.ip_address }}</strong></span>
            </li>
            <li class="contactum-forms-entry-referer">
              <span class="lead-title">Referer:</span>
              <span class="lead-text">
                <strong><a :href="metadata.referer" target="_blank" rel="noopener">View</a></strong>
              </span>
            </li>
          </ul>
        </div>
      </div>
    </el-col>
  </el-row>

  <div v-else class="entry_loading">
    <el-skeleton :rows="6" animated />
  </div>

</div>
</template>

<script>

export default {
  name: "Entry",
  props: ['entry_id', 'form_id'],
  data() {
    return {
      metadata: null,
      fields: [],
    }
  },
  mounted() {
    this.fetchItem();
  },
  methods: {

    goBack() {
      this.$router.push({ name: 'form-entries' });
    },

    printEntry() {
      const printContent = this.buildPrintContent();
      const win = window.open('', '_blank', 'width=800,height=600');
      win.document.write(printContent);
      win.document.close();
      win.focus();
      win.print();
      win.close();
    },

    buildPrintContent() {
      const rows = this.fields.map(f => {
        const val = Array.isArray(f.value) ? f.value.join(' | ') : (f.value || '');
        return `<tr><td style="width:35%;font-weight:600;padding:10px 12px;border-bottom:1px solid #e5e7eb;">${f.label}</td><td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;">${val}</td></tr>`;
      }).join('');

      const meta = this.metadata;
      return `<!DOCTYPE html><html><head><title>Entry #${meta.id}</title>
        <style>body{font-family:sans-serif;padding:24px;color:#111827;}h1{font-size:18px;margin-bottom:4px;}p{color:#6b7280;font-size:13px;margin-bottom:20px;}table{width:100%;border-collapse:collapse;}@media print{button{display:none;}}</style>
        </head><body>
        <h1>Entry #${meta.id}</h1>
        <p>Submitted: ${meta.created} &nbsp;|&nbsp; IP: ${meta.ip_address}</p>
        <table>${rows}</table>
        </body></html>`;
    },

    confirmDelete() {
      this.$confirm(`Delete entry #${this.entry_id}? This cannot be undone.`, 'Delete Entry', {
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        type: 'warning',
        confirmButtonClass: 'el-button--danger'
      }).then(() => {
        jQuery.ajax({
          url: contactum.ajaxurl,
          type: 'POST',
          data: {
            action: 'contactum_delete_entry',
            _ajax_nonce: contactum.nonce,
            entry_id: this.entry_id
          },
          success: (response) => {
            if (response.success) {
              this.$message({ type: 'success', message: 'Entry deleted.' });
              this.$router.push({ name: 'form-entries' });
            } else {
              this.$message({ type: 'error', message: response.data || 'Could not delete entry.' });
            }
          }
        });
      }).catch(() => {});
    },

    fetchItem() {
      const self = this;
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'contactum_get_entries_details',
          _ajax_nonce: contactum.nonce,
          form_id: this.form_id,
          entry_id: this.entry_id
        },
        success: function(response) {
          if (response.success) {
            self.metadata = response.data.metadata;
            self.fields   = response.data.fields;
          }
        }
      });
    }
  }
}
</script>

<style scoped lang="scss">

.entry_details {
  padding: 24px;
}

.entry_details_topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.entry_details_actions {
  display: flex;
  gap: 8px;
}

.entry_loading {
  padding: 40px 0;
}

.form-table {
  width: 100%;
  border-collapse: collapse;

  tr {
    border-bottom: 1px solid #e5e7eb;
  }

  td {
    padding: 12px 16px;
    vertical-align: top;
  }

  .form-title {
    width: 30%;
    font-weight: 500;
    color: #374151;
  }

  .form-value {
    color: #111827;
    font-size: 14px;
    line-height: 1.6;

    a {
      color: #2563eb;
      text-decoration: none;

      &:hover {
        text-decoration: underline;
      }
    }
  }
}

.entry_info_box_title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 12px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 6px;
}

.contactum_list_border_bottom > li:not(:last-child) {
  border-bottom: 1px solid #ececec;
  margin-bottom: 12px;
  padding-bottom: 12px;
}

.contactum_list_border_bottom li {
  display: flex;
}

.contactum_list_border_bottom > li {
  font-size: 15px;
}

.lead-title {
  color: #1e1f21;
  font-size: 15px;
  font-weight: 500;
  flex-shrink: 0;
  width: 120px;
}

.lead-text {
  color: #606266;
  font-size: 13px;
}

</style>
