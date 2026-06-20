<template>
<div class="ctm-webhook-settings">

    <div v-if="loading" class="ctm-webhook-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-webhook-settings__header">
            <span class="ctm-webhook-settings__icon dashicons dashicons-rest-api"></span>
            <div>
                <h2 class="ctm-webhook-settings__title">Webhook / Zapier</h2>
                <p class="ctm-webhook-settings__desc">
                    POST form submissions to any URL — Zapier, Make, n8n, or your own endpoint.
                    Configure a default URL here; each form can also set its own URL.
                </p>
            </div>
        </div>

        <!-- Fields ─────────────────────────────────────────────── -->
        <el-form label-position="top" class="ctm-webhook-settings__form">

            <el-form-item label="Default Webhook URL">
                <el-input
                    v-model="settings.default_url"
                    placeholder="https://hooks.zapier.com/hooks/catch/…"
                    clearable
                />
                <p class="ctm-webhook-settings__help">
                    Optional. Used as a fallback when a form has no URL configured.
                </p>
            </el-form-item>

            <el-form-item label="Default Request Format">
                <el-select v-model="settings.request_format" style="width:220px">
                    <el-option label="JSON (recommended)" value="json" />
                    <el-option label="Form Encoded"       value="form" />
                </el-select>
            </el-form-item>

        </el-form>

        <!-- Payload preview ─────────────────────────────────────── -->
        <div class="ctm-webhook-settings__payload-preview">
            <p class="ctm-webhook-settings__payload-label">Example payload sent on submission:</p>
            <pre class="ctm-webhook-settings__payload-code">{{ examplePayload }}</pre>
        </div>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-webhook-settings__actions">

            <el-button
                type="primary"
                :loading="saving"
                @click="save"
            >Save Settings</el-button>

            <el-button
                :loading="testing"
                :disabled="!settings.default_url"
                @click="sendTest"
            >Send Test</el-button>

        </div>

        <!-- Test result ─────────────────────────────────────────── -->
        <div v-if="testResult" :class="['ctm-webhook-settings__test-result', testResult.ok ? 'is-success' : 'is-error']">
            <i :class="testResult.ok ? 'el-icon-circle-check' : 'el-icon-circle-close'"></i>
            {{ testResult.message }}
        </div>

    </template>
</div>
</template>

<script>
export default {
    name: 'WebhookSettings',

    props: {
        setting_key: { type: String, default: 'webhook' },
    },

    data() {
        return {
            loading:    true,
            saving:     false,
            testing:    false,
            testResult: null,
            settings: {
                default_url:    '',
                request_format: 'json',
                status:         false,
            },
        };
    },

    computed: {
        examplePayload() {
            return JSON.stringify({
                entry_id:     42,
                form_id:      7,
                form_name:    'Contact Us',
                submitted_at: '2026-05-22 10:00:00',
                fields: {
                    Name:    'Jane Doe',
                    Email:   'jane@example.com',
                    Message: 'Hello!',
                },
            }, null, 2);
        },
    },

    watch: {
        setting_key() {
            this.load();
        },
    },

    mounted() {
        this.load();
    },

    methods: {
        load() {
            this.loading = true;
            jQuery.post(
                window.contactum.ajaxurl,
                {
                    action:       'contactum_get_admin_integrations',
                    _ajax_nonce:  window.contactum.nonce,
                    settings_key: 'webhook',
                },
                (res) => {
                    this.loading = false;
                    if (res.success) {
                        const val = res.data.value || {};
                        this.settings = {
                            default_url:    val.default_url    || '',
                            request_format: val.request_format || 'json',
                            status:         !!val.status,
                        };
                    }
                }
            );
        },

        save() {
            this.saving     = true;
            this.testResult = null;
            jQuery.post(
                window.contactum.ajaxurl,
                {
                    action:       'contactum_save_global_integrations',
                    _ajax_nonce:  window.contactum.nonce,
                    settings_key: 'webhook',
                    integration:  this.settings,
                },
                (res) => {
                    this.saving = false;
                    if (res.success) {
                        this.$notify({ title: 'Saved', message: 'Webhook settings saved.', type: 'success' });
                        if (res.data) {
                            this.settings.status = !!res.data.status;
                        }
                    } else {
                        this.$notify({ title: 'Error', message: 'Could not save settings.', type: 'error' });
                    }
                }
            );
        },

        sendTest() {
            if (!this.settings.default_url) return;
            this.testing    = true;
            this.testResult = null;
            jQuery.post(
                window.contactum.ajaxurl,
                {
                    action:      'contactum_webhook_send_test',
                    _ajax_nonce: window.contactum.nonce,
                    url:         this.settings.default_url,
                },
                (res) => {
                    this.testing    = false;
                    this.testResult = {
                        ok:      !!res.success,
                        message: res.success ? res.data : (res.data || 'Test failed.'),
                    };
                }
            );
        },
    },
};
</script>

<style lang="scss">
.ctm-webhook-settings {
    padding: 28px 32px;
    max-width: 680px;

    &__loading {
        color: #94a3b8;
        font-size: 14px;
    }

    &__header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 28px;
    }

    &__icon {
        font-size: 36px;
        color: #1a7efb;
        flex-shrink: 0;
        margin-top: 2px;
    }

    &__title {
        margin: 0 0 6px;
        font-size: 18px;
        font-weight: 600;
        color: #1e1f21;
    }

    &__desc {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
    }

    &__form {
        margin-bottom: 24px;
    }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__payload-preview {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 24px;
    }

    &__payload-label {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    &__payload-code {
        margin: 0;
        font-size: 12px;
        line-height: 1.6;
        color: #374151;
        white-space: pre-wrap;
        word-break: break-word;
    }

    &__actions {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
    }

    &__test-result {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 6px;

        &.is-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        &.is-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        i { font-size: 16px; }
    }
}
</style>
