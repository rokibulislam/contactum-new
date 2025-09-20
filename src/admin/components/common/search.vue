
<template>
  <div class="search-element">
    <div class="contactum-input-wrap">
      <span class="el-icon el-icon-search"></span>
      <el-input ref="searchInput"  v-model="searchElementStr" type="text" :placeholder="placeholder" class="search-field" />
    </div>

    <div class="search-element-result"  style="margin-top: 15px;" v-show="searchResult.length">
      <fields_button :fields="searchResult" />
    </div>

  </div>
</template>


<script>

import fields_button from '../common/fields-button.vue'

export default {
  name: "search_from",
  components: {
    fields_button
  },
  props: {
    list: {
      type: [Array, Object],
      required: true
    },
    placeholder: {
      type: String,
    },
    isSidebarSearch: {
      type: Boolean,
    }
  },
  data() {
    return {
      searchElementStr: '',
      searchResult: [],
    }
  },
  watch: {
    searchElementStr() {
      const searchElementStr = this.searchElementStr.trim().toLowerCase();
      let searchResult = [];
      if (searchElementStr) {
        let fields = Object.values(this.list);
        searchResult = fields.filter(field => field.title.toLowerCase().includes(searchElementStr.toLowerCase())).map(field => field.template);;
        this.$emit('update:isSidebarSearch', true);
      }  else {
        this.$emit('update:isSidebarSearch', false);
      }
      this.searchResult = searchResult;
    }
  }
};
</script>


<style scoped lang="scss">

.search-element .search-field .el-input__inner {
  padding-left: 32px;
}

.contactum-input-wrap {
  position: relative;
  & .el-icon {
    align-items: center;
    display: inline-flex;
    height: 100%;
    left: 0;
    padding-left: 14px;
    position: absolute;
    top: 0;
    z-index: 2;
  }
}

.search-element-wrap .el-input__inner {
  background-color: hsla(0, 0%, 100%, .57);
  border-color: #ced0d4;
  height: 44px;
}
</style>