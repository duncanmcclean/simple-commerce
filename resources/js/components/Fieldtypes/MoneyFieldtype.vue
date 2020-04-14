<template>
    <div>
        <text-input
            :type="inputType"
            :value="formattedValue"
            :prepend="symbol"
            :isReadOnly="config.read_only || readOnly"
            placeholder="00.00"
            @input="update" />
    </div>
</template>

<script>
    export default {
        mixins: [Fieldtype],

        props: ['meta'],

        data() {
            return {
                symbol: this.meta.symbol,
                formattedValue: parseFloat(this.value).toFixed(2),
            }
        },

        computed: {
            inputType() {
                return this.show;
            }
        }
    };
</script>
