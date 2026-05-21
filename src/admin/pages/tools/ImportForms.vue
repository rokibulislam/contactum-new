<template>
  <div class="ctm-tool-section">
    <div class="ctm-tool-section__head">
      <h3 class="ctm-tool-section__title">Import Forms</h3>
      <p class="ctm-tool-section__desc">
        Upload a Contactum JSON export file to restore your forms. All field settings, notifications,
        and form settings will be imported.
      </p>
    </div>

    <div class="ctm-tool-section__body">

      <!-- Upload form -->
      <template v-if="!importedForms">
        <div
          class="ctm-upload-zone"
          :class="{ 'ctm-upload-zone--drag': dragging, 'ctm-upload-zone--has-file': selectedFile }"
          @dragover.prevent="dragging = true"
          @dragleave.prevent="dragging = false"
          @drop.prevent="onDrop"
          @click="$refs.fileInput.click()"
        >
          <input
            ref="fileInput"
            type="file"
            accept=".json"
            class="ctm-upload-zone__input"
            @change="onFileChange"
          />

          <template v-if="!selectedFile">
            <i class="el-icon-upload ctm-upload-zone__icon"></i>
            <p class="ctm-upload-zone__text">
              Drop your JSON file here, or <span class="ctm-upload-zone__link">browse</span>
            </p>
            <p class="ctm-upload-zone__hint">Supports .json files exported from Contactum</p>
          </template>

          <template v-else>
            <i class="el-icon-document ctm-upload-zone__icon ctm-upload-zone__icon--ready"></i>
            <p class="ctm-upload-zone__text ctm-upload-zone__text--ready">{{ selectedFile.name }}</p>
            <p class="ctm-upload-zone__hint">{{ fileSize }} · Click to change file</p>
          </template>
        </div>

        <el-alert
          v-if="error"
          :title="error"
          type="error"
          :closable="false"
          show-icon
          style="margin: 12px 0;"
        />

        <div style="margin-top: 16px; display: flex; gap: 10px; align-items: center;">
          <el-button
            type="primary"
            icon="el-icon-upload2"
            :loading="importing"
            :disabled="!selectedFile"
            @click="importForms"
          >
            Import Forms
          </el-button>

          <el-button
            v-if="selectedFile"
            @click="clearFile"
          >
            Clear
          </el-button>
        </div>
      </template>

      <!-- Results -->
      <template v-else>
        <el-alert
          :title="importedForms.length + ' form' + (importedForms.length !== 1 ? 's' : '') + ' imported successfully.'"
          type="success"
          :closable="false"
          show-icon
          style="margin-bottom: 20px;"
        />

        <el-table :data="importedForms" class="ctm-import-results">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="title" label="Form Name" />
          <el-table-column label="Action" width="120">
            <template slot-scope="scope">
              <a :href="scope.row.edit_url">
                <el-button type="primary" size="mini" icon="el-icon-edit">Edit</el-button>
              </a>
            </template>
          </el-table-column>
        </el-table>

        <el-button style="margin-top: 20px;" icon="el-icon-refresh" @click="reset">
          Import Another File
        </el-button>
      </template>

    </div>
  </div>
</template>

<script>
export default {
  name: "ImportForms",
  data() {
    return {
      selectedFile: null,
      importing: false,
      importedForms: null,
      error: '',
      dragging: false,
    };
  },
  computed: {
    fileSize() {
      if (!this.selectedFile) return '';
      const kb = this.selectedFile.size / 1024;
      return kb < 1024 ? kb.toFixed(1) + ' KB' : (kb / 1024).toFixed(1) + ' MB';
    }
  },
  methods: {
    onFileChange(e) {
      this.setFile(e.target.files[0]);
    },

    onDrop(e) {
      this.dragging = false;
      const file = e.dataTransfer.files[0];
      if (file) this.setFile(file);
    },

    setFile(file) {
      this.error = '';
      if (!file) return;
      if (!file.name.endsWith('.json')) {
        this.error = 'Only .json files are supported.';
        return;
      }
      this.selectedFile = file;
    },

    clearFile() {
      this.selectedFile = null;
      this.error = '';
      this.$refs.fileInput.value = '';
    },

    reset() {
      this.importedForms = null;
      this.clearFile();
    },

    importForms() {
      if (!this.selectedFile) {
        this.error = 'Please select a file first.';
        return;
      }
      this.error = '';
      this.importing = true;

      const formData = new FormData();
      formData.append('format', 'json');
      formData.append('importFile', this.selectedFile);
      formData.append('action', 'contactum_import_form');
      formData.append('security', contactum.nonce);

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: (response) => {
          this.importing = false;
          if (response.success) {
            this.importedForms = response.data.data;
            this.$notify.success({
              title: 'Import complete',
              message: response.data.message,
              position: 'bottom-right'
            });
          } else {
            this.error = response.data || 'Import failed. Please check the file and try again.';
          }
        },
        error: () => {
          this.importing = false;
          this.error = 'Request failed. Please try again.';
        }
      });
    }
  }
};
</script>

<style scoped lang="scss">
.ctm-upload-zone {
  border: 2px dashed #d1d5db;
  border-radius: 10px;
  padding: 40px 24px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  background: #fafafa;
  position: relative;

  &:hover {
    border-color: #409eff;
    background: #f0f7ff;
  }

  &--drag {
    border-color: #409eff;
    background: #ecf5ff;
  }

  &--has-file {
    border-color: #67c23a;
    background: #f0f9eb;
  }

  &__input {
    display: none;
  }

  &__icon {
    font-size: 40px;
    color: #c0c4cc;
    display: block;
    margin-bottom: 12px;

    &--ready {
      color: #67c23a;
    }
  }

  &__text {
    font-size: 14px;
    color: #374151;
    margin: 0 0 4px;

    &--ready {
      font-weight: 500;
    }
  }

  &__link {
    color: #409eff;
    text-decoration: underline;
  }

  &__hint {
    font-size: 12px;
    color: #9ca3af;
    margin: 0;
  }
}

.ctm-import-results {
  width: 100%;
  max-width: 600px;
}
</style>
