<template>
<div>
    <div class="contactum_entries">
        <div class="contactum_entries_head">
            <h1 class="contactum_section_title"> Entries from All Forms test </h1>
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

      <div class="contactum_entries_section_head">

        <el-row>
          <el-col :span="17">

            <el-row :gutter="18">

              <el-col :xs="24" :span="8">
                <el-select
                    class="ff_filter_form_select ff-input-s1 w-100"
                    @change="onchangeForm"
                    clearable
                    filterable
                    v-model="selectedFormId"
                >
                  <el-option
                      v-for="item in forms"
                      :key="item.id"
                      :label="item.name"
                      :value="item.id">
                  </el-option>
                </el-select>
              </el-col>

<!--              <el-col :xs="24" :span="13">-->
<!--                <div class="ff_radio_group_wrap">-->
<!--                  <el-radio-group class="ff_radio_group_s2" @change="fetchEntries('reset')" v-model="entry_status">-->
<!--                    <el-radio-button label="">All</el-radio-button>-->
<!--                    <el-radio-button label="unread">Unread Only</el-radio-button>-->
<!--                    <el-radio-button label="read">Read Only</el-radio-button>-->
<!--                  </el-radio-group>-->
<!--                </div>-->
<!--              </el-col>-->

            </el-row>

          </el-col>

          <el-col :span="7">
            <el-input
                @keyup.enter.native="fetchEntries()"
                clearable
                filterable
                v-model="search"
                placeholder="Search Forms"
                prefix-icon="el-icon-search"
            >
            </el-input>
          </el-col>

        </el-row>

      </div>

      <EntriesTable :paginated-data="paginatedData" />

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
       if( range !== null ) {
         this.fetchEntries();
         this.fetchReport();
       }
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
        jQuery.ajax({
          url: contactum.ajaxurl, // WordPress automatically defines this if you're in admin area
          type: 'POST',
          data: data,
          success: function(response) {
            if (response.success) {
              self.entries = response.data;
              self.paginate.total = self.entries.length;
            }
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
}

.contactum_entries_head {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 40px;
}

.contactum_btn_group {
    align-items: center;
    display: inline-flex;
    margin: -6px;

    &>* {
        padding: 6px;
    }
}

.contactum_entries_section_head {
  margin-bottom: 40px;
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