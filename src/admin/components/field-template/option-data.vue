<template>
  <div class="om-wrap">

    <!-- ── Toggles toolbar ─────────────────────────────────────────────── -->
    <div class="om-toolbar">
      <label class="om-toggle" :class="{ 'om-toggle--on': editfield.show_value }">
        <el-checkbox v-model="editfield.show_value" />
        <span>Values</span>
      </label>
      <label class="om-toggle" :class="{ 'om-toggle--on': editfield.sync_value }">
        <el-checkbox v-model="editfield.sync_value" />
        <span>Auto-sync</span>
      </label>
      <label class="om-toggle" :class="{ 'om-toggle--on': editfield.calc_value }">
        <el-checkbox v-model="editfield.calc_value" />
        <span>Calc</span>
      </label>
      <label v-if="editfield.image" class="om-toggle" :class="{ 'om-toggle--on': editfield.photo_value }">
        <el-checkbox v-model="editfield.photo_value" />
        <span>Photo</span>
      </label>
    </div>

    <!-- ── Column headers ─────────────────────────────────────────────── -->
    <div class="om-col-headers">
      <span class="om-col-headers__spacer"></span><!-- drag + selector -->
      <span>Label</span>
      <span v-if="editfield.show_value">Value</span>
      <span v-if="editfield.calc_value">Calc</span>
      <span v-if="editfield.photo_value && editfield.image">Photo</span>
    </div>

    <!-- ── Option rows ────────────────────────────────────────────────── -->
    <ul class="om-list option-field-swapper">
      <li
        v-for="(option, index) in options"
        :key="index"
        class="om-row"
      >
        <!-- drag handle -->
        <span class="om-row__drag">
          <i class="el-icon-more om-drag-icon"></i>
        </span>

        <!-- default selector -->
        <span class="om-row__selector">
          <input
            v-if="field.is_multiple"
            type="checkbox"
            :value="option.value"
            v-model="selected"
            class="om-check"
          />
          <input
            v-else
            type="radio"
            :value="option.value"
            v-model="selected"
            class="om-check"
          />
        </span>

        <!-- label -->
        <div class="om-row__label">
          <el-input
            v-model="option.label"
            size="small"
            placeholder="Label"
            @input="set_option_value(index, option.label)"
          />
        </div>

        <!-- value -->
        <div v-if="editfield.show_value" class="om-row__value">
          <el-input v-model="option.value" size="small" placeholder="value" />
        </div>

        <!-- calc value -->
        <div v-if="editfield.calc_value" class="om-row__value">
          <el-input v-model="option.calc_value" size="small" placeholder="0" />
        </div>

        <!-- photo -->
        <div v-if="editfield.photo_value" class="om-row__photo">
          <div class="om-photo-thumb" @click="initUploader(option)">
            <img v-if="option.photo" :src="option.photo" />
            <i v-else class="el-icon-picture-outline om-photo-thumb__icon"></i>
          </div>
          <i v-if="option.photo" class="el-icon-close om-photo-remove" @click="option.photo = ''"></i>
        </div>

        <!-- actions -->
        <span class="om-row__actions">
          <i class="el-icon-circle-plus om-btn-add" title="Add option" @click.prevent="add_option"></i>
          <i
            v-if="options.length > 1"
            class="el-icon-remove om-btn-del"
            title="Remove"
            @click="delete_option(index)"
          ></i>
        </span>
      </li>
    </ul>

    <!-- ── Footer ─────────────────────────────────────────────────────── -->
    <div class="om-footer">
      <button class="om-btn om-btn--ghost" @click="initBulkEdit()">
        <i class="el-icon-edit-outline"></i> Bulk Edit
      </button>
      <button
        v-if="!field.is_multiple && selected.length > 0"
        class="om-btn om-btn--danger"
        @click.prevent="clear_selection"
      >
        <i class="el-icon-close"></i> Clear Selection
      </button>
    </div>

    <!-- ── Bulk-edit dialog ───────────────────────────────────────────── -->
    <el-dialog
      :visible.sync="bulkEditVisible"
      custom-class="ctm-bulk-dialog"
      width="500px"
      :close-on-click-modal="false"
      append-to-body
    >
      <!-- header -->
      <div slot="title" class="ctm-bulk-dialog__head">
        <div class="ctm-bulk-dialog__head-icon">
          <i class="el-icon-edit-outline"></i>
        </div>
        <div>
          <div class="ctm-bulk-dialog__head-title">Bulk Edit Options</div>
          <div class="ctm-bulk-dialog__head-sub">
            One option per line &nbsp;·&nbsp;
            <code>Label</code> or <code>Label:value</code>
          </div>
        </div>
      </div>

      <!-- body -->
      <div class="ctm-bulk-dialog__body">

        <!-- presets -->
        <div v-if="Object.keys(editor_options).length" class="ctm-bulk-dialog__presets">
          <div class="ctm-bulk-dialog__presets-header">
            <i class="el-icon-collection-tag"></i>
            <span>Quick presets</span>
          </div>
          <div class="ctm-bulk-dialog__preset-list">
            <button
              v-for="(opts, name) in editor_options"
              :key="name"
              :class="['ctm-preset-btn', { 'ctm-preset-btn--active': activePreset === name }]"
              @click="setOptions(opts, name)"
            >{{ name }}</button>
          </div>
        </div>

        <!-- textarea -->
        <div class="ctm-bulk-dialog__editor">
          <div class="ctm-bulk-dialog__editor-bar">
            <span class="ctm-bulk-dialog__editor-label">Options</span>
            <span class="ctm-bulk-dialog__editor-count">{{ lineCount }} line{{ lineCount !== 1 ? 's' : '' }}</span>
          </div>
          <textarea
            v-model="value_key_pair_text"
            class="ctm-bulk-dialog__textarea"
            placeholder="Option 1&#10;Option 2:value2&#10;Option 3"
            spellcheck="false"
          ></textarea>
        </div>

        <p class="ctm-bulk-dialog__hint">
          <i class="el-icon-info"></i>
          Lines without a colon use the label as the value. Blank lines are ignored.
        </p>
      </div>

      <!-- footer -->
      <div slot="footer" class="ctm-bulk-dialog__footer">
        <button class="om-btn om-btn--ghost" @click="bulkEditVisible = false">Cancel</button>
        <button class="om-btn om-btn--primary" @click="confirmBulkEdit()">
          <i class="el-icon-check"></i> Apply
        </button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import option_field from "../../mixin/option-field.js";
import media from "../../mixin/media.js";
export default {
  name: "field_option_data",
  mixins: [option_field, media],
  data() {
    return {
      options: [],
      selected: [],
      bulkEditVisible: false,
      value_key_pair_text: "",
      activePreset: "",
      editor_options: JSON.parse(window.contactum.bulk_options_json),
    };
  },
  computed: {
    field_selected() {
      return this.editfield.selected;
    },
    lineCount() {
      return this.value_key_pair_text
        .split("\n")
        .filter((l) => l.trim()).length;
    },
  },
  methods: {
    initBulkEdit() {
      this.value_key_pair_text = this.options
        .map((item) => item.label + ":" + item.value)
        .join("\n");
      this.activePreset = "";
      this.bulkEditVisible = true;
    },

    setOptions(options, groupName) {
      this.value_key_pair_text = options.join("\n");
      this.activePreset = groupName;
    },

    confirmBulkEdit() {
      const values = this.value_key_pair_text
        .split("\n")
        .filter((l) => l.trim())
        .map((line) => {
          const idx   = line.indexOf(":");
          const label = idx >= 0 ? line.slice(0, idx).trim() : line.trim();
          const value = idx >= 0 ? line.slice(idx + 1).trim() : label;
          return label ? { label, value: value || label, photo: "" } : null;
        })
        .filter(Boolean);

      this.options       = values;
      this.bulkEditVisible = false;
    },

    set_options() {
      this.options = this.editfield.options;
      if (this.editfield.is_multiple && !Array.isArray(this.field_selected)) {
        this.selected = [this.field_selected];
      } else {
        this.selected = this.field_selected;
      }
    },

    add_option() {
      const numbers   = this.options.map((opt) => {
        const m = opt.label.match(/Option-(\d+)/);
        return m ? parseInt(m[1]) : 0;
      });
      const next  = numbers.length ? Math.max(...numbers) + 1 : 1;
      this.options.push({ label: `Option-${next}`, value: `option-${next}`, photo: "" });
      this.$store.dispatch("update_editing_form_field", {
        id: this.editfield.id, property: "options", value: this.options,
      });
    },

    set_option_value(index, label) {
      if (this.sync_value) {
        this.options[index].value = label.toLowerCase().replace(/\s/g, "_");
      }
    },
  },
};
</script>

<style lang="scss" scoped>

// ── Tokens ────────────────────────────────────────────────────────────────────
$border:   #e5e7eb;
$bg-soft:  #f9fafb;
$blue:     #2563eb;
$blue-lt:  #eff6ff;
$red:      #ef4444;
$gray-4:   #4b5563;
$gray-6:   #6b7280;
$gray-9:   #9ca3af;
$radius:   8px;

// ── Toolbar ───────────────────────────────────────────────────────────────────
.om-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 12px;
}

.om-toggle {
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

  &:hover { border-color: #93c5fd; color: $blue; }
  &--on   { border-color: $blue; background: $blue-lt; color: $blue; font-weight: 500; }
}

// ── Column headers ────────────────────────────────────────────────────────────
.om-col-headers {
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

  span:not(.om-col-headers__spacer) {
    flex: 1;
  }
}

// ── Option list ───────────────────────────────────────────────────────────────
.om-list {
  list-style: none;
  padding: 0;
  margin: 0 0 10px;
  max-height: 320px;
  overflow-y: auto;
  overflow-x: hidden;
}

.om-row {
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
    box-shadow: 0 1px 4px rgba(37,99,235,.08);
  }

  &__drag   { flex-shrink: 0; }
  &__selector { flex-shrink: 0; }
  &__label  { flex: 1; min-width: 0; }
  &__value  { flex: 0 0 90px; }
  &__photo  { flex-shrink: 0; display: flex; align-items: center; gap: 4px; }
  &__actions { flex-shrink: 0; display: flex; align-items: center; gap: 2px; }
}

// drag icon — rotated el-icon-more gives a ⠿ look
.om-drag-icon {
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

.om-check {
  width: 14px;
  height: 14px;
  cursor: pointer;
  accent-color: $blue;
}

// row action icons
.om-btn-add,
.om-btn-del {
  font-size: 16px;
  padding: 3px;
  border-radius: 4px;
  cursor: pointer;
  transition: color .15s, background .15s;
}
.om-btn-add {
  color: $blue;
  opacity: .7;
  &:hover { opacity: 1; background: $blue-lt; }
}
.om-btn-del {
  color: $gray-9;
  opacity: 0;
  .om-row:hover & { opacity: 1; }
  &:hover { color: $red; background: #fef2f2; }
}

// photo thumb
.om-photo-thumb {
  width: 36px;
  height: 36px;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  background: $bg-soft;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  transition: border-color .15s;

  &:hover { border-color: $blue; }
  img { width: 100%; height: 100%; object-fit: cover; }
  &__icon { color: $gray-9; font-size: 16px; }
}
.om-photo-remove {
  font-size: 12px;
  color: $red;
  cursor: pointer;
  &:hover { color: darken($red, 10%); }
}

// ── Footer ────────────────────────────────────────────────────────────────────
.om-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 4px 2px;
}

// ── Shared button ─────────────────────────────────────────────────────────────
.om-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all .15s;
  line-height: 1.4;
  background: none;

  i { font-size: 13px; }

  &--ghost {
    border-color: $border;
    color: $gray-4;
    background: #fff;
    &:hover { border-color: #93c5fd; color: $blue; background: $blue-lt; }
  }
  &--primary {
    background: $blue;
    color: #fff;
    border-color: $blue;
    &:hover { background: darken($blue, 6%); border-color: darken($blue, 6%); }
  }
  &--danger {
    color: $red;
    border-color: #fca5a5;
    background: #fff;
    &:hover { background: #fef2f2; }
  }
}

// ── Bulk-edit dialog (scoped to this component) ───────────────────────────────
// el-dialog injects into body, but the content slots are compiled inside
// the scoped component, so direct class selectors work fine here.

.ctm-bulk-dialog__head {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}
.ctm-bulk-dialog__head-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  background: $blue-lt;
  color: $blue;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
  margin-top: 1px;
}
.ctm-bulk-dialog__head-title {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  line-height: 1.3;
}
.ctm-bulk-dialog__head-sub {
  font-size: 12px;
  color: $gray-6;
  margin-top: 3px;

  code {
    background: #f3f4f6;
    border-radius: 3px;
    padding: 1px 5px;
    font-family: monospace;
    font-size: 11px;
    color: #374151;
  }
}

.ctm-bulk-dialog__body {
  padding: 0 2px;
}

// presets
.ctm-bulk-dialog__presets {
  margin-bottom: 14px;
}
.ctm-bulk-dialog__presets-header {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: $gray-9;
  margin-bottom: 8px;
  i { font-size: 13px; }
}
.ctm-bulk-dialog__preset-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}
.ctm-preset-btn {
  padding: 4px 13px;
  border-radius: 20px;
  font-size: 12px;
  border: 1px solid $border;
  background: #fff;
  color: $gray-4;
  cursor: pointer;
  transition: all .15s;
  line-height: 1.5;

  &:hover          { border-color: #93c5fd; color: $blue; background: $blue-lt; }
  &--active        { background: $blue; border-color: $blue; color: #fff; }
}

// editor area
.ctm-bulk-dialog__editor {
  border: 1px solid $border;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  transition: border-color .15s;

  &:focus-within { border-color: $blue; }
}
.ctm-bulk-dialog__editor-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 12px;
  background: $bg-soft;
  border-bottom: 1px solid $border;
}
.ctm-bulk-dialog__editor-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: $gray-9;
}
.ctm-bulk-dialog__editor-count {
  font-size: 11px;
  color: $gray-9;
  font-variant-numeric: tabular-nums;
}
.ctm-bulk-dialog__textarea {
  display: block;
  width: 100%;
  min-height: 190px;
  padding: 10px 12px;
  border: none;
  outline: none;
  font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
  font-size: 13px;
  line-height: 1.7;
  color: #111827;
  background: transparent;
  resize: vertical;
  box-sizing: border-box;
}

.ctm-bulk-dialog__hint {
  margin: 8px 0 0;
  font-size: 11px;
  color: $gray-9;
  display: flex;
  align-items: flex-start;
  gap: 4px;
  line-height: 1.5;
  i { margin-top: 1px; flex-shrink: 0; }
}

.ctm-bulk-dialog__footer {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  padding-top: 4px;
}
</style>
