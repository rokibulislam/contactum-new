<template>
    <div class="ctm-linechart-wrap">
        <Line :chart-options="options" :chart-data="chartData" />
    </div>
</template>

<script>
import { Line } from 'vue-chartjs/legacy';
import {
    Chart as ChartJS, Title, Tooltip, Legend,
    LineElement, PointElement, CategoryScale, LinearScale, Filler
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, Filler);

export default {
    name: 'LineChart',
    components: { Line },
    props: {
        labels:      { type: Array, default: () => [] },
        submissions: { type: Array, default: () => [] },
        views:       { type: Array, default: () => [] },
    },
    computed: {
        chartData() {
            return {
                labels: this.labels,
                datasets: [
                    {
                        label: 'Views',
                        data: this.views,
                        borderColor: '#a78bfa',
                        backgroundColor: 'rgba(167,139,250,0.12)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Submissions',
                        data: this.submissions,
                        borderColor: '#1a7efb',
                        backgroundColor: 'rgba(26,126,251,0.10)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.4,
                        fill: true,
                    },
                ],
            };
        },
    },
    data() {
        return {
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: { enabled: true },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 10, autoSkip: true },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            callback: v => Number.isInteger(v) ? v : null,
                        },
                    },
                },
            },
        };
    },
};
</script>

<style scoped>
.ctm-linechart-wrap {
    height: 300px;
}
</style>
