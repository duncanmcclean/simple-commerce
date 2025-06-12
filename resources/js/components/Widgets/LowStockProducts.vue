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
                <template #cell-title="{ row: entry }">
                    <a
                        :href="entry.edit_url"
                        class="line-clamp-1 overflow-hidden text-ellipsis"
                    >
                        {{ entry.title }}
                    </a>
                </template>
                <template #cell-stock="{ row: entry }">
                    <div
                        class="text-end font-mono text-xs whitespace-nowrap text-gray-500 antialiased"
                        v-html="`${entry.stock} remaining`"
                    />
                </template>
            </data-list-table>
        </data-list>

        <p v-if="!initializing && !items.length" class="p-3 text-center text-sm text-gray-600">
            {{ __('No products are low in stock at the moment') }}
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
                { label: 'Title', field: 'title', visible: true },
                { label: 'Stock', field: 'stock', visible: true },
            ],
            items: this.initialItems,
            initializing: false,
            listingKey: 'low_stock_products',
        };
    },
};
</script>
