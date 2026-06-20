<template>
<div class="ctm-s3-module">

    <div class="ctm-s3-module__row">
        <label class="ctm-s3-module__label">
            <el-switch v-model="integration.enabled" />
            Upload files from this form to Amazon S3
        </label>
    </div>

    <template v-if="integration.enabled">

        <div class="ctm-s3-module__row">
            <label class="ctm-s3-module__field-label">
                Folder Prefix
                <span class="ctm-s3-module__optional">(optional)</span>
            </label>
            <el-input
                v-model="integration.folder_prefix"
                placeholder="e.g. contact-form/  (leave blank for global default)"
                clearable
                style="max-width:360px"
            />
            <p class="ctm-s3-module__help">
                Appended after the global path prefix set in Settings → Integrations → Amazon S3.
            </p>
        </div>

        <div class="ctm-s3-module__row">
            <label class="ctm-s3-module__label" style="font-size:13px;font-weight:500">
                <el-switch v-model="integration.delete_local" />
                Delete local copy after S3 upload
            </label>
            <p class="ctm-s3-module__help" style="margin-left:46px">
                Overrides the global setting for this form. Use with caution.
            </p>
        </div>

        <div class="ctm-s3-module__info-note">
            <i class="el-icon-cloud-upload"></i>
            Files uploaded through this form will be stored in your S3 bucket.
            The S3 URL is saved with the entry so files remain accessible from the admin.
        </div>

    </template>

    <div v-if="!integration.enabled" class="ctm-s3-module__disabled-note">
        Enable S3 above to offload this form's file uploads to Amazon S3.
        Configure your AWS credentials in
        <strong>Settings → Integrations → Amazon S3</strong> first.
    </div>

</div>
</template>

<script>
import integration_mixin from '../../../mixin/integration.js';

export default {
    name: 's3',
    mixins: [integration_mixin],
};
</script>

<style lang="scss">
.ctm-s3-module {
    padding: 4px 0;

    &__row { margin-bottom: 18px; }

    &__label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        cursor: pointer;
    }

    &__field-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    &__optional {
        font-size: 11px;
        font-weight: 400;
        color: #94a3b8;
    }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
        line-height: 1.5;
    }

    &__info-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12px;
        color: #92400e;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 6px;
        padding: 10px 12px;
        line-height: 1.6;

        i {
            color: #f59e0b;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 1px;
        }
    }

    &__disabled-note {
        font-size: 13px;
        color: #94a3b8;
        font-style: italic;
        line-height: 1.6;
    }
}
</style>
