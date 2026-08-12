<template>
  <div class="ctm-tool-section">
    <div class="ctm-tool-section__head">
      <h3 class="ctm-tool-section__title">Migrate from Other Plugins</h3>
      <p class="ctm-tool-section__desc">
        Import forms straight from another form plugin installed on this site — fields, labels, and
        settings are carried over automatically. Only plugins detected as active can be imported from.
      </p>
    </div>

    <div class="ctm-tool-section__body ctm-migrator">

      <div v-if="!importers.length" class="ctm-migrator__empty">
        No importers are registered.
      </div>

      <div
        v-for="item in importers"
        :key="item.id"
        class="ctm-mig-card"
        :class="{ 'ctm-mig-card--inactive': !item.active }"
      >
        <div class="ctm-mig-card__row">
          <div class="ctm-mig-card__info">
            <span class="ctm-mig-card__name">{{ item.title }}</span>
            <span
              class="ctm-mig-card__badge"
              :class="item.active ? 'ctm-mig-card__badge--active' : 'ctm-mig-card__badge--inactive'"
            >
              {{ item.active ? 'Detected' : 'Not installed' }}
            </span>
          </div>

          <el-button
            v-if="!results[item.id]"
            type="primary"
            size="small"
            :disabled="!item.active"
            :loading="importing === item.id"
            @click="runImport(item)"
          >
            Import Forms
          </el-button>
        </div>

        <!-- Result state -->
        <div v-if="results[item.id]" class="ctm-mig-card__result">

          <el-alert
            v-if="results[item.id].error"
            :title="results[item.id].error"
            type="error"
            :closable="false"
            show-icon
          />

          <template v-else>
            <el-alert
              :title="results[item.id].title"
              type="success"
              :closable="false"
              show-icon
              style="margin-bottom: 12px;"
            />

            <ul class="ctm-mig-card__refs">
              <li v-for="ref in results[item.id].refs" :key="ref.contactum_id">
                <a :href="editUrl(ref.contactum_id)" target="_blank">{{ ref.title }}</a>
                <a :href="editUrl(ref.contactum_id)" target="_blank" class="ctm-mig-card__edit">
                  <i class="el-icon-edit"></i> Edit
                </a>
              </li>
            </ul>

            <div v-if="results[item.id].refs.length && !replaced[item.id]" class="ctm-mig-card__actions">
              <span class="ctm-mig-card__ask">Replace {{ item.title }} shortcodes on your pages with these forms?</span>
              <el-button size="mini" type="primary" :loading="replacing === item.id" @click="runReplace(item, 'replace')">
                Replace Shortcodes
              </el-button>
              <el-button size="mini" @click="runReplace(item, 'skip')">No Thanks</el-button>
            </div>
          </template>

          <el-button size="mini" style="margin-top: 10px;" @click="reset(item)">
            Done
          </el-button>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
/* global jQuery */
const $ = window.jQuery;
const cpm = window.contactum || {};

export default {
  name: 'Migrator',

  data() {
    return {
      importers: cpm.importers || [],
      importing: null,
      replacing: null,
      results: {},
      replaced: {},
    };
  },

  methods: {
    editUrl(formId) {
      // Matches the route the existing per-importer admin-notice banner
      // already links to (class-importer-abstract.php's inline JS).
      return 'admin.php?page=contactum&route=builder#/forms/' + formId;
    },

    runImport(item) {
      this.importing = item.id;

      $.post(cpm.ajaxurl, {
        action:   'contactum_import_xforms_' + item.id,
        _wpnonce: cpm.import_nonce,
      }, (response) => {
        this.importing = null;

        if (response.success) {
          this.$set(this.results, item.id, {
            title: response.data.title,
            refs:  Object.values(response.data.refs || {}),
          });
        } else {
          this.$set(this.results, item.id, {
            error: (response.data && response.data.message) || 'Import failed. Please try again.',
          });
        }
      }).fail(() => {
        this.importing = null;
        this.$set(this.results, item.id, { error: 'Request failed. Please try again.' });
      });
    },

    runReplace(item, type) {
      this.replacing = item.id;

      $.post(cpm.ajaxurl, {
        action:   'contactum_import_xreplace_' + item.id,
        type,
        _wpnonce: cpm.import_nonce,
      }, () => {
        this.replacing = null;
        this.$set(this.replaced, item.id, true);
        this.$message.success(type === 'replace' ? 'Shortcodes replaced.' : 'Skipped.');
      }).fail(() => {
        this.replacing = null;
        this.$message.error('Request failed. Please try again.');
      });
    },

    reset(item) {
      this.$delete(this.results, item.id);
      this.$delete(this.replaced, item.id);
    },
  },
};
</script>

<style scoped lang="scss">
.ctm-migrator {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 680px;
}

.ctm-migrator__empty {
  font-size: 13px;
  color: #9ca3af;
}

.ctm-mig-card {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 14px 16px;
  background: #fafafa;
  transition: border-color 0.2s;

  &--inactive {
    opacity: 0.65;
  }
}

.ctm-mig-card__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.ctm-mig-card__info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ctm-mig-card__name {
  font-size: 14px;
  font-weight: 600;
  color: #1e1f21;
}

.ctm-mig-card__badge {
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.02em;

  &--active {
    background: #ecfdf5;
    color: #059669;
  }

  &--inactive {
    background: #f3f4f6;
    color: #9ca3af;
  }
}

.ctm-mig-card__result {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e5e7eb;
}

.ctm-mig-card__refs {
  list-style: none;
  margin: 0 0 12px;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;

  li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
  }
}

.ctm-mig-card__edit {
  font-size: 12px;
  color: #6b7280;
}

.ctm-mig-card__actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.ctm-mig-card__ask {
  font-size: 12.5px;
  color: #6b7280;
}
</style>
