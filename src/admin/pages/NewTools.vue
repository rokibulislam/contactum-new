<template>
  <div class="ctm-tools">
    <div class="ctm-tools__header">
      <h1 class="ctm-tools__title">Tools</h1>
      <p class="ctm-tools__subtitle">Export and import your Contactum forms.</p>
    </div>

    <div class="ctm-tools__body">
      <el-tabs v-model="activeTab" class="ctm-tools__tabs">
        <el-tab-pane name="export">
          <span slot="label"><i class="el-icon-download"></i> Export Forms</span>
          <exportforms />
        </el-tab-pane>
        <el-tab-pane name="import">
          <span slot="label"><i class="el-icon-upload2"></i> Import Forms</span>
          <importforms />
        </el-tab-pane>
      </el-tabs>
    </div>
  </div>
</template>

<script>
import exportforms from './tools/ExportForms.vue'
import importforms from './tools/ImportForms.vue'

export default {
  name: "NewTools",
  components: { exportforms, importforms },
  data() {
    return { activeTab: 'export' };
  }
}
</script>

<style lang="scss">
/* shared card styles for Export/Import children */
.ctm-tool-section {
  padding: 24px 0 8px;

  &__head {
    margin-bottom: 24px;
  }

  &__title {
    font-size: 16px;
    font-weight: 600;
    color: #1e1f21;
    margin: 0 0 6px;
  }

  &__desc {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
    max-width: 600px;
    line-height: 1.6;
  }

  &__body {
    max-width: 680px;
  }
}
</style>

<style scoped lang="scss">
.ctm-tools {
  padding: 24px;
  background: #f8f9fa;
  min-height: 100vh;
}

.ctm-tools__header {
  margin-bottom: 24px;
}

.ctm-tools__title {
  font-size: 22px;
  font-weight: 600;
  color: #1e1f21;
  margin: 0 0 4px;
  line-height: 1.3;
}

.ctm-tools__subtitle {
  color: #6b7280;
  font-size: 13px;
  margin: 0;
}

.ctm-tools__body {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, 0.08);
  overflow: hidden;
  padding: 0 24px 24px;
}
</style>
