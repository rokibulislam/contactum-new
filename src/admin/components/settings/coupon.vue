<template>
  <div class="contactum_card">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <h3>Available Coupons  </h3>
      <el-button type="primary" @click="addCoupon">+ Add New Coupon </el-button>
    </div>
    <el-table :data="coupons" border style="width: 100%; margin-top: 10px;">
      <el-table-column prop="id" label="ID" width="60"></el-table-column>
      <el-table-column prop="title" label="Title"></el-table-column>
      <el-table-column prop="code" label="Code"></el-table-column>
      <el-table-column prop="amount" label="Amount"></el-table-column>
      <el-table-column label="Actions" width="120">
        <template slot-scope="scope">
          <el-button
              type="primary"
              icon="el-icon-edit"
              size="mini"
              @click="editCoupon(scope.row)">
          </el-button>
          <el-button
              type="danger"
              icon="el-icon-delete"
              size="mini"
              @click="deleteCoupon(scope.row)">
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
      <el-pagination
          background
          layout="total, sizes, prev, pager, next"
          :total="total"
          :page-size="pageSize"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange">
      </el-pagination>
    </div>
    <CreateCoupon   
        ref="createCoupon"
        :new-coupon="currentCoupon"
        :visible="visible" 
        @close="visible = false"
        @refresh="fetchCoupons"
        @saved="fetchCoupons"  
    />
  </div>
</template>

<script>

import CreateCoupon from '../dialog/CreateCoupon.vue'

export default {
  name: 'Coupon',
  components: {
    CreateCoupon
  },
  data() {
    return {
      coupons: [],
      total: 0,
      pageSize: 10,
      currentPage: 1,
      visible: false,
      currentCoupon: {}
    }
  },
  computed: {
    paginatedCoupons() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.coupons.slice(start, end);
    }
  },
  mounted() {
    this.fetchCoupons();
  },
  methods: {

    fetchCoupons() {

      jQuery.post(
        contactum.ajaxurl, {
          action: 'get_coupons',
          _ajax_nonce: contactum.nonce
        },
        (response, textStatus, xhr) => {
          if (response.success) {
              this.coupons = response.data; // Update the table data
              this.total = response.data.length; // Set total for pagination
          } else {
          }
      });

    },

    addCoupon() {
      this.currentCoupon = {}; // Reset for new coupon
      this.visible = true;
    },

    editCoupon(row) {
      const coupon = this.coupons.find(c => c.id === row.id);
      if (coupon) {
        this.currentCoupon = { ...coupon }; // Copy to avoid direct binding
        this.visible = true; // Open the dialog
      } else {
      }
    },

    deleteCoupon(row) {
      jQuery.post(contactum.ajaxurl, {
        action: 'delete_coupon',
        id: row.id,
        _ajax_nonce: contactum.nonce
      }, (response) => {
        if (response.success) {
          this.$message.success('Coupon deleted');
        //  this.fetchCoupons(); // Refresh list
        } else {
          this.$message.error('Failed to delete');
        }
      });
    },
    
    handleSizeChange(size) {
      this.pageSize = size;
    },

    handleCurrentChange(page) {
      this.currentPage = page;
    }

  }
}

</script>