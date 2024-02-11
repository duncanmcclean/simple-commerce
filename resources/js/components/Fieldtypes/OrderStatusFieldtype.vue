<template>
    <div class="relationship-input">
        <div
            class="relationship-input-items space-y-1 outline-none"
            tabindex="0"
        >
            <div class="item item outline-none" tabindex="0">
                <div class="item-inner">
                    <span
                        class="little-dot mr-1"
                        :class="littleDotColour"
                    ></span>
                    <span>
                        {{ statusName }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'order-status-fieldtype',

    mixins: [Fieldtype],

    props: ['meta'],

    computed: {
        littleDotColour() {
            switch (this.value) {
                case 'cart':
                    return 'bg-gray-400'

                case 'placed':
                    return 'bg-orange'

                case 'dispatched':
                    return 'bg-blue-300'

                case 'delivered':
                    return 'bg-green-600'

                case 'cancelled':
                    return 'bg-red-500'

                default:
                    return ''
            }
        },

        statusName() {
            return __(this.meta.statuses[this.value])
        },
    },
}
</script>
