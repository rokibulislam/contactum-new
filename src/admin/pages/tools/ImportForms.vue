<template>
    <div class="contactum_card">
        <div class="contactum_card_head">
            <h5 class="title"> Import Forms </h5>
            <p class="text" style="max-width: 700px;">
                Select the Fluent Forms export file(.json) you would like to import. When you click the import button below, Fluent Forms will import the forms.
            </p>
        </div>

        <el-form v-if="!importedForms" label-position="top">
            <!--Select File-->
            <el-form-item class="contactum-form-item">
                <template slot="label">
                    Select File
                    <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
                        <div slot="content">
                            <p> Click the Choose File button to upload a Fluent Forms export file from your computer
                            </p>
                        </div>

                        <i class="text-primary"></i>
                    </el-tooltip>
                </template>

                <input type="file" id="fileUpload" class="file-input contactum_input_width" @click="clear">
            </el-form-item>
            <el-button type="primary" icon="el-icon-success" @click="importForms" :loading="importing">
                Import Forms
            </el-button>
        </el-form>

      <div v-else>
        <table class="wp-list-table widefat fixed striped pages">
          <thead>
          <tr>
            <td>ID</td>
            <td>Title</td>
            <td>Action</td>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(form) in importedForms" :key="form.id">
            <td>{{form.id}}</td>
            <td>{{form.title}}</td>
            <td><a class="el-button el-button--primary el-button--mini" :href="form.edit_url"><i class="el-icon el-icon-edit"></i> <span>Edit</span></a></td>
          </tr>
          </tbody>
        </table>
      </div>

    </div>
</template>

<script>
export default {
    name: "importforms",
    data: function() {
        return {
            selected: [],
            importing: false,
            importedForms: false
        };
    },
    methods: {
        importForms() {
          var self = this;
            let file = jQuery('#fileUpload')[0].files[0];
            // let file_data = $( '#contactum-forms-import' ).prop( 'files' )[0];
            let data = new FormData();
            data.append('format', 'json');
            data.append('importFile', file);
            data.append('action', 'contactum_import_form');
            data.append( 'security', contactum.nonce );

            jQuery.ajax({
                url: contactum.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: (response) => {
                    self.clear();
                    self.$emit('forms-imported', true)
                    self.importedForms = response.data.data;
                    self.$notify.success({
                      title: '',
                      message: response.data.message,
                      position: "bottom-right"
                    });
                },
                error: (error) => {
                    self.clear();
                    self.$emit('forms-imported', false);
                    self.$notify.fail({
                      title: 'Error',
                      message: 'Failed to imported',
                      position: "bottom-right"
                    });
                }
            });
        },

        clear() {
            this.importing = false;
            jQuery('#fileUpload').val('');
        }
    }
};
</script>