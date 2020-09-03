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

        // A bunch of Publish Field events
        errors(fieldHandle) {
            // const state = this.$store.state.publish[this.storeName]
            // if (! state) return []
            // return state.errors[this.errorKey(handle)] || []
        },

        updated(variantIndex, fieldHandle, value) {
            this.variants[variantIndex][fieldHandle] = value
        },

        metaUpdated(fieldHandle, event) {
            // let meta = clone(this.meta)
            // meta[fieldHandle] = value
            // this.$emit('meta-updated', meta)
        },
    },
}
</script>
