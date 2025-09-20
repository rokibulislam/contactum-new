<template>
  <el-dialog :visible.sync="visible" :before-close="cancelRename">
    <h5 slot="title" class="el-dialog__title">Embed  </h5>

     <!-- Toggle buttons -->
    <div class="mode-switch" v-if="!mode">
      <el-button :type="mode === 'select' ? 'primary' : 'default'" @click="mode = 'select'">
        Select Existing Page
      </el-button>
      <el-button  class="create-page" :type="mode === 'create' ? 'primary' : 'default'" @click="mode = 'create'">
        Create New Page
      </el-button>
    </div>


    <!-- Existing Pages Dropdown -->
    <div v-if="mode === 'select'" class="select-wrapper">
      <el-select v-model="pageId" placeholder="Choose a page" @change="embedForm">
        <el-option v-for="(page, index) in pages" :key="index" :label="page" :value="index"></el-option>
      </el-select>
      <el-button type="warning" class="back-btn" @click="resetMode">Back</el-button>
    </div>

    <!-- Create New Page Form -->
    <div v-if="mode === 'create'" class="create-wrapper">
      <el-input v-model="newPageTitle" placeholder="Enter new page title"></el-input>
      <div class="btn-group">
        <el-button type="primary" :loading="loading" @click="createPage">Create & Embed</el-button>
        <el-button type="warning" class="back-btn" @click="resetMode">Back</el-button>
      </div>
    </div>
    <!--
    <el-button v-if="mode === 'select' || mode==='create' " type="warning" @click="resetButtons">Back</el-button>
    -->
    <span slot="footer" class="dialog-footer">
      <el-button @click="cancelRename" type="info" class="el-button--soft"
      >Cancel</el-button
      >
    </span>
</el-dialog>
</template>
<script>
export default {
  name: "EmbedModal",
    props: {
      shortcode: {
        type: Boolean,
        required: true
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
      loading: false
    };
  },
  methods: {

    renameForm() {
      this.$emit("close");
      this.mode = '';
    },


    resetMode() {
      this.mode = "";
      this.pageId = "";
      this.newPageTitle = "";
      this.loading = false;
    },

    cancelRename() {
      this.$emit("close");
      this.mode = '';
      this.pageId = "";
      this.newPageTitle = "";
    },

    embedForm() {

      if (!this.pageId) {
        this.$message.error("Please select a page");
        return;
      }

      this.loading = true;

      const url = new URL(window.contactum.admin_url); // e.g. admin.php
      url.pathname = url.pathname.replace('admin.php', 'post.php'); // Go to post edit page
      url.searchParams.set('post', this.page_id);
      url.searchParams.set('action', 'edit');
      url.searchParams.set('embed_shortcode', this.shortcode); // Example shortcode
      url.searchParams.set('_wpnonce', window.contactum.nonce);

      window.location.href = url.toString(); // Redirect to WP admin edit page
    },

    goBack() {
      this.mode = '';
      this.pageId = '';
      this.form_name = '';
    },

    createPage() {
      
      if (!this.newPageTitle) {
        this.$message.error("Please enter a page title");
        return;
      }

      this.loading = true;

      this.$emit('embed-form');

      const url = new URL(window.contactum.admin_url); // e.g. admin.php
      url.pathname = url.pathname.replace('admin.php', 'post.php'); // Go to post edit page
      url.searchParams.set("title", this.newPageTitle);
      url.searchParams.set('action', 'edit');
      url.searchParams.set("embed_shortcode", this.shortcode);
      url.searchParams.set("_wpnonce", window.contactum.nonce);

      window.location.href = url.toString();
    }

  }
}
</script>


<style>

.create-page {
  margin-top: 10px;
}

.el-dialog__body {
  padding: 0px !important;
}

.el-dialog__header {
  padding: 0 0 24px !important;
}

.el-dialog {
  padding: 24px 30px;
}

.el-dialog__headerbtn {
  background-color: #fafafa !important;
  border-radius: 50% !important;
  font-size: 1.25rem !important;
  height: 2rem !important;
  right: 22px !important;
  top: 18px !important;
  transition: 0.2s !important;
  width: 2rem !important;
}

.el-dialog__title {
  margin: 0;
}

.el-form-item__label {
  align-items: center !important;
  color: #1e1f21 !important;
  display: flex !important;
  font-size: 15px !important;
  font-weight: 500 !important;
}

.el-form-item__label {
  line-height: 1 !important;
  padding-bottom: 16px !important;
}

.el-dialog__footer {
  padding: 0 !important;
}

.btn-group {
  margin-top: 10px;
}

</style>