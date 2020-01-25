<template>
    <div>
        <text-input :type="inputType" :value="value" :prepend="symbol" placeholder="00.00" @input="update" />
    </div>
</template>

<script>
    export default {
        mixins: [Fieldtype],

        props: ['meta'],

        data() {
            return {
                symbol: this.meta.symbol
            }
        },

        computed: {
            inputType() {
                return this.show;
            }
        }
    };
</script>
