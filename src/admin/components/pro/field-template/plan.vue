<template>
  <div>
    <label> Subscription Type </label>
    <br/>
    <el-radio-group
        size="small"
        @change="checkPricingPlans()"
    >
      <el-radio-button label="single">Single Recurring Plan</el-radio-button>
      <el-radio-button label="multiple">Multiple Pricing Plans</el-radio-button>
    </el-radio-group>

    <label> Plan Display Type </label>

    <el-radio-group size="small">
      <el-radio-button label="radio">Radio input field</el-radio-button>
      <el-radio-button label="select">Select input field</el-radio-button>
    </el-radio-group>

    <p><strong>Pricing Plans</strong></p>

    <div class="subscription-field-options">

      <div class="plan_header">

        <div class="plan_label">

        </div>

        <div class="plan_actions">

        </div>

      </div>

    </div>

    <div class="plan_actions">
      <el-button @click="addItem">Add New Plan</el-button>
    </div>

  </div>
</template>

<script>

import option_field from "../../../mixin/option-field.js";

export default {
  name: "plan",
  data() {
    return {}
  },
  mixins: [option_field],
  methods: {

    deleteItem(index) {

    },

    addItem() {

    },

    checkPricingPlans: function() {

    }
  }
}
</script>

<style scoped>

</style>