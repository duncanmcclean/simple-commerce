<template>
    <div>
        <!-- Variants -->
        <div class="grid-fieldtype-container mb-4">
            <div class="grid-stacked">
                <div
                    v-for="(variant, variantIndex) in variants"
                    :key="variantIndex"
                    class="bg-grey-10 shadow-sm mb-2 rounded border variants-sortable-item"
                >
                    <div
                        class="grid-item-header"
                    >
                        {{ variant.name || 'Variant' }}
                        <button
                            v-if="variants.length > 1"
                            class="icon icon-cross cursor-pointer"
                            @click="deleteVariant(variantIndex)"
                            :aria-label="__('Delete Variant')" />
                    </div>
                    <publish-fields-container>
                        <publish-field
                            v-for="field in meta.variant_fields"
                            :key="field.handle"
                            :config="field"
                            :value="variant[field.handle]"
                            :meta="meta[field.handle]"
                            :errors="errors(field.handle)"
                            class="p-2 w-1/2"
                            @input="updated(variantIndex, field.handle, $event)"
                            @meta-updated="metaUpdated(field.handle, $event)"
                            @focus="$emit('focus')"
                            @blur="$emit('blur')"
                        />
                    </publish-fields-container>
                </div>
            </div>
            <button class="btn" @click="addVariant">Add Variant</button>
        </div>

        <!-- Variant Prices -->
        <div class="grid-fieldtype-container">
            <table class="grid-table" v-if="options.length > 0">
                <thead>
                    <tr>
                        <grid-header-cell
                            v-for="field in meta.option_fields"
                            :key="field.handle"
                            :field="field"
                        />
                    </tr>
                </thead>
                <sortable-list
                    :value="options"
                    :vertical="true"
                    :item-class="sortableItemClass"
                    :handle-class="sortableHandleClass"
                    @dragstart="$emit('focus')"
                    @dragend="$emit('blur')"
                    @input="(rows) => $emit('sorted', rows)"
                >
                    <tbody slot-scope="{}">
                        <grid-row
                            v-for="(row, index) in options"
                            :key="`row-${index}`"
                            :index="index"
                            :fields="meta.option_fields"
                            :values="row"
                            :can-delete="false"
                            :meta="meta"
                            @updated="(row, value) => $emit('updated', row, value)"
                            @meta-updated="$emit('meta-updated', row._id, $event)"
                            @removed="(row) => $emit('removed', row)"
                            @focus="$emit('focus')"
                            @blur="$emit('blur')"
                        />
                    </tbody>
                </sortable-list>
            </table>
        </div>
    </div>
</template>

<script>
import uniqid from 'uniqid'

// import GridRow from '../../../vendor/statamic/cms/resources/js/components/fieldtypes/grid/Row'
import GridRow from '../statamic/Row'
import SortableList from '../../../vendor/statamic/cms/resources/js/components/sortable/SortableList'
import GridHeaderCell from '../../../vendor/statamic/cms/resources/js/components/fieldtypes/grid/HeaderCell'
import View from '../../../vendor/statamic/cms/resources/js/components/fieldtypes/grid/View'

export default {
    name: 'product-variants-fieldtype',

    mixins: [Fieldtype, View],

    components: {
        GridHeaderCell,
        GridRow,
        SortableList,
    },

    props: ['meta'],

    data() {
        return {
            variants: [
                {
                    name: '',
                    values: [],
                },
            ],
            options: [],
        }
    },

    computed: {
        cartesian() {
            let data = this.variants.filter((variant) => {
                return variant.values.length != 0
            }).flatMap((variant) => [variant.values])

            if (data.length == 0) {
                return []
            }

            return data.reduce((acc, curr) => acc.flatMap(c => curr.map(n => [].concat(c, n))))
        },
    },

    watch: {
        variants: {
            handler: function (value) {
                this.options = this.cartesian.map((item) => {
                    if (typeof item === 'string') {
                        return {
                            variant: item,
                            price: 0,
                        }
                    }

                    return {
                        variant: item.join(', '),
                        price: 0,
                    }
                })

                this.saveData()
            },
            deep: true
        },
    },

    mounted() {
        if (this.value.variants && this.value.options) {
            this.variants = this.value.variants
            this.options = this.value.options
        }
    },

    methods: {
        addVariant() {
            this.variants.push({
                name: '',
                values: [],
            })
        },

        deleteVariant(variantIndex) {
            this.variants.splice(variantIndex, 1)
        },

        saveData() {
            this.$emit('input', {
                variants: this.variants,
                options: this.options,
            });
        },

        errors(fieldHandle) {
            //
        },

        updated(variantIndex, fieldHandle, value) {
            this.variants[variantIndex][fieldHandle] = value
        },

        metaUpdated(fieldHandle, event) {
            //
        },
    },
}
</script>
