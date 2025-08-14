<template>
    <div>
        <money-fieldtype
            v-if="mode === 'fixed'"
            :value="couponValue"
            :meta="meta.meta.money"
            :config="meta.config.money"
            @update:value="couponValueUpdated"
        />
        <integer-fieldtype
            v-else-if="mode === 'percentage'"
            :value="couponValue"
            :meta="meta.meta.integer"
            :config="meta.config.integer"
            @update:value="couponValueUpdated"
        />

        <div v-if="errors">
            <small v-for="error in errors" class="help-block text-red-500 mt-2 mb-0">{{ error }}</small>
        </div>
    </div>
</template>

<script>
import { FieldtypeMixin } from 'statamic';

export default {
    name: 'CouponValueFieldtype',

    mixins: [FieldtypeMixin],

    computed: {
        mode() {
            return this.publishContainer.values.type;
        },

        couponValue() {
            return this.value?.value;
        },

        // Statamic won't show error messages, unless they're for the top-level field.
        // So, we'll show the error message ourselves.
        errors() {
            let errors = this.publishContainer.errors;

            return errors[`value.mode`] || errors[`value.value`];
        },
    },

    methods: {
        hideValueField() {
            document.querySelectorAll('.coupon-value-fieldtype').forEach((el) => el.classList.add('hidden'))
        },

        showValueField() {
            document.querySelectorAll('.coupon-value-fieldtype').forEach((el) => el.classList.remove('hidden'))
        },

        couponValueUpdated(value) {
            this.update({
                mode: this.mode,
                value: value,
            });
        },
    },

    watch: {
        mode(mode) {
            this.update({ mode, value: null });

            if (mode !== null) this.showValueField();
        },
    },
}
</script>
