<template>
<div>
    <div class="contactum_entries">
        <div class="contactum_entries_head">
            <h1 class="contactum_section_title"> All Entries </h1>
            <div class="entries_head_content">
              <div class="contactum_btn_group">
                  <div class="">
                      <el-button @click="toggleChart()" type="primary" class="el-button--soft-2"> {{ chart_status ? 'Hide Chart' : 'Show Chart' }} </el-button>
                  </div>
                  <DateFilter :filter_date_range.sync="filter_date_range"  @date-filter-changed="onDateFilterChanged" />
              </div>
          </div>
        </div>

        <div v-if="chart_status" class="entry_chart">
          <Chartview :form_id="selectedFormId" :labels="labels" :datasets="datasets" :entry_status="entry_status" />
        </div>


    <div class="contactum-entries-content">

      <div class="ctm-table-card">

        <div class="ctm-table-toolbar">
          <el-select
              class="ctm-form-select"
              @change="onchangeForm"
              clearable
              filterable
              v-model="selectedFormId"
              placeholder="All Forms"
          >
            <el-option
                v-for="item in forms"
                :key="item.id"
                :label="item.name"
                :value="item.id">
            </el-option>
          </el-select>

          <el-input
              class="ctm-search-input"
              @keyup.enter.native="fetchEntries()"
              clearable
              v-model="search"
              placeholder="Search entries…"
              prefix-icon="el-icon-search"
          >
          </el-input>
        </div>

        <EntriesTable :paginated-data="paginatedData" :loading="loading" @entry-deleted="onEntryDeleted" />

        <div class="ctm-table-footer">
          <el-pagination
              background
              @size-change="handleSizeChange"
              @current-change="goToPage"
              :current-page.sync="paginate.current_page"
              :page-sizes="[5, 10, 20, 50, 100]"
              :page-size="parseInt(paginate.per_page)"
              layout="total, sizes, prev, pager, next, jumper"
              :total="paginate.total">
          </el-pagination>
        </div>

      </div>

    </div>
</div>

<!--      <Payment />-->
</div>
</template>
  
<script>

import Chartview from './entries/chartview.vue'
import EntriesTable from './entries/EntriesTable.vue'
import DateFilter from '../components/common/filter.vue'

export default {
    name: "Entries",
    components: { EntriesTable, Chartview, DateFilter },
    data: function() {
        return {
            labels: [],
            datasets:[],
            entries: [],
            forms: window.contactum.forms.forms,
            chart_status: false,
            advancedFilter: false,
            search: '',
            radioOption: 'all',
            filter_date_range: null,
            selectedFormId: null,
            entry_status: '',
            loading: false,
            paginate: {
              total: 5,
              current_page: (localStorage.getItem('ItemsCurrentPage') || 1),
              last_page: 5,
              per_page: localStorage.getItem('ItemsPerPage') || 5
            },
        };
    },

    computed: {
      paginatedData() {
        const start = (this.paginate.current_page - 1) * this.paginate.per_page;
        const end = start + this.paginate.per_page;
        return this.entries.slice(start, end);
      }
    },

  mounted() {
    this.fetchEntries();
    this.fetchReport();
  },
  methods: {

    onchangeForm() {
      this.fetchEntries();
      this.fetchReport();
    },

    onDateFilterChanged( range ) {
      this.filter_date_range = range;
      this.paginate.current_page = 1;
      this.fetchEntries();
      this.fetchReport();
    },

    fetchReport() {
      const self = this;

      const data = {
        action: 'contactum_get_entries_report',
        _ajax_nonce: contactum.nonce,
        form_id: this.selectedFormId,
        entry_type: this.entry_status
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
            self.labels = response.data.labels;
            self.datasets = response.data.datasets;
          }
        }
      });
    },

      handleSizeChange(val) {
        localStorage.setItem('ItemsPerPage', val);
        this.paginate.per_page = val;
        this.fetchEntries();
      },

      goToPage(pageNumber) {
        localStorage.setItem('ItemsCurrentPage', pageNumber);
        this.paginate.current_page = pageNumber
        this.fetchEntries();
      },

      toggleChart() {
        this.chart_status = !this.chart_status;
      },

      filterDateRangedPicked() {
			    this.radioOption = '';
			    this.fetchEntries();
      },

      onEntryDeleted(entryId) {
        this.entries = this.entries.filter(e => e.id !== entryId);
        this.paginate.total = this.entries.length;
      },

      fetchEntries( type = null ) {
        if (type == 'reset') {
          // this.paginate.current_page = 1;
        }

        const data = {
          action: 'contactum_get_entries',
          _ajax_nonce: contactum.nonce,
          form_id: this.selectedFormId,
          // entry_type: this.entry_status,
          search: this.search,
        };

        if (this.filter_date_range != null ) {
          data.startdate = this.filter_date_range [0];
          data.enddate = this.filter_date_range [this.filter_date_range.length - 1];
        }

        const self = this;
        this.loading = true;
        jQuery.ajax({
          url: contactum.ajaxurl,
          type: 'POST',
          data: data,
          success: function(response) {
            if (response.success) {
              self.entries = response.data;
              self.paginate.total = self.entries.length;
            }
          },
          complete: function() {
            self.loading = false;
          }
        });
      }
    },

    watch: {

      radioOption: {
        handler() {
          const start = new Date();
          const end = new Date();
          let number = 1;
          localStorage.setItem('contactum_entries_date_filter', this.radioOption);
          switch (this.radioOption) {
            case 'today' :
              number = 0;
              break;
            case 'yesterday':
              end.setTime(end.getTime() - 3600 * 1000 * 24 * number);
              break;
            case 'last-week':
              number = 7;
              break;
            case 'last-month':
              number = 30;
              break;
            case 'all':
              this.fetchEntries();
              return;
            default:
              return;
          }
          start.setTime(start.getTime() - 3600 * 1000 * 24 * number);
          const startDate = start.getFullYear() + '/' + (start.getMonth() + 1) + '/' + start.getDate();
          const endDate = end.getFullYear() + '/' + (end.getMonth() + 1) + '/' + end.getDate();
          this.filter_date_range = [startDate, endDate];
          this.fetchEntries();
        },
        // immediate: true
      },

      search: function (newVal, oldVal) {
        if (oldVal && !newVal) {
          this.paginate.current_page = 1;
          this.fetchEntries();
        }
      }
    }
}


</script>

<style lang="scss">

.contactum_entries {
  padding: 24px;
  background-color: var(--background, #f8f9fa);
  color: var(--foreground, #1e1f21);
}

.contactum_entries_head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
}

.contactum_section_title {
  color: var(--foreground, #1e1f21);
  margin: 0;
}

.contactum_btn_group {
  align-items: center;
  display: inline-flex;
  gap: 8px;
}

/* ── Table card ── */
.ctm-table-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, 0.08);
  overflow: hidden;
}

.ctm-table-toolbar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid #f0f0f0;

  .ctm-form-select {
    width: 220px;
  }

  .ctm-search-input {
    flex: 1;
    max-width: 320px;
    margin-left: auto;
  }
}

.ctm-table-footer {
  display: flex;
  justify-content: flex-end;
  padding: 14px 20px;
  border-top: 1px solid #f0f0f0;
}

</style>