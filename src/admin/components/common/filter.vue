<template>
  <div class="contactum_advanced_filter_wrap">

    <el-button @click="advancedFilter = !advancedFilter" :class="localRange && 'ff_filter_selected'">
      <span><i class="el-icon-s-operation"></i> Filter</span>
      <i v-if="advancedFilter" class="el-icon-circle-close"></i>
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
        <p>Filter By Date Range</p>
        <el-date-picker
            v-model="localRange"
            type="daterange"
            :picker-options="pickerOptions"
            @change="onDatePickerChange"
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
  data() {
    return {
      radioOption: 'all',
      localRange: null,
      advancedFilter: false,
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() >= Date.now();
        },
        shortcuts: [
          {
            text: 'Today',
            onClick(picker) {
              const d = new Date();
              picker.$emit('pick', [d, d]);
            }
          },
          {
            text: 'Yesterday',
            onClick(picker) {
              const d = new Date();
              d.setTime(d.getTime() - 3600 * 1000 * 24);
              picker.$emit('pick', [d, d]);
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
          },
          {
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
    };
  },
  methods: {
    onDatePickerChange() {
      this.radioOption = '';
      this.emit();
    },

    emit() {
      this.$emit('date-filter-changed', this.localRange);
    }
  },
  watch: {
    radioOption(val) {
      if (val === 'all' || val === '') {
        this.localRange = null;
        this.emit();
        return;
      }

      const end = new Date();
      const start = new Date();
      const days = { today: 0, yesterday: 1, 'last-week': 7, 'last-month': 30 }[val];

      if (days === undefined) return;

      if (val === 'yesterday') {
        start.setTime(start.getTime() - 3600 * 1000 * 24);
        end.setTime(end.getTime() - 3600 * 1000 * 24);
      } else if (days > 0) {
        start.setTime(start.getTime() - 3600 * 1000 * 24 * days);
      }

      const fmt = d => d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate();
      this.localRange = [fmt(start), fmt(end)];
      this.emit();
    }
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

  .el-radio {
    margin-bottom: 20px;
    margin-right: 0;
  }
}
</style>
