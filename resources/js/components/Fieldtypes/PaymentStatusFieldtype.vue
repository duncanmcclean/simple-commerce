<template>
    <div class="relationship-input">
        <div
            class="relationship-input-items space-y-1 outline-none"
            tabindex="0"
        >
            <div class="item item outline-none" tabindex="0">
                <div class="item-inner">
                    <span
                        class="little-dot mr-2"
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
    name: 'payment-status-fieldtype',

    mixins: [Fieldtype],

    props: ['meta'],

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
            return __(this.meta.statuses[this.value])
        },
    },
}
</script>
