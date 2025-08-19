<template>
    <Badge
        :color="badgeColour"
        :text="statusLabel"
        size="lg"
        :pill="true"
    />
</template>

<script>
import { FieldtypeMixin } from '@statamic/cms';
import { Badge } from '@statamic/cms/ui'

export default {
    name: 'payment-status-fieldtype',

    mixins: [FieldtypeMixin],

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
