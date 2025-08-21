<template>
    <Input
        :prepend="symbol"
        :disabled="config.read_only || readOnly"
        :model-value="value"
        @update:model-value="update"
    />
</template>

<script>
import { FieldtypeMixin } from '@statamic/cms';
import { Input } from '@statamic/cms/ui'

export default {
    name: 'money-fieldtype',

    mixins: [FieldtypeMixin],

    components: { Input },

    data() {
        return {
            symbol: this.meta.symbol,
        }
    },
}
</script>
