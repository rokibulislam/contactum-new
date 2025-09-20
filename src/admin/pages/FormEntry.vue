<template>
  <div>
    <!-- Header -->
    <div class="entries-header">

      <div>
        <h2>Entries</h2>
      </div>

      <div>

<!--        <el-button type="primary" icon="el-icon-refresh">View Visual Report</el-button>-->

<!--        <el-dropdown @command="selectFieldsToExport" trigger="click">-->
<!--          <el-button>Export<i class="el-icon-arrow-down el-icon&#45;&#45;right"></i></el-button>-->
<!--          <el-dropdown-menu slot="dropdown">-->
<!--            <el-dropdown-item command="csv">Export as CSV</el-dropdown-item>-->
<!--            <el-dropdown-item command="xlsx">Export as Excel (xlsv)</el-dropdown-item>-->
<!--            <el-dropdown-item command="ods">Export as ODS</el-dropdown-item>-->
<!--            <el-dropdown-item command="json">Export as JSON Data</el-dropdown-item>-->
<!--          </el-dropdown-menu>-->
<!--        </el-dropdown>-->

        <el-select
            class="contactum_filter_form_select contactum-input-s1 w-100"
            @change="onchangeForm"
            filterable
            v-model="form_id"
        >
          <el-option
              v-for="item in forms"
              :key="item.id"
              :label="item.name"
              :value="item.id">
          </el-option>
        </el-select>

      </div>

    </div>


    <div class="entries-header">

      <div>

        <el-input
            @keyup.enter.native="fetchEntries()"
            clearable
            filterable
            v-model="search"
            placeholder="Search Entries"
            prefix-icon="el-icon-search"
        >
        </el-input>

      </div>

      <div>

        <DateFilter :filter_date_range.sync="filter_date_range"  @date-filter-changed="onDateFilterChanged" />

      </div>

    </div>

    <EntriesTable :paginated-data="paginatedData" :form="form_id" />

    <el-pagination
        class="contactum_pagination"
        background
        @size-change="handleSizeChange"
        @current-change="goToPage"
        :current-page.sync="paginate.current_page"
        :page-sizes="[ 5, 10, 20, 50, 100]"
        :page-size="parseInt(paginate.per_page)"
        layout="total, sizes, prev, pager, next, jumper"
        :total="paginate.total">
    </el-pagination>

  </div>
</template>


<script>
import EntriesTable from "admin/pages/entries/EntriesTable.vue";
import DateFilter from "admin/components/common/filter.vue";

export default{
  name: "FormEntry",
  components: {
    DateFilter,
    EntriesTable
  },
  data() {
    return {
      form_id: '',
      search: '',
      filter_date_range: null,
      loading: true,
      forms: window.contactum.forms.forms,
      entries: [],
      paginate: {
        total: 0,
        current_page: 1,
        last_page: 1,
        per_page: localStorage.getItem('formEntriesItemsPerPage') || 5
      },
    }
  },
  computed: {
    paginatedData() {
      const start = (this.paginate.current_page - 1) * this.paginate.per_page;
      const end = start + this.paginate.per_page;
      return this.entries.slice(start, end);
    }
  },

  mounted() {
    this.form_id =  Number(this.$route.params.form_id);
    this.fetchEntries();
  },
  methods: {

    onchangeForm() {
      window.location.href = contactum.admin_url + '?page=contactum#/forms/entries/' + this.form_id;
      this.fetchEntries();
    },

    onDateFilterChanged( range ) {
      if (!range || range.length === 0) return; // ignore empty
      this.filter_date_range = range;
      this.fetchEntries();
    },

    goToPage(pageNumber) {
      localStorage.setItem('formEntriesItemsCurrentPage', pageNumber);
      this.paginate.current_page = pageNumber
      this.fetchEntries();
    },

    handleSizeChange(val) {
      localStorage.setItem('formEntriesItemsPerPage', val);
      this.paginate.per_page = val;
      this.fetchEntries();
    },


    fetchEntries() {
      const self = this;

      const data = {
        action: 'contactum_get_entries',
        _ajax_nonce: contactum.nonce,
        form_id: this.form_id,
        search: this.search,
      };

      if (this.filter_date_range != null ) {
        data.startdate = this.filter_date_range [0];
        data.enddate = this.filter_date_range [this.filter_date_range.length - 1];
      }

      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: data,
        success: function(response) {
          if (response.success) {
            self.entries = response.data;
            self.paginate.total = self.entries.length;
          }
        }
      });
    },

    exportEntries() {
      
    }
  },
  watch: {

  }
}
</script>

<style scoped lang="scss">

.entries-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  margin-top: 24px;
}

.contactum_pagination {
  float: right;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(30, 31, 33, .08);
  display: inline-flex;
  font-weight: 500;
  max-width: 100%;
  padding: 12px 20px;
  margin-top: 20px;
}

</style>