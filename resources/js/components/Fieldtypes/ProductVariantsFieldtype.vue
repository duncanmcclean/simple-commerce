<template>
    <div>
        <!-- Variants -->
        <div class="flex flex-col gap-4 mb-10">
            <div
                v-for="(variant, index) in variants"
                :key="index"
                class="dark:border-dark-900 border shadow-sm rounded-lg overflow-hidden"
            >
                <header class="bg-gray-100 dark:bg-black/25 px-4 py-2 flex items-center justify-between">
                    <span class="text-sm">{{ variant.name }}</span>
                    <button type="button" class="flex items-center" @click="deletingVariant = index">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M1.5 4.01121c3.70225 -0.48695 7.29775 -0.48695 11 0" stroke-width="1"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M2.48113 3.89331c-0.03491 1.86889 -0.08342 5.02765 0.49568 7.61009 0.22419 0.9997 1.07518 1.7149 2.08968 1.8579 0.65577 0.0925 1.25364 0.1387 1.93361 0.1387 0.68 0 1.27796 -0.0462 1.93382 -0.1387 1.0145 -0.143 1.86548 -0.8582 2.08968 -1.8579 0.5791 -2.58241 0.5306 -5.74115 0.4957 -7.61005 -3.03257 -0.32979 -6.00563 -0.32981 -9.03817 -0.00004Z" stroke-width="1"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M4.41786 3.70312c-0.00772 -0.10103 -0.01161 -0.50235 -0.01161 -0.60949C4.40625 1.43371 5.33996 0.5 6.99988 0.5s2.59363 0.93371 2.59363 2.59363c0 0.10714 -0.00389 0.50846 -0.01162 0.60949" stroke-width="1"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M5.3476 6.40408c-0.05908 1.0462 -0.00048 2.90177 0.18132 4.24122m2.94168 0c0.18175 -1.33949 0.24026 -3.19505 0.18117 -4.24125" stroke-width="1"/></svg>
                    </button>
                </header>

                <confirmation-modal
                    v-if="deletingVariant === index"
                    :ref="`variant-deleter-${index}`"
                    :title="__('Delete Variant')"
                    @cancel="deletingVariant = null"
                    @confirm="deleteVariant(index)"
                >
                    <p>{{ __('Are you sure you want to delete this variant?') }}</p>
                </confirmation-modal>

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
        <div class="grid gap-4" :class="{ 'lg:grid-cols-2': config.columns === 2 }">
            <div
                v-for="(option, index) in options"
                :key="option.key"
                class="dark:border-dark-900 border shadow-sm rounded-lg"
            >
                <header class="bg-gray-100 dark:bg-black/25 px-4 py-2 flex items-center justify-between">
                    <span class="text-sm">{{ option.variant }}</span>
                </header>

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
import { Button, Icon, Tooltip } from '@statamic/ui'

export default {
    mixins: [
        Fieldtype,
        ValidatesFieldConditions,
    ],

    components: {
        Icon,
        Tooltip,
        Button,
        Fields,
        FieldsProvider,
        PublishContainer,
    },

    inject: ['store'],

    data() {
        return {
            deletingVariant: null,
        }
    },

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

        deleteVariant(index) {
            this.update({
                variants: this.value.variants.filter((_, i) => i !== index),
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
