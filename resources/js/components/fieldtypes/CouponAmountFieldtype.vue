<template>
    <div>
        <money-fieldtype
            v-if="mode === 'fixed'"
            :value="couponValue"
            @input="couponValue = $event"
            :meta="meta.meta.money"
            :config="meta.config.money"
        />

        <integer-fieldtype
            v-else-if="mode === 'percentage'"
            :value="couponValue"
            @input="couponValue = $event"
            :meta="meta.meta.integer"
            :config="meta.config.integer"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    props: ['meta'],

    data() {
        return {
            mode: null,
            couponValue: null,
            previousAmounts: {},
        };
    },

    mounted() {
        this.couponValue = this.value?.value || this.value || null;
        this.mode = this.value?.mode || this.$store.state.publish.base.values.type;

        this.$store.watch(
            (state) => state.publish.base.values.type,
            (type) => {
                // Keep track of the previous amount, so we can restore it when switching between modes.
                this.previousAmounts[this.mode] = this.couponValue;

                this.mode = type;
                this.couponValue = this.previousAmounts[type] || null;
            },
            { immediate: false }
        )
    },

    watch: {
        couponValue(couponValue) {
            let value = {
                mode: this.mode,
                value: couponValue,
            }

            if (JSON.stringify(this.couponValue) !== JSON.stringify(this.value)) {
                this.update(value);
            }
        },
    },
}
</script>