<template>
    <div>
        <div>
            <div class="loading flex h-full items-center justify-center" v-if="loading">
                <Icon name="loading" />
            </div>

            <Heading size="sm" class="p-3" v-if="!loading && statusLog.length === 0">
                {{ __('No status log events.') }}
            </Heading>

            <div v-for="group in statusLog" :key="group.day">
                <Heading size="sm" class="p-3 text-gray-600 dark:text-gray-300" v-text="formatRelativeDate(group.day)" />
                <div class="relative grid gap-3">
                    <div class="absolute inset-y-0 left-6 top-3 border-l-1 border-gray-400 dark:border-gray-600 border-dashed" />
                    <status-log-event
                        v-for="event in group.events"
                        :key="event.timestamp"
                        :event="event"
                        :order-id="orderId"
                        :resend-notifications-url="resendNotificationsUrl"
                        :order-statuses="orderStatuses"
                        :payment-statuses="paymentStatuses"
                        :current-order-status="currentOrderStatus"
                        :current-payment-status="currentPaymentStatus"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import StatusLogEvent from './StatusLogEvent.vue';
import { Button, Heading, Icon } from '@statamic/cms/ui'
import { DateFormatter } from '@statamic/cms';

export default {
    components: {
        Button,
        Heading,
        Icon,
        StatusLogEvent,
    },

    props: {
        indexUrl: String,
        resendNotificationsUrl: String,
        orderId: String,
        orderStatuses: Array,
        paymentStatuses: Array,
        currentOrderStatus: String,
        currentPaymentStatus: String,
    },

    data() {
        return {
            loading: true,
            statusLog: [],
            escBinding: null,
        }
    },

    mounted() {
        this.$axios.post(this.indexUrl, { order_id: this.orderId }).then(response => {
            this.loading = false;
            this.statusLog = response.data.reverse();
        });

        this.escBinding = this.$keys.bindGlobal(['esc'], e => {
            this.close();
        });
    },

    beforeDestroy() {
        this.escBinding.destroy();
    },

    methods: {
        formatRelativeDate(value) {
            const isToday = new Date(value * 1000) < new Date().setUTCHours(0, 0, 0, 0);

            return !isToday
                ? __('Today')
                : DateFormatter.format(value * 1000, {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric',
                });
        },

        close() {
            this.$emit('closed');
        },
    },
}
</script>
