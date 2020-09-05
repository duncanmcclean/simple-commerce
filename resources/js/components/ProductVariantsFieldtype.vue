<template>
    <div>
        <!-- Variants -->
        <div class="grid-fieldtype-container">
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
                            class="p-2"
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
                            :can-delete="true"
                            @updated="(row, value) => $emit('updated', row, value)"
                            @meta-updated="$emit('meta-updated', row._id, $event)"
                            @duplicate="(row) => $emit('duplicate', row)"
                            @removed="(row) => $emit('removed', row)"
                            @focus="$emit('focus')"
                            @blur="$emit('blur')"
                        />
                    </tbody>
                </sortable-list>
            </table>

            <button class="btn">Add Option</button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'product-variants-fieldtype',

    // 1. It should allow the user to setup variants (done)
    // 2. the user should be able to setup a price & other configurable fields per variant
    // 2. the user should be able to toggle if a variant is available for purchase

    props: ['meta'],

    data() {
        return {
            variants: [
                {
                    name: '',
                    values: [],
                },
            ],

            options: [
                {'variant': 'test', 'price': 500},
            ],
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

        // A bunch of Publish Field events
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
