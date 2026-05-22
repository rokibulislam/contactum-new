<template>
<div class="ctm-s3-settings">

    <div v-if="loading" class="ctm-s3-settings__loading">
        <i class="el-icon-loading"></i> Loading…
    </div>

    <template v-else>

        <!-- Header ─────────────────────────────────────────────── -->
        <div class="ctm-s3-settings__header">
            <span class="ctm-s3-settings__icon dashicons dashicons-cloud-upload"></span>
            <div>
                <h2 class="ctm-s3-settings__title">Amazon S3 Storage</h2>
                <p class="ctm-s3-settings__desc">
                    Offload form file and image uploads to Amazon S3.
                    Once connected, enable S3 per-form under
                    <strong>Form Settings → Integrations → Amazon S3</strong>.
                    Files are stored using your chosen ACL — public files get a direct URL; private files require authenticated access.
                </p>
            </div>
        </div>

        <!-- Status badge ─────────────────────────────────────────── -->
        <div v-if="settings.access_key" class="ctm-s3-settings__status-row">
            <span :class="['ctm-s3-settings__badge', settings.status ? 'is-connected' : 'is-disconnected']">
                <i :class="settings.status ? 'el-icon-circle-check' : 'el-icon-warning-outline'"></i>
                {{ settings.status ? 'Connected — bucket is reachable' : 'Not connected — save credentials to verify' }}
            </span>
        </div>

        <!-- Credentials ──────────────────────────────────────────── -->
        <el-form label-position="top" class="ctm-s3-settings__form">

            <el-row :gutter="16">
                <el-col :span="12">
                    <el-form-item label="Access Key ID">
                        <el-input
                            v-model="settings.access_key"
                            placeholder="AKIAIOSFODNN7EXAMPLE"
                            autocomplete="off"
                        />
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="Secret Access Key">
                        <el-input
                            v-model="settings.secret_key"
                            :type="showSecret ? 'text' : 'password'"
                            placeholder="••••••••••••••••••••"
                            autocomplete="new-password"
                        >
                            <i
                                slot="suffix"
                                :class="showSecret ? 'el-icon-view' : 'el-icon-minus'"
                                style="cursor:pointer;margin-right:6px"
                                @click="showSecret = !showSecret"
                            />
                        </el-input>
                        <p class="ctm-s3-settings__help">
                            Stored securely in WordPress options. Use an IAM user with
                            <code>s3:PutObject</code>, <code>s3:DeleteObject</code> on your bucket only.
                        </p>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-row :gutter="16">
                <el-col :span="12">
                    <el-form-item label="Bucket Name">
                        <el-input v-model="settings.bucket" placeholder="my-forms-bucket" />
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="Region">
                        <el-select v-model="settings.region" style="width:100%">
                            <el-option-group label="US">
                                <el-option label="US East (N. Virginia) — us-east-1"    value="us-east-1" />
                                <el-option label="US East (Ohio) — us-east-2"           value="us-east-2" />
                                <el-option label="US West (Oregon) — us-west-2"         value="us-west-2" />
                                <el-option label="US West (N. California) — us-west-1"  value="us-west-1" />
                            </el-option-group>
                            <el-option-group label="Europe">
                                <el-option label="Europe (Ireland) — eu-west-1"         value="eu-west-1" />
                                <el-option label="Europe (London) — eu-west-2"          value="eu-west-2" />
                                <el-option label="Europe (Frankfurt) — eu-central-1"    value="eu-central-1" />
                                <el-option label="Europe (Paris) — eu-west-3"           value="eu-west-3" />
                                <el-option label="Europe (Stockholm) — eu-north-1"      value="eu-north-1" />
                            </el-option-group>
                            <el-option-group label="Asia Pacific">
                                <el-option label="Asia Pacific (Singapore) — ap-southeast-1"  value="ap-southeast-1" />
                                <el-option label="Asia Pacific (Sydney) — ap-southeast-2"     value="ap-southeast-2" />
                                <el-option label="Asia Pacific (Tokyo) — ap-northeast-1"      value="ap-northeast-1" />
                                <el-option label="Asia Pacific (Mumbai) — ap-south-1"         value="ap-south-1" />
                            </el-option-group>
                            <el-option-group label="Other">
                                <el-option label="Canada (Central) — ca-central-1"       value="ca-central-1" />
                                <el-option label="South America (São Paulo) — sa-east-1" value="sa-east-1" />
                            </el-option-group>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-row :gutter="16">
                <el-col :span="12">
                    <el-form-item label="Path Prefix">
                        <el-input v-model="settings.path_prefix" placeholder="contactum/uploads/" />
                        <p class="ctm-s3-settings__help">Leading folder for all uploads. Include trailing slash.</p>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="File Visibility (ACL)">
                        <el-select v-model="settings.acl" style="width:100%">
                            <el-option label="Public — direct URL access"              value="public-read" />
                            <el-option label="Private — authenticated access only"     value="private" />
                        </el-select>
                        <p class="ctm-s3-settings__help">
                            Public files get a standard HTTPS URL. Private requires bucket policy/pre-signed URLs.
                        </p>
                    </el-form-item>
                </el-col>
            </el-row>

            <el-form-item>
                <el-switch v-model="settings.delete_local" />
                <span class="ctm-s3-settings__switch-label">Delete local copy after upload</span>
                <p class="ctm-s3-settings__help">Saves server disk space. Cannot be undone — use only when S3 is stable.</p>
            </el-form-item>

        </el-form>

        <!-- IAM policy hint ──────────────────────────────────────── -->
        <div class="ctm-s3-settings__policy-box">
            <p class="ctm-s3-settings__policy-title">Minimum IAM Policy</p>
            <pre class="ctm-s3-settings__policy-code">{{ iamPolicy }}</pre>
        </div>

        <!-- Test result ─────────────────────────────────────────── -->
        <div v-if="testResult" :class="['ctm-s3-settings__test-result', testResult.ok ? 'is-success' : 'is-error']">
            <i :class="testResult.ok ? 'el-icon-circle-check' : 'el-icon-circle-close'"></i>
            {{ testResult.message }}
        </div>

        <!-- Actions ─────────────────────────────────────────────── -->
        <div class="ctm-s3-settings__actions">
            <el-button type="primary" :loading="saving" @click="save">Save Settings</el-button>
            <el-button :loading="testing" :disabled="!canTest" @click="testConnection">Test Connection</el-button>
        </div>

    </template>
</div>
</template>

<script>
export default {
    name: 'S3Settings',

    props: {
        setting_key: { type: String, default: 's3' },
    },

    data() {
        return {
            loading:    true,
            saving:     false,
            testing:    false,
            showSecret: false,
            testResult: null,
            settings: {
                access_key:   '',
                secret_key:   '',
                bucket:       '',
                region:       'us-east-1',
                path_prefix:  'contactum/',
                acl:          'public-read',
                delete_local: false,
                status:       false,
            },
        };
    },

    computed: {
        canTest() {
            return !!(this.settings.access_key && this.settings.bucket);
        },
        iamPolicy() {
            const bucket = this.settings.bucket || 'YOUR-BUCKET';
            return JSON.stringify({
                Version: '2012-10-17',
                Statement: [{
                    Effect: 'Allow',
                    Action: ['s3:PutObject', 's3:DeleteObject'],
                    Resource: `arn:aws:s3:::${bucket}/*`,
                }],
            }, null, 2);
        },
    },

    watch: {
        setting_key() { this.load(); },
    },

    mounted() {
        this.load();
    },

    methods: {
        load() {
            this.loading = true;
            jQuery.post(
                window.contactum.ajaxurl,
                { action: 'contactum_get_admin_integrations', _ajax_nonce: window.contactum.nonce, settings_key: 's3' },
                (res) => {
                    this.loading = false;
                    if (res.success) {
                        const val = res.data.value || {};
                        this.settings = {
                            access_key:   val.access_key   || '',
                            secret_key:   val.secret_key   || '',
                            bucket:       val.bucket       || '',
                            region:       val.region       || 'us-east-1',
                            path_prefix:  val.path_prefix  || 'contactum/',
                            acl:          val.acl          || 'public-read',
                            delete_local: !!val.delete_local,
                            status:       !!val.status,
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
                    settings_key: 's3',
                    integration:  this.settings,
                },
                (res) => {
                    this.saving = false;
                    if (res.success) {
                        if (res.data) {
                            this.settings.status = !!res.data.status;
                            if (res.data.secret_key) {
                                this.settings.secret_key = res.data.secret_key;
                            }
                        }
                        const msg = this.settings.status
                            ? 'Settings saved and S3 connection verified.'
                            : 'Settings saved. Connection could not be verified — check credentials.';
                        this.$notify({ title: 'Saved', message: msg, type: this.settings.status ? 'success' : 'warning' });
                    } else {
                        this.$notify({ title: 'Error', message: 'Could not save settings.', type: 'error' });
                    }
                }
            );
        },

        testConnection() {
            this.testing    = true;
            this.testResult = null;
            jQuery.post(
                window.contactum.ajaxurl,
                { action: 'contactum_s3_test_connection', _ajax_nonce: window.contactum.nonce },
                (res) => {
                    this.testing    = false;
                    this.testResult = { ok: !!res.success, message: res.data || (res.success ? 'Connected.' : 'Connection failed.') };
                    if (res.success) this.settings.status = true;
                }
            );
        },
    },
};
</script>

<style lang="scss">
.ctm-s3-settings {
    padding: 28px 32px;
    max-width: 760px;

    &__loading { color: #94a3b8; font-size: 14px; }

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

    &__status-row { margin-bottom: 20px; }

    &__badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        padding: 5px 12px;
        border-radius: 20px;

        &.is-connected    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        &.is-disconnected { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    }

    &__form { margin-bottom: 16px; }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
        line-height: 1.5;

        code {
            background: #f1f5f9;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 11px;
        }
    }

    &__switch-label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-left: 10px;
        vertical-align: middle;
    }

    &__policy-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 20px;
    }

    &__policy-title {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    &__policy-code {
        margin: 0;
        font-size: 11px;
        line-height: 1.6;
        color: #374151;
        white-space: pre-wrap;
        word-break: break-word;
    }

    &__test-result {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        padding: 10px 14px;
        border-radius: 6px;
        margin-bottom: 16px;

        &.is-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        &.is-error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        i { font-size: 16px; }
    }

    &__actions {
        display: flex;
        gap: 10px;
    }
}
</style>
