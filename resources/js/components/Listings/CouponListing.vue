<script setup>
import { DropdownItem, Listing } from '@statamic/ui';
import { ref } from 'vue';

const props = defineProps({
    actionUrl: String,
    sortColumn: String,
    sortDirection: String,
    columns: Array,
    filters: Array,
});

const preferencesPrefix = 'simple_commerce.coupons';
const requestUrl = cp_url('simple-commerce/coupons/listing-api');
const items = ref(null);
const page = ref(null);
const perPage = ref(null);

function requestComplete({ items: newItems, parameters }) {
    items.value = newItems;
    page.value = parameters.page;
    perPage.value = parameters.perPage;
}
</script>

<template>
    <Listing
        ref="listing"
        :url="requestUrl"
        :columns="columns"
        :action-url="actionUrl"
        :sort-column="sortColumn"
        :sort-direction="sortDirection"
        :preferences-prefix="preferencesPrefix"
        :filters="filters"
        push-query
        @request-completed="requestComplete"
    >
        <template #cell-code="{ row: coupon, isColumnVisible }">
            <a class="title-index-field" :href="coupon.edit_url" @click.stop>
                <span v-text="coupon.code" />
            </a>
        </template>
        <template #cell-value="{ row: coupon, isColumnVisible }">
            <span v-text="coupon.discount_text" />
        </template>
        <template #prepended-row-actions="{ row: coupon }">
            <DropdownItem :text="__('Edit')" :href="coupon.edit_url" icon="edit" v-if="coupon.editable" />
        </template>
    </Listing>
</template>