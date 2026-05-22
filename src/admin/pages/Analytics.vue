<template>
<div class="ctm-analytics">

    <!-- Header ─────────────────────────────────────────────── -->
    <div class="ctm-analytics__header">
        <h1 class="ctm-analytics__title">Analytics</h1>
        <div class="ctm-analytics__filters">
            <el-select
                v-model="selectedFormId"
                clearable
                filterable
                placeholder="All Forms"
                @change="fetchData"
                class="ctm-analytics__form-select"
            >
                <el-option
                    v-for="form in forms"
                    :key="form.id"
                    :label="form.name"
                    :value="form.id"
                />
            </el-select>

            <el-date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="–"
                start-placeholder="Start date"
                end-placeholder="End date"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                @change="fetchData"
                class="ctm-analytics__datepicker"
            />
        </div>
    </div>

    <!-- Stats cards ─────────────────────────────────────────── -->
    <div class="ctm-stats-grid">
        <div class="ctm-stat-card">
            <div class="ctm-stat-card__icon ctm-stat-card__icon--blue">
                <i class="el-icon-view"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">{{ loading ? '—' : data.total_views }}</div>
                <div class="ctm-stat-card__label">Total Views</div>
            </div>
        </div>

        <div class="ctm-stat-card">
            <div class="ctm-stat-card__icon ctm-stat-card__icon--green">
                <i class="el-icon-document"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">{{ loading ? '—' : data.total_submissions }}</div>
                <div class="ctm-stat-card__label">Submissions</div>
            </div>
        </div>

        <div class="ctm-stat-card">
            <div class="ctm-stat-card__icon ctm-stat-card__icon--purple">
                <i class="el-icon-data-line"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">{{ loading ? '—' : data.conversion_rate + '%' }}</div>
                <div class="ctm-stat-card__label">Conversion Rate</div>
            </div>
        </div>

        <div class="ctm-stat-card">
            <div class="ctm-stat-card__icon ctm-stat-card__icon--amber">
                <i class="el-icon-date"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">{{ loading ? '—' : data.avg_per_day }}</div>
                <div class="ctm-stat-card__label">Avg. Submissions / Day</div>
            </div>
        </div>

        <div class="ctm-stat-card">
            <div class="ctm-stat-card__icon ctm-stat-card__icon--orange">
                <i class="el-icon-s-release"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">{{ loading ? '—' : data.total_abandonments }}</div>
                <div class="ctm-stat-card__label">Abandonments</div>
            </div>
        </div>

        <div class="ctm-stat-card">
            <div class="ctm-stat-card__icon ctm-stat-card__icon--red">
                <i class="el-icon-warning"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">{{ loading ? '—' : data.abandonment_rate + '%' }}</div>
                <div class="ctm-stat-card__label">Abandonment Rate</div>
            </div>
        </div>
    </div>

    <!-- Line chart ──────────────────────────────────────────── -->
    <div class="ctm-chart-card">
        <div class="ctm-chart-card__head">
            <span class="ctm-chart-card__title">Views vs Submissions</span>
        </div>
        <div class="ctm-chart-card__body" v-loading="loading">
            <LineChart
                :labels="data.labels"
                :submissions="data.submissions"
                :views="data.views"
                :abandonments="data.abandonments"
            />
        </div>
    </div>

    <!-- Bottom row: device chart + top forms ────────────────── -->
    <div class="ctm-bottom-row">

        <div class="ctm-chart-card ctm-chart-card--half">
            <div class="ctm-chart-card__head">
                <span class="ctm-chart-card__title">Device Breakdown</span>
            </div>
            <div class="ctm-chart-card__body" v-loading="loading">
                <DeviceChart :devices="data.devices" />
            </div>
        </div>

        <div class="ctm-chart-card ctm-chart-card--half">
            <div class="ctm-chart-card__head">
                <span class="ctm-chart-card__title">Top Forms</span>
            </div>
            <div class="ctm-chart-card__body" v-loading="loading">
                <el-table :data="data.top_forms" size="small" style="width:100%">
                    <el-table-column prop="form_name" label="Form" min-width="120" show-overflow-tooltip />
                    <el-table-column prop="views" label="Views" width="62" align="right" />
                    <el-table-column prop="submissions" label="Subs" width="62" align="right" />
                    <el-table-column prop="abandonments" label="Abnd" width="62" align="right" />
                    <el-table-column label="Rate" width="62" align="right">
                        <template slot-scope="{ row }">
                            {{ row.conversion_rate }}%
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </div>

    </div>

</div>
</template>

<script>
import LineChart  from './analytics/LineChart.vue';
import DeviceChart from './analytics/DeviceChart.vue';

const DEFAULT = {
    labels: [], submissions: [], views: [], abandonments: [],
    total_views: 0, total_submissions: 0, total_abandonments: 0,
    conversion_rate: 0, abandonment_rate: 0, avg_per_day: 0,
    devices: [], top_forms: [],
};

export default {
    name: 'Analytics',
    components: { LineChart, DeviceChart },

    data() {
        const today = new Date();
        const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        return {
            loading: false,
            selectedFormId: null,
            dateRange: [
                firstOfMonth.toISOString().slice(0, 10),
                today.toISOString().slice(0, 10),
            ],
            forms: window.contactum.forms.forms || [],
            data: { ...DEFAULT },
        };
    },

    mounted() {
        this.fetchData();
    },

    methods: {
        fetchData() {
            const self = this;
            const payload = {
                action: 'contactum_get_form_analytics',
                _ajax_nonce: contactum.nonce,
                form_id: this.selectedFormId || '',
            };

            if (Array.isArray(this.dateRange) && this.dateRange.length === 2) {
                payload.startdate = this.dateRange[0];
                payload.enddate   = this.dateRange[1];
            }

            this.loading = true;
            jQuery.ajax({
                url: contactum.ajaxurl,
                type: 'POST',
                data: payload,
                success(res) {
                    if (res.success) {
                        self.data = { ...DEFAULT, ...res.data };
                    }
                },
                complete() {
                    self.loading = false;
                },
            });
        },
    },
};
</script>

<style lang="scss">
.ctm-analytics {
    padding: 24px;
    background: var(--background, #f8f9fa);
    min-height: 100vh;
    color: var(--foreground, #1e1f21);
}

/* ── Header ── */
.ctm-analytics__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}

.ctm-analytics__title {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
    color: var(--foreground, #1e1f21);
}

.ctm-analytics__filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.ctm-analytics__form-select {
    width: 200px;
}

.ctm-analytics__datepicker {
    width: 260px !important;
}

/* ── Stats grid ── */
.ctm-stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 16px;
    margin-bottom: 20px;

    @media (max-width: 1200px) {
        grid-template-columns: repeat(3, 1fr);
    }
    @media (max-width: 700px) {
        grid-template-columns: repeat(2, 1fr);
    }
}

.ctm-stat-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(30, 31, 33, 0.08);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.ctm-stat-card__icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;

    &--blue   { background: rgba(26,126,251,.12); color: #1a7efb; }
    &--green  { background: rgba(16,185,129,.12); color: #10b981; }
    &--purple { background: rgba(139,92,246,.12); color: #8b5cf6; }
    &--amber  { background: rgba(245,158,11,.12); color: #f59e0b; }
    &--orange { background: rgba(234,88,12,.12);  color: #ea580c; }
    &--red    { background: rgba(220,38,38,.12);  color: #dc2626; }
}

.ctm-stat-card__value {
    font-size: 26px;
    font-weight: 700;
    line-height: 1.2;
    color: var(--foreground, #1e1f21);
}

.ctm-stat-card__label {
    font-size: 13px;
    color: #6b7280;
    margin-top: 2px;
}

/* ── Chart card ── */
.ctm-chart-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(30, 31, 33, 0.08);
    margin-bottom: 20px;
    overflow: hidden;

    &--half {
        margin-bottom: 0;
        flex: 1 1 0;
        min-width: 0;
    }
}

.ctm-chart-card__head {
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.ctm-chart-card__title {
    font-weight: 600;
    font-size: 15px;
    color: var(--foreground, #1e1f21);
}

.ctm-chart-card__body {
    padding: 20px;
    min-height: 80px;
}

/* ── Bottom row ── */
.ctm-bottom-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
</style>
