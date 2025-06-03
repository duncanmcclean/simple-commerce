<template>
    <div
        class="block space-y-2 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-900"
        :class="{
            'status-published': isCurrent,
        }"
    >
        <div class="revision-item-note truncate">
            {{ statusType }} changed to <span class="font-medium">{{ status }}</span>
        </div>

        <div class="flex items-center gap-2">
            <avatar v-if="event.user" :user="event.user" class="size-6 shrink-0" />

            <div class="revision-item-content flex w-full">
                <div class="flex-1">
                    <Subheading>
                        <template v-if="event.user">
                            {{ event.user.name || event.user.email }} &ndash;
                        </template>
                        {{ time }}
                    </Subheading>
                </div>
            </div>
        </div>

        <div v-if="isCurrent" class="flex items-center gap-1">
            <Badge size="sm" color="orange"  v-text="__('Current')" />
        </div>

        <div v-if="event.data?.reason" class="revision-item-note truncate ml-2 mt-3 font-normal text-xs" v-text="event.data.reason" />
    </div>
</template>

<script>
import axios from 'axios';
import Avatar from '../../../../vendor/statamic/cms/resources/js/components/Avatar.vue'
import { Badge, Subheading } from '@statamic/ui'

export default {
    components: { Badge, Subheading, Avatar },
    props: {
        event: Object,
        orderId: String,
        resendNotificationsUrl: String,
        orderStatuses: Array,
        paymentStatuses: Array,
        currentOrderStatus: String,
        currentPaymentStatus: String,
    },

    computed: {
        status() {
            let status = this.event.status;
            status = status.charAt(0).toUpperCase() + status.slice(1);

            return __(status);
        },

        statusType() {
            if (this.orderStatuses.includes(this.event.status)) {
                return __('Order Status');
            }

            if (this.paymentStatuses.includes(this.event.status)) {
                return __('Payment Status');
            }
        },

        date() {
            return moment.unix(this.event.timestamp);
        },

        isCurrent() {
            return this.event.status === this.currentOrderStatus
                || this.event.status === this.currentPaymentStatus;
        },
    },

    methods: {
        resendNotifications() {
            axios.post(this.resendNotificationsUrl, {
                order_id: this.orderId,
                status: this.event.status,
            }).then(response => {
                this.$toast.success(__('Notifications resent'));
            }).catch(error => {
                this.$toast.error(__('Unable to resend notifications'));
            })
        },
    },
}
</script>
