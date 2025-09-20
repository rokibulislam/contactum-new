<template>
    <div class="chart-container">
        <div class="chartjs-size-monitor"> </div>
        <Bar
            :chart-options="options"
            :chart-data="chartData"
        />
    </div>
</template>

<script>
import { Bar } from 'vue-chartjs/legacy'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)



export default {
    name: "Chartview",
    props: ['entries', 'forms',
      'labels',
      'datasets'
    ],
    components: { Bar },
    computed: {
      chartData() {
          return {
            labels: this.labels,
            datasets: [{
              label: 'Submission Count',
              data: this.datasets,
              backgroundColor: '#1a7efb',   // Blue bars
              borderColor: '#1559b0',       // Darker blue border
              hoverBackgroundColor: '#3b99fc', // Lighter blue on hover
              borderWidth: 1
            }
            ]
          }
      }
    },
    data: function() {
        return {
          options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
              display: false,
              labels: {
                fontColor: '#1a7efb',
                boxWidth: 16,
                fontSize: 14
              }
            },
            scales: {
              yAxes: [
                {
                  id: 'byDate',
                  type: 'linear',
                  position: 'left',
                  gridLines: {
                    color: '#1a7efb',
                    drawOnChartArea: true,
                    zeroLineColor: '#1a7efb'
                  },
                  ticks: {
                    beginAtZero: true,
                    userCallback: function(label, index, labels) {
                      // when the floored value is the same as the value we have a whole number
                      if (Math.floor(label) === label) {
                        return label;
                      }
                    }
                  }
                }
              ],
              xAxes: [
                {
                  gridLines: {
                    color: '#1a7efb',
                    drawOnChartArea: true,
                    zeroLineColor: '#1a7efb'
                  },
                  ticks: {
                    beginAtZero: true,
                    autoSkip: true,
                    maxTicksLimit: 10
                  }
                }
              ]
            },
            drawBorder: false,
            layout: {
              padding: {
                left: 0,
                right: 0,
                top: 0,
                bottom: 20
              }
            }
          }
        };
    },
}
</script>


<style scoped>
.chart-container {
    background-color: #fff;
    border: 1px solid transparent;
    border-radius: 8px;
    box-shadow: 0 2px 3px 0 hsla(0, 0%, 51%, .1);
    padding: 24px;
  width: 100%;
  height: 400px; /* Set desired height here */
  margin-bottom: 30px;
}

.chartjs-size-monitor {
  position: absolute;
  inset: 0px;
  overflow: hidden;
  pointer-events: none;
  visibility: hidden;
  z-index: -1;
}

</style>