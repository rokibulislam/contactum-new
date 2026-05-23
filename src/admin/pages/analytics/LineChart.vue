<template>
    <div class="ctm-linechart-wrap">
        <Line v-if="hasData" :chart-options="chartOptions" :chart-data="chartData" />
        <div v-else class="ctm-linechart-empty">No data to display</div>
    </div>
</template>

<script>
import { Line } from 'vue-chartjs/legacy';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    LineElement, PointElement, CategoryScale, LinearScale, Filler
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler);

const SERIES = {
    views: {
        label:           'Views',
        borderColor:     '#a78bfa',
        backgroundColor: 'rgba(167,139,250,0.10)',
    },
    submissions: {
        label:           'Submissions',
        borderColor:     '#1a7efb',
        backgroundColor: 'rgba(26,126,251,0.10)',
    },
    abandonments: {
        label:           'Abandonments',
        borderColor:     '#f59e0b',
        backgroundColor: 'rgba(245,158,11,0.08)',
    },
};

function formatLabel(isoDate) {
    const d = new Date(isoDate + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

export default {
    name: 'LineChart',
    components: { Line },
    props: {
        labels:       { type: Array,  default: () => [] },
        submissions:  { type: Array,  default: () => [] },
        views:        { type: Array,  default: () => [] },
        abandonments: { type: Array,  default: () => [] },
        visible:      { type: Object, default: () => ({ views: true, submissions: true, abandonments: true }) },
    },
    computed: {
        hasData() {
            return this.labels.length > 0;
        },
        formattedLabels() {
            return this.labels.map(formatLabel);
        },
        chartData() {
            const seriesMap = {
                views:        this.views,
                submissions:  this.submissions,
                abandonments: this.abandonments,
            };
            const datasets = Object.keys(SERIES)
                .filter(key => this.visible[key] !== false)
                .map(key => ({
                    ...SERIES[key],
                    data:        seriesMap[key] || [],
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension:     0.4,
                    fill:        true,
                }));

            return { labels: this.formattedLabels, datasets };
        },
        chartOptions() {
            const rawLabels = this.labels;
            return {
                responsive:          true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e1f21',
                        titleColor:      '#e5e7eb',
                        bodyColor:       '#d1d5db',
                        borderColor:     '#374151',
                        borderWidth:     1,
                        padding:         10,
                        callbacks: {
                            title(items) {
                                const idx = items[0]?.dataIndex;
                                return rawLabels[idx] ? formatLabel(rawLabels[idx]) : '';
                            },
                            label(item) {
                                return `  ${item.dataset.label}: ${item.parsed.y}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid:  { display: false },
                        ticks: {
                            color:         '#9ca3af',
                            font:          { size: 11 },
                            maxTicksLimit: 10,
                            autoSkip:      true,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color:     'rgba(0,0,0,.05)',
                            drawBorder: false,
                        },
                        ticks: {
                            color:    '#9ca3af',
                            font:     { size: 11 },
                            stepSize: 1,
                            callback: v => (Number.isInteger(v) ? v : null),
                        },
                    },
                },
            };
        },
    },
};
</script>

<style scoped>
.ctm-linechart-wrap {
    height: 300px;
    position: relative;
}
.ctm-linechart-empty {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 13px;
}
</style>
