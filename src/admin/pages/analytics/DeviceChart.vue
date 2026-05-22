<template>
    <div class="ctm-device-wrap">
        <Doughnut v-if="hasData" :chart-options="options" :chart-data="chartData" />
        <div v-else class="ctm-no-data">No device data yet</div>
    </div>
</template>

<script>
import { Doughnut } from 'vue-chartjs/legacy';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, ArcElement);

const COLORS = {
    Desktop: '#1a7efb',
    Mobile:  '#10b981',
    Tablet:  '#f59e0b',
};

export default {
    name: 'DeviceChart',
    components: { Doughnut },
    props: {
        devices: { type: Array, default: () => [] },
    },
    computed: {
        hasData() {
            return this.devices.length > 0;
        },
        chartData() {
            return {
                labels: this.devices.map(d => d.device),
                datasets: [{
                    data: this.devices.map(d => parseInt(d.total)),
                    backgroundColor: this.devices.map(d => COLORS[d.device] || '#94a3b8'),
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            };
        },
    },
    data() {
        return {
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    tooltip: { enabled: true },
                },
            },
        };
    },
};
</script>

<style scoped>
.ctm-device-wrap {
    height: 240px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ctm-no-data {
    color: #94a3b8;
    font-size: 14px;
}
</style>
