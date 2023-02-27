<template>
    <div>
        <div class="flex items-center">
            <div class="little-dot mr-1" :class="littleDotColour" />
            <span>
                {{ statusName }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    mixins: [IndexFieldtype],

    computed: {
        littleDotColour() {
            switch (this.value) {
                case 'unpaid':
                    return 'bg-grey-40'

                case 'paid':
                    return 'bg-green'

                case 'refunded':
                    return 'bg-grey'

                default:
                    return ''
            }
        },

        statusName() {
            return (
                this.value.charAt(0).toUpperCase() +
                this.value.substr(1).toLowerCase()
            )
        },
    },
}
</script>
