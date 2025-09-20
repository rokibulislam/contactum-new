<template>
    <div class="contactum-admin-tools">
      <el-tabs v-model="activeName" class="demo-tabs" @tab-click="handleClick">
        <el-tab-pane label="Export" name="first"> <exportforms /> </el-tab-pane>
        <el-tab-pane label="Import" name="second"> <importforms /> </el-tab-pane>
      </el-tabs>
    </div>
</template>

<script>
import exportforms from './tools/ExportForms.vue'
import importforms from './tools/ImportForms.vue'

export default {
    components: {
        exportforms,
        importforms
    },
    data: function() {
        return {
            activeName: "first"
        }
    },

    methods: {
        importx: function(target, plugin) {

        },

        handleClick: function() {
            
        }
    }
}

</script>

<style scoped>

.contactum-admin-tools {
  padding: 24px;
}


</style>