<template>
  <div class="panel-field">

    <ul class="list-inline conditional_list">
      <li>
        <label class="contactum-label"> Conditional Logic </label>
        <el-radio-group v-model="contactum_cond.condition_status">
          <el-radio label="yes">Yes</el-radio>
          <el-radio label="no">No</el-radio>
        </el-radio-group>
      </li>
    </ul>

    <ul class="list-inline conditional_list" v-if=" contactum_cond.condition_status == 'yes' ">
      <li>
        <label class="contactum-label"> Condition Match </label>
        <el-radio-group v-model="contactum_cond.cond_logic">
          <el-radio label="any">Any</el-radio>
          <el-radio label="all">All</el-radio>
        </el-radio-group>
      </li>
    </ul>

    <div v-if=" 'yes' == contactum_cond.condition_status">
      <ul class="conditional_list">
        <li v-for="(condition, index) in conditions" class="conditional">

          <div class="conditional-field">
            <el-select v-model="condition.name" size="small">
              <el-option
                  v-for="dep_field in dependencies"
                  :key="dep_field.name"
                  :label="dep_field.label"
                  :value="dep_field.name">
              </el-option>
            </el-select>
          </div>

          <div class="conditional-operator">
            <select v-model="condition.operator" size="small">
              <option value="=">equal</option>
              <option value="!=">not equal</option>
            </select>
          </div>


          <div class="conditional-option">
            <el-select v-model="condition.option" placeholder="Select" size="small">
              <el-option
                  v-for="cond_option in get_condition_option(condition.name)"
                  :key="cond_option.option_label"
                  :label="cond_option.option_label"
                  :value="cond_option.option_label">
              </el-option>
            </el-select>
          </div>

          <div class="conditional-action-btn">
            <i class="el-icon-circle-plus-outline" @click="add_condition"></i>
            <i class="el-icon-remove-outline"  v-if="conditions.length > 1" @click="delete_condition" > </i>
          </div>

        </li>
      </ul>

    </div>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_conditional_logic",
  mixins: [option_field],
  data: function() {
    return {
      conditions: [],
      status: false,
      cond_logic: 'all',
      options: [
        { value: 'any', label: 'any'},
        { value: 'all', label: 'all'}
      ]
    };
  },
  computed: {
    contactum_cond: {
      get() {
        return this.editfield.contactum_cond  || {};
      },
      set(val) {
        this.$set(this.editfield, 'contactum_cond', val);
      }
    },

    condition_supported_field: function() {
      return window.contactum.contactum_cond_supported_fields;
    },

    dependencies: function() {
      let form_fields        = this.$store.getters.form_fields;
      let dependenciesFields = form_fields.filter( item => this.condition_supported_field.includes(item.template) &&  this.editfield.name != item.name );

      return dependenciesFields;
    }

  },
  created: function() {

    // clone with fallback
    let contactum_cond = {
      condition_status: 'no',   // default so radios always work
      cond_logic: 'all',
      cond_field: [],
      cond_operator: [],
      cond_option: [],
      ...this.editfield.contactum_cond
    };

    // rebuild conditions
    for (var i = 0; i < contactum_cond.cond_field.length; i++) {
      if (contactum_cond.cond_field[i] && contactum_cond.cond_operator[i]) {
        this.conditions.push({
          name: contactum_cond.cond_field[i],
          operator: contactum_cond.cond_operator[i],
          option: contactum_cond.cond_option[i]
        });
      }
    }

    // ensure at least one condition row
    if (!this.conditions.length) {
      this.conditions.push({
        name: "",
        operator: "=",
        option: ""
      });
    }

    // update back to editfield (so radio works instantly)
    this.$set(this.editfield, 'contactum_cond', contactum_cond);
  },

  methods: {
    get_condition_option: function (field_name) {
      let options = [];
      let dep = this.dependencies.filter(field => field.name === field_name);


      if (dep.length && dep[0].options) {
        let f_options = {...dep[0].options};

        for (const [key, value ] of Object.entries(f_options)) {
          options.push({
            option_value: value.value,
            option_label: value.label
          });
        };
      }
      return options;
    },
    add_condition: function(index) {
      this.conditions.push({
        name: "",
        operator: "=",
        option: ""
      });
    },
    delete_condition: function(index) {
      this.conditions.splice(index, 1);
    }
  },

  watch: {
    conditions: {
      deep: true,
      handler: function (new_conditions) {
        let new_contactum_cond = { ...this.contactum_cond };
        if ( !this.contactum_cond ) {
          new_contactum_cond.condition_status = 'no';
          new_contactum_cond.cond_logic = 'all';
        }

        new_contactum_cond.cond_field       = [];
        new_contactum_cond.cond_operator    = [];
        new_contactum_cond.cond_option      = [];

        let i = 0;

        for (i = 0; i < new_conditions.length; i++) {
          new_contactum_cond.cond_field.push(new_conditions[i].name);
          new_contactum_cond.cond_operator.push(new_conditions[i].operator);
          new_contactum_cond.cond_option.push(new_conditions[i].option);
        }

        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: 'contactum_cond',
          value: new_contactum_cond
        });
      }
    }
  }
};
</script>