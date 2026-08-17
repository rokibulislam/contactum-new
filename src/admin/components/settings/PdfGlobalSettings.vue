<template>
<div class="ctm-pdf-settings">

    <div v-if="loading" class="ctm-pdf-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-pdf-settings__header">
            <span class="ctm-pdf-settings__icon dashicons dashicons-media-document"></span>
            <div>
                <h2 class="ctm-pdf-settings__title">Global PDF Settings</h2>
                <p class="ctm-pdf-settings__desc">
                    This global settings will be set as default for your new PDF feed for any form.
                    Then you can customize for a specific PDF generator feed.
                </p>
            </div>
        </div>

        <!-- Fields ─────────────────────────────────────────────── -->
        <el-form label-position="top" class="ctm-pdf-settings__form">

            <el-row :gutter="20">
                <el-col :span="12">
                    <el-form-item label="Paper size">
                        <el-select v-model="settings.paper_size" style="width:100%">
                            <el-option label="A4 (210 x 297mm)" value="A4" />
                            <el-option label="Letter (216 x 279mm)" value="LETTER" />
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="Orientation">
                        <el-radio-group v-model="settings.orientation" style="width:100%">
                            <el-radio-button label="portrait">Portrait</el-radio-button>
                            <el-radio-button label="landscape">Landscape</el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-form-item label="Font Family">
                <el-select v-model="settings.font_family" style="width:100%" :loading="loadingFonts">
                    <el-option v-for="font in fonts" :key="font" :label="font" :value="font" />
                </el-select>
                <p class="ctm-pdf-settings__help">
                    Only fonts bundled with the PDF engine are shown — these render reliably without any extra setup.
                </p>
            </el-form-item>

            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="Font size">
                        <el-input-number v-model="settings.font_size" :min="8" :max="32" style="width:100%" />
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="Font color">
                        <el-color-picker v-model="settings.font_color" />
                    </el-form-item>
                </el-col>
                <el-col :span="8">
                    <el-form-item label="Heading color">
                        <el-color-picker v-model="settings.heading_color" />
                    </el-form-item>
                </el-col>
            </el-row>

            <el-row :gutter="20">
                <el-col :span="8">
                    <el-form-item label="Accent color">
                        <el-color-picker v-model="settings.accent_color" />
                    </el-form-item>
                </el-col>
                <el-col :span="16">
                    <el-form-item label="Language Direction">
                        <el-radio-group v-model="settings.text_direction">
                            <el-radio-button label="ltr">Left to Right</el-radio-button>
                            <el-radio-button label="rtl">Right to Left</el-radio-button>
                        </el-radio-group>
                    </el-form-item>
                </el-col>
            </el-row>

        </el-form>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-pdf-settings__actions">
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
const DEFAULTS = {
    paper_size:     'A4',
    orientation:    'portrait',
    font_family:    'DejaVu Sans',
    font_size:      14,
    font_color:     '#1f2937',
    heading_color:  '#111827',
    accent_color:   '#6366f1',
    text_direction: 'ltr',
};

export default {
    name: 'PdfGlobalSettings',

    props: {
        setting_key: { type: String, default: 'pdf_global_settings' },
    },

    data() {
        return {
            loading:      true,
            loadingFonts: true,
            saving:       false,
            fonts:        [ DEFAULTS.font_family ],
            settings:     { ...DEFAULTS },
        };
    },

    mounted() {
        this.load();
        this.loadFonts();
    },

    methods: {
        load() {
            this.loading = true;
            jQuery.post(contactum.ajaxurl, {
                action:       'contactum_get_global_settings',
                _ajax_nonce:  contactum.nonce,
                settings_key: 'pdf_global_settings',
                key:          [ '_contactum_pdf_global_settings_details' ],
            }, (response) => {
                this.loading = false;
                const saved  = (response.data && response.data.settings && response.data.settings._contactum_pdf_global_settings_details) || {};
                this.settings = { ...DEFAULTS, ...saved };
            });
        },

        loadFonts() {
            this.loadingFonts = true;
            jQuery.post(contactum.ajaxurl, {
                action:      'contactum_get_pdf_fonts',
                _ajax_nonce: contactum.nonce,
            }, (response) => {
                this.loadingFonts = false;
                if (response.success && response.data.fonts && response.data.fonts.length) {
                    this.fonts = response.data.fonts;
                }
            });
        },

        save() {
            this.saving = true;
            jQuery.post(contactum.ajaxurl, {
                action:       'contactum_save_global_settings',
                _ajax_nonce:  contactum.nonce,
                settings_key: 'pdf_global_settings',
                settings:     this.settings,
            }, (response) => {
                this.saving = false;
                if (response.success) {
                    this.settings = { ...DEFAULTS, ...response.data.settings };
                    this.$notify({ title: 'Success', message: response.data.message, type: 'success' });
                } else {
                    this.$notify({ title: 'Error', message: 'Could not save settings.', type: 'error' });
                }
            });
        },
    },
};
</script>

<style lang="scss">
.ctm-pdf-settings {
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
    }

    &__form {
        margin-bottom: 16px;
    }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__actions {
        display: flex;
        gap: 10px;
    }
}
</style>
