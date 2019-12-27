<template>
    <div>
        <data-list
            :rows="rows"
            :columns="columns"
            :search="false"
            :search-query="search"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card p-0">
                    <div class="data-list-header min-h-16">
                        <data-list-search v-model="search" />
                    </div>

                    <div v-if="rows.length === 0" class="p-3 text-center text-grey-50">No results</div>

                    <data-list-table
                            v-else
                            @sorted="sorted"
                    >
                        <template v-if="primary === 'title'" slot="cell-title" slot-scope="{ row: item }">
                            <div class="flex items-center">
                                <div v-if="item.enabled != null" class="little-dot mr-1" :class="[item.enabled ? 'bg-green' : 'bg-grey-40']"></div>
                                <a @click.stop="redirect(item.edit_url)">{{ item.title }}</a>
                            </div>
                        </template>

                        <template v-if="primary === 'name'" slot="cell-slug" slot-scope="{ row: item }">
                            <div class="flex items-center">
                                <div v-if="item.enabled != null" class="little-dot mr-1" :class="[item.enabled ? 'bg-green' : 'bg-grey-40']"></div>
                                <a @click.stop="redirect(item.edit_url)">{{ item.name }}</a>
                            </div>
                        </template>

                        <template v-if="primary === 'slug'" slot="cell-slug" slot-scope="{ row: item }">
                            <div class="flex items-center">
                                <div
                                        v-if="item.status != null"
                                        class="little-dot mr-1"
                                        :class="[
                                            item.status === 'created' ? 'bg-blue' :
                                            item.status === 'paid' ? 'bg-orange' :
                                            item.status === 'cancelled' ? 'bg-red' :
                                            item.status === 'fulfilled' ? 'bg-green' :
                                            item.status === 'returned' ? 'bg-yellow' :
                                            'bg-grey-40'
                                        ]"
                                ></div>

                                <a @click.stop="redirect(item.edit_url)">{{ item.slug }}</a>
                            </div>
                        </template>

                        <template v-else slot="cell-slug" slot-scope="{ row : item }">
                            <span class="font-mono text-2xs">{{ item.slug }}</span>
                        </template>

                        <template slot="actions" slot-scope="{ row : item, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('Edit')" :redirect="item.edit_url"></dropdown-item>
                                <dropdown-item class="warning" :text="__('Delete')" :redirect="item.delete_url"></dropdown-item>
                            </dropdown-list>
                        </template>
                    </data-list-table>
                </div>
            </div>
        </data-list>
    </div>
</template>
<script>
    export default {
        props: {
            model: String,
            cols: String,
            items: String,
            primary: String
        },

        data() {
            return {
                rows: JSON.parse(this.items),
                columns: JSON.parse(this.cols),

                search: '',
                sortColumn: 'title',
                sortDirection: 'asc',

                requestUrl: cp_url(this.model+'/search')
            }
        },

        methods: {
            redirect(url) {
                location.href = url;
                return;
            },

            actionStarted() {},
            actionCompleted() {},

            sorted(column, direction) {
                this.sortColumn = column;
                this.sortDirection = direction;
            },

            request() {
                if (this.source) this.source.cancel();
                this.source = this.$axios.CancelToken.source();

                this.$axios.get(this.requestUrl, {
                    params: this.parameters,
                    cancelToken: this.source.token
                }).then(response => {
                    this.sortColumn = response.data.meta.sortColumn;
                    this.rows = response.data.data;
                    this.meta = response.data.meta;
                }).catch(e => {
                    if (this.$axios.isCancel(e)) return;
                    this.$toast.error(e.response ? e.response.data.message : __('Something went wrong'), { duration: null });
                })
            },
        },

        computed: {
            parameters() {
                return Object.assign({
                    search: this.search,
                }, this.additionalParameters);
            }
        },

        watch: {
            parameters: {
                deep: true,

                handler(after, before) {
                    // A change to the search query would trigger both watchers.
                    // We only want the searchQuery one to kick in.
                    if (before.search !== after.search) return;

                    if (JSON.stringify(before) === JSON.stringify(after)) return;
                    this.request();
                }
            },

            search(query) {
                this.sortColumn = null;
                this.sortDirection = null;
                this.request();
            }
        }
    }
</script>
