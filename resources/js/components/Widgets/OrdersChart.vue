<template>
    <Widget :title :icon>
        <div class="flex flex-wrap -mx-2 mb-4">
            <div class="px-2 w-full">
                <div class="px-1" v-if="ready">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>
        </div>

        <template #actions>
            <slot name="actions" />
        </template>
    </Widget>
</template>

<script>
import { Widget } from '@statamic/cms/ui';

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
)

export default {
    components: {
        Widget,
        Line,
    },

    props: {
        title: { type: String },
        icon: { type: String },
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
