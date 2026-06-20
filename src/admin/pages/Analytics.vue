<template>
<div class="ctm-analytics">

    <!-- ── Header ──────────────────────────────────────────────────────────── -->
    <div class="ctm-analytics__header">
        <div class="ctm-analytics__header-left">
            <h1 class="ctm-analytics__title">Analytics</h1>
            <p class="ctm-analytics__subtitle">Track views, submissions and conversions across your forms</p>
        </div>
        <div class="ctm-analytics__controls">
            <el-select
                v-model="selectedFormId"
                clearable filterable
                placeholder="All Forms"
                @change="fetchData"
                class="ctm-analytics__form-select"
            >
                <el-option v-for="form in forms" :key="form.id" :label="form.name" :value="form.id" />
            </el-select>

            <div class="ctm-date-presets">
                <button
                    v-for="p in presets"
                    :key="p.key"
                    :class="['ctm-preset-btn', { 'ctm-preset-btn--active': activePreset === p.key }]"
                    @click="applyPreset(p)"
                >{{ p.label }}</button>
            </div>

            <el-date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="–"
                start-placeholder="Start date"
                end-placeholder="End date"
                format="yyyy-MM-dd"
                value-format="yyyy-MM-dd"
                @change="onDateChange"
                class="ctm-analytics__datepicker"
            />
        </div>
    </div>

    <!-- ── Stat cards ───────────────────────────────────────────────────────── -->
    <div class="ctm-stats-grid">
        <div v-for="stat in statCards" :key="stat.key" class="ctm-stat-card">
            <div :class="['ctm-stat-card__icon', 'ctm-stat-card__icon--' + stat.color]">
                <i :class="stat.icon"></i>
            </div>
            <div class="ctm-stat-card__body">
                <div class="ctm-stat-card__value">
                    {{ loading ? '—' : (stat.value + (stat.suffix || '')) }}
                </div>
                <div class="ctm-stat-card__label">{{ stat.label }}</div>
            </div>
        </div>
    </div>

    <!-- ── Conversion funnel ─────────────────────────────────────────────────── -->
    <div class="ctm-funnel-card">
        <div class="ctm-funnel-card__head">
            <div>
                <span class="ctm-funnel-card__title">Conversion Funnel</span>
                <span class="ctm-funnel-card__sub">How visitors move from view to submission</span>
            </div>
        </div>
        <div class="ctm-funnel-card__body" v-loading="loading">
            <div class="ctm-funnel">

                <!-- Step: Views -->
                <div class="ctm-funnel__step">
                    <div class="ctm-funnel__step-meta">
                        <span class="ctm-funnel__step-num">{{ data.total_views }}</span>
                        <span class="ctm-funnel__step-label">Views</span>
                    </div>
                    <div class="ctm-funnel__bar-track">
                        <div class="ctm-funnel__bar-fill ctm-funnel__bar-fill--blue" style="width:100%"></div>
                    </div>
                    <span class="ctm-funnel__step-pct">100%</span>
                </div>

                <!-- Connector -->
                <div class="ctm-funnel__connector">
                    <i class="el-icon-arrow-down ctm-funnel__connector-icon"></i>
                    <span class="ctm-funnel__connector-rate">{{ engagementRate }}% engaged</span>
                </div>

                <!-- Step: Started -->
                <div class="ctm-funnel__step">
                    <div class="ctm-funnel__step-meta">
                        <span class="ctm-funnel__step-num">{{ engaged }}</span>
                        <span class="ctm-funnel__step-label">Started</span>
                    </div>
                    <div class="ctm-funnel__bar-track">
                        <div class="ctm-funnel__bar-fill ctm-funnel__bar-fill--amber" :style="{ width: engagementRate + '%' }"></div>
                    </div>
                    <span class="ctm-funnel__step-pct">{{ engagementRate }}%</span>
                </div>

                <!-- Connector -->
                <div class="ctm-funnel__connector">
                    <i class="el-icon-arrow-down ctm-funnel__connector-icon"></i>
                    <span class="ctm-funnel__connector-rate">{{ completionRate }}% completed</span>
                </div>

                <!-- Step: Submitted -->
                <div class="ctm-funnel__step">
                    <div class="ctm-funnel__step-meta">
                        <span class="ctm-funnel__step-num">{{ data.total_submissions }}</span>
                        <span class="ctm-funnel__step-label">Submitted</span>
                    </div>
                    <div class="ctm-funnel__bar-track">
                        <div class="ctm-funnel__bar-fill ctm-funnel__bar-fill--green" :style="{ width: overallRate + '%' }"></div>
                    </div>
                    <span class="ctm-funnel__step-pct">{{ overallRate }}%</span>
                </div>

            </div>
        </div>
    </div>

    <!-- ── Line chart ────────────────────────────────────────────────────────── -->
    <div class="ctm-chart-card">
        <div class="ctm-chart-card__head">
            <span class="ctm-chart-card__title">Views vs Submissions</span>
            <div class="ctm-series-toggles">
                <button
                    v-for="s in seriesOptions"
                    :key="s.key"
                    :class="['ctm-series-btn', { 'ctm-series-btn--on': visible[s.key] }]"
                    @click="visible[s.key] = !visible[s.key]"
                >
                    <span class="ctm-series-dot" :style="{ background: visible[s.key] ? s.color : '#d1d5db' }"></span>
                    {{ s.label }}
                </button>
            </div>
        </div>
        <div class="ctm-chart-card__body" v-loading="loading">
            <LineChart
                v-if="hasChartData"
                :labels="data.labels"
                :submissions="data.submissions"
                :views="data.views"
                :abandonments="data.abandonments"
                :visible="visible"
            />
            <div v-else class="ctm-empty-state">
                <i class="el-icon-data-line ctm-empty-state__icon"></i>
                <p class="ctm-empty-state__text">No activity in the selected period</p>
            </div>
        </div>
    </div>

    <!-- ── Bottom row ────────────────────────────────────────────────────────── -->
    <div class="ctm-bottom-row">

        <!-- Device breakdown -->
        <div class="ctm-chart-card ctm-chart-card--half">
            <div class="ctm-chart-card__head">
                <span class="ctm-chart-card__title">Device Breakdown</span>
            </div>
            <div class="ctm-chart-card__body" v-loading="loading">
                <DeviceChart :devices="data.devices" />
            </div>
        </div>

        <!-- Top forms -->
        <div class="ctm-chart-card ctm-chart-card--half">
            <div class="ctm-chart-card__head">
                <span class="ctm-chart-card__title">Top Forms</span>
            </div>
            <div class="ctm-chart-card__body" v-loading="loading">
                <template v-if="data.top_forms && data.top_forms.length">
                    <el-table :data="data.top_forms" size="small" style="width:100%">
                        <el-table-column prop="form_name" label="Form" min-width="110" show-overflow-tooltip />
                        <el-table-column prop="views"       label="Views" width="56" align="right" />
                        <el-table-column prop="submissions" label="Subs"  width="56" align="right" />
                        <el-table-column label="Conversion" min-width="110">
                            <template slot-scope="{ row }">
                                <div class="ctm-conv-cell">
                                    <div class="ctm-conv-bar">
                                        <div
                                            class="ctm-conv-bar__fill"
                                            :style="{ width: Math.min(row.conversion_rate, 100) + '%' }"
                                        ></div>
                                    </div>
                                    <span class="ctm-conv-pct">{{ row.conversion_rate }}%</span>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>
                <div v-else class="ctm-empty-state ctm-empty-state--sm">
                    <i class="el-icon-document ctm-empty-state__icon"></i>
                    <p class="ctm-empty-state__text">No submissions yet</p>
                </div>
            </div>
        </div>

    </div>

</div>
</template>

<script>
import LineChart   from './analytics/LineChart.vue';
import DeviceChart from './analytics/DeviceChart.vue';

const DEFAULT = {
    labels: [], submissions: [], views: [], abandonments: [],
    total_views: 0, total_submissions: 0, total_abandonments: 0,
    conversion_rate: 0, abandonment_rate: 0, avg_per_day: 0,
    devices: [], top_forms: [],
};

const fmt = d => d.toISOString().slice(0, 10);

export default {
    name: 'Analytics',
    components: { LineChart, DeviceChart },

    data() {
        const today = new Date();
        const thirtyAgo = new Date(today.getTime() - 29 * 86400000);
        return {
            loading:        false,
            selectedFormId: null,
            activePreset:   '30D',
            dateRange: [fmt(thirtyAgo), fmt(today)],
            forms:   window.contactum.forms.forms || [],
            data:    { ...DEFAULT },
            visible: { views: true, submissions: true, abandonments: true },
            presets: [
                { key: 'today',      label: 'Today'      },
                { key: '7D',         label: '7 Days'     },
                { key: '30D',        label: '30 Days'    },
                { key: 'MTD',        label: 'This Month' },
                { key: 'last_month', label: 'Last Month' },
            ],
            seriesOptions: [
                { key: 'views',        label: 'Views',        color: '#a78bfa' },
                { key: 'submissions',  label: 'Submissions',  color: '#1a7efb' },
                { key: 'abandonments', label: 'Abandonments', color: '#f59e0b' },
            ],
        };
    },

    computed: {
        statCards() {
            const d = this.data;
            return [
                { key: 'views',    icon: 'el-icon-view',        color: 'blue',   label: 'Total Views',        value: d.total_views },
                { key: 'subs',     icon: 'el-icon-document',    color: 'green',  label: 'Submissions',        value: d.total_submissions },
                { key: 'rate',     icon: 'el-icon-data-line',   color: 'purple', label: 'Conversion Rate',    value: d.conversion_rate, suffix: '%' },
                { key: 'avg',      icon: 'el-icon-date',        color: 'amber',  label: 'Avg / Day',          value: d.avg_per_day },
                { key: 'abnd',     icon: 'el-icon-s-release',   color: 'orange', label: 'Abandonments',       value: d.total_abandonments },
                { key: 'abndrate', icon: 'el-icon-warning',     color: 'red',    label: 'Abandonment Rate',   value: d.abandonment_rate, suffix: '%' },
            ];
        },
        engaged() {
            return this.data.total_submissions + this.data.total_abandonments;
        },
        engagementRate() {
            if (!this.data.total_views) return 0;
            return Math.round((this.engaged / this.data.total_views) * 100);
        },
        completionRate() {
            if (!this.engaged) return 0;
            return Math.round((this.data.total_submissions / this.engaged) * 100);
        },
        overallRate() {
            if (!this.data.total_views) return 0;
            return Math.round((this.data.total_submissions / this.data.total_views) * 100);
        },
        hasChartData() {
            if (this.loading) return true;
            return this.data.total_views > 0 || this.data.total_submissions > 0;
        },
    },

    mounted() {
        this.fetchData();
    },

    methods: {
        applyPreset(preset) {
            this.activePreset = preset.key;
            const today = new Date();
            switch (preset.key) {
                case 'today':
                    this.dateRange = [fmt(today), fmt(today)];
                    break;
                case '7D':
                    this.dateRange = [fmt(new Date(today.getTime() - 6 * 86400000)), fmt(today)];
                    break;
                case '30D':
                    this.dateRange = [fmt(new Date(today.getTime() - 29 * 86400000)), fmt(today)];
                    break;
                case 'MTD': {
                    const first = new Date(today.getFullYear(), today.getMonth(), 1);
                    this.dateRange = [fmt(first), fmt(today)];
                    break;
                }
                case 'last_month': {
                    const first = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    const last  = new Date(today.getFullYear(), today.getMonth(), 0);
                    this.dateRange = [fmt(first), fmt(last)];
                    break;
                }
            }
            this.fetchData();
        },

        onDateChange() {
            this.activePreset = '';
            this.fetchData();
        },

        fetchData() {
            const self    = this;
            const payload = {
                action:      'contactum_get_form_analytics',
                _ajax_nonce: contactum.nonce,
                form_id:     this.selectedFormId || '',
            };
            if (Array.isArray(this.dateRange) && this.dateRange.length === 2) {
                payload.startdate = this.dateRange[0];
                payload.enddate   = this.dateRange[1];
            }
            this.loading = true;
            jQuery.ajax({
                url:  contactum.ajaxurl,
                type: 'POST',
                data: payload,
                success(res) {
                    if (res.success) self.data = { ...DEFAULT, ...res.data };
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
    background: #f4f6f9;
    min-height: 100vh;
    color: #1e1f21;
}

/* ── Header ─────────────────────────────────────────────────────────────────── */
.ctm-analytics__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.ctm-analytics__title {
    margin: 0 0 4px;
    font-size: 22px;
    font-weight: 700;
    color: #111827;
}
.ctm-analytics__subtitle {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
}
.ctm-analytics__controls {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.ctm-analytics__form-select { width: 180px; }
.ctm-analytics__datepicker  { width: 240px !important; }

/* quick preset pills */
.ctm-date-presets {
    display: flex;
    gap: 4px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 3px;
}
.ctm-preset-btn {
    padding: 4px 10px;
    border-radius: 6px;
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;

    &:hover           { background: #f3f4f6; color: #374151; }
    &--active         { background: #2563eb; color: #fff; }
}

/* ── Stat cards ─────────────────────────────────────────────────────────────── */
.ctm-stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
    margin-bottom: 20px;

    @media (max-width: 1200px) { grid-template-columns: repeat(3, 1fr); }
    @media (max-width: 700px)  { grid-template-columns: repeat(2, 1fr); }
}
.ctm-stat-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    padding: 18px 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: box-shadow .15s;
    &:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }
}
.ctm-stat-card__icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;

    &--blue   { background: rgba(26,126,251,.1);  color: #1a7efb; }
    &--green  { background: rgba(16,185,129,.1);  color: #10b981; }
    &--purple { background: rgba(139,92,246,.1);  color: #8b5cf6; }
    &--amber  { background: rgba(245,158,11,.1);  color: #f59e0b; }
    &--orange { background: rgba(234,88,12,.1);   color: #ea580c; }
    &--red    { background: rgba(220,38,38,.1);   color: #dc2626; }
}
.ctm-stat-card__value {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    color: #111827;
}
.ctm-stat-card__label {
    font-size: 12px;
    color: #6b7280;
    margin-top: 3px;
}

/* ── Conversion funnel ──────────────────────────────────────────────────────── */
.ctm-funnel-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    margin-bottom: 20px;
    overflow: hidden;
}
.ctm-funnel-card__head {
    display: flex;
    align-items: baseline;
    gap: 10px;
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.ctm-funnel-card__title {
    font-weight: 600;
    font-size: 15px;
    color: #111827;
}
.ctm-funnel-card__sub {
    font-size: 12px;
    color: #9ca3af;
}
.ctm-funnel-card__body {
    padding: 24px 20px;
}

.ctm-funnel {
    display: flex;
    flex-direction: column;
    gap: 0;
    max-width: 560px;
    margin: 0 auto;
}

.ctm-funnel__step {
    display: grid;
    grid-template-columns: 120px 1fr 48px;
    align-items: center;
    gap: 16px;
}
.ctm-funnel__step-meta {
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.ctm-funnel__step-num {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
}
.ctm-funnel__step-label {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}
.ctm-funnel__bar-track {
    height: 10px;
    background: #f3f4f6;
    border-radius: 10px;
    overflow: hidden;
}
.ctm-funnel__bar-fill {
    height: 100%;
    border-radius: 10px;
    transition: width .6s ease;

    &--blue  { background: linear-gradient(90deg, #a78bfa, #1a7efb); }
    &--amber { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
    &--green { background: linear-gradient(90deg, #34d399, #10b981); }
}
.ctm-funnel__step-pct {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    text-align: right;
    white-space: nowrap;
}

.ctm-funnel__connector {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0 6px 48px;
}
.ctm-funnel__connector-icon {
    font-size: 12px;
    color: #9ca3af;
}
.ctm-funnel__connector-rate {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 20px;
}

/* ── Chart card ─────────────────────────────────────────────────────────────── */
.ctm-chart-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
    margin-bottom: 20px;
    overflow: hidden;

    &--half { margin-bottom: 0; flex: 1 1 0; min-width: 0; }
}
.ctm-chart-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f0f0f0;
}
.ctm-chart-card__title {
    font-weight: 600;
    font-size: 15px;
    color: #111827;
}
.ctm-chart-card__body {
    padding: 20px;
    min-height: 80px;
}

/* series toggle pills */
.ctm-series-toggles {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.ctm-series-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px 4px 8px;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    background: #fff;
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    transition: all .15s;

    &:hover     { border-color: #93c5fd; color: #374151; }
    &--on       { border-color: #e5e7eb; color: #374151; }
    &:not(&--on){ opacity: .45; }
}
.ctm-series-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
    transition: background .15s;
}

/* ── Bottom row ─────────────────────────────────────────────────────────────── */
.ctm-bottom-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;

    .ctm-chart-card--half {
        @media (max-width: 900px) { flex: 1 1 100%; }
    }
}

/* conversion bar in top-forms table */
.ctm-conv-cell {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ctm-conv-bar {
    flex: 1;
    height: 6px;
    background: #f3f4f6;
    border-radius: 6px;
    overflow: hidden;
    min-width: 40px;
}
.ctm-conv-bar__fill {
    height: 100%;
    background: linear-gradient(90deg, #34d399, #10b981);
    border-radius: 6px;
    transition: width .4s ease;
}
.ctm-conv-pct {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    min-width: 36px;
    text-align: right;
}

/* ── Empty state ────────────────────────────────────────────────────────────── */
.ctm-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 260px;
    gap: 10px;
    color: #9ca3af;

    &--sm { min-height: 120px; }
}
.ctm-empty-state__icon {
    font-size: 36px;
    opacity: .4;
}
.ctm-empty-state__text {
    font-size: 13px;
    margin: 0;
}

</style>
