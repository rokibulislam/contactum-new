<template>
  <div class="contactum_advanced_filter_wrap">

    <el-button @click="advancedFilter = !advancedFilter" :class="this.filter_date_range && 'ff_filter_selected'">
      <span> <i class="el-icon-s-operation"> </i> Filter </span>
      <i v-if="advancedFilter" class="el-icon-circle-close"></i>
      <i v-else class="ff-icon-filter"></i>
    </el-button>

    <div v-if="advancedFilter" class="contactum_advanced_search">

      <div class="contactum_advanced_search_radios">
        <el-radio-group v-model="radioOption" class="el-radio-group-column">
          <el-radio label="all">All</el-radio>
          <el-radio label="today">Today</el-radio>
          <el-radio label="yesterday">Yesterday</el-radio>
          <el-radio label="last-week">Last Week</el-radio>
          <el-radio label="last-month">Last Month</el-radio>
        </el-radio-group>
      </div>

      <div class="contactum_advanced_search_date_range">
        <p> Filter By Date Range </p>
        <el-date-picker
            v-model="filter_date_range"
            type="daterange"
            :picker-options="pickerOptions"
            @change="filterDateRangedPicked"
            format="dd MMM, yyyy"
            value-format="yyyy-MM-dd"
            range-separator="-"
            start-placeholder="Start Date"
            end-placeholder="End Date"
        />
      </div>

    </div>

  </div>
</template>

<script>

export default {
  name: "DateFilter",
  props: ['filter_date_range'],
  data: function() {
    return {
      radioOption: 'all',
      advancedFilter: false,
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() >= Date.now();
        },
        shortcuts: [
          {
            text: 'Today',
            onClick(picker) {
              const start = new Date();
              picker.$emit('pick', [start, start]);
            }
          },
          {
            text: 'Yesterday',
            onClick(picker) {
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
              picker.$emit('pick', [start, start]);
            }
          },
          {
            text: 'Last week',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit('pick', [start, end]);
            }
          }, {
            text: 'Last month',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
              picker.$emit('pick', [start, end]);
            }
          }
        ]
      }

    }
  },
  methods: {
    filterDateRangedPicked() {
      this.radioOption = "";
      this.fetchItems();
    },

    fetchItems() {

      this.$emit("date-filter-changed", this.filter_date_range);
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
            // this.filter_date_range = 'all';
            this.fetchItems();
            return;
          default:
            return;
        }
        start.setTime(start.getTime() - 3600 * 1000 * 24 * number);
        const startDate = start.getFullYear() + '/' + (start.getMonth() + 1) + '/' + start.getDate();
        const endDate = end.getFullYear() + '/' + (end.getMonth() + 1) + '/' + end.getDate();
        this.filter_date_range = [startDate, endDate];
        this.fetchItems();
      },
      immediate: true
    },
  }
}

</script>

<style scoped lang="scss">
  .contactum_advanced_filter_wrap {
    position: relative;
  }

  .contactum_advanced_search {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 40px 64px -12px rgba(0,0,0,.08), 0 0 14px -4px rgba(0,0,0,.08), 0 32px 48px -8px rgba(0,0,0,.1);
    margin-top: 10px;
    padding: 20px;
    position: absolute;
    right: 0;
    top: 100%;
    width: 350px;
    z-index: 1024;
  }

  .el-radio-group-column {
    display: flex;
    flex-direction: column;

    & .el-radio {
      margin-bottom: 20px;
      margin-right: 0;
    }
  }


</style>