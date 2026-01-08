<template>
    <div v-if="!isCreating">
        <Button
            class="w-full"
            :text="__('Visit Status Log')"
            icon="history"
            target="_blank"
            @click="showStatusLog = true"
        />

        <Stack
            ref="statusLogStack"
            size="narrow"
            :title="__('Status Log')"
            v-model:open="showStatusLog"
        >
            <status-log
                slot-scope="{ close }"
                :index-url="meta.indexUrl"
                :order-id="orderId"
                :resend-notifications-url="meta.resendNotificationsUrl"
                :order-statuses="meta.orderStatuses"
                :payment-statuses="meta.paymentStatuses"
                :current-order-status="currentOrderStatus"
                :current-payment-status="currentPaymentStatus"
                @closed="$refs.statusLogStack.close()"
            />
        </Stack>
    </div>
</template>

<script>
import StatusLog from '../StatusLog/StatusLog.vue';
import { FieldtypeMixin } from '@statamic/cms';
import { Button, Stack } from '@statamic/cms/ui'

export default {
    name: 'status-log-fieldtype',

    components: { Button, Stack, StatusLog },

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
