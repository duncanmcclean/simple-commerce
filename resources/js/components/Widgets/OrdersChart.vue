<template>
    <Widget :title :icon>
        <div class="flex flex-wrap -mx-2 mb-4">
            <div class="px-2 w-full">
                <div class="px-1">
                    <canvas ref="chartCanvas"></canvas>
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
    LineController,
    Title,
    Tooltip,
    Legend
} from 'chart.js'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    LineController,
    Title,
    Tooltip,
    Legend
)

export default {
    components: {
        Widget,
    },

    props: {
        title: { type: String },
        icon: { type: String },
        data: Array,
    },

    data() {
        return {
            chart: null,
        }
    },

    methods: {
        createChart() {
            const ctx = this.$refs.chartCanvas.getContext('2d');

            const labels = [];
            const chartData = [];

            this.data.forEach((item) => {
                labels.push(item.date);
                chartData.push(item.count);
            });

            this.chart = new ChartJS(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: __('Paid Orders'),
                            backgroundColor: '#16a34a',
                            borderColor: '#16a34a',
                            data: chartData,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        },
    },

    mounted() {
        this.createChart();
    },

    beforeUnmount() {
        this.chart?.destroy();
    },
}
</script>
