<template>

  <div class="contactum_payments_wrapper">
    <h2> Transactions </h2>

    <div class="contactum-entries-content">

      <div class="contactum_filter_wrapper payment_filters">
        <div class="el-row" style="margin-left: -10px; margin-right: -10px;">
          <div class="el-col-8 el-col-lg-5" style="padding-left: 10px; padding-right: 10px;">
            <div class="contactum_form_group">
              <label class="contactum_form_group_label">Form</label>
              <el-select class="contactum-input-s1" @change="fetchPayments()" clearable v-model="selectedFormId" :placeholder="'Select Form'">
                <el-option
                    v-for="item in available_forms"
                    :key="item.form_id"
                    :label="item.title"
                    :value="item.form_id">
                </el-option>
              </el-select>
            </div>
          </div>
          <div class="el-col-8 el-col-lg-5" style="padding-left: 10px; padding-right: 10px;">
            <div class="contactum_form_group">
              <label class="contactum_form_group_label">Status</label>
              <el-select class="contactum-input-s1" @change="fetchPayments()" clearable v-model="selectedPaymentStatuses" :placeholder="'Select Status'">
                <el-option
                    v-for="item in available_statuses"
                    :key="item"
                    :label="item"
                    :value="item">
                </el-option>
              </el-select>
            </div>
          </div>
          <div class="el-col-8 el-col-lg-5" style="padding-left: 10px; padding-right: 10px;">
            <div class="contactum_form_group">
              <label class="contactum_form_group_label">Payment Methods</label>
              <el-select class="contactum-input-s1" @change="fetchPayments()" clearable v-model="selectedPaymentMethods" :placeholder="'Select Method'">
                <el-option
                    v-for="item in available_methods"
                    :key="item.key"
                    :label="item.value"
                    :value="item.key">
                </el-option>
              </el-select>
            </div>
          </div>
          <div class="el-col-8 el-col-lg-5" style="padding-left: 10px; padding-right: 10px;">
            <div class="contactum_form_group">
              <label class="contactum_form_group_label">Payment Types</label>
              <el-select class="ff-input-s1" @change="fetchPayments()" clearable v-model="selectedPaymentTypes" :placeholder="'Select Type'">
                <el-option
                    v-for="item in available_payment_types"
                    :key="item.key"
                    :label="item.value"
                    :value="item.key">
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
      </div><!-- ff_filter_wrapper -->

    </div>


    <div class="payment_details">
      <div class="contactum_table">

        <el-table class="ff_payment_table" :stripe="true" :row-class-name="tableRowClassName"  :data="payments"  @selection-change="handleSelectionChange">
          <el-table-column width="120" label="Submission ID">
          </el-table-column>
          <el-table-column width="200" label="Form" prop="title"></el-table-column>
          <el-table-column width="100" label="Type"></el-table-column>
          <el-table-column label="Customer" prop="payer_name"></el-table-column>
          <el-table-column width="160" label="Amount"> </el-table-column>
          <el-table-column width="180" label="Status"> </el-table-column>
          <el-table-column width="180" label="Time"> </el-table-column>
        </el-table>
      </div>
    </div>

  </div>
</template>


<script>

export default {
  name: "Payment",
  data() {
    return {
      payments: [],
      loading: false,
      selectedFormId: '',
      selectedPaymentStatuses: '',
      selectedPaymentMethods: '',
      selectedPaymentTypes: ''
    }
  },
  computed: {

  },
  mounted() {
    this.fetchPayments();
  },
  methods: {
    fetchPayments() {

    },

    handleSelectionChange() {

    }
  }
}
</script>

<style scoped>

.contactum_filter_wrapper {
  margin-bottom: 20px;
  overflow: hidden;
  width: 100%;
}

.contactum_form_group_label {
  color: #1e1f21;
  display: block;
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 8px;
}

</style>