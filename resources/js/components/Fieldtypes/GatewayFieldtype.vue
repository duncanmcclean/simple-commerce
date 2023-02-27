<template>
    <div class="relationship-input">
        <p v-if="value == null" class="text-sm py-1">{{ __('No Gateway') }}</p>

        <div
            v-else
            class="relationship-input-items space-y-1 outline-none"
            tabindex="0"
        >
            <div class="item select-none item outline-none" tabindex="0">
                <div class="item-inner">
                    <a :href="display.url" target="_blank">
                        {{ display.text }}
                    </a>
                </div>

                <div
                    class="text-4xs text-grey-60 uppercase whitespace-no-wrap mr-1"
                >
                    {{ gatewayName }}
                </div>

                <div class="pr-1 flex items-center" v-if="!readOnly">
                    <dropdown-list>
                        <data-list-inline-actions
                            :item="entry"
                            :url="actionUrl"
                            :actions="actions"
                            @started="actionStarted"
                            @completed="actionCompleted"
                        />
                    </dropdown-list>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// import HasActions from '../../../../vendor/statamic/cms/resources/js/components/data-list/HasActions.vue';

export default {
    name: 'gateway-fieldtype',

    mixins: [
        Fieldtype,
        // HasActions
    ],

    props: ['meta'],

    data() {
        return {
            gatewayData: this.value.data,
            entry: this.value.entry,
            actions: this.value.actions,
            actionUrl: this.value.action_url,
            gatewayClass: this.value.gateway_class,
            display: this.value.display,
        }
    },

    computed: {
        display() {
            return this.value.display
        },

        gatewayName() {
            const gatewayClass = this.value.gateway_class

            const gateway = this.meta.gateways.find((gateway) => {
                return gateway.class === gatewayClass
            })

            return gateway.name
        },
    },

    methods: {
        actionStarted() {
            //
        },

        actionCompleted() {
            this.$events.$emit('clear-selections')
            this.$events.$emit('reset-action-modals')

            this.$toast.success(__('Action completed'))
        },
    },
}
</script>
