<template>
  <div class="ctm-tool-section">
    <div class="ctm-tool-section__head">
      <h3 class="ctm-tool-section__title">Export Forms</h3>
      <p class="ctm-tool-section__desc">
        Select the forms you want to export. Contactum will generate a JSON file you can save and re-import on any site.
      </p>
    </div>

    <div class="ctm-tool-section__body">
      <el-form label-position="top" @submit.native.prevent>

        <el-form-item label="Select Forms to Export">
          <div class="ctm-export-select-row">
            <el-select
              v-model="selected"
              multiple
              filterable
              collapse-tags
              placeholder="Choose forms…"
              class="ctm-export-select"
            >
              <el-option
                v-for="form in forms"
                :key="form.id"
                :label="'#' + form.id + ' — ' + form.name"
                :value="form.id"
              />
            </el-select>

            <el-button
              size="small"
              :type="allSelected ? 'default' : 'text'"
              @click="toggleSelectAll"
            >
              {{ allSelected ? 'Deselect All' : 'Select All' }}
            </el-button>
          </div>

          <div v-if="forms.length === 0" class="ctm-hint ctm-hint--info">
            <i class="el-icon-loading"></i> Loading forms…
          </div>
          <div v-else class="ctm-hint">
            {{ forms.length }} form{{ forms.length !== 1 ? 's' : '' }} available
            <span v-if="selected.length"> · <strong>{{ selected.length }}</strong> selected</span>
          </div>
        </el-form-item>

        <el-alert
          v-if="showEmptyWarning"
          title="Please select at least one form to export."
          type="warning"
          :closable="false"
          show-icon
          style="margin-bottom: 16px;"
        />

        <el-button
          type="primary"
          icon="el-icon-download"
          :loading="exporting"
          @click="exportForms"
        >
          Download Export File
        </el-button>

      </el-form>
    </div>
  </div>
</template>

<script>
export default {
  name: "ExportForms",
  data() {
    return {
      forms: [],
      selected: [],
      allSelected: false,
      exporting: false,
      showEmptyWarning: false,
    };
  },
  mounted() {
    this.fetchForms();
  },
  methods: {
    toggleSelectAll() {
      this.allSelected = !this.allSelected;
      this.selected = this.allSelected ? this.forms.map(f => f.id) : [];
    },

    fetchForms() {
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: { action: 'contactum_get_forms', _ajax_nonce: contactum.nonce },
        success: (response) => {
          if (response.success) {
            this.forms = Object.values(response.data.forms);
          }
        }
      });
    },

    exportForms() {
      if (!this.selected.length) {
        this.showEmptyWarning = true;
        return;
      }
      this.showEmptyWarning = false;
      this.exporting = true;

      const params = {
        action: 'contactum_export_forms',
        forms: this.selected,
        format: 'json',
        nonce: window.contactum.export_nonce
      };
      location.href = contactum.ajaxurl + '?' + jQuery.param(params);

      setTimeout(() => { this.exporting = false; }, 2000);
    }
  },
  watch: {
    selected(val) {
      if (val.length) this.showEmptyWarning = false;
      this.allSelected = this.forms.length > 0 && val.length === this.forms.length;
    }
  }
};
</script>

<style scoped lang="scss">
.ctm-export-select-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.ctm-export-select {
  width: 400px;
  max-width: 100%;
}

.ctm-hint {
  margin-top: 6px;
  font-size: 12px;
  color: #9ca3af;

  &--info {
    color: #6b7280;
  }
}
</style>
