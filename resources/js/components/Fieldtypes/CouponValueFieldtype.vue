<template>
    <div>
        <money-fieldtype
            v-if="mode === 'fixed'"
            v-model="couponValue"
            :meta="meta.meta.money"
            :config="meta.config.money"
        />
        <integer-fieldtype
            v-else-if="mode === 'percentage'"
            v-model="couponValue"
            :meta="meta.meta.integer"
            :config="meta.config.integer"
        />

        <div v-if="errors">
            <small v-for="error in errors" class="help-block text-red-500 mt-2 mb-0">{{ error }}</small>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CouponValueFieldtype',

    mixins: [Fieldtype],

    props: ['meta'],

    data() {
        return {
            mode: null,
            couponValue: null,
        };
    },

    computed: {
        // Statamic won't show error messages, unless they're for the top-level field.
        // So, we'll show the error message ourselves.
        errors() {
            let errors = this.$store.state.publish.base.errors;

            return errors[`value.mode`] || errors[`value.value`];
        },
    },

    mounted() {
        this.hideValueField();

        this.mode = this.$store.state.publish.base.values.type;
        this.couponValue = this.value;

        if (this.mode !== null) {
            this.showValueField();
        }

        this.$store.watch(
            (state) => state.publish.base.values.type,
            (type) => {
                this.mode = type;
                this.couponValue = null;

                if (this.mode !== null) {
                    this.showValueField();
                }
            },
            { immediate: false }
        )
    },

    methods: {
        hideValueField() {
            document.querySelectorAll('.coupon-value-fieldtype').forEach((el) => el.classList.add('hidden'))
        },

        showValueField() {
            document.querySelectorAll('.coupon-value-fieldtype').forEach((el) => el.classList.remove('hidden'))
        },
    },

    watch: {
        couponValue(couponValue) {
            let value = {
                mode: this.mode,
                value: couponValue,
            }

            this.value = value;
            this.$emit('input', value);
        },
    },
}
</script>
