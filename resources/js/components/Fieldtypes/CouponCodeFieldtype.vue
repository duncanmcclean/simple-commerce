<template>
    <Input
        class="uppercase font-mono"
        :copyable="true"
        :model-value="value"
        @update:model-value="updateDebounced"
        @keydown.native.space.prevent
    />
</template>

<script>
import { FieldtypeMixin } from 'statamic';
import { Input } from '@statamic/ui';

export default {
    name: 'CouponCodeFieldtype',

    components: { Input },

    mixins: [FieldtypeMixin],
}
</script>
