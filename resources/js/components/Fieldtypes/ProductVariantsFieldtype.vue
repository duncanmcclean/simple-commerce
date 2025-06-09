<template>
    <div>
        <!-- Variants -->
        <div class="flex flex-col gap-y-2 mb-8">
            <div
                v-for="(variant, index) in variants"
                :key="index"
                class="dark:border-dark-900 rounded-sm border shadow-sm"
            >
                <PublishContainer
                    :name="`product-variant-${index}`"
                    :blueprint="meta.variants.fields"
                    :values="variant"
                    :meta="meta.variants.existing[index]"
                    :errors="getErrorsForVariant(index)"
                >
                    <FieldsProvider :fields="meta.variants.fields">
                        <Fields class="p-4" />
                    </FieldsProvider>
                </PublishContainer>
            </div>

            <div>
                <Button :text="__('Add Variant')" @click="addVariant" />
            </div>
        </div>

        <!-- Variant Options -->
        <div class="flex flex-col gap-y-2">
            <div
                v-for="(option, index) in options"
                :key="index"
                class="dark:border-dark-900 rounded-sm border shadow-sm"
            >
                <PublishContainer
                    v-if="meta.options.existing[index]"
                    :name="`product-variant-option-${index}`"
                    :blueprint="meta.options.fields"
                    :values="option"
                    :meta="meta.options.existing[index]"
                    :errors="getErrorsForOption(index)"
                >
                    <FieldsProvider :fields="meta.options.fields">
                        <Fields class="p-4" />
                    </FieldsProvider>
                </PublishContainer>
            </div>
        </div>
    </div>
</template>

<script>
import { Fieldtype, ValidatesFieldConditions } from 'statamic';
import Fields from '@statamic/components/ui/Publish/Fields.vue';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import PublishContainer from '@statamic/components/ui/Publish/Container.vue';
import { Button } from '@statamic/ui'

export default {
    mixins: [
        Fieldtype,
        ValidatesFieldConditions,
    ],

    components: {
        Button,
        Fields,
        FieldsProvider,
        PublishContainer,
    },

    inject: ['store'],

    computed: {
        variants() {
            return this.value.variants || [];
        },

        options() {
            return this.value.options || [];
        },

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
    },

    methods: {
        addVariant() {
            this.update({
                variants: [
                    ...this.value.variants,
                    {
                        name: '',
                        values: [],
                    }
                ],
                options: this.value.options,
            });
        },

        getErrorsForVariant(index) {
            return Object.entries(this.store.errors)
                .filter(([key]) => key.startsWith(`${this.handle}.variants.${index}.`))
                .reduce((acc, [key, error]) => {
                    const newKey = key.replace(`${this.handle}.variants.${index}.`, '');
                    acc[newKey] = error;
                    return acc;
                }, {});
        },

        getErrorsForOption(index) {
            return Object.entries(this.store.errors)
                .filter(([key]) => key.startsWith(`${this.handle}.options.${index}.`))
                .reduce((acc, [key, error]) => {
                    const newKey = key.replace(`${this.handle}.options.${index}.`, '');
                    acc[newKey] = error;
                    return acc;
                }, {});
        },
    },

    watch: {
        variants: {
            handler() {
                this.update({
                    variants: this.value.variants,
                    options: this.cartesian.map((item, index) => {
                        let key = typeof item === 'string' ? item : item.join('_')
                        let variantName = typeof item === 'string' ? item : item.join(', ')

                        let existingData = this.value.options.filter((option) => {
                            return option.key === key
                        })[0]

                        if (existingData === undefined) {
                            existingData = {
                                price: 0,
                            }

                            Object.entries(this.meta.options.defaults).forEach(
                                ([key, value]) => {
                                    existingData[key] = value
                                }
                            )

                            let meta = this.meta;
                            meta['options']['existing'][index] = this.meta.options.new;
                            this.updateMeta(meta)
                        }

                        return {
                            ...existingData,
                            key: key,
                            variant: variantName,
                        }
                    })
                })
            },
            deep: true,
        },
    },
}
</script>
