<template>
    <div>
        <div class="contactum_card">
            <div class="contactum_card_head">
                <h2> Export Forms </h2>
            </div>
            <div class="contactum_card_body">
                <el-form label-position="top">
                    <el-form-item class="contactum-form-item">
                        <template slot="label"> Select Forms
                            <el-tooltip class="item" placement="bottom-start" popper-class="contactum_tooltip_wrap">
                                <div slot="content">
                                    <p> Select the forms you would like to export.
                                      When you click the download button below,
                                      Contactum Forms will create a JSON file for you to save to your computer.
                                      Once you've saved the downloaded file, you can use the Import tool to import the forms.
                                    </p>
                                </div>
                                <i class="ff-icon ff-icon-info-filled text-primary"></i>
                            </el-tooltip>
                        </template>
                        <el-select class="contactum_input_width" v-model="selected" multiple>
                            <el-option v-for="(form, index) in forms" :key="index"
                                    :label="'#'+ form.id +' - ' +form.name" :value="form.id"
                            ></el-option>
                        </el-select>

                      <el-button @click="selectAll()">
                        <span v-if="!allSelected">Select All</span>
                        <span v-else>Deselect All</span>
                      </el-button>

                    </el-form-item>

                    <el-button type="primary" icon="el-icon-success" @click="exportForms"> Export Forms </el-button>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "exportforms",
    data: function() {
        return {
            forms: [],
            selected: [],
            allSelected :false,
        };
    },
    mounted() {
      this.fetchForms();
    },

    methods: {

      selectAll(){
        this.allSelected = !this.allSelected;
        if (this.allSelected){
          this.selected = this.forms.map(form => form.id);
        }else{
          this.selected = [];
        }
      },

      fetchForms() {
        var self = this;

        const data = {
          action: 'contactum_get_forms',
          _ajax_nonce: contactum.nonce
        };

        jQuery.ajax({
            url: contactum.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
              if (response.success) {
                self.forms = Object.values( response.data.forms );
              }
            }
        });
      },

      exportForms() {

        if (this.selected.length) {
          const data = {
            action: 'contactum_export_forms',
            forms: [this.selected],
            format: 'json',
            nonce: window.contactum.export_nonce
          };
          location.href = contactum.ajaxurl + '?' + jQuery.param(data);
        }
      },

      /*
      exportForms() {
        if (this.selected.length) {
          jQuery.post(
              contactum.ajaxurl,
              {
                action: "contactum_export_forms",
                export_type: 'selected',
                selected_forms: this.selected
              },
              (response, textStatus, xhr) => {
                
              })
        }
      }
      */
    }
};
</script>


<style scoped lang="scss">

.contactum_input_width {
    width: 420px;
}

</style>