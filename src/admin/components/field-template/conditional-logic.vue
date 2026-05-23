<template>
  <div class="cl-wrap">

    <!-- ── Header / enable toggle ────────────────────────────────────────── -->
    <div class="cl-header">
      <div class="cl-header__info">
        <span class="cl-header__label">Conditional Logic</span>
        <span class="cl-header__desc">Show or hide this field based on other field values</span>
      </div>
      <el-switch
        :value="contactum_cond.condition_status === 'yes'"
        @change="toggleCondition"
        active-color="#2563eb"
      />
    </div>

    <!-- ── Active body ───────────────────────────────────────────────────── -->
    <div v-if="contactum_cond.condition_status === 'yes'" class="cl-body">

      <!-- Action sentence -->
      <div class="cl-sentence">
        <el-select v-model="contactum_cond.cond_action" size="small" class="cl-sel cl-sel--action">
          <el-option label="Show" value="show" />
          <el-option label="Hide" value="hide" />
        </el-select>
        <span class="cl-sentence__text">this field if</span>
        <el-select v-model="contactum_cond.cond_logic" size="small" class="cl-sel cl-sel--logic">
          <el-option label="ALL" value="all" />
          <el-option label="ANY" value="any" />
        </el-select>
        <span class="cl-sentence__text">of these match:</span>
      </div>

      <!-- Condition rows -->
      <div class="cl-conditions">
        <template v-for="(condition, index) in conditions">

          <!-- AND / OR connector -->
          <div v-if="index > 0" :key="'sep-' + index" class="cl-connector">
            <span>{{ contactum_cond.cond_logic === 'any' ? 'OR' : 'AND' }}</span>
          </div>

          <div :key="index" class="cl-row">

            <!-- Field -->
            <el-select
              v-model="condition.name"
              size="small"
              placeholder="Select field"
              class="cl-sel cl-sel--field"
            >
              <el-option
                v-for="dep in dependencies"
                :key="dep.name"
                :label="dep.label"
                :value="dep.name"
              />
            </el-select>

            <!-- Operator -->
            <el-select v-model="condition.operator" size="small" class="cl-sel cl-sel--op">
              <el-option label="equals" value="=" />
              <el-option label="not equals" value="!=" />
              <el-option label="contains" value="contains" />
              <el-option label="not contains" value="not_contains" />
            </el-select>

            <!-- Value — dropdown if field has options, text otherwise -->
            <el-select
              v-if="get_condition_option(condition.name).length"
              v-model="condition.option"
              size="small"
              placeholder="Select value"
              class="cl-sel cl-sel--val"
            >
              <el-option
                v-for="opt in get_condition_option(condition.name)"
                :key="opt.option_value"
                :label="opt.option_label"
                :value="opt.option_label"
              />
            </el-select>
            <el-input
              v-else
              v-model="condition.option"
              size="small"
              placeholder="Value"
              class="cl-sel cl-sel--val"
            />

            <!-- Delete -->
            <button
              v-if="conditions.length > 1"
              class="cl-row__del"
              title="Remove"
              @click="delete_condition(index)"
            >
              <i class="el-icon-close"></i>
            </button>

          </div>
        </template>
      </div>

      <!-- Add condition -->
      <button class="cl-add-btn" @click="add_condition">
        <i class="el-icon-plus"></i> Add condition
      </button>

    </div>
  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
export default {
  name: "field_conditional_logic",
  mixins: [option_field],
  data() {
    return {
      conditions: [],
    };
  },
  computed: {
    contactum_cond: {
      get() {
        return this.editfield.contactum_cond || {};
      },
      set(val) {
        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: "contactum_cond",
          value: val,
        });
      },
    },
    condition_supported_field() {
      return window.contactum.contactum_cond_supported_fields;
    },
    dependencies() {
      return this.$store.getters.form_fields.filter(
        (item) =>
          this.condition_supported_field.includes(item.template) &&
          this.editfield.name !== item.name
      );
    },
  },
  created() {
    const base = {
      condition_status: "no",
      cond_logic: "all",
      cond_action: "show",
      cond_field: [],
      cond_operator: [],
      cond_option: [],
      ...this.editfield.contactum_cond,
    };

    for (let i = 0; i < base.cond_field.length; i++) {
      if (base.cond_field[i] && base.cond_operator[i]) {
        this.conditions.push({
          name: base.cond_field[i],
          operator: base.cond_operator[i],
          option: base.cond_option[i] || "",
        });
      }
    }

    if (!this.conditions.length) {
      this.conditions.push({ name: "", operator: "=", option: "" });
    }

    this.$store.dispatch("update_editing_form_field", {
      id: this.editfield.id,
      property: "contactum_cond",
      value: base,
    });
  },
  methods: {
    toggleCondition(val) {
      this.contactum_cond = {
        ...this.contactum_cond,
        condition_status: val ? "yes" : "no",
      };
    },
    get_condition_option(field_name) {
      const dep = this.dependencies.find((f) => f.name === field_name);
      if (!dep || !dep.options) return [];
      return Object.values(dep.options).map((o) => ({
        option_value: o.value,
        option_label: o.label,
      }));
    },
    add_condition() {
      this.conditions.push({ name: "", operator: "=", option: "" });
    },
    delete_condition(index) {
      this.conditions.splice(index, 1);
    },
  },
  watch: {
    conditions: {
      deep: true,
      handler(newConds) {
        const updated = { ...this.contactum_cond };
        updated.cond_field    = newConds.map((c) => c.name);
        updated.cond_operator = newConds.map((c) => c.operator);
        updated.cond_option   = newConds.map((c) => c.option);
        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: "contactum_cond",
          value: updated,
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
$gray-4:  #4b5563;
$gray-6:  #6b7280;
$gray-9:  #9ca3af;
$radius:  8px;

// ── Wrapper ───────────────────────────────────────────────────────────────────
.cl-wrap {
  border: 1px solid $border;
  border-radius: $radius;
  overflow: hidden;
  background: #fff;
}

// ── Header ────────────────────────────────────────────────────────────────────
.cl-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 14px;
  background: #f9fafb;
  border-bottom: 1px solid $border;
}
.cl-header__info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.cl-header__label {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
}
.cl-header__desc {
  font-size: 11px;
  color: $gray-6;
}

// ── Body ──────────────────────────────────────────────────────────────────────
.cl-body {
  padding: 14px;
}

// ── Action sentence ───────────────────────────────────────────────────────────
.cl-sentence {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
  padding: 10px 12px;
  background: $blue-lt;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  font-size: 13px;
  color: $gray-4;
}
.cl-sentence__text {
  white-space: nowrap;
  color: #374151;
  font-weight: 500;
}

// ── Selects ───────────────────────────────────────────────────────────────────
.cl-sel {
  &--action { width: 76px; }
  &--logic  { width: 72px; }
  &--field  { flex: 1 1 120px; min-width: 0; }
  &--op     { width: 120px; flex-shrink: 0; }
  &--val    { flex: 1 1 100px; min-width: 0; }
}

// ── Condition rows ────────────────────────────────────────────────────────────
.cl-conditions {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin-bottom: 12px;
}

.cl-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  background: #fff;
  border: 1px solid $border;
  border-radius: $radius;
  transition: border-color .15s, box-shadow .15s;

  &:hover {
    border-color: #93c5fd;
    box-shadow: 0 1px 4px rgba(37, 99, 235, .07);
  }
}

// AND / OR pill between rows
.cl-connector {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 4px 0;

  span {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    background: #f3f4f6;
    color: $gray-6;
    border: 1px solid $border;
  }
}

// delete button
.cl-row__del {
  flex-shrink: 0;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: 1px solid transparent;
  background: transparent;
  color: $gray-9;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  transition: all .15s;
  padding: 0;

  &:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: $red;
  }
}

// ── Add condition button ───────────────────────────────────────────────────────
.cl-add-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px dashed #93c5fd;
  background: transparent;
  color: $blue;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all .15s;

  i { font-size: 12px; }

  &:hover {
    background: $blue-lt;
    border-style: solid;
  }
}
</style>
