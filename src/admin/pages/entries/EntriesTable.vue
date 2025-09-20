<template>
<div>
  <el-table :data="paginatedData">
    <el-table-column prop="id" label="Submission Id:"> </el-table-column>
    <el-table-column prop="post_title" label="Form"> </el-table-column>
    <el-table-column prop="user_id" label="User"> </el-table-column>
    <el-table-column prop="user_id" label="Status"> </el-table-column>
    <el-table-column prop="created_at" label="Time"> </el-table-column>
    <el-table-column
        label="Actions"
    >

      <template slot-scope="scope">

        <a v-if="hasForm"
            :href="`${admin_url}?page=contactum-entries#/forms/${form_id}/entries/${scope.row.id}`"
        >
          <el-button type="primary">View</el-button>
        </a>

        <router-link v-else
            :to="{
                name: 'form-entry',
                params: {
                  form_id: scope.row.form_id,
                  entry_id: scope.row.id
                }
            }"
        >
          <el-button type="primary">
            View
          </el-button>
        </router-link>
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
    }
  },
  data() {
    return {
      hasForm: false,
      admin_url: window.contactum.admin_url
    }
  },
  mounted() {
    this.form_id =  this.$route.params.form_id;
    if( this.$route.params.form_id ) {
      this.hasForm = true;
    }
  },
}
</script>

<style scoped>

</style>