<template>
    <div>
        <!-- Variants -->
        <div class="grid-fieldtype-container mb-16">
            <div class="grid-stacked">
                <div
                    v-for="(variant, variantIndex) in variants"
                    :key="variantIndex"
                    class="bg-grey-10 shadow-sm mb-4 rounded border variants-sortable-item"
                >
                    <div class="grid-item-header rounded-t">
                        {{ variant.name || 'Variant' }}
                        <button
                            v-if="variants.length > 1"
                            class="icon icon-cross cursor-pointer"
                            @click="deleteVariant(variantIndex)"
                            :aria-label="__('Delete Variant')"
                        >
                            <svg-icon name="micro/trash" class="w-4 h-4 text-gray-600 group-hover:text-gray-900" />
                        </button>
                    </div>
                    <publish-fields-container>
                        <publish-field
                            v-for="field in meta.variant_fields"
                            :key="field.handle"
                            :config="field"
                            :value="variant[field.handle]"
                            :meta="meta[field.handle]"
                            :errors="errors(field.handle)"
                            class="p-3 w-1/2"
                            @input="updated(variantIndex, field.handle, $event)"
                            @meta-updated="metaUpdated(field.handle, $event)"
                            @focus="$emit('focus')"
                            @blur="$emit('blur')"
                        />
                    </publish-fields-container>
                </div>
            </div>
            <button class="btn" @click="addVariant">
                {{ __('Add Variant') }}
            </button>
        </div>

        <!-- Variant Options -->
        <div class="grid-fieldtype-container">
            <div class="grid-stacked">
                <VariantOptionRow
                    v-for="(option, index) in options"
                    :key="index"
                    :option="option"
                    :index="index"
                    :meta="meta"
                    :values="value.options[index]"
                    :fieldPathPrefix="handle + '.options.' + index"
                    @updated="optionsUpdated"
                    @metaUpdated="metaUpdated"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                />
            </div>
        </div>
    </div>
</template>

<script>
import GridRow from '../../../statamic/Row.vue'
import SortableList from '../../../../../vendor/statamic/cms/resources/js/components/sortable/SortableList.vue'
import GridHeaderCell from '../../../../../vendor/statamic/cms/resources/js/components/fieldtypes/grid/HeaderCell.vue'
import View from '../../../statamic/View.vue'
import VariantOptionRow from './VariantOptionRow.vue'

export default {
    name: 'product-variants-fieldtype',

    mixins: [Fieldtype, View],

    components: {
        GridHeaderCell,
        GridRow,
        SortableList,
        VariantOptionRow,
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

            canWatchVariants: true,
        }
    },

    computed: {
        cartesian() {
            let data = this.variants
                .filter((variant) => {
                    return variant.values.length != 0
                })
                .flatMap((variant) => [variant.values])

            if (data.length == 0) {
                return []
            }

            return data.reduce((acc, curr) =>
                acc.flatMap((c) => curr.map((n) => [].concat(c, n)))
            )
        },

        baseContainer() {
            let parent = this.$parent

            while (
                parent &&
                parent.$options._componentTag !== 'publish-container'
            ) {
                parent = parent.$parent
            }

            return parent
        },
    },

    mounted() {
        if (this.value.variants && this.value.options) {
            this.canWatchVariants = false
            this.variants = this.value.variants
            this.options = this.value.options
            this.canWatchVariants = true
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
            })

            this.baseContainer.saved()
        },

        errors(fieldHandle) {
            //
        },

        updated(variantIndex, fieldHandle, value) {
            this.variants[variantIndex][fieldHandle] = value
        },

        optionsUpdated(index, value) {
            this.options[index] = value
        },

        metaUpdated(fieldHandle, event) {
            //
        },
    },

    watch: {
        variants: {
            handler(value) {
                if (this.canWatchVariants === false) {
                    return
                }

                this.options = this.cartesian.map((item) => {
                    let key = typeof item === 'string' ? item : item.join('_')
                    let variantName =
                        typeof item === 'string' ? item : item.join(', ')

                    let existingData = this.value.options.filter((option) => {
                        return option.key === key
                    })[0]

                    if (existingData === undefined) {
                        existingData = {
                            price: 0,
                        }

                        Object.entries(this.meta.option_field_defaults).forEach(
                            ([key, value]) => {
                                existingData[key] = value
                            }
                        )
                    }

                    return {
                        key: key,
                        variant: variantName,
                        ...existingData,
                    }
                })

                this.saveData()
            },
            deep: true,
        },
    },
}
</script>