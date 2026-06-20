<template>
  <div class="ctm-payment-summary-settings">

    <div class="ctm-ps-row">
      <label class="ctm-ps-label">Currency Symbol</label>
      <el-input v-model="editfield.currency_symbol" style="width:80px" placeholder="$" />
      <el-select v-model="editfield.currency_position" style="width:130px;margin-left:8px">
        <el-option label="Left  ($10)" value="left" />
        <el-option label="Right (10$)" value="right" />
      </el-select>
    </div>

    <div class="ctm-ps-row">
      <label class="ctm-ps-label">Tax Rate (%)</label>
      <el-input-number
        v-model="editfield.tax_rate"
        :min="0"
        :max="100"
        :precision="2"
        :step="1"
        controls-position="right"
        style="width:120px"
      />
      <span class="ctm-ps-hint">Set to 0 to hide the tax row</span>
    </div>

    <div class="ctm-ps-row">
      <label class="ctm-ps-label">Subtotal Label</label>
      <el-input v-model="editfield.subtotal_label" placeholder="Subtotal" style="max-width:220px" />
    </div>

    <div class="ctm-ps-row">
      <label class="ctm-ps-label">Tax Label</label>
      <el-input v-model="editfield.tax_label" placeholder="Tax" style="max-width:220px" />
    </div>

    <div class="ctm-ps-row">
      <label class="ctm-ps-label">Total Label</label>
      <el-input v-model="editfield.total_label" placeholder="Total" style="max-width:220px" />
    </div>

    <div class="ctm-ps-row">
      <label class="ctm-ps-label">Empty Message</label>
      <el-input
        v-model="editfield.empty_message"
        placeholder="No items selected yet."
        style="max-width:300px"
      />
      <span class="ctm-ps-hint">Shown when no product fields are filled</span>
    </div>

  </div>
</template>

<script>
export default {
  name: "field_payment_summary",
  props: {
    field:     { type: Object, required: true },
    editfield: { type: Object, required: true },
  },
};
</script>

<style lang="scss" scoped>
.ctm-payment-summary-settings {
  padding: 4px 0;
}

.ctm-ps-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 14px;
}

.ctm-ps-label {
  width: 130px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  flex-shrink: 0;
}

.ctm-ps-hint {
  font-size: 11px;
  color: #9ca3af;
}
</style>
