<template>
    <div class="m-2 flex h-full flex-col rounded-xl bg-white dark:bg-gray-800">
        <header
            class="flex items-center justify-between rounded-t-xl border-b border-gray-300 bg-gray-50 px-4 py-2 dark:border-gray-950 dark:bg-gray-900"
        >
            <Heading size="lg">{{ __('Status Log') }}</Heading>
            <Button icon="x" variant="ghost" class="-me-2" @click="close" />
        </header>

        <div class="flex-1 overflow-auto">
            <div class="loading flex h-full items-center justify-center" v-if="loading">
                <loading-graphic />
            </div>

            <Heading size="sm" class="p-3" v-if="!loading && statusLog.length === 0">
                {{ __('No status log events.') }}
            </Heading>

            <div v-for="group in statusLog" :key="group.day">
                <Heading size="sm" class="p-3" v-text="formatRelativeDate(group.day)" />
                <div class="divide-y divide-gray-200 dark:divide-gray-900">
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
import { Button, Heading } from '@statamic/cms/ui'
import { DateFormatter } from '@statamic/cms';

export default {
    components: {
        Button,
        Heading,
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
