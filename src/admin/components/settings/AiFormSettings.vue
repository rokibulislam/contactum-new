<template>
<div class="ctm-ai-settings">

    <div v-if="loading" class="ctm-ai-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-ai-settings__header">
            <span class="ctm-ai-settings__icon dashicons dashicons-superhero-alt"></span>
            <div>
                <h2 class="ctm-ai-settings__title">AI Form Generator</h2>
                <p class="ctm-ai-settings__desc">
                    Describe a form in plain English and Contactum builds it for you. Uses your own OpenAI API
                    key — requests go straight from your site to OpenAI, nothing passes through a third party.
                    Get a key at
                    <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer">platform.openai.com</a>.
                </p>
            </div>
        </div>

        <!-- Status badge ─────────────────────────────────────────── -->
        <div v-if="settings.api_key" class="ctm-ai-settings__status-row">
            <span :class="['ctm-ai-settings__badge', settings.status ? 'is-active' : 'is-inactive']">
                <i :class="settings.status ? 'el-icon-circle-check' : 'el-icon-warning-outline'"></i>
                {{ settings.status ? 'Active — API key is valid' : 'Inactive — API key not verified' }}
            </span>
        </div>

        <!-- Fields ─────────────────────────────────────────────── -->
        <el-form label-position="top" class="ctm-ai-settings__form">

            <el-form-item label="OpenAI API Key">
                <el-input
                    v-model="settings.api_key"
                    type="password"
                    show-password
                    placeholder="sk-…"
                    clearable
                />
                <p class="ctm-ai-settings__help">
                    The key is validated with OpenAI when you save, and stored only on your own site.
                </p>
            </el-form-item>

        </el-form>

        <!-- How it works ─────────────────────────────────────────── -->
        <div class="ctm-ai-settings__info-box">
            <p class="ctm-ai-settings__info-title">How it works</p>
            <ul class="ctm-ai-settings__info-list">
                <li>Once a key is saved, "Generate with AI" appears on the Add Form screen.</li>
                <li>Only field types Contactum already supports are used — an unrecognised suggestion is skipped rather than guessed at.</li>
                <li>Each admin is limited to 20 generations per day by default, since usage is billed to your own OpenAI account.</li>
            </ul>
        </div>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-ai-settings__actions">
            <el-button
                type="primary"
                :loading="saving"
                @click="save"
            >Save Settings</el-button>

            <el-button
                v-if="settings.api_key"
                :loading="clearing"
                @click="clearSettings"
            >Remove Key</el-button>
        </div>

    </template>
</div>
</template>

<script>
export default {
    name: 'AiFormSettings',

    props: {
        setting_key: { type: String, default: 'ai_form_generator' },
    },

    data() {
        return {
            loading:  true,
            saving:   false,
            clearing: false,
            settings: {
                api_key: '',
                status:  false,
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
            jQuery.post(contactum.ajaxurl, {
                action:       'contactum_get_global_settings',
                _ajax_nonce:  contactum.nonce,
                settings_key: 'ai_form_generator',
                key:          [ '_contactum_ai_form_generator_details' ],
            }, (response) => {
                this.loading  = false;
                const details = (response.data && response.data.settings && response.data.settings._contactum_ai_form_generator_details) || {};
                this.settings = {
                    api_key: details.api_key || '',
                    status:  !!details.status,
                };
            });
        },

        save() {
            this.saving = true;
            jQuery.post(contactum.ajaxurl, {
                action:       'contactum_save_global_settings',
                _ajax_nonce:  contactum.nonce,
                settings_key: 'ai_form_generator',
                settings:     this.settings,
            }, (response) => {
                this.saving = false;
                if (response.success) {
                    this.settings.status = !!response.data.status;
                    this.$notify({
                        title:   response.data.status ? 'Success' : 'Warning',
                        message: response.data.message,
                        type:    response.data.status ? 'success' : 'warning',
                    });
                } else {
                    this.$notify({ title: 'Error', message: 'Could not save settings.', type: 'error' });
                }
            });
        },

        clearSettings() {
            this.clearing = true;
            jQuery.post(contactum.ajaxurl, {
                action:       'contactum_save_global_settings',
                _ajax_nonce:  contactum.nonce,
                settings_key: 'ai_form_generator',
                settings:     this.settings,
                action_type:  'clear-settings',
            }, (response) => {
                this.clearing = false;
                this.settings = { api_key: '', status: false };
                if (response.success) {
                    this.$notify({ title: 'Removed', message: response.data.message, type: 'success' });
                }
            });
        },
    },
};
</script>

<style lang="scss">
.ctm-ai-settings {
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
        color: #6366f1;
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

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__info-box {
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 24px;
    }

    &__info-title {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 600;
        color: #5b21b6;
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
