<template>
  <div>
  <el-dialog :visible.sync="visible" :before-close="cancelNewForm">
    <el-form :model="localCoupon" label-width="180px">
      <!-- Coupon Title -->
      <el-form-item label="Coupon Title" class="contactum-form-item">
        <label slot="label" class="el-form-item__label">Coupon Title </label>
        <el-input v-model="localCoupon.title" placeholder="Coupon Title"></el-input>
        <p class="mt-1 text-note">The name of this discount</p>
      </el-form-item>

      <!-- Coupon Code -->
      <el-form-item label="Coupon Code" class="contactum-form-item">
        <label slot="label" class="el-form-item__label">Coupon Code </label>
        <el-input v-model="localCoupon.code" placeholder="Coupon Code"></el-input>
        <p class="mt-1 text-note"> Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed. </p>
      </el-form-item>


      <el-row>

        <el-col :span="12">
      <!-- Discount Amount / Percent -->
      <el-form-item label="Discount Amount / Percent" class="contactum-form-item">
        <label slot="label" class="el-form-item__label">Discount Amount / Percent </label>
        <el-input v-model="localCoupon.amount" placeholder="Discount Amount / Percent"></el-input>
        <p class="mt-1 text-note"> Enter the discount percentage. 10 = 10% </p>
      </el-form-item>

        </el-col>

        <el-col :span="12">

      <!-- Discount Type -->
      <el-form-item label="Discount Type" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Discount Type </label>
        <el-radio-group v-model="localCoupon.type">
          <el-radio label="percent">Percent based discount</el-radio>
          <el-radio label="fixed">Fixed Discount</el-radio>
        </el-radio-group>
        <p class="mt-1 text-note"> The kind of discount to apply for this discount. </p>
      </el-form-item>

        </el-col>

      </el-row>

      <el-row>

        <el-col :span="12">

      <!-- Min Purchase Amount -->
      <el-form-item label="Min Purchase Amount" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Min Purchase Amount </label>
        <el-input v-model="localCoupon.minPurchase" placeholder="Min Purchase Amount"></el-input>
        <p class="mt-1 text-note"> The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum. </p>
      </el-form-item>

        </el-col>

        <el-col :span="12">

      <!-- Stackable -->
      <el-form-item label="Stackable" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Stackable </label>
        <el-radio-group v-model="localCoupon.stackable">
          <el-radio :label="true">Yes</el-radio>
          <el-radio :label="false">No</el-radio>
        </el-radio-group>
        <p class="mt-1 text-note"> Can this coupon code can be used with other coupon code </p>
      </el-form-item>

        </el-col>

      </el-row>

      <el-row>

        <el-col :span="12">
      <!-- Start Date -->
      <el-form-item label="Start Date" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Start Date </label>
        <el-date-picker v-model="localCoupon.startDate" type="date" placeholder="Start Date"></el-date-picker>
        <p class="mt-1 text-note"> Enter the start date for this discount code in the format of yyyy-mm-dd. For no start date, leave blank. </p>
      </el-form-item>

        </el-col>

        <el-col :span="12">

      <!-- End Date -->
      <el-form-item label="End Date" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> End Date </label>
        <el-date-picker v-model="localCoupon.endDate" type="date" placeholder="End date"></el-date-picker>
        <p class="mt-1 text-note"> Enter the expiration date for this discount code in the format of yyyy-mm-dd. For no expiration, leave blank </p>
      </el-form-item>

        </el-col>

      </el-row>

      <!-- Applicable Forms -->
      <el-form-item label="Applicable Forms" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Applicable Forms </label>
        <el-select v-model="localCoupon.forms" placeholder="Applicable Forms" clearable>
          <el-option label="Form A" value="formA"></el-option>
          <el-option label="Form B" value="formB"></el-option>
        </el-select>
        <p class="mt-1 text-note"> Leave blank for applicable for all payment forms </p>
      </el-form-item>

      <!-- Coupon Limit -->
      <el-form-item label="Coupon Limit" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Coupon Limit </label>
        <el-input v-model="localCoupon.limit" placeholder="Coupon Limit"></el-input>
        <p class="mt-1 text-note"> Set the limit for how many times a logged-in user can apply this coupon. Keep this empty or put zero for no limit. </p>
      </el-form-item>

      <!-- Status -->
      <el-form-item label="Status" class="contactum-form-item">
        <label slot="label" class="el-form-item__label"> Status </label>
        <el-radio-group v-model="localCoupon.status">
          <el-radio label="active">Active</el-radio>
          <el-radio label="inactive">Inactive</el-radio>
        </el-radio-group>
      </el-form-item>

    </el-form>

    <div slot="footer" class="dialog-footer">
      <el-button @click="visible = false">Cancel</el-button>
      <el-button type="primary" @click="saveCoupon">Save Coupon</el-button>
    </div>
  </el-dialog>
  </div>
</template>


<script>

export default {
  name: "CreateCoupon",
  data() {
    return {
      /*
      newCoupon: {
        title: '',
        code: '',
        amount: '',
        type: 'percent',
        minPurchase: '',
        stackable: false,
        startDate: '',
        endDate: '',
        forms: '',
        limit: 0,
        status: 'active'
      },
      */
      localCoupon: { ...this.newCoupon } 
    }
  },
   computed: {
    isEditMode() {
      return !!this.newCoupon.id; // If ID exists, it's edit mode
    }
  },
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
    newCoupon: {
      type: Object,
      default: () => ({})
    },
    visible: Boolean
  },
  watch: {
    newCoupon: {
      handler(newVal) {
        this.localCoupon = { ...newVal }; // update local when parent changes
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    cancelNewForm() {
      this.$emit("close");
    },
    saveCoupon() {

      if (!this.localCoupon.title || !this.localCoupon.code || !this.localCoupon.amount) {
        this.$message.error('Please fill all fields.');
        return;
      }

              // Prepare data for API
      const action = this.isEditMode ? 'update_contactum_coupon' : 'save_contactum_coupon';

      /*
      const data = {
        action: action,
        nonce: contactum.nonce,
        title: this.newCoupon.title,
        code: this.newCoupon.code,
        amount: this.newCoupon.amount,
        type: this.newCoupon.type,
        minPurchase: this.newCoupon.minPurchase,
        stackable: this.newCoupon.stackable,
        startDate: this.newCoupon.startDate,
        endDate: this.newCoupon.endDate,
        forms: '',
        limit: this.newCoupon.limit,
        status: this.newCoupon.status
      }
      */

      // Send AJAX Request
      jQuery.ajax({
        url: contactum.ajaxurl,
        type: 'POST',
        data: {
          action: action,
          nonce: contactum.nonce,
          data: this.localCoupon,
        },
        success: (response) => {
          if (response.success) {
            this.$message.success('Coupon saved successfully!');
            this.visible = false;
            // this.$emit('saved');
          } else {
            this.$message.error(response.data || 'Failed to save coupon');
          }
        },
        error: () => {
          this.$message.error('Server error. Please try again.');
        }
      });

    }
  }


}
</script>

<style scoped>

.contactum-form-item .el-form-item__label {
  align-items: center;
  color: #1e1f21;
  display: flex;
  font-size: 15px;
  font-weight: 500;
}

.el-form--label-top .el-form-item__label {
  line-height: 1;
  padding-bottom: 16px;
}

</style>