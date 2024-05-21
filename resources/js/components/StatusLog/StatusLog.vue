<template>
    <div class="bg-white dark:bg-dark-800 h-full flex flex-col">
        <div class="bg-gray-200 dark:bg-dark-600 px-4 py-2 border-b border-gray-300 dark:border-dark-900 text-lg font-medium flex items-center justify-between">
            {{ __('Status Log') }}
            <button
                type="button"
                class="btn-close"
                @click="close"
                v-html="'&times'" />
        </div>

        <div class="flex-1 overflow-auto">
            <div class="flex h-full items-center justify-center loading" v-if="loading">
                <loading-graphic />
            </div>

            <div v-if="!loading && statusLog.length === 0" class="p-4 text-gray dark:text-dark-150 text-sm">
                {{ __('No status log events.') }}
            </div>

            <div
                v-for="group in statusLog"
                :key="group.day"
            >
                <h6 class="revision-date" v-text="$moment.unix(group.day).isBefore($moment().startOf('day')) ? $moment.unix(group.day).format('LL') : __('Today')" />
                <div class="revision-list">
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

export default {
    components: {
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
        close() {
            this.$emit('closed');
        },
    },
}
</script>
