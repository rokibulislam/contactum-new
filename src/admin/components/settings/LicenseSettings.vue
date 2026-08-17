<template>
<div class="ctm-license-settings">

    <div v-if="loading" class="ctm-license-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-license-settings__header">
            <span class="ctm-license-settings__icon dashicons dashicons-admin-network"></span>
            <div>
                <h2 class="ctm-license-settings__title">License</h2>
                <p class="ctm-license-settings__desc">
                    Activate your Contactum Pro license to enable automatic updates and priority support.
                    Your key is tied to this site's URL — deactivate here first if you need to move it elsewhere.
                </p>
            </div>
        </div>

        <!-- Status badge ─────────────────────────────────────────── -->
        <div class="ctm-license-settings__status-row">
            <span :class="['ctm-license-settings__badge', 'is-' + license.status]">
                <i :class="statusIcon"></i>
                {{ statusLabel }}
            </span>
            <span v-if="license.expires" class="ctm-license-settings__expires">
                Expires {{ license.expires }}
            </span>
        </div>

        <p v-if="license.message" class="ctm-license-settings__message" :class="{ 'is-error': !isActive }">
            {{ license.message }}
        </p>

        <!-- Fields ─────────────────────────────────────────────── -->
        <el-form label-position="top" class="ctm-license-settings__form">
            <el-form-item label="License Key">
                <el-input
                    v-model="licenseKey"
                    type="password"
                    show-password
                    placeholder="Paste your license key"
                    :disabled="isActive"
                    clearable
                />
            </el-form-item>
        </el-form>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-license-settings__actions">
            <el-button
                v-if="!isActive"
                type="primary"
                :loading="activating"
                @click="activate"
            >Activate License</el-button>

            <el-button
                v-else
                :loading="deactivating"
                @click="deactivate"
            >Deactivate License</el-button>
        </div>

    </template>
</div>
</template>

<script>
export default {
    name: 'LicenseSettings',

    props: {
        setting_key: { type: String, default: 'license' },
    },

    data() {
        return {
            loading:      true,
            activating:   false,
            deactivating: false,
            licenseKey:   '',
            license: {
                key: '',
                status: 'inactive',
                expires: '',
                message: '',
            },
        };
    },

    computed: {
        isActive() {
            return this.license.status === 'valid';
        },

        statusLabel() {
            const labels = {
                valid:    'Active',
                inactive: 'Inactive',
                expired:  'Expired',
                invalid:  'Invalid',
            };
            return labels[this.license.status] || 'Inactive';
        },

        statusIcon() {
            return this.isActive ? 'el-icon-circle-check' : 'el-icon-warning-outline';
        },
    },

    mounted() {
        this.load();
    },

    methods: {
        load() {
            this.loading = true;
            jQuery.post(contactum.ajaxurl, {
                action:      'contactum_pro_get_license',
                _ajax_nonce: contactum.nonce,
            }, (response) => {
                this.loading = false;
                if (response.success) {
                    this.license    = response.data;
                    this.licenseKey = response.data.key;
                }
            });
        },

        activate() {
            if (!this.licenseKey.trim()) {
                this.$notify({ title: 'Warning', message: 'Please enter a license key.', type: 'warning' });
                return;
            }

            this.activating = true;
            jQuery.post(contactum.ajaxurl, {
                action:      'contactum_pro_activate_license',
                _ajax_nonce: contactum.nonce,
                license_key: this.licenseKey,
            }, (response) => {
                this.activating = false;
                const data = response.data || {};
                if (data.license) {
                    this.license = data.license;
                }
                this.$notify({
                    title:   response.success ? 'Success' : 'Error',
                    message: data.message || (response.success ? 'License activated.' : 'Could not activate license.'),
                    type:    response.success ? 'success' : 'error',
                });
            }).fail(() => {
                this.activating = false;
                this.$notify({ title: 'Error', message: 'Request failed. Please try again.', type: 'error' });
            });
        },

        deactivate() {
            this.deactivating = true;
            jQuery.post(contactum.ajaxurl, {
                action:      'contactum_pro_deactivate_license',
                _ajax_nonce: contactum.nonce,
            }, (response) => {
                this.deactivating = false;
                const data = response.data || {};
                if (data.license) {
                    this.license    = data.license;
                    this.licenseKey = data.license.key;
                }
                this.$notify({
                    title:   'Deactivated',
                    message: data.message || 'License deactivated.',
                    type:    'success',
                });
            }).fail(() => {
                this.deactivating = false;
                this.$notify({ title: 'Error', message: 'Request failed. Please try again.', type: 'error' });
            });
        },
    },
};
</script>

<style lang="scss">
.ctm-license-settings {
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

    &__status-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    &__badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;

        &.is-valid {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        &.is-inactive {
            background: #f4f4f5;
            color: #606266;
            border: 1px solid #e4e4e7;
        }
        &.is-expired,
        &.is-invalid {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
    }

    &__expires {
        font-size: 12px;
        color: #94a3b8;
    }

    &__message {
        font-size: 13px;
        color: #6b7280;
        margin: 0 0 16px;

        &.is-error {
            color: #b91c1c;
        }
    }

    &__form {
        margin-bottom: 16px;
    }

    &__actions {
        display: flex;
        gap: 10px;
    }
}
</style>
