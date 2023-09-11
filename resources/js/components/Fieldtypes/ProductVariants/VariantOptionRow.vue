<template>
    <div class="bg-grey-10 shadow-sm mb-2 rounded border variants-sortable-item">
        <div class="grid-item-header">
            {{ option.variant || __('Variants') }}
        </div>

        <publish-fields-container>
            <publish-field
                v-for="(optionField, optionIndex) in meta.option_fields"
                v-show="showField(optionField, fieldPath(optionIndex, optionField.handle))"
                :key="'option-' + optionField.handle"
                :config="optionField"
                :value="option[optionField.handle]"
                :meta="meta[optionField.handle]"
                :errors="errors(optionField.handle)"
                :field-path-prefix="fieldPath(optionIndex, optionField.handle)"
                class="p-2"
                @input="updatedOptions(optionField.handle, $event)"
                @meta-updated="metaUpdated(option.handle, $event)"
                @focus="$emit('focus')"
                @blur="$emit('blur')"
            />
        </publish-fields-container>
    </div>
</template>

<script>
import { ValidatesFieldConditions } from '../../../../../vendor/statamic/cms/resources/js/components/field-conditions/FieldConditions'

export default {
    mixins: [ValidatesFieldConditions],

    props: {
        option: Object,
        index: Number,
        meta: Object,
        fieldPathPrefix: String,
        values: Object,
    },

    methods: {
        updatedOptions(fieldHandle, value) {
            let values = this.values
            values[fieldHandle] = value

            this.$emit('updated', this.index, values)
        },

        metaUpdated(fieldHandle, event) {
            this.$emit('metaUpdated', fieldHandle, event)
        },

        fieldPath(index, fieldHandle) {
            return `${this.fieldPathPrefix}.${index}.${fieldHandle}`
        },

        errors(fieldHandle) {
            //
        },
    },
}
</script>
