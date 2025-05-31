<template>
    <Input
        :prepend="symbol"
        :disabled="config.read_only || readOnly"
        :model-value="value"
        @update:model-value="update"
    />
</template>

<script>
import { Fieldtype } from 'statamic';
import { Input } from '@statamic/ui'

export default {
    name: 'money-fieldtype',

    mixins: [Fieldtype],

    components: { Input },

    data() {
        return {
            symbol: this.meta.symbol,
        }
    },
}
</script>
