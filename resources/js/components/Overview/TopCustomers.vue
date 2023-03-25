<template>
    <div class="flex-1 card p-2 overflow-hidden h-full">
        <div class="flex justify-between items-center p-2">
            <h2 class="font-semibold text-base">
                {{ __('Top Customers') }}
            </h2>
        </div>

        <ul v-if="data.length">
            <li
                v-for="customer in data"
                :key="customer.id"
                class="px-2 py-1.5 flex items-center justify-between hover:bg-grey-10"
            >
                <a :href="customer.edit_url">{{ customer.email }}</a>
                <span class="text-sm"
                    >{{ customer.orders_count }}
                    <span
                        v-text="
                            customer.orders_count === 1
                                ? __('order')
                                : __('orders')
                        "
                    ></span>
                </span>
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
