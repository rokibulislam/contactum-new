<template>
  <div class="int-page">

    <!-- Header ────────────────────────────────────────────────────────────── -->
    <div class="int-header">
      <div class="int-header__left">
        <div class="int-header__icon">
          <span class="dashicons dashicons-admin-plugins"></span>
        </div>
        <div>
          <h2 class="int-header__title">Integrations</h2>
          <p class="int-header__sub">Connect your forms with your favourite services</p>
        </div>
      </div>
      <div class="int-header__badges" v-if="is_pro && filteredAddons.length">
        <span class="int-badge int-badge--green">
          <span class="dashicons dashicons-yes-alt"></span>
          {{ activeCount }} Active
        </span>
        <span class="int-badge">
          {{ filteredAddons.length }} Total
        </span>
      </div>
    </div>

    <!-- Toolbar ───────────────────────────────────────────────────────────── -->
    <div class="int-toolbar">
      <div class="int-toolbar__actions" v-if="is_pro">
        <button class="int-btn int-btn--primary" @click.prevent="toggleModule('all', 'activate')">
          <span class="dashicons dashicons-yes-alt"></span>
          Activate All
        </button>
        <button class="int-btn int-btn--ghost" @click.prevent="toggleModule('all', 'deactivate')">
          <span class="dashicons dashicons-minus"></span>
          Deactivate All
        </button>
      </div>
      <div class="int-search">
        <span class="dashicons dashicons-search int-search__icon"></span>
        <input
          v-model="search"
          type="text"
          placeholder="Search integrations..."
          class="int-search__input"
        />
        <button v-if="search" class="int-search__clear" @click="search = ''">
          <span class="dashicons dashicons-no-alt"></span>
        </button>
      </div>
    </div>

    <!-- Grid ──────────────────────────────────────────────────────────────── -->
    <div class="int-grid" v-if="filteredAddons.length > 0">
      <div
        v-for="(integration, index) in filteredAddons"
        :key="integration.path || integration.id"
        class="int-card"
        :class="{ 'int-card--active': integration.enable }"
      >
        <!-- Body -->
        <div class="int-card__body">
          <div class="int-card__thumb">
            <img
              :src="integration.thumbnail"
              :alt="integration.name"
              class="int-card__img"
            />
          </div>
          <div class="int-card__info">
            <h3 class="int-card__name">{{ integration.name }}</h3>
            <p class="int-card__desc">{{ integration.description }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="int-card__footer">
          <!-- Pro: toggle + status -->
          <template v-if="is_pro">
            <div class="int-card__footer-left">
              <el-switch
                v-model="integration.enable"
                :active-value="1"
                :inactive-value="0"
                @change="toggleState(integration, $event, index)"
              />
              <span class="int-status" :class="integration.enable ? 'int-status--on' : 'int-status--off'">
                {{ integration.enable ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <a
              v-if="integration.enable"
              :href="`${admin_url}?page=contactum-settings#${integration.author_uri}`"
              class="int-settings-link"
              title="Configure"
            >
              <span class="dashicons dashicons-admin-generic"></span>
              Configure
            </a>
          </template>

          <!-- Free: upgrade CTA -->
          <template v-else>
            <div class="int-card__footer-left">
              <span class="int-pro-tag">
                <span class="dashicons dashicons-lock"></span>
                Pro Only
              </span>
            </div>
            <button class="int-upgrade-btn" @click="goToPro">Upgrade</button>
          </template>
        </div>
      </div>
    </div>

    <!-- Empty state ────────────────────────────────────────────────────────── -->
    <div class="int-empty" v-else>
      <span class="dashicons dashicons-search int-empty__icon"></span>
      <p class="int-empty__title">No integrations found</p>
      <p class="int-empty__sub">Try a different search term.</p>
      <button class="int-btn int-btn--ghost" @click="search = ''">Clear Search</button>
    </div>

  </div>
</template>

<script>
export default {
  name: 'integration',
  data() {
    return {
      search:       '',
      integrations: [],
      admin_url:    window.contactum.admin_url,
      is_pro:       window.contactum.is_pro,
    };
  },
  computed: {
    filteredAddons() {
      const entries = Object.entries(this.integrations).map(([key, val]) => ({
        path: key,
        ...val,
      }));
      if (!this.search) return entries;
      const q = this.search.toLowerCase();
      return entries.filter(i => i.name.toLowerCase().includes(q));
    },
    activeCount() {
      return this.filteredAddons.filter(i => i.enable).length;
    },
  },
  mounted() {
    if (this.is_pro) {
      this.getIntegration();
    } else {
      this.integrations = window.contactum.modules;
    }
  },
  methods: {
    goToPro() {
      window.open('https://wpcontactum.com/', '_blank');
    },

    getIntegration() {
      jQuery.ajax({
        url:     window.contactum.ajaxurl,
        type:    'GET',
        data:    { action: 'contactum_get_modules', nonce: window.contactum.nonce },
        success: (res) => {
          if (res.success) this.integrations = res.data.all;
        },
      });
    },

    toggleState(integration, value, index) {
      jQuery.ajax({
        url:  window.contactum.ajaxurl,
        type: 'POST',
        data: {
          action: 'contactum_toggle_modules',
          module: integration.path,
          nonce:  window.contactum.nonce,
          type:   value ? 'activate' : 'deactivate',
        },
        success: (res) => {
          if (res.success) {
            const isActive = res.data === 'Activated' ? 1 : 0;
            this.$set(this.integrations[integration.path], 'enable', isActive);
          }
        },
      });
    },

    toggleModule(module, state) {
      jQuery.ajax({
        url:      window.contactum.ajaxurl,
        type:     'POST',
        data:     { action: 'contactum_toggle_all_modules', type: state, nonce: window.contactum.nonce },
        complete: () => window.location.reload(true),
      });
    },
  },
};
</script>

<style scoped lang="scss">

/* ── Page ────────────────────────────────────────────── */
.int-page {
  padding: 24px;
  background: #f8f9fa;
  min-height: 100vh;
}

/* ── Header ──────────────────────────────────────────── */
.int-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.int-header__left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.int-header__icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: linear-gradient(135deg, #409eff 0%, #1a7efb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  .dashicons {
    font-size: 22px;
    width: 22px;
    height: 22px;
    color: #fff;
  }
}
.int-header__title {
  margin: 0 0 2px;
  font-size: 22px;
  font-weight: 600;
  color: #1e1f21;
  line-height: 1.3;
}
.int-header__sub {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
}
.int-header__badges {
  display: flex;
  gap: 8px;
}
.int-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  background: #f3f4f6;
  color: #6b7280;

  .dashicons { font-size: 13px; width: 13px; height: 13px; }

  &--green {
    background: #ecfdf5;
    color: #059669;
  }
}

/* ── Toolbar ─────────────────────────────────────────── */
.int-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.int-toolbar__actions {
  display: flex;
  gap: 8px;
}

/* Buttons */
.int-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
  font-weight: 500;
  padding: 7px 14px;
  border-radius: 7px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: background .15s, border-color .15s, color .15s;
  line-height: 1;

  .dashicons { font-size: 14px; width: 14px; height: 14px; }

  &--primary {
    background: #409eff;
    color: #fff;
    border-color: #409eff;
    &:hover { background: #1a7efb; border-color: #1a7efb; }
  }
  &--ghost {
    background: #fff;
    color: #374151;
    border-color: #d1d5db;
    &:hover { border-color: #9ca3af; color: #1e1f21; }
  }
}

/* Search */
.int-search {
  position: relative;
  display: flex;
  align-items: center;
  min-width: 260px;
}
.int-search__icon {
  position: absolute;
  left: 10px;
  font-size: 16px;
  width: 16px;
  height: 16px;
  color: #9ca3af;
  pointer-events: none;
}
.int-search__input {
  width: 100%;
  padding: 8px 32px 8px 34px;
  font-size: 13px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  outline: none;
  color: #374151;
  background: #fff;
  transition: border-color .15s, box-shadow .15s;
  line-height: 1;

  &:focus {
    border-color: #409eff;
    box-shadow: 0 0 0 3px rgba(64, 158, 255, .12);
  }
  &::placeholder { color: #9ca3af; }
}
.int-search__clear {
  position: absolute;
  right: 8px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 2px;
  color: #9ca3af;
  line-height: 1;
  &:hover { color: #374151; }
  .dashicons { font-size: 14px; width: 14px; height: 14px; }
}

/* ── Grid ────────────────────────────────────────────── */
.int-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

/* ── Card ────────────────────────────────────────────── */
.int-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-top: 3px solid #e5e7eb;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  transition: box-shadow .18s, border-top-color .18s, transform .18s;

  &:hover {
    box-shadow: 0 4px 16px rgba(30, 31, 33, .1);
    transform: translateY(-2px);
  }

  &--active {
    border-top-color: #409eff;
    box-shadow: 0 2px 10px rgba(64, 158, 255, .12);
  }
}

.int-card__body {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  padding: 18px 18px 14px;
  flex: 1;
}
.int-card__thumb {
  width: 42px;
  height: 42px;
  border-radius: 8px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}
.int-card__img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}
.int-card__name {
  margin: 0 0 5px;
  font-size: 14px;
  font-weight: 600;
  color: #1e1f21;
  line-height: 1.3;
}
.int-card__desc {
  margin: 0;
  font-size: 12.5px;
  color: #6b7280;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Card footer */
.int-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 18px;
  border-top: 1px solid #f0f0f0;
  gap: 10px;
}
.int-card__footer-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Status label */
.int-status {
  font-size: 12px;
  font-weight: 600;

  &--on  { color: #059669; }
  &--off { color: #9ca3af; }
}

/* Settings link */
.int-settings-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  font-weight: 500;
  color: #409eff;
  text-decoration: none;
  padding: 4px 10px;
  border: 1px solid #c6e2ff;
  border-radius: 6px;
  background: #ecf5ff;
  transition: background .12s, border-color .12s;

  .dashicons { font-size: 13px; width: 13px; height: 13px; }

  &:hover {
    background: #d9ecff;
    border-color: #a0cfff;
    color: #1a7efb;
  }
}

/* Pro tag */
.int-pro-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  color: #9ca3af;

  .dashicons { font-size: 12px; width: 12px; height: 12px; }
}

/* Upgrade button */
.int-upgrade-btn {
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  background: #fff;
  color: #374151;
  cursor: pointer;
  transition: background .12s, border-color .12s, color .12s;

  &:hover {
    background: #409eff;
    border-color: #409eff;
    color: #fff;
  }
}

/* ── Empty state ─────────────────────────────────────── */
.int-empty {
  text-align: center;
  padding: 64px 24px;
}
.int-empty__icon {
  font-size: 48px !important;
  width: 48px !important;
  height: 48px !important;
  color: #d1d5db;
  margin-bottom: 12px;
}
.int-empty__title {
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 6px;
}
.int-empty__sub {
  font-size: 13px;
  color: #9ca3af;
  margin: 0 0 16px;
}
</style>
