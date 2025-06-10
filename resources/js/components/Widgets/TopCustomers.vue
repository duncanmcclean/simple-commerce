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
                <template #cell-email="{ row: customer }">
                    <a
                        :href="customer.edit_url"
                        class="line-clamp-1 overflow-hidden text-ellipsis"
                    >
                        {{ customer.email }}
                    </a>
                </template>
                <template #cell-orders="{ row: customer }">
                    <div
                        class="text-end font-mono text-xs whitespace-nowrap text-gray-500 antialiased"
                        v-html="`${customer.orders_count} orders`"
                    />
                </template>
            </data-list-table>
        </data-list>

        <p v-if="!initializing && !items.length" class="p-3 text-center text-sm text-gray-600">
            {{ __('There are no top customers') }}
        </p>

        <template #actions>
            <slot name="actions" />
        </template>
    </Widget>
</template>

<script>
import Listing from '@statamic/components/Listing.vue';
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
                { label: 'Email', field: 'email', visible: true },
                { label: 'Orders', field: 'orders', visible: true },
            ],
            items: this.initialItems,
            initializing: false,
            listingKey: 'top_customers',
        };
    },
};
</script>
