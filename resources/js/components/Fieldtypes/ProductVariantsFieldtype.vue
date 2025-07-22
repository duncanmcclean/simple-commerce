<script setup>
import { Fieldtype } from 'statamic';
import { Icon, Button, PublishContainer, FieldsProvider, PublishFields as Fields } from '@statamic/ui';
import { computed, ref, watch } from 'vue';
import { injectContainerContext } from '@statamic/components/ui/Publish/Container.vue';
const { values, errors } = injectContainerContext()

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, updateMeta } = Fieldtype.use(emit, props);
defineExpose(expose);

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

    deletingVariant.value = null;
}

function variantErrors(index) {
    const prefix = `${props.handle}.variants.${index}.`;

    return Object.keys(errors.value ?? [])
        .filter((handle) => handle.startsWith(prefix))
        .reduce((acc, handle) => {
            const newKey = handle.replace(prefix, '');
            acc[newKey] = errors.value[handle];
            return acc;
        }, {});
}

function optionErrors(index) {
    const prefix = `${props.handle}.options.${index}.`;

    return Object.keys(errors.value ?? [])
        .filter((handle) => handle.startsWith(prefix))
        .reduce((acc, handle) => {
            const newKey = handle.replace(prefix, '');
            acc[newKey] = errors.value[handle];
            return acc;
        }, {});
}

function variantUpdated(index, values) {
    let variants = [...props.value.variants];
    variants[index] = values;

    update({
        variants: variants,
        options: props.value.options,
    });
}

function optionUpdated(index, values) {
    let options = [...props.value.options];
    options[index] = values;

    update({
        variants: props.value.variants,
        options: options,
    });
}

watch(
    variants,
    () => {
        let values = [];
        let meta = [];

        let originalOptions = options.value;

        cartesian.value.forEach((keys, index) => {
            if (typeof keys === 'string') keys = [keys];
            let key = typeof keys === 'string' ? keys : keys.join('_');

            let existingOption = originalOptions.find((option) => option.key === key);

            // When the option already exists, use its values and meta.
            if (existingOption) {
                let existingOptionIndex = originalOptions.findIndex((option) => option.key === key);

                values.push(existingOption);
                meta.push(props.meta.options.existing[existingOptionIndex]);

                return;
            }

            // Attempt to find existing options by progressively removing parts of the key.
            // This handles both adding new variants and removing variants.
            let keyParts = key.split('_');
            let foundOption = null;
            let foundOptionIndex = -1;

            // Try all possible shorter keys (removing parts from the end)
            for (let i = keyParts.length - 1; i >= 0; i--) {
                let possibleKey = keyParts.slice(0, i).join('_');
                if (possibleKey === '') continue;

                foundOption = originalOptions.find(option => option.key === possibleKey);

                if (foundOption) {
                    foundOptionIndex = originalOptions.findIndex(option => option.key === possibleKey);
                    break;
                }
            }

            // Also check for options that start with our key (for when variants are removed)
            if (!foundOption) {
                foundOption = originalOptions.find(option => option.key.startsWith(key + '_'));

                if (foundOption) {
                    foundOptionIndex = originalOptions.findIndex(option => option.key === foundOption.key);
                }
            }

            if (foundOption) {
                values.push({
                    ...foundOption,
                    key: key,
                    variant: keys.join(', '),
                });

                meta.push(props.meta.options.existing[foundOptionIndex]);

                return;
            }

            // Otherwise, create a new option using default values.
            values.push({
                ...props.meta.options.defaults,
                price: 0,
                key: key,
                variant: keys.join(', '),
            });

            meta.push(props.meta.options.new);
        });

        if (JSON.stringify(values) === JSON.stringify(props.value.options)) {
            return;
        }

        update({
            variants: props.value.variants,
            options: values,
        });

        updateMeta({
            ...props.meta,
            options: {
                ...props.meta.options,
                existing: meta,
            },
        });
    },
    { deep: true }
);
</script>

<template>
    <div class="mt-2">
        <!-- Variants -->
        <div class="mb-10 flex flex-col gap-4">
            <div
                v-for="(variant, index) in variants"
                :key="index"
                class="dark:border-dark-900 overflow-hidden rounded-lg border shadow-sm"
            >
                <header class="flex items-center justify-between bg-gray-100 px-4 py-2 dark:bg-black/25">
                    <span class="text-sm">{{ variant.name }}</span>
                    <button type="button" class="flex items-center cursor-pointer" aria-label="Delete variant" @click="deletingVariant = index">
                        <Icon name="trash" />
                    </button>
                </header>

                <confirmation-modal
                    v-if="deletingVariant === index"
                    :ref="`variant-deleter-${index}`"
                    :title="__('Delete Variant')"
                    :danger="true"
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
                    :extra-values="values"
                    :errors="variantErrors(index)"
                    @update:model-value="variantUpdated(index, $event)"
                >
                    <FieldsProvider :fields="meta.variants.fields">
                        <Fields class="p-4" />
                    </FieldsProvider>
                </PublishContainer>
            </div>

            <div>
                <Button size="sm" :text="__('Add Variant')" @click="addVariant" />
            </div>
        </div>

        <!-- Variant Options -->
        <div class="product-variant-options grid gap-4" :class="{ 'lg:grid-cols-2': config.columns === 2 }">
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
                    :name="`product-variant-option-${option.key}`"
                    :key="option.key"
                    :blueprint="meta.options.fields"
                    :model-value="option"
                    :meta="meta.options.existing[index]"
                    :extra-values="values"
                    :errors="optionErrors(index)"
                    @update:model-value="optionUpdated(index, $event)"
                >
                    <FieldsProvider :fields="meta.options.fields">
                        <Fields class="p-4" />
                    </FieldsProvider>
                </PublishContainer>
            </div>
        </div>
    </div>
</template>