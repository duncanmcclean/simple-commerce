<template>
    <div v-if="!isCreating">
        <button
            class="flex items-center justify-center btn-flat px-2 w-full"
            @click="showStatusLog = true"
        >
            <svg-icon name="light/history" class="h-4 w-4 mr-2" />
            <span>{{ __('View Status Log') }}</span>
        </button>

        <stack name="status-log" v-if="showStatusLog" @closed="showStatusLog = false" :narrow="true">
            <status-log
                slot-scope="{ close }"
                :index-url="meta.indexUrl"
                :order-id="orderId"
                :resend-notifications-url="meta.resendNotificationsUrl"
                :order-statuses="meta.orderStatuses"
                :payment-statuses="meta.paymentStatuses"
                :current-order-status="currentOrderStatus"
                :current-payment-status="currentPaymentStatus"
                @closed="close"
            />
        </stack>
    </div>
</template>

<script>
import StatusLog from '../StatusLog/StatusLog.vue';

export default {
    name: 'status-log-fieldtype',

    components: { StatusLog },

    mixins: [Fieldtype],

    props: ['meta'],

    inject: ['storeName'],

    data() {
        return {
            showStatusLog: false,
        };
    },

    computed: {
        isCreating() {
            return this.$store.state.publish[this.storeName].values?.id === null;
        },

        orderId() {
            return this.$store.state.publish[this.storeName].values.id
        },

        currentOrderStatus() {
            return this.$store.state.publish[this.storeName].values.order_status
        },

        currentPaymentStatus() {
            return this.$store.state.publish[this.storeName].values.payment_status
        },
    },
}
</script>
