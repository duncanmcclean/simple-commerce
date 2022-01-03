<template>
    <div>
        <div class="flex justify-between items-center mb-2">
            <h3 class="font-bold text-grey">Sales</h3>

            <select class="text-xs" v-model="count">
                <option value="sevenDays">7 Days</option>
                <option value="fourteenDays">14 Days</option>
                <option value="thirtyDays">30 Days</option>
            </select>
        </div>

        <div v-if="count === 'sevenDays'" class="text-4xl mb-2">{{ sevenDaysCount }}</div>
        <div v-if="count === 'fourteenDays'" class="text-4xl mb-2">{{ fourteenDaysCount }}</div>
        <div v-if="count === 'thirtyDays'" class="text-4xl mb-2">{{ thirtyDaysCount }}</div>

        <div class="flex items-center">
            <span class="leading-none text-sm" v-html="totalSalesText"></span>
        </div>
    </div>
</template>

<script>
export default {
    name: 'sales-widget',

    props: [
        'sevenDaysCount',
        'sevenDaysTotal',
        'fourteenDaysCount',
        'fourteenDaysTotal',
        'thirtyDaysCount',
        'thirtyDaysTotal',
    ],

    data() {
        return {
            count: 'sevenDays',
        }
    },

    computed: {
        totalSalesText() {
            if (this.count === 'sevenDays') {
                return this.sevenDaysTotal + ' total in sales'
            }

            if (this.count === 'fourteenDays') {
                return this.fourteenDaysTotal + ' total in sales'
            }

            if (this.count === 'thirtyDays') {
                return this.thirtyDaysTotal + ' total in sales'
            }

            return 'Unknown'
        },
    },
}
</script>
