<template>
    <div
        class="relative block cursor-pointer space-y-2 px-3 py-2 text-sm hover:[&_.revision-message]:underline"
        :class="{
            'status-published': isCurrent,
        }"
    >
        <div class="flex gap-3">
            <Avatar v-if="event.user" :user="event.user" class="size-6 shrink-0 mt-1" />
            <div v-else class="size-6 shrink-0 mt-1" />

            <div class="grid gap-1">
                <div class="revision-message font-medium">
                    {{ statusType }} changed to <span class="font-medium">{{ status }}</span>
                </div>
                <div v-if="event.data.reason" class="revision-message font-medium text-xs" v-text="event.data.reason" />
                <Subheading class="text-xs text-gray-500! dark:text-gray-400!">
                    {{ time }}
                    <template v-if="event.user">
                        by {{ event.user.name || event.user.email }}
                    </template>
                </Subheading>
            </div>

            <div class="flex items-center gap-1 ml-auto">
                <Badge
                    v-if="isCurrent"
                    size="sm"
                    color="gray"
                    :text="__('Current')"
                />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { DateFormatter } from '@statamic/cms';
import { Badge, Subheading, Avatar } from '@statamic/cms/ui'

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

        time() {
            return DateFormatter.format(this.event.timestamp * 1000, 'time');
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
