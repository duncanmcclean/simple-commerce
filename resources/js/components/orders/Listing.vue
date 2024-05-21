<template>
    <div>
        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            ref="dataList"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card overflow-hidden p-0 relative">
                    <div v-if="!reordering" class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b">

                        <data-list-filter-presets
                            ref="presets"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                        />

                        <data-list-search class="h-8 mt-2 min-w-[240px] w-full" ref="search" v-model="searchQuery" :placeholder="searchPlaceholder" />

                        <div class="flex space-x-2 mt-2">
                            <button class="btn btn-sm rtl:mr-2 ltr:ml-2" v-text="__('Reset')" v-show="isDirty" @click="$refs.presets.refreshPreset()" />
                            <button class="btn btn-sm rtl:mr-2 ltr:ml-2" v-text="__('Save')" v-show="isDirty" @click="$refs.presets.savePreset()" />
                            <data-list-column-picker :preferences-key="preferencesKey('columns')" />
                        </div>
                    </div>
                    <div v-show="!reordering">
                        <data-list-filters
                            ref="filters"
                            :filters="filters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :active-filter-badges="activeFilterBadges"
                            :active-count="activeFilterCount"
                            :search-query="searchQuery"
                            :is-searching="true"
                            :saves-presets="true"
                            :preferences-prefix="preferencesPrefix"
                            @changed="filterChanged"
                            @saved="$refs.presets.setPreset($event)"
                            @deleted="$refs.presets.refreshPresets()"
                        />
                    </div>

                    <div v-show="items.length === 0" class="p-6 text-center text-gray-500" v-text="__('No results')" />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        :context="actionContext"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                    <div class="overflow-x-auto overflow-y-hidden">
                        <data-list-table
                            v-show="items.length"
                            :allow-bulk-actions="!reordering"
                            :loading="loading"
                            :reorderable="reordering"
                            :sortable="!reordering"
                            :toggle-selection-on-row-click="true"
                            @sorted="sorted"
                            @reordered="reordered"
                        >
                            <template slot="cell-order_number" slot-scope="{ row: order }">
                                <a class="title-index-field inline-flex items-center" :href="order.edit_url" @click.stop>
                                    <span v-text="order.order_number" />
                                </a>
                            </template>
                            <template slot="actions" slot-scope="{ row: order, index }">
                                <dropdown-list placement="left-start">
                                    <dropdown-item :text="__('Edit')" :redirect="order.edit_url" v-if="order.editable" />
                                    <div class="divider" v-if="order.actions.length" />
                                    <data-list-inline-actions
                                        :item="order.order_number"
                                        :url="actionUrl"
                                        :actions="order.actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </dropdown-list>
                            </template>
                        </data-list-table>
                    </div>
                </div>
                <data-list-pagination
                    class="mt-6"
                    :resource-meta="meta"
                    :per-page="perPage"
                    :show-totals="true"
                    @page-selected="selectPage"
                    @per-page-changed="changePerPage"
                />
            </div>
        </data-list>
    </div>
</template>

<script>
import Listing from '../../../../vendor/statamic/cms/resources/js/components/Listing.vue'

export default {
    mixins: [Listing],

    data() {
        return {
            listingKey: 'orders',
            preferencesPrefix: `simple-commerce.orders`,
            requestUrl: cp_url(`orders`),
            // pushQuery: true, // todo: reenable this at some point
        }
    },

    methods: {
        columnShowing(column) {
            return this.visibleColumns.find(c => c.field === column);
        },
    }
}
</script>