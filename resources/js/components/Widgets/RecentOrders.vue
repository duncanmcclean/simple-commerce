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
                    {{ __('No recent orders') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.5 [&_td]:text-sm " :class="{ 'opacity-50': loading }">
                        <TableHead sr-only />
                        <TableBody>
                            <template #cell-order_number="{ row: order }">
                                <div class="flex items-center gap-2">
                                    <a :href="order.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis">
                                        #{{ order.order_number }}
                                    </a>
                                </div>
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
} from '@statamic/cms/ui';

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
                { label: 'Order Number', field: 'order_number', visible: true },
                { label: 'Grand Total', fieldtype: 'money', field: 'grand_total', visible: true },
                { label: 'Date', fieldtype: 'date', field: 'date', visible: true },
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
