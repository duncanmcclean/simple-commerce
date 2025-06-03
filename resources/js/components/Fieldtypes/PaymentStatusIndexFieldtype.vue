<template>
    <Badge
        :color="badgeColour"
        :text="statusLabel"
        :pill="true"
        variant="flat"
    />
</template>

<script>
import { IndexFieldtype } from 'statamic';
import { Badge } from '@statamic/ui'

export default {
    mixins: [IndexFieldtype],

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
            return __((
                this.value.charAt(0).toUpperCase() +
                this.value.substr(1).toLowerCase()
            ))
        },
    },
}
</script>
