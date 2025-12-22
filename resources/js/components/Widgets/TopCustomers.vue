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
                    {{ __('There are no top customers') }}
                </ui-description>
                <div class="px-4 py-3">
                    <table class="w-full [&_td]:p-0.5 [&_td]:text-sm " :class="{ 'opacity-50': loading }">
                        <TableHead sr-only />
                        <TableBody>
                            <template #cell-email="{ row: customer }">
                                <div class="flex items-center gap-2">
                                    <a :href="customer.edit_url" class="line-clamp-1 overflow-hidden text-ellipsis">{{
                                            customer.email
                                        }}</a>
                                </div>
                            </template>
                            <template #cell-orders="{ row: customer }">
                                <ui-description class="flex justify-end">
                                    {{ customer.orders_count }} orders
                                </ui-description>
                            </template>
                        </TableBody>
                    </table>
                </div>
                <template #actions>
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
} from '@statamic/cms/ui';

export default {
    components: {
        Listing,
        Widget,
        Icon,
        TableHead,
        TableBody,
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
                { label: 'Email', field: 'email', visible: true },
                { label: 'Orders', field: 'orders', visible: true },
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
