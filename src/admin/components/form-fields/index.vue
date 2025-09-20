<template>
  <ul class="field-panel panel-header">
    <search_from
        placeholder="Search"
        :list=field_settings
        :isSidebarSearch.sync=isSidebarSearch
    />
    <li class="panel-form-fields" v-for="(section, index) in panel_sections" :key="index" v-show="!isSidebarSearch">
      <h3  @click="panel_toggle(index)">{{ section.title }}
        <i :class="[section.show ? 'fa fa-angle-down' : 'fa fa-angle-right']"> </i>
      </h3>

        <transition name="slide-fade">
            <ul v-show="section.show">
                <fields_button :fields="section.fields" />
            </ul>
        </transition>
    </li>
  </ul>
</template>

<script>
import search_from from '../common/search.vue';
import fields_button from '../common/fields-button.vue';

export default {
  name: "form_fields",
  data: function() {
    return {
      isSidebarSearch: false,
    }
  },
  components: {
    search_from,
    fields_button
  },
  computed: {
    panel_sections: function() {
      return this.$store.getters.panel_sections;
    },
    field_settings: function() {
      return this.$store.getters.field_settings;
    },
    form_fields: function() {
      return this.$store.getters.form_fields;
    }
  },
  methods: {
    panel_toggle: function (index) {
        this.$store.dispatch('panel_toggle', index);
    },
  }
};
</script>

<style scoped lang="scss">

.panel-form-fields {
  border-radius: 8px;
  background: #fff;
  margin-bottom: 12px;

  & h3 i {
      float: right;
  }
}


</style>
