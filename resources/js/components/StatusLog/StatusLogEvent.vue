<template>
    <div class="revision-item"
        :class="{
            'status-working-copy': true,
            'status-published': false,
        }"
    >
        <div class="flex items-center">
            <div class="revision-item-content w-full flex items-center justify-between">
                <div>
                    <span class="badge" :class="statusColour" v-text="__(event.status)" />
                    <span class="badge bg-orange" v-if="isCurrent" v-text="__('Current')" />
                </div>

                <div class="flex-1 flex items-center justify-end">
                    <div>
                        <div class="revision-author text-gray-700 text-2xs">
                            <!-- <template v-if="event.user">{{ event.user.name || event.user.email }} &ndash;</template> -->
                            {{ date.isBefore($moment().startOf('day')) ? date.format('LT') : date.fromNow() }}
                        </div>
                    </div>

                    <dropdown-list class="ml-2">
                        <dropdown-item :text="__('Re-send notifications')" :redirect="`#`" />
                    </dropdown-list>
                </div>
            </div>
        </div>

        <div v-if="event.data?.reason" class="revision-item-note truncate ml-2 mt-2" v-text="event.data.reason" />

        <div v-if="event.user" class="flex items-center ml-2 mt-2">
            <avatar v-if="event.user" :user="event.user" class="shrink-0 mr-2 w-6" />
            <div class="flex-1 revision-author text-gray-700 text-2xs">
                <template v-if="event.user">{{ event.user.name || event.user.email }}</template>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        event: Object,
        currentOrderStatus: String,
        currentPaymentStatus: String,
    },

    computed: {
        date() {
            return moment.unix(this.event.timestamp);
        },

        statusColour() {
            switch (this.event.status) {
                case 'cart':
                    return 'bg-grey-40'

                case 'placed':
                    return 'bg-orange'

                case 'dispatched':
                    return 'bg-blue-300'

                case 'cancelled':
                    return 'bg-red'

                    case 'unpaid':
                    return 'bg-grey-40'

                case 'paid':
                    return 'bg-green-600'

                case 'refunded':
                    return 'bg-grey'

                default:
                    return ''
            }
        },

        isCurrent() {
            return this.event.status === this.currentOrderStatus
                || this.event.status === this.currentPaymentStatus;
        },
    },
}
</script>
