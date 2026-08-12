<template>
  <div class="csup-page">

    <!-- Header ─────────────────────────────────────────────────────────── -->
    <div class="csup-header">
      <div class="csup-header__icon">
        <span class="dashicons dashicons-sos"></span>
      </div>
      <div>
        <h1 class="csup-header__title">Help &amp; Support</h1>
        <p class="csup-header__sub">Find answers, check your environment, or get in touch</p>
      </div>
    </div>

    <!-- Quick links ────────────────────────────────────────────────────── -->
    <div class="csup-links">
      <a class="csup-link-card" :href="mailtoHref">
        <span class="csup-link-card__icon dashicons dashicons-email-alt"></span>
        <div>
          <strong>Contact Support</strong>
          <span>Email the site administrator</span>
        </div>
      </a>
      <a class="csup-link-card" target="_blank" href="https://wpcontactum.com/docs/">
        <span class="csup-link-card__icon dashicons dashicons-book"></span>
        <div>
          <strong>Documentation</strong>
          <span>Guides for building forms &amp; payments</span>
        </div>
      </a>
      <a class="csup-link-card"  target="_blank" href="https://www.youtube.com/@wpspicy-hh8tn">
        <span class="csup-link-card__icon dashicons dashicons-video-alt3"></span>
        <div>
          <strong>Video Tutorials</strong>
          <span>Step-by-step walkthroughs</span>
        </div>
      </a>
    </div>

    <div class="csup-columns">

      <!-- FAQ ───────────────────────────────────────────────────────────── -->
      <div class="csup-card csup-card--faq">
        <h2 class="csup-card__title">Frequently Asked Questions</h2>

        <el-collapse v-model="openFaq" accordion>
          <el-collapse-item
            v-for="(item, index) in faqs"
            :key="index"
            :name="index"
            :title="item.q"
          >
            <p class="csup-faq__answer">{{ item.a }}</p>
          </el-collapse-item>
        </el-collapse>
      </div>

      <!-- System info ──────────────────────────────────────────────────── -->
      <div class="csup-card csup-card--info">
        <div class="csup-card__head">
          <h2 class="csup-card__title">System Information</h2>
          <el-tooltip content="Copy to clipboard" placement="top">
            <button class="csup-copy-btn" @click="copyInfo">
              <span class="dashicons dashicons-clipboard"></span>
            </button>
          </el-tooltip>
        </div>
        <p class="csup-hint">Include this when contacting support.</p>

        <table class="csup-info-table">
          <tbody>
            <tr v-for="row in infoRows" :key="row.label">
              <td class="csup-info-table__label">{{ row.label }}</td>
              <td class="csup-info-table__value">{{ row.value }}</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

  </div>
</template>

<script>
const cpm = window.contactum || {};

export default {
  name: 'Support',

  data() {
    return {
      openFaq: 0,
      faqs: [
        {
          q: 'A field I added disappeared from the form builder — what happened?',
          a: 'If a field type comes from a module or the Pro add-on and that module gets deactivated, the field stays on the form (its data is preserved) but is shown as unavailable in the builder and left out of the live form until the module is reactivated.',
        },
        {
          q: 'How do I stop spam submissions without adding a CAPTCHA?',
          a: 'Every form already includes an invisible honeypot field and a minimum-time-to-submit check — both run automatically and reject obvious bot submissions before they\'re saved as entries.',
        },
        {
          q: 'Where do I enable a payment gateway?',
          a: 'Go to Contactum → Payment Settings, enable the gateway you want, and fill in its API credentials. A gateway only appears on the frontend once it\'s enabled and its credentials are complete.',
        },
        {
          q: 'How do I export my form entries?',
          a: 'Open a form\'s Entries screen and use the export action to download a CSV of all entries, optionally filtered.',
        },
        {
          q: 'Why isn\'t my reCAPTCHA / hCaptcha / Turnstile showing up?',
          a: 'Double-check the site key and secret key are saved under Settings, and that the corresponding field has been added to the form — the widget only renders when both are present.',
        },
      ],
    };
  },

  computed: {
    mailtoHref() {
      return 'mailto:' + (cpm.admin_email || '');
    },

    infoRows() {
      return [
        { label: 'Contactum Version',     value: cpm.contactum_version || '—' },
        { label: 'Contactum Pro Version', value: cpm.contactum_pro || 'Not active' },
        { label: 'WordPress Version',     value: cpm.wp_version || '—' },
        { label: 'PHP Version',           value: cpm.php_version || '—' },
        { label: 'MySQL Version',         value: cpm.mysql_version || '—' },
        { label: 'PHP Memory Limit',      value: cpm.memory_limit || '—' },
        { label: 'Max Upload Size',       value: cpm.max_upload_size || '—' },
        { label: 'Active Theme',          value: cpm.theme || '—' },
        { label: 'Multisite',             value: cpm.is_multisite ? 'Yes' : 'No' },
      ];
    },
  },

  methods: {
    copyInfo() {
      const text = this.infoRows.map(r => r.label + ': ' + r.value).join('\n');

      navigator.clipboard.writeText(text).then(() => {
        this.$message.success('System info copied to clipboard');
      }).catch(() => {
        this.$message.error('Could not copy — please copy manually');
      });
    },
  },
};
</script>

<style scoped>
.csup-page {
  padding: 24px;
  background: #f8f9fa;
  min-height: 100vh;
}

.csup-header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 24px;
}
.csup-header__icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: linear-gradient(135deg, #409eff 0%, #1a7efb 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.csup-header__icon .dashicons {
  font-size: 22px;
  width: 22px;
  height: 22px;
  color: #fff;
}
.csup-header__title {
  margin: 0 0 2px;
  font-size: 22px;
  font-weight: 600;
  color: #1e1f21;
}
.csup-header__sub {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
}

.csup-links {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.csup-link-card {
  flex: 1;
  min-width: 220px;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 16px 18px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, .06);
  text-decoration: none;
  transition: box-shadow .15s, border-color .15s;
}
.csup-link-card:hover {
  border-color: #6366f1;
  box-shadow: 0 4px 12px rgba(99,102,241,.12);
}
.csup-link-card__icon {
  font-size: 20px;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: #eef2ff;
  color: #6366f1;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.csup-link-card strong {
  display: block;
  font-size: 14px;
  color: #1e1f21;
}
.csup-link-card span {
  display: block;
  font-size: 12px;
  color: #9ca3af;
  margin-top: 2px;
}

.csup-columns {
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 16px;
  align-items: start;
}
@media (max-width: 900px) {
  .csup-columns { grid-template-columns: 1fr; }
}

.csup-card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(30, 31, 33, .08);
  padding: 20px 22px;
}
.csup-card__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.csup-card__title {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: #1e1f21;
}
.csup-hint {
  margin: 0 0 14px;
  font-size: 12.5px;
  color: #9ca3af;
}

.csup-faq__answer {
  margin: 0;
  font-size: 13px;
  line-height: 1.6;
  color: #4b5563;
}

.csup-copy-btn {
  background: #f3f4f6;
  border: none;
  border-radius: 6px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #6b7280;
  transition: background .15s, color .15s;
}
.csup-copy-btn:hover {
  background: #eef2ff;
  color: #6366f1;
}

.csup-info-table {
  width: 100%;
  border-collapse: collapse;
}
.csup-info-table tr:not(:last-child) td {
  border-bottom: 1px solid #f3f4f6;
}
.csup-info-table td {
  padding: 8px 0;
  font-size: 12.5px;
}
.csup-info-table__label {
  color: #9ca3af;
  white-space: nowrap;
}
.csup-info-table__value {
  text-align: right;
  color: #374151;
  font-weight: 500;
  word-break: break-word;
}
</style>
