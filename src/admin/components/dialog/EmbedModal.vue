<template>
  <el-dialog :visible.sync="visible" :before-close="cancel" custom-class="embed-modal" width="480px">
    <h5 slot="title" class="el-dialog__title">Embed Form</h5>

    <!-- Copy shortcode — the fastest path, works anywhere (existing page,
         page builder, another plugin's content area) without leaving this
         screen. -->
    <div class="embed-section">
      <label class="embed-section__label">Shortcode</label>
      <div class="embed-copy-row">
        <el-input :value="shortcode" readonly />
        <el-button @click="copyShortcode">
          <i class="el-icon-document-copy"></i> Copy
        </el-button>
      </div>
      <p class="embed-section__hint">Paste this anywhere — a page, post, or widget area.</p>
    </div>

    <div class="embed-divider"><span>or insert into a page automatically</span></div>

    <div class="embed-section">
      <div class="embed-mode-switch" v-if="!mode">
        <button type="button" class="embed-mode-card" @click="mode = 'select'">
          <i class="el-icon-document"></i>
          <span>Add to Existing Page</span>
        </button>
        <button type="button" class="embed-mode-card" @click="mode = 'create'">
          <i class="el-icon-circle-plus-outline"></i>
          <span>Create New Page</span>
        </button>
      </div>

      <div v-if="mode === 'select'" class="embed-mode-body">
        <el-select v-model="pageId" placeholder="Choose a page" class="embed-mode-body__input">
          <el-option v-for="(page, index) in pages" :key="index" :label="page" :value="index"></el-option>
        </el-select>
        <div class="embed-mode-body__actions">
          <el-button @click="resetMode">Back</el-button>
          <el-button type="primary" :loading="loading" @click="embedIntoExistingPage">Insert & Edit Page</el-button>
        </div>
      </div>

      <div v-if="mode === 'create'" class="embed-mode-body">
        <el-input v-model="newPageTitle" placeholder="Enter new page title" class="embed-mode-body__input" />
        <div class="embed-mode-body__actions">
          <el-button @click="resetMode">Back</el-button>
          <el-button type="primary" :loading="loading" @click="createPage">Create & Embed</el-button>
        </div>
      </div>
    </div>

    <span slot="footer" class="dialog-footer">
      <el-button @click="cancel" type="info" class="el-button--soft">Cancel</el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
  name: "EmbedModal",
  props: {
    shortcode: {
      type: String,
      required: true,
    },
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      mode: "", // "select" or "create"
      pageId: "",
      newPageTitle: "",
      pages: window.contactum.pages,
      loading: false,
    };
  },
  methods: {
    copyShortcode() {
      navigator.clipboard.writeText(this.shortcode).then(() => {
        this.$message.success("Shortcode copied.");
      });
    },

    resetMode() {
      this.mode = "";
      this.pageId = "";
      this.newPageTitle = "";
      this.loading = false;
    },

    cancel() {
      this.$emit("close");
      this.resetMode();
    },

    embedIntoExistingPage() {
      if (!this.pageId) {
        this.$message.error("Please select a page");
        return;
      }

      this.loading = true;

      const url = new URL(window.contactum.admin_url);
      url.pathname = url.pathname.replace('admin.php', 'post.php');
      url.searchParams.set('post', this.pageId);
      url.searchParams.set('action', 'edit');
      url.searchParams.set('embed_shortcode', this.shortcode);
      url.searchParams.set('_wpnonce', window.contactum.nonce);

      window.location.href = url.toString();
    },

    createPage() {
      if (!this.newPageTitle) {
        this.$message.error("Please enter a page title");
        return;
      }

      this.loading = true;

      this.$emit('embed-form');

      const url = new URL(window.contactum.admin_url);
      url.pathname = url.pathname.replace('admin.php', 'post.php');
      url.searchParams.set("title", this.newPageTitle);
      url.searchParams.set('action', 'edit');
      url.searchParams.set("embed_shortcode", this.shortcode);
      url.searchParams.set("_wpnonce", window.contactum.nonce);

      window.location.href = url.toString();
    },
  }
}
</script>

<style>
.embed-modal .el-dialog__body {
  padding: 0 !important;
}

.embed-modal .el-dialog__header {
  padding: 0 0 20px !important;
}

.embed-modal.el-dialog {
  padding: 24px 30px;
}

.embed-modal .el-dialog__headerbtn {
  background-color: #fafafa !important;
  border-radius: 50% !important;
  font-size: 1.25rem !important;
  height: 2rem !important;
  right: 22px !important;
  top: 18px !important;
  transition: 0.2s !important;
  width: 2rem !important;
}

.embed-modal .el-dialog__title {
  margin: 0;
  font-weight: 700;
}

.embed-modal .el-dialog__footer {
  padding: 20px 0 0 !important;
}

.embed-section {
  margin-bottom: 4px;
}

.embed-section__label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #606266;
  margin-bottom: 8px;
}

.embed-section__hint {
  margin: 8px 0 0;
  font-size: 12px;
  color: #909399;
}

.embed-copy-row {
  display: flex;
  gap: 8px;
}

.embed-copy-row .el-input {
  flex: 1;
}

.embed-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 22px 0;
  font-size: 12px;
  color: #909399;
}

.embed-divider::before,
.embed-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #ebeef5;
}

.embed-mode-switch {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.embed-mode-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 20px 12px;
  border: 1.5px dashed #d1d5db;
  border-radius: 8px;
  background: #fafafa;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  transition: border-color 0.15s, background 0.15s, color 0.15s;
}

.embed-mode-card i {
  font-size: 22px;
  color: #9ca3af;
  transition: color 0.15s;
}

.embed-mode-card:hover {
  border-color: #409eff;
  border-style: solid;
  background: #eff6ff;
  color: #409eff;
}

.embed-mode-card:hover i {
  color: #409eff;
}

.embed-mode-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.embed-mode-body__input {
  width: 100%;
}

.embed-mode-body__actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
