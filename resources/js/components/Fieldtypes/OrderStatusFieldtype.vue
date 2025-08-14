<template>
    <Badge
        :color="badgeColour"
        :text="statusLabel"
        size="lg"
        :pill="true"
    />
</template>

<script>
import { FieldtypeMixin } from 'statamic';
import { Badge } from '@statamic/ui'

export default {
    name: 'order-status-fieldtype',

    mixins: [FieldtypeMixin],

    components: { Badge },

    computed: {
        badgeColour() {
            switch (this.value) {
                case 'cart':
                    return 'white'

                case 'placed':
                    return 'orange'

                case 'dispatched':
                    return 'blue'

                case 'delivered':
                    return 'green'

                case 'cancelled':
                    return 'red'

                default:
                    return ''
            }
        },

        statusLabel() {
            return __(this.meta.statuses[this.value])
        },
    },
}
</script>
