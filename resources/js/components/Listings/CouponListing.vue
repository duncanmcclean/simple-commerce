<template>
    <div>
        <div v-if="initializing" class="card loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!initializing"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card overflow-hidden p-0 relative">
                    <div
                        class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b"
                    >
                        <data-list-filter-presets
                            ref="presets"
                            v-show="alwaysShowFilters || !showFilters"
                            :active-preset="activePreset"
                            :active-preset-payload="activePresetPayload"
                            :active-filters="activeFilters"
                            :has-active-filters="hasActiveFilters"
                            :preferences-prefix="preferencesPrefix"
                            :search-query="searchQuery"
                            @selected="selectPreset"
                            @reset="filtersReset"
                            @hide-filters="filtersHide"
                            @show-filters="filtersShow"
                        />

                        <data-list-search
                            class="h-8 mt-2 min-w-[240px] w-full"
                            ref="search"
                            v-model="searchQuery"
                            :placeholder="searchPlaceholder"
                        />

                        <div class="flex space-x-2 mt-2">
                            <button
                                class="btn btn-sm ml-2"
                                v-text="__('Reset')"
                                v-show="isDirty"
                                @click="$refs.presets.refreshPreset()"
                            />
                            <button
                                class="btn btn-sm ml-2"
                                v-text="__('Save')"
                                v-show="isDirty"
                                @click="$refs.presets.savePreset()"
                            />
                            <data-list-column-picker
                                :preferences-key="preferencesKey('columns')"
                            />
                        </div>
                    </div>

                    <div>
                        <data-list-filters
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

                    <div
                        v-show="items.length === 0"
                        class="p-6 text-center text-gray-500"
                        v-text="__('No results')"
                    />

                    <data-list-bulk-actions
                        :url="actionUrl"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />

                    <div class="overflow-x-auto overflow-y-hidden">
                        <data-list-table
                            v-show="items.length"
                            :allow-bulk-actions="true"
                            :loading="loading"
                            :reorderable="false"
                            :sortable="true"
                            :toggle-selection-on-row-click="true"
                            :allow-column-picker="true"
                            :column-preferences-key="preferencesKey('simple-commerce.columns')"
                            @sorted="sorted"
                        >
                            <template slot="cell-code" slot-scope="{ row, value }">
                                <div class="title-index-field">
                                    <a :href="row.edit_url" @click.stop>{{ row.code }}</a>
                                </div>
                            </template>

                            <template slot="cell-value" slot-scope="{ row, value }">
                                <div>
                                    {{ row.discount_text }}
                                </div>
                            </template>

                            <template
                                slot="actions"
                                slot-scope="{ row, index }"
                            >
                                <dropdown-list
                                    v-if="
                                        canEditRow(row) ||
                                        row.actions.length
                                    "
                                >
                                    <dropdown-item
                                        v-if="canEditRow(row)"
                                        :text="__('Edit')"
                                        :redirect="row.edit_url"
                                    />

                                    <div
                                        class="divider"
                                        v-if="canEditRow(row) && row.actions.length"
                                    />

                                    <data-list-inline-actions
                                        :item="row.id"
                                        :url="actionUrl"
                                        :actions="row.actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </dropdown-list>
                                <div v-else class="w-10 block"></div>
                            </template>
                        </data-list-table>
                    </div>
                </div>

                <data-list-pagination
                    class="mt-3"
                    :resource-meta="meta"
                    :per-page="perPage"
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

    props: {
        listingConfig: Object,
        initialColumns: Array,
        actionUrl: String,
    },

    data() {
        return {
            listingKey: 'code',
            preferencesPrefix: this.listingConfig.preferencesPrefix ?? 'simple-commerce.coupons',
            requestUrl: this.listingConfig.requestUrl,
            columns: this.initialColumns,
            meta: {},
        }
    },

    methods: {
        canEditRow(row) {
            return row.editable
        },

        columnShowing(column) {
            return this.visibleColumns.find(c => c.field === column);
        },
    },
}
</script>
