<template>
    <div class="relationship-input @container w-full h-full">
        <p v-if="value == null" class="text-sm py-1">{{ __('No Gateway') }}</p>

        <div
            v-else
            class="grid grid-cols-1 gap-2 outline-hidden @xl:grid-cols-2"
            tabindex="0"
        >
            <div class="shadow-ui-sm relative z-2 flex w-full h-full items-center gap-2 rounded-lg border border-gray-200 bg-white px-1.5 py-1.5 mb-1.5 last:mb-0 text-base dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:bg-gray-900 dark:inset-shadow-2xs dark:inset-shadow-black">
                <div class="flex flex-1 items-center">
                    <a class="line-clamp-1 text-sm text-gray-600 dark:text-gray-300" :href="display.url" target="_blank">
                        {{ display.text }}
                    </a>

                    <div class="flex flex-1 items-center justify-end">
                        <div
                            v-text="gatewayName"
                            class="text-2xs tracking-tight hidden me-2 whitespace-nowrap text-gray-500 @sm:block"
                        />

                        <div class="flex items-center" v-if="!readOnly">
                            <ItemActions
                                :url="actionUrl"
                                :actions="actions"
                                :item="entry"
                                @started="actionStarted"
                                @completed="actionCompleted"
                                v-slot="{ actions }"
                            >
                                <Dropdown placement="left-start">
                                    <DropdownMenu>
                                        <DropdownItem
                                            v-for="action in actions"
                                            :key="action.handle"
                                            :text="__(action.title)"
                                            icon="edit"
                                            :class="{ 'text-red-500': action.dangerous }"
                                            @click="action.run"
                                        />
                                    </DropdownMenu>
                                </Dropdown>
                            </ItemActions>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { FieldtypeMixin, ItemActions } from 'statamic'
import { Dropdown, DropdownMenu, DropdownItem } from '@statamic/ui'

export default {
    name: 'gateway-fieldtype',

    mixins: [FieldtypeMixin],

    components: { ItemActions, Dropdown, DropdownMenu, DropdownItem },

    props: ['meta'],

    data() {
        return {
            gatewayData: this.value?.data,
            entry: this.value?.entry,
            actions: this.value?.actions,
            actionUrl: this.value?.action_url,
            gatewayClass: this.value?.gateway_class,
            display: this.value?.display,
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
