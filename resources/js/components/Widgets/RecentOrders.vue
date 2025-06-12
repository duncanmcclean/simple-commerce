<template>
    <Widget :title :icon>
        <data-list v-if="!initializing && items.length" :rows="items" :columns="cols" :sort="false" class="w-full">
            <div v-if="initializing" class="loading">
                <loading-graphic />
            </div>

            <data-list-table
                v-else
                :loading="loading"
                unstyled
                class="[&_td]:px-0.5 [&_td]:py-0.75 [&_td]:text-sm [&_thead]:hidden"
            >
                <template #cell-order_number="{ row: order }">
                    <div class="flex items-center gap-2">
                        <a
                            :href="order.edit_url"
                            class="line-clamp-1 overflow-hidden text-ellipsis"
                        >
                            #{{ order.order_number }}
                        </a>
                        <span class="text-xs text-gray-500">({{ order.grand_total }})</span>
                    </div>
                </template>
                <template #cell-date="{ row: order }">
                    <div
                        class="text-end font-mono text-xs whitespace-nowrap text-gray-500 antialiased"
                        v-html="formatDate(order.date)"
                    />
                </template>
            </data-list-table>
        </data-list>

        <p v-if="!initializing && !items.length" class="p-3 text-center text-sm text-gray-600">
            {{ __('No recent orders') }}
        </p>

        <template #actions>
            <slot name="actions" />
        </template>
    </Widget>
</template>

<script>
import Listing from '@statamic/components/Listing.vue';
import { DateFormatter } from 'statamic';
import { Widget } from '@statamic/ui';

export default {
    mixins: [Listing],

    components: {
        Widget,
    },

    props: {
        title: { type: String },
        icon: { type: String },
        initialItems: { type: Array, default: () => [] },
    },

    data() {
        return {
            cols: [
                { label: 'Order Number', field: 'order_number', visible: true },
                { label: 'Date', field: 'date', visible: true },
            ],
            items: this.initialItems,
            initializing: false,
            listingKey: 'recent_orders',
        };
    },

    methods: {
        formatDate(value) {
            return DateFormatter.format(value, { relative: 'hour' }).toString();
        },
    },
};
</script>
