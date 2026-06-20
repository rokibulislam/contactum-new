<template>
  <div class="rd-wrap">

    <!-- ── Toolbar ───────────────────────────────────────────────────────── -->
    <div class="rd-toolbar">
      <span class="rd-toolbar__title">
        {{ field.title }}
        <help-text v-if="field.help_text" :text="field.help_text" />
      </span>
      <label class="rd-toggle" :class="{ 'rd-toggle--on': show_value }">
        <el-checkbox v-model="show_value" />
        <span>Values</span>
      </label>
    </div>

    <!-- ── Column headers ────────────────────────────────────────────────── -->
    <div class="rd-col-headers">
      <span class="rd-col-headers__spacer"></span>
      <span>Label</span>
      <span v-if="show_value">Value</span>
    </div>

    <!-- ── Option rows ───────────────────────────────────────────────────── -->
    <ul class="rd-list option-field-swapper">
      <li
        v-for="(option, index) in options"
        :key="index"
        class="rd-row"
      >
        <!-- drag handle -->
        <span class="rd-row__drag">
          <i class="el-icon-more rd-drag-icon"></i>
        </span>

        <!-- default selector -->
        <span class="rd-row__selector">
          <input
            type="radio"
            :value="option.value"
            v-model="selected"
            class="rd-check"
          />
        </span>

        <!-- label -->
        <div class="rd-row__label">
          <el-input
            v-model="option.label"
            size="small"
            placeholder="Label"
            @input="sync_label(index, option.label)"
          />
        </div>

        <!-- value -->
        <div v-if="show_value" class="rd-row__value">
          <el-input v-model="option.value" size="small" placeholder="value" />
        </div>

        <!-- actions -->
        <span class="rd-row__actions">
          <i
            class="el-icon-circle-plus rd-btn-add"
            title="Add row"
            @click.prevent="add_option"
          ></i>
          <i
            v-if="options.length > 1"
            class="el-icon-remove rd-btn-del"
            title="Remove"
            @click="delete_option(index)"
          ></i>
        </span>
      </li>
    </ul>

    <!-- ── Footer ────────────────────────────────────────────────────────── -->
    <div class="rd-footer">
      <button class="rd-add-btn" @click.prevent="add_option">
        <i class="el-icon-plus"></i> Add Row
      </button>
      <button
        v-if="selected"
        class="rd-clear-btn"
        @click.prevent="clear_selection"
      >
        <i class="el-icon-close"></i> Clear Default
      </button>
    </div>

  </div>
</template>

<script>
import option_field from "../../../mixin/option-field.js";
export default {
  name: "field_row_data",
  mixins: [option_field],
  data() {
    return {
      show_value: false,
      options: [],
      selected: "",
    };
  },
  computed: {
    field_selected() {
      return this.editfield.selected;
    },
  },
  mounted() {
    this.set_options();
  },
  methods: {
    set_options() {
      this.options  = this.editfield.grid_rows || [];
      this.selected = this.editfield.selected  || "";
    },
    sync_label(index, label) {
      if (this.editfield.sync_value) {
        this.options[index].value = label.toLowerCase().replace(/\s/g, "_");
      }
    },
    add_option() {
      const nums = this.options.map((o) => {
        const m = o.label.match(/Row-(\d+)/);
        return m ? parseInt(m[1]) : 0;
      });
      const next = nums.length ? Math.max(...nums) + 1 : 1;
      this.options.push({ label: `Row-${next}`, value: `row-${next}` });
      this.dispatch_options();
    },
    delete_option(index) {
      this.options.splice(index, 1);
      this.dispatch_options();
    },
    clear_selection() {
      this.selected = "";
    },
    dispatch_options() {
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "grid_rows",
        value: this.options,
      });
    },
  },
  watch: {
    options: {
      deep: true,
      handler(val) {
        this.$store.dispatch("update_editing_form_field", {
          id: this.editfield.id,
          property: "grid_rows",
          value: val,
        });
      },
    },
    selected(val) {
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id,
        property: "selected",
        value: val,
      });
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

// ── Toolbar ───────────────────────────────────────────────────────────────────
.rd-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}
.rd-toolbar__title {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 4px;
}

.rd-toggle {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px 4px 8px;
  border-radius: 20px;
  border: 1px solid $border;
  background: #fff;
  font-size: 12px;
  color: $gray-6;
  cursor: pointer;
  transition: all .15s;
  user-select: none;

  &:hover     { border-color: #93c5fd; color: $blue; }
  &--on       { border-color: $blue; background: $blue-lt; color: $blue; font-weight: 500; }
}

// ── Column headers ────────────────────────────────────────────────────────────
.rd-col-headers {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 8px 4px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .04em;
  color: $gray-9;

  &__spacer { width: 56px; flex-shrink: 0; }
  span:not(.rd-col-headers__spacer) { flex: 1; }
}

// ── List ──────────────────────────────────────────────────────────────────────
.rd-list {
  list-style: none;
  padding: 0;
  margin: 0 0 10px;
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
}

.rd-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 8px;
  background: #fff;
  border: 1px solid $border;
  border-radius: $radius;
  margin-bottom: 6px;
  transition: box-shadow .15s, border-color .15s;

  &:hover {
    border-color: #93c5fd;
    box-shadow: 0 1px 4px rgba(37, 99, 235, .08);
  }

  &__drag     { flex-shrink: 0; }
  &__selector { flex-shrink: 0; }
  &__label    { flex: 1; min-width: 0; }
  &__value    { flex: 0 0 90px; }
  &__actions  { flex-shrink: 0; display: flex; align-items: center; gap: 2px; }
}

.rd-drag-icon {
  display: block;
  transform: rotate(90deg);
  color: $gray-9;
  font-size: 14px;
  cursor: grab;
  padding: 2px 4px;
  border-radius: 4px;
  transition: color .15s;
  &:hover { color: $blue; }
}

.rd-check {
  width: 14px;
  height: 14px;
  cursor: pointer;
  accent-color: $blue;
}

.rd-btn-add,
.rd-btn-del {
  font-size: 16px;
  padding: 3px;
  border-radius: 4px;
  cursor: pointer;
  transition: color .15s, background .15s;
}
.rd-btn-add {
  color: $blue;
  opacity: .7;
  &:hover { opacity: 1; background: $blue-lt; }
}
.rd-btn-del {
  color: $gray-9;
  opacity: 0;
  .rd-row:hover & { opacity: 1; }
  &:hover { color: $red; background: #fef2f2; }
}

// ── Footer ────────────────────────────────────────────────────────────────────
.rd-footer {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0 2px;
}

.rd-add-btn {
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
  &:hover { background: $blue-lt; border-style: solid; }
}

.rd-clear-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  border-radius: 6px;
  border: 1px solid #fca5a5;
  background: #fff;
  color: $red;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all .15s;
  i { font-size: 12px; }
  &:hover { background: #fef2f2; }
}
</style>
