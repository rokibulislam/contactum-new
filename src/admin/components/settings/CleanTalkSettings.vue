<template>
<div class="ctm-cleantalk-settings">

    <div v-if="loading" class="ctm-cleantalk-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-cleantalk-settings__header">
            <span class="ctm-cleantalk-settings__icon dashicons dashicons-shield-alt"></span>
            <div>
                <h2 class="ctm-cleantalk-settings__title">CleanTalk Spam Protection</h2>
                <p class="ctm-cleantalk-settings__desc">
                    Cloud-based spam filter that checks every form submission against the CleanTalk database.
                    No CAPTCHA needed — invisible to real users. Get a free access key at
                    <a href="https://cleantalk.org" target="_blank" rel="noopener noreferrer">cleantalk.org</a>.
                </p>
            </div>
        </div>

        <!-- Status badge ─────────────────────────────────────────── -->
        <div v-if="settings.access_key" class="ctm-cleantalk-settings__status-row">
            <span :class="['ctm-cleantalk-settings__badge', settings.status ? 'is-active' : 'is-inactive']">
                <i :class="settings.status ? 'el-icon-circle-check' : 'el-icon-warning-outline'"></i>
                {{ settings.status ? 'Active — API key is valid' : 'Inactive — API key not verified' }}
            </span>
        </div>

        <!-- Fields ─────────────────────────────────────────────── -->
        <el-form label-position="top" class="ctm-cleantalk-settings__form">

            <el-form-item label="Access Key">
                <div class="ctm-cleantalk-settings__key-row">
                    <el-input
                        v-model="settings.access_key"
                        placeholder="Your CleanTalk access key"
                        clearable
                        style="flex:1"
                    />
                    <el-button
                        :loading="verifying"
                        :disabled="!settings.access_key"
                        @click="verifyKey"
                    >Verify Key</el-button>
                </div>
                <p class="ctm-cleantalk-settings__help">
                    Get a free key at cleantalk.org. The key is validated when you save settings.
                </p>
            </el-form-item>

        </el-form>

        <!-- Verify result ───────────────────────────────────────── -->
        <div v-if="verifyResult" :class="['ctm-cleantalk-settings__verify-result', verifyResult.ok ? 'is-success' : 'is-error']">
            <i :class="verifyResult.ok ? 'el-icon-circle-check' : 'el-icon-circle-close'"></i>
            {{ verifyResult.message }}
        </div>

        <!-- How it works ─────────────────────────────────────────── -->
        <div class="ctm-cleantalk-settings__info-box">
            <p class="ctm-cleantalk-settings__info-title">How it works</p>
            <ul class="ctm-cleantalk-settings__info-list">
                <li>Each form submission is checked against the CleanTalk spam database in real time.</li>
                <li>Spam submissions are blocked before the entry is saved.</li>
                <li>If the CleanTalk API is unreachable, submissions are allowed through (fail-open).</li>
                <li>Enable per-form in <strong>Form Settings → Integrations → CleanTalk</strong>.</li>
            </ul>
        </div>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-cleantalk-settings__actions">
            <el-button
                type="primary"
                :loading="saving"
                @click="save"
            >Save Settings</el-button>
        </div>

    </template>
</div>
</template>

<script>
export default {
    name: 'CleanTalkSettings',

    props: {
        setting_key: { type: String, default: 'cleantalk' },
    },

    data() {
        return {
            loading:      true,
            saving:       false,
            verifying:    false,
            verifyResult: null,
            settings: {
                access_key: '',
                status:     false,
            },
        };
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
                    settings_key: 'cleantalk',
                },
                (res) => {
                    this.loading = false;
                    if (res.success) {
                        const val = res.data.value || {};
                        this.settings = {
                            access_key: val.access_key || '',
                            status:     !!val.status,
                        };
                    }
                }
            );
        },

        save() {
            this.saving      = true;
            this.verifyResult = null;
            jQuery.post(
                window.contactum.ajaxurl,
                {
                    action:       'contactum_save_global_integrations',
                    _ajax_nonce:  window.contactum.nonce,
                    settings_key: 'cleantalk',
                    integration:  this.settings,
                },
                (res) => {
                    this.saving = false;
                    if (res.success) {
                        if (res.data) {
                            this.settings.status = !!res.data.status;
                        }
                        const statusMsg = this.settings.status
                            ? 'Settings saved. API key is valid.'
                            : 'Settings saved. API key could not be verified — check your key.';
                        this.$notify({ title: 'Saved', message: statusMsg, type: this.settings.status ? 'success' : 'warning' });
                    } else {
                        this.$notify({ title: 'Error', message: 'Could not save settings.', type: 'error' });
                    }
                }
            );
        },

        verifyKey() {
            if (!this.settings.access_key) return;
            this.verifying    = true;
            this.verifyResult = null;
            jQuery.post(
                window.contactum.ajaxurl,
                {
                    action:      'contactum_cleantalk_verify_key',
                    _ajax_nonce: window.contactum.nonce,
                    access_key:  this.settings.access_key,
                },
                (res) => {
                    this.verifying    = false;
                    this.verifyResult = {
                        ok:      !!res.success,
                        message: res.success ? res.data : (res.data || 'Verification failed.'),
                    };
                    if (res.success) {
                        this.settings.status = true;
                    }
                }
            );
        },
    },
};
</script>

<style lang="scss">
.ctm-cleantalk-settings {
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
        margin-bottom: 24px;
    }

    &__icon {
        font-size: 36px;
        color: #16a34a;
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

        a {
            color: #1a7efb;
            text-decoration: none;
            &:hover { text-decoration: underline; }
        }
    }

    &__status-row {
        margin-bottom: 20px;
    }

    &__badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;

        &.is-active {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        &.is-inactive {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
    }

    &__form {
        margin-bottom: 16px;
    }

    &__key-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__verify-result {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 6px;
        margin-bottom: 20px;

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

    &__info-box {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 24px;
    }

    &__info-title {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 600;
        color: #0369a1;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    &__info-list {
        margin: 0;
        padding-left: 18px;

        li {
            font-size: 13px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 4px;
        }
    }

    &__actions {
        display: flex;
        gap: 10px;
    }
}
</style>
