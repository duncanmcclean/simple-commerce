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
                    :blueprint="meta.variant_fields"
                    :values="variant"
                    :meta="meta.variant_field_meta[index]"
                    :errors="getErrorsForVariant(index)"
                >
                    <FieldsProvider :fields="meta.variant_fields">
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
                    v-if="meta.option_field_meta[index]"
                    :name="`product-variant-option-${index}`"
                    :blueprint="meta.option_fields"
                    :values="option"
                    :meta="meta.option_field_meta[index]"
                    :errors="getErrorsForOption(index)"
                >
                    <FieldsProvider :fields="meta.option_fields">
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
import { Button } from '../../../../../vendor/statamic/cms/resources/js/components/ui'

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

                            Object.entries(this.meta.option_field_defaults).forEach(
                                ([key, value]) => {
                                    existingData[key] = value
                                }
                            )

                            let meta = this.meta;
                            meta['option_field_meta'][index] = this.meta.option_field_new;
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
