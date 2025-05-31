<template>
    <text-fieldtype
        :meta="meta"
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
import { Fieldtype } from 'statamic';

export default {
    name: 'CouponCodeFieldtype',

    mixins: [Fieldtype],

    props: ['meta'],
}
</script>
