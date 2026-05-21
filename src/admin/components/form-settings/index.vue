<template>
  <div class="cfs-wrap">

    <!-- ── Sidebar ── -->
    <aside class="cfs-sidebar">
      <div class="cfs-sidebar-header" @click="sidebarOpen = !sidebarOpen">
        Settings
        <i :class="sidebarOpen ? 'el-icon-arrow-up' : 'el-icon-arrow-down'"></i>
      </div>
      <ul v-show="sidebarOpen" class="cfs-nav">
        <li v-for="sec in navSections" :key="sec.id">
          <a
            href="#"
            :class="{ active: activeSection === sec.id }"
            @click.prevent="goToSection(sec.id)"
          >{{ sec.label }}</a>
        </li>
      </ul>
    </aside>

    <!-- ── Main content ── -->
    <div class="cfs-content" ref="content" @scroll="onContentScroll">

      <!-- Confirmation Settings -->
      <section class="cfs-section" id="confirmation">
        <h2 class="cfs-section-title">Confirmation Settings</h2>

        <div class="cfs-field">
          <label class="cfs-label">
            Confirmation Type
            <el-tooltip content="Choose what happens after a successful submission" placement="top">
              <i class="el-icon-info cfs-info"></i>
            </el-tooltip>
          </label>
          <div class="cfs-radio-group">
            <label
              v-for="opt in redirects_to"
              :key="opt.value"
              class="cfs-radio-pill"
              :class="{ active: settings.redirect_to === opt.value }"
            >
              <input type="radio" v-model="settings.redirect_to" :value="opt.value" />
              <span class="cfs-radio-dot"></span>
              {{ opt.label }}
            </label>
          </div>
        </div>

        <div class="cfs-field" v-show="settings.redirect_to === 'same'">
          <label class="cfs-label">
            Message to show
            <el-tooltip content="This message is shown to the user after a successful submission" placement="top">
              <i class="el-icon-info cfs-info"></i>
            </el-tooltip>
          </label>
          <wp_editor
            :value="settings.message"
            @content-changed="val => updateSetting('message', val)"
          />
        </div>

        <div class="cfs-field" v-show="settings.redirect_to === 'page'">
          <label class="cfs-label">Page</label>
          <el-select v-model="settings.page_id" placeholder="Select a page">
            <el-option
              v-for="(page, index) in settings.pages"
              :key="index"
              :label="index"
              :value="page"
            ></el-option>
          </el-select>
        </div>

        <div class="cfs-field" v-show="settings.redirect_to === 'url'">
          <label class="cfs-label">Custom URL</label>
          <el-input type="url" v-model="settings.url" placeholder="https://example.com/thank-you" />
        </div>

        <div class="cfs-field">
          <label class="cfs-label">
            After Form Submission
            <el-tooltip content="Choose whether to hide or reset the form after submission" placement="top">
              <i class="el-icon-info cfs-info"></i>
            </el-tooltip>
          </label>
          <div class="cfs-radio-group">
            <label class="cfs-radio-pill" :class="{ active: settings.after_submit !== 'reset' }">
              <input type="radio" v-model="settings.after_submit" value="hide" />
              <span class="cfs-radio-dot"></span>
              Hide Form
            </label>
            <label class="cfs-radio-pill" :class="{ active: settings.after_submit === 'reset' }">
              <input type="radio" v-model="settings.after_submit" value="reset" />
              <span class="cfs-radio-dot"></span>
              Reset Form
            </label>
          </div>
        </div>

        <div class="cfs-field">
          <label class="cfs-label">Submit Button Text</label>
          <el-input type="text" v-model="settings.submit_text" style="max-width: 320px;" />
        </div>
      </section>

      <!-- Form Layout -->
      <section class="cfs-section" id="layout">
        <h2 class="cfs-section-title">Form Layout</h2>

        <div class="cfs-field">
          <label class="cfs-label">Label Position</label>
          <div class="cfs-radio-group">
            <label
              v-for="pos in label_positions"
              :key="pos.value"
              class="cfs-radio-pill"
              :class="{ active: settings.label_position === pos.value }"
            >
              <input type="radio" v-model="settings.label_position" :value="pos.value" />
              <span class="cfs-radio-dot"></span>
              {{ pos.label }}
            </label>
          </div>
          <p class="cfs-description">Where the labels of the form should display.</p>
        </div>

        <div class="cfs-field">
          <label class="cfs-label">Use Theme CSS</label>
          <div class="cfs-radio-group">
            <label class="cfs-radio-pill" :class="{ active: settings.use_theme_css === 'contactum-theme-style' }">
              <input type="radio" v-model="settings.use_theme_css" value="contactum-theme-style" />
              <span class="cfs-radio-dot"></span>
              Yes
            </label>
            <label class="cfs-radio-pill" :class="{ active: settings.use_theme_css === 'contactum-style' }">
              <input type="radio" v-model="settings.use_theme_css" value="contactum-style" />
              <span class="cfs-radio-dot"></span>
              No
            </label>
          </div>
          <p class="cfs-description">
            Selecting <strong>Yes</strong> will use your theme's style for form fields.
          </p>
        </div>
      </section>

      <!-- Scheduling & Restrictions -->
      <section class="cfs-section" id="scheduling">
        <h2 class="cfs-section-title">Scheduling &amp; Restrictions</h2>

        <div class="cfs-field">
          <label class="cfs-label">Schedule Form</label>
          <label class="cfs-toggle">
            <el-switch v-model="settings.schedule_form" />
            <span>Schedule form for a time period</span>
          </label>
          <p class="cfs-description">Restrict when the form accepts submissions.</p>
        </div>

        <template v-if="settings.schedule_form">
          <div class="cfs-field">
            <label class="cfs-label">Schedule Period</label>
            <div class="cfs-date-row">
              <span class="cfs-date-label">From</span>
              <el-date-picker v-model="settings.schedule_start" type="date" placeholder="Start date" />
              <span class="cfs-date-label">To</span>
              <el-date-picker v-model="settings.schedule_end" type="date" placeholder="End date" />
            </div>
          </div>

          <div class="cfs-field">
            <label class="cfs-label">Form Pending Message</label>
            <wp_editor
              :value="settings.sc_pending_message"
              @content-changed="val => updateSetting('sc_pending_message', val)"
            />
          </div>

          <div class="cfs-field">
            <label class="cfs-label">Form Expired Message</label>
            <wp_editor
              :value="settings.sc_expired_message"
              @content-changed="val => updateSetting('sc_expired_message', val)"
            />
          </div>
        </template>

        <div class="cfs-field">
          <label class="cfs-label">Require Login</label>
          <label class="cfs-toggle">
            <el-switch v-model="settings.require_login" />
            <span>Require user to be logged in to submit</span>
          </label>
        </div>

        <div class="cfs-field" v-if="settings.require_login">
          <label class="cfs-label">Require Login Message</label>
          <wp_editor
            :value="settings.req_login_message"
            @content-changed="val => updateSetting('req_login_message', val)"
          />
        </div>

        <div class="cfs-field">
          <label class="cfs-label">Limit Entries</label>
          <label class="cfs-toggle">
            <el-switch v-model="settings.limit_entries" />
            <span>Enable form entry limit</span>
          </label>
          <p class="cfs-description">Limit the number of entries allowed for this form.</p>
        </div>

        <template v-if="settings.limit_entries">
          <div class="cfs-field">
            <label class="cfs-label">Number of Entries</label>
            <el-input type="number" v-model="settings.limit_number" style="max-width: 180px;" />
          </div>

          <div class="cfs-field">
            <label class="cfs-label">Limit Reached Message</label>
            <wp_editor
              :value="settings.limit_message"
              @content-changed="val => updateSetting('limit_message', val)"
            />
          </div>
        </template>
      </section>

      <!-- Custom CSS/JS -->
      <section class="cfs-section" id="custom-css-js">
        <h2 class="cfs-section-title">Custom CSS &amp; JS</h2>

        <div class="cfs-field">
          <label class="cfs-label">Custom CSS</label>
          <p class="cfs-description">
            Applies only to this form. Use <code>#contactum_form_{{ id }}</code> as a selector prefix.
            Do not include <code>&lt;style&gt;</code> tags.
          </p>
          <AceCSSEditor mode="css" v-model="settings.custom_css" />
        </div>

        <div class="cfs-field">
          <label class="cfs-label">Custom JS</label>
          <p class="cfs-description">
            Runs after the form initialises. <code>$form</code> is the jQuery DOM object of the form.
            Do not include <code>&lt;script&gt;</code> tags.
          </p>
          <AceJSEditor mode="javascript" v-model="settings.custom_js" />
        </div>
      </section>

    </div>
  </div>
</template>

<script>
import wp_editor from '../common/wp-editor.vue'
import AceJSEditor from "../common/ace-editor-js.vue";
import AceCSSEditor from "../common/ace-editor-css.vue";

import "ace-builds/src-noconflict/mode-javascript";
import "ace-builds/src-noconflict/theme-monokai";
import "ace-builds/src-noconflict/mode-css";

export default {
  name: "form_settings",
  props: ["id"],
  components: { wp_editor, AceJSEditor, AceCSSEditor },

  data() {
    return {
      sidebarOpen: true,
      activeSection: 'confirmation',

      navSections: [
        { id: 'confirmation', label: 'Confirmation Settings' },
        { id: 'layout',       label: 'Form Layout' },
        { id: 'scheduling',   label: 'Scheduling & Restrictions' },
        { id: 'custom-css-js', label: 'Custom CSS/JS' },
      ],

      label_positions: [
        { value: 'above',  label: 'Above Element' },
        { value: 'left',   label: 'Left of Element' },
        { value: 'right',  label: 'Right of Element' },
        { value: 'hidden', label: 'Hidden' },
      ],

      redirects_to: [
        { value: 'same', label: 'Same Page' },
        { value: 'page', label: 'To a Page' },
        { value: 'url',  label: 'To a Custom URL' },
      ],
    };
  },

  computed: {
    settings() {
      return this.$store.getters.settings;
    },
  },

  methods: {
    updateSetting(key, value) {
      this.$store.dispatch('set_form_settings', { ...this.settings, [key]: value });
    },

    goToSection(id) {
      this.activeSection = id;
      const el = document.getElementById(id);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    },

    onContentScroll() {
      const sections = this.navSections.map(s => document.getElementById(s.id)).filter(Boolean);
      const contentEl = this.$refs.content;
      const scrollTop = contentEl.scrollTop + 40;

      let current = this.navSections[0].id;
      for (const el of sections) {
        if (el.offsetTop <= scrollTop) {
          current = el.id;
        }
      }
      this.activeSection = current;
    },
  },

  watch: {
    settings: {
      deep: true,
      handler(value) {
        this.$store.dispatch('set_form_settings', value);
      },
    },
  },
};
</script>

<style lang="scss">
/* ── Layout ──────────────────────────────────── */
.cfs-wrap {
  display: flex;
  align-items: flex-start;
  gap: 24px;
  min-height: calc(100vh - 170px);
}

.cfs-content {
  flex: 1;
  overflow-y: auto;
  max-height: calc(100vh - 170px);
  padding-right: 4px;
}

/* ── Sidebar ─────────────────────────────────── */
.cfs-sidebar {
  width: 230px;
  flex-shrink: 0;
  position: sticky;
  top: 0;
}

.cfs-sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #3c3e42;
  color: #fff;
  padding: 12px 16px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
}

.cfs-nav {
  margin: 0;
  padding: 6px 0;
  list-style: none;

  li a {
    display: block;
    padding: 9px 16px;
    font-size: 14px;
    color: #374151;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.15s, color 0.15s;

    &:hover {
      background: #f3f4f6;
      color: var(--primary);
    }

    &.active {
      color: var(--primary);
      font-weight: 600;
      background: rgba(99, 102, 241, 0.06);
    }
  }
}

/* ── Sections ────────────────────────────────── */
.cfs-section {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 24px;
  margin-bottom: 20px;
}

.cfs-section-title {
  margin: 0 0 20px;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  padding-bottom: 14px;
  border-bottom: 1px solid #f3f4f6;
}

/* ── Field wrapper ───────────────────────────── */
.cfs-field {
  margin-bottom: 22px;

  &:last-child {
    margin-bottom: 0;
  }
}

.cfs-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.cfs-info {
  font-size: 14px;
  color: #9ca3af;
  cursor: default;

  &:hover {
    color: var(--primary);
  }
}

.cfs-description {
  margin: 6px 0 0;
  font-size: 12px;
  color: #6b7280;
  line-height: 1.5;
}

/* ── Radio pill buttons ──────────────────────── */
.cfs-radio-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.cfs-radio-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 16px;
  border: 1.5px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  background: #fff;
  transition: border-color 0.15s, color 0.15s;
  user-select: none;

  input[type="radio"] {
    display: none;
  }

  .cfs-radio-dot {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 2px solid #d1d5db;
    flex-shrink: 0;
    position: relative;
    transition: border-color 0.15s, background 0.15s;
  }

  &.active {
    border-color: var(--primary);
    color: var(--primary);

    .cfs-radio-dot {
      border-color: var(--primary);
      background: var(--primary);

      &::after {
        content: '';
        position: absolute;
        width: 5px;
        height: 5px;
        background: #fff;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }
    }
  }

  &:hover:not(.active) {
    border-color: #9ca3af;
  }
}

/* ── Toggle row ──────────────────────────────── */
.cfs-toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
}

/* ── Date row ────────────────────────────────── */
.cfs-date-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.cfs-date-label {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}
</style>
