<template>
    <div>
        <text-input
            :type="inputType"
            :value="value"
            :prepend="symbol"
            :isReadOnly="config.read_only || readOnly"
            placeholder="00.00"
            @input="update"
        />
    </div>
</template>

<script>
export default {
    name: 'money-fieldtype',

    mixins: [Fieldtype],

    props: ['meta'],

    data() {
        return {
            symbol: this.meta.symbol,
        }
    },

    computed: {
        inputType() {
            return this.show
        },
    },
}
</script>
