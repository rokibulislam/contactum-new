<template>
<div class="ctm-abandonment-settings">

    <div v-if="loading" class="ctm-abandonment-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-abandonment-settings__header">
            <span class="ctm-abandonment-settings__icon dashicons dashicons-chart-line"></span>
            <div>
                <h2 class="ctm-abandonment-settings__title">Form Abandonment Tracking</h2>
                <p class="ctm-abandonment-settings__desc">
                    Detects when a visitor starts filling a form but leaves without submitting.
                    Abandonment events are recorded automatically — no configuration required for tracking.
                    Enable follow-up emails below to re-engage users who provided their email address.
                </p>
            </div>
        </div>

        <!-- How it works ─────────────────────────────────────────── -->
        <div class="ctm-abandonment-settings__info-box">
            <p class="ctm-abandonment-settings__info-title">How tracking works</p>
            <ul class="ctm-abandonment-settings__info-list">
                <li>A JavaScript listener fires when the user interacts with any form field.</li>
                <li>If the user navigates away without submitting, the event is sent via <code>sendBeacon</code>.</li>
                <li>The email address is only captured (and stored) when follow-up emails are enabled.</li>
                <li>Abandonment rate appears on the <strong>Analytics</strong> dashboard.</li>
            </ul>
        </div>

        <!-- Follow-up email settings ────────────────────────────── -->
        <div class="ctm-abandonment-settings__section-title">
            <el-switch v-model="settings.followup_enabled" />
            <span>Enable follow-up emails</span>
        </div>

        <el-form
            v-if="settings.followup_enabled"
            label-position="top"
            class="ctm-abandonment-settings__form"
        >

            <el-form-item label="Delay (minutes)">
                <el-input-number
                    v-model="settings.followup_delay"
                    :min="1"
                    :max="10080"
                    style="width:160px"
                />
                <p class="ctm-abandonment-settings__help">
                    How long to wait after abandonment before sending the email. Minimum 1 minute.
                </p>
            </el-form-item>

            <el-row :gutter="16">
                <el-col :span="12">
                    <el-form-item label="From Name">
                        <el-input v-model="settings.followup_from_name" placeholder="Your site name" />
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="From Email">
                        <el-input v-model="settings.followup_from_email" placeholder="noreply@yourdomain.com" />
                    </el-form-item>
                </el-col>
            </el-row>

            <el-form-item label="Subject">
                <el-input
                    v-model="settings.followup_subject"
                    placeholder="Did you forget to finish?"
                />
            </el-form-item>

            <el-form-item label="Message Body">
                <el-input
                    v-model="settings.followup_body"
                    type="textarea"
                    :rows="6"
                    placeholder="Hi,&#10;&#10;You started filling out a form on our site but didn't finish…"
                />
                <p class="ctm-abandonment-settings__help">
                    Plain text. The email is sent only when the visitor provided an email address before leaving.
                </p>
            </el-form-item>

        </el-form>

        <div v-if="!settings.followup_enabled" class="ctm-abandonment-settings__disabled-note">
            Follow-up emails are disabled. Tracking is still active — abandonment data will appear in Analytics.
        </div>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-abandonment-settings__actions">
            <el-button type="primary" :loading="saving" @click="save">Save Settings</el-button>
        </div>

    </template>
</div>
</template>

<script>
export default {
    name: 'AbandonmentSettings',

    props: {
        setting_key: { type: String, default: 'abandonment' },
    },

    data() {
        return {
            loading: true,
            saving:  false,
            settings: {
                followup_enabled:    false,
                followup_delay:      60,
                followup_from_name:  '',
                followup_from_email: '',
                followup_subject:    '',
                followup_body:       '',
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
                    action:      'contactum_get_abandonment_settings',
                    _ajax_nonce: window.contactum.nonce,
                },
                (res) => {
                    this.loading = false;
                    if (res.success && res.data) {
                        this.settings = { ...this.settings, ...res.data };
                        this.settings.followup_enabled = !!this.settings.followup_enabled;
                        this.settings.followup_delay   = parseInt(this.settings.followup_delay, 10) || 60;
                    }
                }
            );
        },

        save() {
            this.saving = true;
            jQuery.post(
                window.contactum.ajaxurl,
                {
                    action:      'contactum_save_abandonment_settings',
                    _ajax_nonce: window.contactum.nonce,
                    settings:    JSON.stringify(this.settings),
                },
                (res) => {
                    this.saving = false;
                    if (res.success) {
                        this.$notify({ title: 'Saved', message: 'Abandonment settings saved.', type: 'success' });
                    } else {
                        this.$notify({ title: 'Error', message: 'Could not save settings.', type: 'error' });
                    }
                }
            );
        },
    },
};
</script>

<style lang="scss">
.ctm-abandonment-settings {
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
        color: #f59e0b;
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

    &__info-box {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 24px;
    }

    &__info-title {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 600;
        color: #92400e;
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

            code {
                background: #fef3c7;
                padding: 1px 4px;
                border-radius: 3px;
                font-size: 12px;
            }
        }
    }

    &__section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 20px;
    }

    &__form {
        margin-bottom: 16px;
    }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__disabled-note {
        font-size: 13px;
        color: #94a3b8;
        font-style: italic;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    &__actions {
        display: flex;
        gap: 10px;
    }
}
</style>
