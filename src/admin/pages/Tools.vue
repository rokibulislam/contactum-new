<template>
    <div class="contactum-entries">
        <exportforms />
        <importforms />
    </div>
</template>
  
<script>

import exportforms from './tools/ExportForms.vue'
import importforms from './tools/ImportForms.vue'

export default {
    name: "Tools",
    components: {
        exportforms,
        importforms
    },
    data: function() {
        return {
            requesting: false,
            loading: false,
            modules: {
                all: {},
                active: []
            }
        };
    }
};
</script>