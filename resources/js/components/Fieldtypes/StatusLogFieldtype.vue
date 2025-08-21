<template>
    <div v-if="!isCreating">
        <Button
            class="w-full"
            :text="__('Visit Status Log')"
            icon="history"
            target="_blank"
            @click="showStatusLog = true"
        />

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
import { FieldtypeMixin } from '@statamic/cms';
import { Button } from '@statamic/cms/ui'

export default {
    name: 'status-log-fieldtype',

    components: { Button, StatusLog },

    mixins: [FieldtypeMixin],

    data() {
        return {
            showStatusLog: false,
        };
    },

    computed: {
        isCreating() {
            return this.publishContainer.values?.id === null;
        },

        orderId() {
            return this.publishContainer.values.id;
        },

        currentOrderStatus() {
            return this.publishContainer.values.order_status;
        },

        currentPaymentStatus() {
            return this.publishContainer.values.payment_status;
        },
    },
}
</script>
