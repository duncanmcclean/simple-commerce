<template>
    <div class="flex flex-wrap -mx-2 mb-4">
        <div class="px-2 w-full">
            <div class="px-1" v-if="ready">
                <LineChartGenerator
                    :chart-options="chartOptions"
                    :chart-data="chartData"
                    :chart-id="'orders-chart'"
                    :dataset-id-key="'label'"
                    :cssClasses="''"
                    :styles="{}"
                    :plugins="[]"
                    :width="'400'"
                    :height="'200'"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { Line as LineChartGenerator } from 'vue-chartjs/legacy'

import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    CategoryScale,
    PointElement,
} from 'chart.js'

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    CategoryScale,
    PointElement
)

export default {
    components: {
        LineChartGenerator,
    },

    props: {
        data: Array,
    },

    data() {
        return {
            ready: false,

            chartData: {
                labels: [],
                datasets: [
                    {
                        label: __('Paid Orders'),
                        backgroundColor: '#16a34a',
                        data: [],
                    },
                ],
            },

            chartOptions: {
                responsive: true,
                maintainAspectRatio: false,
            },
        }
    },

    mounted() {
        this.data.forEach((item) => {
            this.chartData.labels.push(item.date)
            this.chartData.datasets[0].data.push(item.count)
        })

        this.ready = true
    },
}
</script>
