<template>
    <div>
        <data-list
                :rows="rows"
                :columns="columns"
                :search="false"
                :search-query="search"
                :sort="false"
                :sort-colunm="sortColumn"
                :sort-direction="sortDirection"
        >
            <div slot-scope="{ hasSelections }">
                <div class="card p-0">
                    <div class="data-list-header min-h-16">
                        <data-list-search v-model="search" />
                    </div>

                    <div v-if="rows.length === 0" class="p-3 text-center text-grey-50" v-text="__('No results')" />

                    <data-list-table
                            v-else
                            @sorted="sorted"
                    >
                        <template slot="cell-title" slot-scope="{ row: product }">
                            <div class="flex items-center">
                                <div class="little-dot mr-1" :class="[product.enabled ? 'bg-green' : 'bg-grey-40']"></div>
                                <a @click.stop="redirect(product.edit_url)">{{ product.title }}</a>
                            </div>
                        </template>

                        <template slot="cell-slug" slot-scope="{ row : product }">
                            <span class="font-mono text-2xs">{{ product.slug }}</span>
                        </template>

                        <template slot="actions" slot-scope="{ row : product, index }">
                            <dropdown-list>
                                <dropdown-item :text="__('Edit')" :redirect="product.edit_url"></dropdown-item>
                                <dropdown-item :text="__('Delete')" :redirect="product.delete_url"></dropdown-item>
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
            items: String
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

            actionStarted() {
                //
            },

            actionCompleted() {
                //
            },

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
