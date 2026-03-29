<template>
    <div>
      <div>
        <h1 class="contactum_section_title">Forms </h1>
      </div>
      <el-row class="ff_all_forms_action_row">
        <el-col :sm="12">
              <el-button type="primary" class="el-button el-button--primary" @click.prevent="showAddFormModal = true">
                <i class="el-icon-plus el-icon-left el-icon"></i>
                <span>Add New Form</span>
              </el-button>
        </el-col>

        <el-col :sm="12">
          <div class="justify-end">
            <el-form @submit.native.prevent="searchForms">
              <el-input
                  clearable
                  @clear="refetchItems"
                  v-model="searchFormsKeyWord"
                  type="text"
                  placeholder="Search Forms"
                  class="all-forms-search"
                  prefix-icon="el-icon-search"
              >
              </el-input>
            </el-form>
              <DateFilter :filter_date_range.sync="filter_date_range"  @date-filter-changed="onDateFilterChanged" />
          </div>
        </el-col>
      </el-row>

      <div class="forms_table contactum_table">
        <el-table :data="paginatedData" v-if="forms !=null ">
          {{ paginatedData }}
          <el-table-column prop="id" label="Id"> </el-table-column>
          <el-table-column
              prop="name"
              label="Form Name"
          >
            <template slot-scope="scope">

              <strong>
                {{ scope.row.name }}
              </strong>

              <div class="row-actions">

              <!--     <span class="row-actions-item contactum_edit">
                       <a :href="admin_url + '?page=contactum&route=builder#/forms/' + scope.row.id"> Edit </a>
                  </span> -->

<!--                  <span class="row-actions-item contactum_edit">-->
<!--                    <a :href="admin_url + '?page=contactum&route=settings&form_id='+ scope.row.id +'#/forms/settings/' + scope.row.id"> Settings </a>-->
<!--                  </span>-->

<!--                  <span class="row-actions-item contactum_edit">-->
<!--                    <a :href="admin_url + '?page=contactum&route=notifications#/forms/notifications/' + scope.row.id"> Notifications </a>-->
<!--                  </span>-->

<!-- 
                  <span class="row-actions-item contactum_edit">
                    <a :href="admin_url + '?page=contactum&route=builder#/forms/entries/' + scope.row.id"> Entries </a>
                  </span>

                  <span class="row-actions-item contactum_duplicate">
                    <a href="#" @click.prevent="duplicateFormConfirmation(scope.row.id, scope.$index)"> Duplicate </a>
                  </span>

                  <span class="row-actions-item contactum_export">
                    <a href="#"  @click.prevent="exportForm(scope.row.id)" > Export </a>
                  </span>

                  <span class="row-actions-item trash">
                    <a href="#" @click.prevent="removeFormConfirmation(scope.row.id, scope.$index)"> Delete </a>
                  </span>

-->

 
              </div>

            </template>

          </el-table-column>

          <el-table-column prop="id" label="Shortcode">

            <template slot-scope="scope">
              <div class="contactum_shortcode_wrap">
                <code class="copy  contactum_shortcode_btn contactum_shortcode_btn_thin" title="Click to copy" :data-clipboard-text='`[contactum id="${scope.row.id}"]`'>
                  <span><i class="el-icon el-icon-document-copy"></i> [contactum id="{{ scope.row.id }}"]</span>
                </code>
              </div>
            </template>

          </el-table-column>
          <el-table-column prop="data.post_date" label="Date"> </el-table-column>
          <el-table-column prop="entries" label="Entries"> </el-table-column>
          
          <el-table-column> 


<template slot-scope="scope"> 
<div class="row-actions">


<el-dropdown trigger="click" size="mini">
  <el-button>
    <i class="el-icon-more"> </i>
  </el-button>

  <el-dropdown-menu slot="dropdown">
    <el-dropdown-item>
      <a :href="`${admin_url}?page=contactum&route=builder#/forms/${scope.row.id}`">
        <i class="el-icon-edit"></i> Edit
      </a>
    </el-dropdown-item>

    <el-dropdown-item @click.native="removeFormConfirmation(scope.row.id, scope.$index)">
      <i class="el-icon-delete"></i> Delete
    </el-dropdown-item>

    <el-dropdown-item>
      <a :href="`${admin_url}?page=contactum&route=builder#/forms/entries/${scope.row.id}`">
        Entries
      </a>
    </el-dropdown-item>

    <el-dropdown-item @click.native="duplicateFormConfirmation(scope.row.id, scope.$index)">
      Duplicate
    </el-dropdown-item>

    <el-dropdown-item @click.native="exportForm(scope.row.id)">
      Export
    </el-dropdown-item>
  </el-dropdown-menu>
</el-dropdown>

</div>


</template>

          </el-table-column>

        </el-table>
      </div>

      <el-pagination
          class="contactum_pagination"
          background
          @size-change="handleSizeChange"
          @current-change="goToPage"
          :current-page.sync="paginate.current_page"
          :page-sizes="[5, 10, 20, 50, 100]"
          :page-size="parseInt(paginate.per_page)"
          layout="total, sizes, prev, pager, next, jumper"
          :total="paginate.total">
      </el-pagination>

      <CreateNewFormModal
          :templates=templates
          :visibility.sync=showAddFormModal
          @close="showAddFormModal = false;"
      />
    </div>
</template>

<script>

import CreateNewFormModal from '../components/dialog/CreateNewFormModal.vue'
import DateFilter from '../components/common/filter.vue'

export default {
  name: "AllForms",
  props: ["id"],
  components: {
    CreateNewFormModal,
    DateFilter
  },
  data() {
    return {
      hasMounted: false,
      paginate: {
        total: 0,
        current_page: +(localStorage.getItem('formItemsCurrentPage') || 1),
        last_page: 2,
        per_page: localStorage.getItem('formItemsPerPage') || 5
      },
      templates: Object.values(window.contactum.templates),
      forms:  null,
      searchFormsKeyWord: '',
      clearingSearchKeyword: false,
      filter_date_range: [],
      showAddFormModal: false,
      entries: [],
      admin_url: window.contactum.admin_url
    }
  },
  computed: {
    paginatedData() {
      const start = (this.paginate.current_page - 1) * this.paginate.per_page;
      const end = start + this.paginate.per_page;
      return this.forms.slice(start, end);
    }
  },
  methods: {

    onDateFilterChanged( range ) {
      if (!this.hasMounted) return;
      this.filter_date_range = range;
      if( range != null ) {
        this.fetchItems();
      }
    },

    fetchItems( search = null) {
      const self = this;
      const data = {
        action: 'contactum_get_forms', // Matches the PHP hook
        _ajax_nonce: contactum.nonce   // Security nonce
      };

      if (this.filter_date_range != null ) {
        data.startdate = this.filter_date_range [0];
        data.enddate = this.filter_date_range [this.filter_date_range.length - 1];
      }


      if (search !== '') {
        data.search = search;
      }

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: data,
        success: function(response) {
          if (response.success) {
            self.forms = Object.values( response.data.forms );
            self.paginate.total = response.data.meta.total;
          }
        }
      });
    },

    searchForms(event) {
      this.paginate.current_page = 1;
      this.fetchItems( this.searchFormsKeyWord );
    },

    goToPage(pageNumber) {
      localStorage.setItem('formItemsCurrentPage', pageNumber);
      this.paginate.current_page = pageNumber
      this.fetchItems();
    },

    filterDateRangedPicked() {
      this.radioOption = "";
      this.fetchItems();
    },

    refetchItems() {
      this.paginate.current_page = 1;
      this.clearingSearchKeyword = true;
      this.fetchItems();
      this.$nextTick(() => {
        this.clearingSearchKeyword = false;
      });
    },

    handleSizeChange(val) {
      localStorage.setItem('formItemsPerPage', val);
      this.paginate.per_page = val;
      this.fetchItems();
    },

    removeFormConfirmation(id, index) {
      this.$confirm('This will permanently delete the file. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
        confirmButtonClass: 'contactum_form_remove_confirm_class'
      }).then(() => {
        this.$prompt('Please type DELETE to confirm. All entries and integration feeds of this form will be deleted', 'Delete Form', {
          confirmButtonText: 'Confirm Delete',
          cancelButtonText: 'Cancel',
          confirmButtonClass: 'contactum_form_remove_confirm_class'
        }).then((response) => {
          if (response.value === 'DELETE') {
            this.removeForm(id, index);
          } else {
            this.$notify.error({
              title: 'Error',
              message: 'You must type DELETE to confirm',
              position: 'bottom-right'
            });
          }
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled'
        });
      });
    },

    exportForm(id) {
      const data = {
        action: 'contactum_export_forms',
        form_id: [id],
        format: 'json',
        nonce: window.contactum.export_nonce
      };

      location.href = contactum.ajaxurl + '?' + jQuery.param(data);
    },

    duplicateFormConfirmation(id, index) {
      var self = this;
      console.log('duplicate form', id);
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'duplicate_contactum_form',
          post_id: id,
          _ajax_nonce: contactum.nonce
        },
        success: function(response) {
          if (response.success) {
            self.$notify.success({
              title: '',
              message: 'Form Duplicated successfully.',
              position: "bottom-right"
            });
            window.location.href = `${self.admin_url}?page=contactum&route=builder#/forms/${response.data.id}`;
          } else {
            self.$notify.success("error");
          }
        }
      });
    },

    removeForm(id, index) {


      var self = this;

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'delete_contactum_form',
          post_id: id,
          _ajax_nonce: contactum.nonce
        },
        success: function(response) {
          if (response.success) {
            self.forms.splice(index, 1);
            self.$notify.success({
              title: '',
              message: 'Form Deleted successfully.',
              position: "bottom-right"
            });
          } else {
            self.$notify.error({
              title: 'Error',
              message: 'Failed to delete form',
              position: "bottom-right"
            });
          }
        }
      });
    }
  },


  mounted() {
    this.hasMounted = true;
    this.fetchItems();

    //bind clipboard
    let clipboard = new ClipboardJS(".copy");

    clipboard.on('success', event => {
      this.$notify.success({
        title: "Copied to Clipboard.", position: 'bottom-right'
      });
    });

  },
  created() {

  },
  watch: {
    searchFormsKeyWord: function (newVal, oldVal) {
      if ((oldVal && !newVal) && !this.clearingSearchKeyword) {
        this.paginate.current_page = 1;
        this.fetchItems();
      }
    },
  }
}
</script>

<style scoped lang="scss">

.contactum_section_title {
  margin-top: 30px;
  margin-bottom: 30px;
  color: var(--foreground);
}

.justify-end {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-end !important;
  gap: 30px;
}

.all-forms-search {
  width: 270px;
}

.forms_table {
  margin-top: 24px;
}

.contactum_shortcode_btn_thin {
  background-color: var(--secondary);
}

.contactum_shortcode_btn {
  align-items: center;
  background-color: var(--muted);
  border: 0;
  border-radius: 6px;
  color: var(--muted-foreground);
  display: inline-flex;
  font-size: 12px;
  margin: 0;
  overflow: hidden;
  padding: 4px 10px;
}



.contactum_table {
    .el-table__row:hover {
        .row-actions {
            position: relative;
            opacity: 1;

            &-item a {
                cursor: pointer;
            }
        }
    }
    .row-actions {
        transition: 0.2s all;
        left: 0;
        opacity: 0;
        .el-loading-spinner .circular {
            height: 17px;
            width: 17px;
            margin-top: 14px;
        }
    }
}

.row-actions .row-actions-item {
    display: inline-block;
    font-size: 13px;
    line-height: 1;
    padding-right: 10px;
    position: relative;
}


</style>