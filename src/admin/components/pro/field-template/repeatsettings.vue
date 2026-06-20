<template>
  <div class="rs-wrap">

    <!-- ── Section header ────────────────────────────────────────────────── -->
    <div class="rs-header">
      <span class="rs-header__title">Repeat Field Columns</span>
      <span class="rs-header__count">{{ inner_fields.length }} column{{ inner_fields.length !== 1 ? 's' : '' }}</span>
    </div>

    <!-- ── Column cards ──────────────────────────────────────────────────── -->
    <div class="rs-columns">
      <div
        v-for="(innerfield, i) in inner_fields"
        :key="i"
        class="rs-card"
      >

        <!-- card header -->
        <div class="rs-card__head" @click="toggleCollapse(i)">
          <div class="rs-card__head-left">
            <span class="rs-card__index">{{ i + 1 }}</span>
            <span class="rs-card__title">{{ innerfield.field_properties.title || 'Untitled column' }}</span>
            <span class="rs-card__badge">{{ available_elements[innerfield.fields] || innerfield.fields }}</span>
          </div>
          <div class="rs-card__head-right" @click.stop>
            <el-tooltip content="Add column" placement="top">
              <button class="rs-icon-btn rs-icon-btn--add" @click.prevent="add_field">
                <i class="el-icon-plus"></i>
              </button>
            </el-tooltip>
            <el-tooltip content="Remove column" placement="top">
              <button
                class="rs-icon-btn rs-icon-btn--del"
                :class="{ 'rs-icon-btn--disabled': inner_fields.length === 1 }"
                :disabled="inner_fields.length === 1"
                @click.prevent="delete_field(i)"
              >
                <i class="el-icon-minus"></i>
              </button>
            </el-tooltip>
            <i
              class="el-icon-arrow-down rs-chevron"
              :class="{ 'rs-chevron--open': !collapsed[i] }"
            ></i>
          </div>
        </div>

        <!-- card body -->
        <div v-show="!collapsed[i]" class="rs-card__body">

          <!-- Field type -->
          <div class="rs-field">
            <label class="rs-label">Field Type</label>
            <el-select v-model="innerfield.fields" size="small" class="rs-full">
              <el-option
                v-for="(label, key) in available_elements"
                :key="key"
                :value="key"
                :label="label"
              />
            </el-select>
          </div>

          <!-- Label -->
          <div class="rs-field">
            <label class="rs-label">Label</label>
            <el-input v-model="innerfield.field_properties.title" size="small" placeholder="Column label" />
          </div>

          <!-- Placeholder -->
          <div class="rs-field">
            <label class="rs-label">Placeholder</label>
            <el-input v-model="innerfield.field_properties.placeholder" size="small" placeholder="Placeholder text" />
          </div>

          <!-- Options — dropdown only -->
          <div v-if="innerfield.fields === 'form_dropdown_field'" class="rs-field">
            <label class="rs-label">Options</label>
            <field_option_data :editfield="innerfield.field_properties" :field="innerfield" />
          </div>

          <!-- Default — text / number / email -->
          <div
            v-if="['form_text_field','form_number_field','form_email_address'].includes(innerfield.fields)"
            class="rs-field"
          >
            <label class="rs-label">Default value</label>
            <el-input v-model="innerfield.field_properties.default" size="small" placeholder="Default" />
          </div>

          <!-- Required -->
          <div class="rs-field rs-field--inline">
            <label class="rs-label">Required</label>
            <el-switch
              v-model="innerfield.field_properties.required"
              active-color="#2563eb"
              :active-value="true"
              :inactive-value="false"
            />
          </div>

          <!-- Error message -->
          <div v-if="innerfield.field_properties.required" class="rs-field">
            <label class="rs-label">Error message</label>
            <el-input
              v-model="innerfield.field_properties.message"
              size="small"
              placeholder="This field is required"
            />
          </div>

        </div>
      </div>
    </div>

    <!-- ── Add column button ─────────────────────────────────────────────── -->
    <button class="rs-add-btn" @click.prevent="add_field">
      <i class="el-icon-plus"></i> Add Column
    </button>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
import field_option_data from "../../field-template/option-data.vue";

export default {
  name: "field_repeatsettings",
  mixins: [option_field],
  components: { field_option_data },
  data() {
    return {
      collapsed: [],
      available_elements: {
        form_text_field:    "Text",
        form_email_address: "Email",
        form_number_field:  "Number",
        form_dropdown_field:"Select",
      },
    };
  },
  computed: {
    inner_fields() {
      return this.editfield.inner_fields || [];
    },
  },
  created() {
    this.collapsed = this.inner_fields.map(() => false);
  },
  methods: {
    toggleCollapse(i) {
      this.$set(this.collapsed, i, !this.collapsed[i]);
    },
    add_field() {
      const clone = JSON.parse(JSON.stringify(this.inner_fields[0] || {
        fields: "form_text_field",
        field_properties: { title: "", placeholder: "", required: false, message: "", default: "" },
      }));
      const updated = [...this.inner_fields, clone];
      this.$set(this.collapsed, updated.length - 1, false);
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "inner_fields",
        value: updated,
      });
    },
    delete_field(index) {
      if (this.inner_fields.length <= 1) return;
      const updated = this.inner_fields.filter((_, i) => i !== index);
      this.collapsed.splice(index, 1);
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "inner_fields",
        value: updated,
      });
    },
  },
  watch: {
    inner_fields: {
      deep: true,
      handler(val) {
        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: "inner_fields",
          value: val,
        });
      },
    },
  },
};
</script>

<style lang="scss" scoped>

$border:  #e5e7eb;
$blue:    #2563eb;
$blue-lt: #eff6ff;
$red:     #ef4444;
$gray-3:  #374151;
$gray-4:  #4b5563;
$gray-6:  #6b7280;
$gray-9:  #9ca3af;
$radius:  8px;

// ── Section header ────────────────────────────────────────────────────────────
.rs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.rs-header__title {
  font-size: 12px;
  font-weight: 600;
  color: $gray-3;
}
.rs-header__count {
  font-size: 11px;
  color: $gray-9;
  background: #f3f4f6;
  border-radius: 20px;
  padding: 2px 9px;
}

// ── Column list ───────────────────────────────────────────────────────────────
.rs-columns {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}

// ── Card ──────────────────────────────────────────────────────────────────────
.rs-card {
  border: 1px solid $border;
  border-radius: $radius;
  overflow: hidden;
  background: #fff;
  transition: box-shadow .15s;

  &:hover { box-shadow: 0 1px 4px rgba(37,99,235,.07); }
}

.rs-card__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 12px;
  background: #f9fafb;
  border-bottom: 1px solid $border;
  cursor: pointer;
  user-select: none;
  transition: background .15s;

  &:hover { background: #f3f4f6; }
}

.rs-card__head-left {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.rs-card__index {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: $blue;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.rs-card__title {
  font-size: 12px;
  font-weight: 600;
  color: $gray-3;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  max-width: 120px;
}

.rs-card__badge {
  font-size: 10px;
  font-weight: 500;
  color: $blue;
  background: $blue-lt;
  border-radius: 20px;
  padding: 1px 8px;
  border: 1px solid #bfdbfe;
  white-space: nowrap;
}

.rs-card__head-right {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
}

// icon buttons in header
.rs-icon-btn {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: 1px solid $border;
  background: #fff;
  color: $gray-6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  cursor: pointer;
  transition: all .15s;
  padding: 0;

  &--add:hover  { border-color: $blue; color: $blue; background: $blue-lt; }
  &--del:hover  { border-color: #fca5a5; color: $red; background: #fef2f2; }
  &--disabled   { opacity: .35; pointer-events: none; }
}

.rs-chevron {
  font-size: 13px;
  color: $gray-9;
  margin-left: 2px;
  transition: transform .2s;

  &--open { transform: rotate(180deg); }
}

// ── Card body ─────────────────────────────────────────────────────────────────
.rs-card__body {
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

// ── Field rows ────────────────────────────────────────────────────────────────
.rs-field {
  display: flex;
  flex-direction: column;
  gap: 4px;

  &--inline {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.rs-label {
  font-size: 11px;
  font-weight: 600;
  color: $gray-6;
  text-transform: uppercase;
  letter-spacing: .04em;
}

.rs-full { width: 100%; }

// ── Add column button ─────────────────────────────────────────────────────────
.rs-add-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 14px;
  border-radius: 6px;
  border: 1px dashed #93c5fd;
  background: transparent;
  color: $blue;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all .15s;
  width: 100%;
  justify-content: center;

  i { font-size: 12px; }
  &:hover { background: $blue-lt; border-style: solid; }
}
</style>
