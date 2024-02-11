<template>
    <div>
        <div class="flex items-center">
            <div class="little-dot mr-2" :class="littleDotColour" />
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
                    return 'bg-gray-400'

                case 'paid':
                    return 'bg-green-600'

                case 'refunded':
                    return 'bg-grey'

                default:
                    return ''
            }
        },

        statusName() {
            return __((
                this.value.charAt(0).toUpperCase() +
                this.value.substr(1).toLowerCase()
            ))
        },
    },
}
</script>
