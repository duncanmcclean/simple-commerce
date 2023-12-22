<template>
    <div class="revision-item">
        <div class="flex items-center">
            <div class="revision-item-content w-full flex items-center justify-between">
                <div>
                    <p class="ml-2">{{ statusType }} changed to <span class="font-medium">{{ status }}</span></p>
                    <span class="badge bg-orange" v-if="isCurrent" v-text="__('Current')" />
                </div>

                <div class="flex-1 flex items-center justify-end" style="flex-shrink: 0">
                    <div>
                        <div class="revision-author text-gray-700 text-2xs">
                            {{ date.isBefore($moment().startOf('day')) ? date.format('LT') : date.fromNow() }}
                        </div>
                    </div>

                    <dropdown-list class="ml-2">
                        <dropdown-item :text="__('Resend notifications')" @click="resendNotifications" />
                    </dropdown-list>
                </div>
            </div>
        </div>

        <div v-if="event.data?.reason" class="revision-item-note truncate ml-2 mt-3 font-normal text-xs" v-text="event.data.reason" />

        <div v-if="event.user" class="flex items-center ml-2 mt-2" style="flex-shrink: 0">
            <avatar v-if="event.user" :user="event.user" class="shrink-0 mr-2 w-6" />
            <div class="flex-1 revision-author text-gray-700 text-2xs">
                <template v-if="event.user">{{ event.user.name || event.user.email }}</template>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { __ } from '../../../../vendor/statamic/cms/resources/js/bootstrap/globals';

export default {
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
