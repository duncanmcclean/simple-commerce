<template>
    <div class="flex-1 card p-2 overflow-hidden h-full">
        <div class="flex justify-between items-center p-2">
            <h2 class="font-semibold text-base">
                {{ __('Recent Orders') }}
            </h2>
        </div>

        <ul v-if="data.length">
            <li
                v-for="order in data"
                :key="order.id"
                class="px-2 py-1.5 flex items-center justify-between hover:bg-grey-10"
            >
                <a :href="order.edit_url"
                    ><strong class="font-medium"
                        >#{{ order.order_number }}</strong
                    >
                    - {{ order.grand_total }}</a
                >
                <span class="text-sm">{{ order.paid_at }}</span>
            </li>
        </ul>

        <p v-else class="px-2 pt-1 pb-2" style="color: #737f8c">
            {{ __('No results') }}
        </p>
    </div>
</template>

<script>
export default {
    props: {
        data: Array,
    },
}
</script>
