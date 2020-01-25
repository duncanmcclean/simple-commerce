<template>
    <div>
        <text-input :type="inputType" :value="value" :prepend="symbol" placeholder="00.00" @input="update" />
    </div>
</template>

<script>
    export default {
        mixins: [Fieldtype],

        data() {
            return {
                symbol: Statamic.$config.get('commerceCurrencySymbol')
            }
        },

        computed: {
            inputType() {
                return this.show;
            }
        }
    };
</script>
