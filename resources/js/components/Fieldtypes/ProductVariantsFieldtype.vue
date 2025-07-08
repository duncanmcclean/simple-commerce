<script setup>
import { Fieldtype } from 'statamic';
import Fields from '@statamic/components/ui/Publish/Fields.vue';
import FieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import PublishContainer from '@statamic/components/ui/Publish/Container.vue';
import { Button } from '@statamic/ui';
import { computed, inject, ref, watch } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, updateMeta } = Fieldtype.use(emit, props);
defineExpose(expose);

const store = inject('store');
const deletingVariant = ref(null);
const variants = computed(() => props.value.variants || []);
const options = computed(() => props.value.options || []);

const cartesian = computed(() => {
    let cartesian = variants.value
        .filter((variant) => variant.values.length !== 0)
        .flatMap((variant) => [variant.values]);

    if (cartesian.length == 0) {
        return [];
    }

    return cartesian.reduce((acc, curr) => acc.flatMap((c) => curr.map((n) => [].concat(c, n))));
});

function addVariant() {
    update({
        variants: [
            ...props.value.variants,
            {
                name: '',
                values: [],
            },
        ],
        options: props.value.options,
    });
}

function deleteVariant(index) {
    update({
        variants: props.value.variants.filter((_, i) => i !== index),
        options: props.value.options,
    });
}

function variantUpdated(index, variant) {
    let variants = [...props.value.variants];
    variants[index] = variant;

    update({
        variants: variants,
        options: props.value.options,
    });
}

function optionUpdated(index, option) {
    let options = [...props.value.options];
    options[index] = option;

    update({
        variants: props.value.variants,
        options: options,
    });
}

function getErrorsForVariant(index) {
    return Object.entries(store.errors)
        .filter(([key]) => key.startsWith(`${props.handle}.variants.${index}.`))
        .reduce((acc, [key, error]) => {
            const newKey = key.replace(`${props.handle}.variants.${index}.`, '');
            acc[newKey] = error;
            return acc;
        }, {});
}

function getErrorsForOption(index) {
    return Object.entries(store.errors)
        .filter(([key]) => key.startsWith(`${props.handle}.options.${index}.`))
        .reduce((acc, [key, error]) => {
            const newKey = key.replace(`${props.handle}.options.${index}.`, '');
            acc[newKey] = error;
            return acc;
        }, {});
}

watch(
    variants,
    () => {
        update({
            variants: props.value.variants,
            options: cartesian.value.map((item, index) => {
                let key = typeof item === 'string' ? item : item.join('_');
                let variantName = typeof item === 'string' ? item : item.join(', ');

                let existingData = props.value.options.filter((option) => {
                    return option.key === key;
                })[0];

                if (existingData === undefined) {
                    existingData = {
                        price: 0,
                    };

                    Object.entries(props.meta.options.defaults).forEach(([key, value]) => {
                        existingData[key] = value;
                    });

                    let meta = props.meta;
                    meta['options']['existing'][index] = props.meta.options.new;
                    updateMeta(meta);
                }

                return {
                    ...existingData,
                    key: key,
                    variant: variantName,
                };
            }),
        });
    },
    { deep: true }
);
</script>

<template>
    <div>
        <!-- Variants -->
        <div class="mb-10 flex flex-col gap-4">
            <div
                v-for="(variant, index) in variants"
                :key="index"
                class="dark:border-dark-900 overflow-hidden rounded-lg border shadow-sm"
            >
                <header class="flex items-center justify-between bg-gray-100 px-4 py-2 dark:bg-black/25">
                    <span class="text-sm">{{ variant.name }}</span>
                    <button type="button" class="flex items-center" @click="deletingVariant = index">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M1.5 4.01121c3.70225 -0.48695 7.29775 -0.48695 11 0"
                                stroke-width="1"
                            />
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M2.48113 3.89331c-0.03491 1.86889 -0.08342 5.02765 0.49568 7.61009 0.22419 0.9997 1.07518 1.7149 2.08968 1.8579 0.65577 0.0925 1.25364 0.1387 1.93361 0.1387 0.68 0 1.27796 -0.0462 1.93382 -0.1387 1.0145 -0.143 1.86548 -0.8582 2.08968 -1.8579 0.5791 -2.58241 0.5306 -5.74115 0.4957 -7.61005 -3.03257 -0.32979 -6.00563 -0.32981 -9.03817 -0.00004Z"
                                stroke-width="1"
                            />
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.41786 3.70312c-0.00772 -0.10103 -0.01161 -0.50235 -0.01161 -0.60949C4.40625 1.43371 5.33996 0.5 6.99988 0.5s2.59363 0.93371 2.59363 2.59363c0 0.10714 -0.00389 0.50846 -0.01162 0.60949"
                                stroke-width="1"
                            />
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5.3476 6.40408c-0.05908 1.0462 -0.00048 2.90177 0.18132 4.24122m2.94168 0c0.18175 -1.33949 0.24026 -3.19505 0.18117 -4.24125"
                                stroke-width="1"
                            />
                        </svg>
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
                    :model-value="variant"
                    :meta="meta.variants.existing[index]"
                    :errors="getErrorsForVariant(index)"
                    @update:model-value="variantUpdated(index, $event)"
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
                class="dark:border-dark-900 rounded-lg border shadow-sm"
            >
                <header class="flex items-center justify-between bg-gray-100 px-4 py-2 dark:bg-black/25">
                    <span class="text-sm">{{ option.variant }}</span>
                </header>

                <PublishContainer
                    v-if="meta.options.existing[index]"
                    :name="`product-variant-option-${index}`"
                    :blueprint="meta.options.fields"
                    :model-value="store.values"
                    :meta="meta.options.existing[index]"
                    :errors="getErrorsForOption(index)"
                    @update:model-value="optionUpdated(index, $event)"
                >
                    <FieldsProvider :fields="meta.options.fields" :field-path-prefix="`${handle}.options.${index}`">
                        <Fields class="p-4" />
                    </FieldsProvider>
                </PublishContainer>
            </div>
        </div>
    </div>
</template>
