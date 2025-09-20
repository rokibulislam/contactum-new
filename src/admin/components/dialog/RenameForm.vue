<template>
  <el-dialog :visible.sync="post_title_editing" :before-close="cancelRename">
    <h5 slot="title" class="el-dialog__title">Rename Form</h5>
    <el-form
        class="mt-4"
        :model="{}"
        label-position="top"
        @submit.native.prevent="renameForm"
    >
      <el-form-item class="ff-form-item" label="Form Title">
        <el-input
            class="renameForm"
            v-model="post.post_title"
            name="post_title"
            type="text"
        ></el-input>
      </el-form-item>
    </el-form>

    <span slot="footer" class="dialog-footer">
      <el-button @click="cancelRename" type="info" class="el-button--soft"
      >Cancel</el-button
      >
      <el-button :loading="loading" type="primary" @click="renameForm">
        <span v-if="loading">Renaming Form</span>
        <span v-else>Rename</span>
      </el-button>
    </span>
  </el-dialog>
</template>

<script>
export default {
  name: "RenameFormModal",
  props: {
    post: {
      type: Object,
      required: true,
    },
    post_title_editing: {
      type: Boolean,
      required: true,
    },
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      loading: false
    }
  },
  methods: {
    renameForm() {
      // Emit event or save logic here
     // this.$emit("rename", this.post.post_title);
      this.$emit("close");
      this.$emit('rename-form');
    },
    cancelRename() {
      this.$emit("close");
    },
  },
};
</script>


<style>


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


</style>