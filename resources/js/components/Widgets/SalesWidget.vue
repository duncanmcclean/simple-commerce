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
            <span v-if="count === 'sevenDays'" class="leading-none text-sm">{{ sevenDaysTotal }} total in sales</span>
            <span v-if="count === 'fourteenDays'" class="leading-none text-sm">{{ fourteenDaysTotal }} total in sales</span>
            <span v-if="count === 'thirtyDays'" class="leading-none text-sm">{{ thirtyDaysTotal }} total in sales</span>
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
}
</script>
