<template>
    <Badge
        :color="badgeColour"
        :text="statusLabel"
        :pill="true"
        variant="flat"
    />
</template>

<script>
import { IndexFieldtypeMixin } from '@statamic/cms';
import { Badge } from '@statamic/cms/ui'

export default {
    mixins: [IndexFieldtypeMixin],

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
            return __((
                this.value.charAt(0).toUpperCase() +
                this.value.substr(1).toLowerCase()
            ))
        },
    },
}
</script>
