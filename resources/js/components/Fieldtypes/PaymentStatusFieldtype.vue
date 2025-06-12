<template>
    <Badge
        :color="badgeColour"
        :text="statusLabel"
        size="lg"
        :pill="true"
    />
</template>

<script>
import { Fieldtype } from 'statamic';
import { Badge } from '@statamic/ui'

export default {
    name: 'payment-status-fieldtype',

    mixins: [Fieldtype],

    components: { Badge },

    computed: {
        badgeColour() {
            switch (this.value) {
                case 'unpaid':
                    return 'white'

                case 'paid':
                    return 'green'

                case 'refunded':
                    return 'yellow'

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
