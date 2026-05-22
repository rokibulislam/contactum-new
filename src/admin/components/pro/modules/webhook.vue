<template>
<div class="ctm-webhook-module">

    <div class="ctm-webhook-module__row">
        <label class="ctm-webhook-module__label">
            <el-switch v-model="integration.enabled" /> Enable Webhook for this form
        </label>
    </div>

    <template v-if="integration.enabled">

        <div class="ctm-webhook-module__row">
            <label class="ctm-webhook-module__field-label">Webhook URL</label>
            <el-input
                v-model="integration.url"
                placeholder="https://hooks.zapier.com/hooks/catch/… (leave blank to use default)"
                clearable
            />
            <p class="ctm-webhook-module__help">
                Overrides the global default URL for this form only.
            </p>
        </div>

        <div class="ctm-webhook-module__row">
            <label class="ctm-webhook-module__field-label">Request Format</label>
            <el-select v-model="integration.request_format" placeholder="Use global default" clearable style="width:220px">
                <el-option label="JSON (recommended)" value="json" />
                <el-option label="Form Encoded"       value="form" />
            </el-select>
        </div>

        <div class="ctm-webhook-module__payload-note">
            <i class="el-icon-info"></i>
            All submitted field values will be sent automatically. No field mapping required.
        </div>

    </template>

    <div v-if="!integration.enabled" class="ctm-webhook-module__disabled-note">
        Enable the webhook above to configure delivery for this form.
    </div>

</div>
</template>

<script>
import integration_mixin from '../../../mixin/integration.js';

export default {
    name: 'webhook',
    mixins: [integration_mixin],
};
</script>

<style lang="scss">
.ctm-webhook-module {
    padding: 4px 0;

    &__row {
        margin-bottom: 18px;
    }

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

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__payload-note {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #6b7280;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 6px;
        padding: 8px 12px;

        i { color: #0ea5e9; }
    }

    &__disabled-note {
        font-size: 13px;
        color: #94a3b8;
        font-style: italic;
    }
}
</style>
