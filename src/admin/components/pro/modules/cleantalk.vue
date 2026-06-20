<template>
<div class="ctm-cleantalk-module">

    <div class="ctm-cleantalk-module__row">
        <label class="ctm-cleantalk-module__label">
            <el-switch v-model="integration.enabled" /> Enable CleanTalk Spam Protection for this form
        </label>
    </div>

    <template v-if="integration.enabled">

        <div class="ctm-cleantalk-module__row">
            <label class="ctm-cleantalk-module__field-label">Email Field Name</label>
            <el-input
                v-model="integration.email_field"
                placeholder="e.g. email  (blank = auto-detect)"
                clearable
                style="max-width:320px"
            />
            <p class="ctm-cleantalk-module__help">
                Leave blank to use the first email-type field automatically.
            </p>
        </div>

        <div class="ctm-cleantalk-module__row">
            <label class="ctm-cleantalk-module__field-label">Name Field Name <span class="ctm-cleantalk-module__optional">(optional)</span></label>
            <el-input
                v-model="integration.name_field"
                placeholder="e.g. name"
                clearable
                style="max-width:320px"
            />
            <p class="ctm-cleantalk-module__help">
                Providing the submitter's name improves spam detection accuracy.
            </p>
        </div>

        <div class="ctm-cleantalk-module__info-note">
            <i class="el-icon-shield"></i>
            Submissions are checked against the CleanTalk database before the entry is saved.
            Spam is blocked silently — no CAPTCHA required.
        </div>

    </template>

    <div v-if="!integration.enabled" class="ctm-cleantalk-module__disabled-note">
        Enable CleanTalk above to activate spam protection for this form.
        Make sure your access key is configured in
        <strong>Settings → CleanTalk Spam Protection</strong>.
    </div>

</div>
</template>

<script>
import integration_mixin from '../../../mixin/integration.js';

export default {
    name: 'cleantalk',
    mixins: [integration_mixin],
};
</script>

<style lang="scss">
.ctm-cleantalk-module {
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

    &__optional {
        font-size: 11px;
        font-weight: 400;
        color: #94a3b8;
    }

    &__help {
        margin: 4px 0 0;
        font-size: 12px;
        color: #94a3b8;
    }

    &__info-note {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 12px;
        color: #166534;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        padding: 10px 12px;
        line-height: 1.6;

        i {
            color: #16a34a;
            font-size: 15px;
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
