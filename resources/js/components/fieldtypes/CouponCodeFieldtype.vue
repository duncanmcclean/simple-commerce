<template>
    <text-fieldtype
        :meta="{}"
        :config="{
            classes: 'uppercase font-mono',
            focus: 'code',
            autoselect: true,
            input_type: 'text',
        }"
        :value="value"
        @input="updateDebounced"
        @keydown.native.space.prevent
    />
</template>

<script>
export default {
    mixins: [Fieldtype],

    methods: {
        updateDebounced: _.debounce(function (value) {
            this.$emit('input', value.toUpperCase())
        }, 500),
    }
}
</script>