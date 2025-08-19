<template>
    <div>
        <div
            v-if="initializing"
            class="flex flex-col items-center justify-center text-center p-1"
        >
            <loading-graphic />
        </div>

        <div v-else>
            <p v-if="value == null" class="text-sm p-1">
                {{ __("Product doesn't support variants.") }}
            </p>

            <p v-else-if="productVariantsData == null" class="text-sm p-1">
                {{ __('No product selected.') }}
            </p>

            <Select
                v-else-if="
                    productVariantsData &&
                    productVariantsData.purchasable_type === 'variant'
                "
                class="w-full"
                :options="productVariantOptions"
                :disabled="readOnly"
                :model-value="variant.variant"
                @update:model-value="variant.variant = $event"
            />

            <p
                v-else-if="
                    productVariantsData &&
                    productVariantsData.purchasable_type === 'product'
                "
                class="text-sm p-1"
            >
                {{ __("Product doesn't support variants.") }}
            </p>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import { FieldtypeMixin } from '@statamic/cms';
import { Select, publishContextKey } from '@statamic/cms/ui'

export default {
    name: 'product-variant-fieldtype',

    components: {
        Select,
    },

    mixins: [FieldtypeMixin],

    inject: {
        publishContext: { from: publishContextKey },
    },

    data() {
        return {
            initializing: true,
            variant: this.value,

            productVariantsData: null,
        }
    },

    computed: {
        product() {
            let index = this.fieldPathKeys[this.fieldPathKeys.length - 1];

            return this.publishContext.values.value.items[index].product[0] || null;
        },

        productVariantOptions() {
            return this.productVariantsData.variants.options.map((variant) => {
                return {
                    label: variant.variant,
                    value: variant.key,
                }
            })
        },
    },

    methods: {
        getProductVariants() {
            if (!this.product) {
                this.initializing = false
                return
            }

            axios
                .post(this.meta.api, { product: this.product })
                .then((response) => {
                    this.productVariantsData = response.data
                    this.initializing = false
                })
                .catch((error) => {
                    console.error(
                        'There was an error fetching variants for this product.'
                    )
                })
        },
    },

    mounted() {
        if (this.value == null) {
            this.initializing = false
        }

        if (this.variant && !this.variant.product) {
            this.variant.product = this.product
        }

        this.getProductVariants()
    },

    watch: {
        product() {
            this.variant = {
                variant: null,
                product: this.product,
            }

            this.productVariantsData = null
            this.getProductVariants()
        },
    },
}
</script>
