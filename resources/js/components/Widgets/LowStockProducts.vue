<template>
    <Listing
        :items
        :columns
        :show-pagination-totals="false"
        :show-pagination-page-links="false"
        :show-pagination-per-page-selector="false"
    >
        <template #initializing>
            <Widget v-bind="widgetProps"><Icon name="loading" /></Widget>
        </template>

        <template #default="{ items, loading }">
            <Widget v-bind="widgetProps">
                <ui-description v-if="!items.length" class="flex-1 flex items-center justify-center">
                    {{ __('No products are low in stock at the moment') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.5 [&_td]:text-sm " :class="{ 'opacity-50': loading }">
                        <TableHead sr-only />
                        <TableBody>
                            <template #cell-title="{ row: entry }">
                                <div class="flex items-center gap-2">
                                    <a :href="entry.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis">{{
                                            entry.title
                                        }}</a>
                                </div>
                            </template>
                            <template #cell-stock="{ row: entry }">
                                <ui-description class="flex justify-end">
                                    {{ entry.stock }} remaining
                                </ui-description>
                            </template>
                        </TableBody>
                    </table>
                </div>
                <template #actions>
                    <Pagination />
                    <slot name="actions" />
                </template>
            </Widget>
        </template>
    </Listing>
</template>

<script>
import {
    Listing,
    Widget,
    Icon,
    ListingTableHead as TableHead,
    ListingTableBody as TableBody,
    ListingPagination as Pagination
} from '@statamic/ui';

export default {
    components: {
        Listing,
        Widget,
        Icon,
        TableHead,
        TableBody,
        Pagination
    },

    props: {
        title: { type: String },
        icon: { type: String },
        initialItems: { type: Array, default: () => [] },
    },

    data() {
        return {
            items: this.initialItems,
            columns: [
                { label: 'Title', field: 'title', visible: true },
                { label: 'Stock', field: 'stock', visible: true },
            ],
        };
    },

    computed: {
        widgetProps() {
            return {
                title: this.title,
                icon: this.icon
            };
        },
    },
};
</script>
