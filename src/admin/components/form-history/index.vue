<template>
  <div class="ctm-form-history">
    <div class="ctm-form-history__toolbar">
      <h2 class="section-title">History</h2>
      <button
        v-if="history.length"
        class="btn btn-ghost"
        @click.prevent="clearHistory"
      >
        <i class="el-icon-delete"></i> Clear History
      </button>
    </div>

    <div v-loading="loading" class="ctm-form-history__body">
      <ul v-if="history.length" class="ctm-form-history__timeline">
        <li
          v-for="entry in history"
          :key="entry.id"
          class="ctm-form-history__item"
          @mouseenter="preview(entry)"
          @mouseleave="cancelPreview()"
        >
          <div class="ctm-form-history__row">
            <span class="ctm-form-history__title">{{ entry.change_title }}</span>
            <button class="btn btn-link" @click.prevent="restore(entry)">Restore</button>
          </div>

          <button
            type="button"
            class="ctm-form-history__time"
            @click.prevent="toggleExpanded(entry.id)"
          >
            {{ entry.time_ago }}
          </button>

          <transition name="slide-fade">
            <ul v-if="expanded === entry.id" class="ctm-form-history__changes">
              <li v-for="(change, index) in entry.changes" :key="index">
                {{ describeChange(change) }}
              </li>
            </ul>
          </transition>
        </li>
      </ul>

      <div v-else class="ctm-form-history__empty">
        <i class="el-icon-time"></i>
        <p>
          No history yet. Save your current form changes and this timeline will start filling up
          — hover an entry to preview it on the canvas, or restore it and click Save to keep it.
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { v4 as uuidv4 } from "uuid";

export default {
  name: "FormHistory",
  props: ["id"],
  data() {
    return {
      loading: false,
      history: [],
      expanded: null,
      previewSnapshot: null,
      isRestoring: false,
    };
  },
  computed: {
    form_fields() {
      return this.$store.getters.form_fields;
    },
  },
  mounted() {
    this.fetchHistory();
  },
  beforeDestroy() {
    // Leaving the History tab with a hover-preview still applied (e.g. via
    // keyboard nav or a fast tab switch that skips mouseleave) shouldn't
    // silently leave the canvas showing a state the user never chose.
    this.cancelPreview();
  },
  methods: {
    fetchHistory() {
      this.loading = true;

      jQuery.post(
        contactum.ajaxurl,
        {
          action: "contactum_get_form_history",
          form_id: this.id,
          _ajax_nonce: contactum.nonce,
        },
        (response) => {
          this.loading = false;

          if (response.success) {
            this.history = response.data;
          }
        }
      );
    },

    clearHistory() {
      this.$confirm(
        "This permanently deletes this form's saved history. Continue?",
        "Clear History",
        { type: "warning" }
      )
        .then(() => {
          jQuery.post(
            contactum.ajaxurl,
            {
              action: "contactum_clear_form_history",
              form_id: this.id,
              _ajax_nonce: contactum.nonce,
            },
            (response) => {
              if (response.success) {
                this.history = [];
                this.expanded = null;

                this.$notify.success({
                  title: "",
                  message: "History cleared.",
                  position: "bottom-right",
                });
              }
            }
          );
        })
        .catch(() => {});
    },

    toggleExpanded(id) {
      this.expanded = this.expanded === id ? null : id;
    },

    describeChange(change) {
      switch (change.type) {
        case "added":
          return `Added "${change.label}"`;
        case "removed":
          return `Removed "${change.label}"`;
        case "modified":
          return `Modified "${change.label}"`;
        case "reordered":
          return "Reordered fields";
        default:
          return change.label;
      }
    },

    preview(entry) {
      if (this.isRestoring) {
        return;
      }

      this.previewSnapshot = JSON.parse(JSON.stringify(this.form_fields));
      this.$store.dispatch(
        "set_form_fields",
        JSON.parse(JSON.stringify(entry.old_data))
      );
    },

    cancelPreview() {
      if (this.isRestoring || !this.previewSnapshot) {
        return;
      }

      this.$store.dispatch("set_form_fields", this.previewSnapshot);
      this.previewSnapshot = null;
    },

    restore(entry) {
      this.isRestoring = true;
      this.previewSnapshot = null;

      // A historical snapshot's field ids may point at field posts that
      // were since permanently deleted, so every restored field is treated
      // as brand new (fresh id, is_new flag) — the same path a field added
      // from scratch already takes — rather than risk the next Save trying
      // to update a post that no longer exists.
      const restored = entry.old_data.map((field) => ({
        ...field,
        id: uuidv4(),
        is_new: true,
      }));

      this.$store.dispatch("set_form_fields", restored);

      this.$notify.success({
        title: "",
        message: "Restored from History! Click Save Form to confirm.",
        position: "bottom-right",
      });

      this.$nextTick(() => {
        this.isRestoring = false;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.ctm-form-history {
  &__toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
  }

  &__timeline {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  &__item {
    padding: 12px 14px;
    border: 1px solid #e4e7ed;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #fff;

    &:hover {
      border-color: #c6d9f1;
      background: #f7fafd;
    }
  }

  &__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  &__title {
    font-size: 13px;
    font-weight: 600;
    color: #303133;
  }

  &__time {
    background: none;
    border: none;
    padding: 4px 0 0;
    margin: 0;
    font-size: 12px;
    color: #909399;
    cursor: pointer;

    &:hover {
      color: #409eff;
      text-decoration: underline;
    }
  }

  &__changes {
    list-style: disc;
    margin: 8px 0 0 18px;
    padding: 0;
    font-size: 12px;
    color: #606266;

    li {
      margin-bottom: 4px;
    }
  }

  &__empty {
    text-align: center;
    padding: 40px 20px;
    color: #909399;

    i {
      font-size: 28px;
      margin-bottom: 10px;
      display: block;
    }

    p {
      max-width: 360px;
      margin: 0 auto;
      font-size: 13px;
      line-height: 1.6;
    }
  }
}
</style>
