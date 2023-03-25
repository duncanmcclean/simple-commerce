<template>
    <div>
        <button class="btn flex items-center" @click="open = !open">
            {{ __('Configure') }}
            <svg viewBox="0 0 10 6.5" class="ml-1 w-2">
                <path
                    fill="currentColor"
                    d="M9.9 1.4 5 6.4l-5-5L1.4 0 5 3.5 8.5 0l1.4 1.4z"
                ></path>
            </svg>
        </button>

        <div
            class="popover-container dropdown-list"
            :class="{ 'popover-open': open }"
        >
            <div class="popover">
                <div
                    class="popover-content bg-white shadow-popover rounded-md p-0 overflow-hidden"
                >
                    <div class="outline-none text-left">
                        <header
                            class="border-y px-2 py-2 text-sm bg-white font-medium"
                        >
                            {{ __('Available Widgets') }}
                        </header>

                        <div
                            class="flex flex-col space-y-1 py-2 px-3 select-none"
                        >
                            <div
                                v-for="widget in widgets"
                                :key="widget.handle"
                                class="column-picker-item"
                            >
                                <label class="flex items-center cursor-pointer"
                                    ><input
                                        type="checkbox"
                                        v-model="
                                            selectedWidgets[`${widget.handle}`]
                                        "
                                        @disabled="saving"
                                        @change="setSharedStateColumns"
                                        class="mr-2"
                                    />
                                    {{ widget.name }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex border-t text-gray-800">
                        <button
                            class="p-2 hover:bg-gray-100 rounded-bl text-xs flex-1 text-center hover:text-gray-800"
                            @click="reset"
                            @disabled="saving"
                        >
                            {{ __('Reset') }}
                        </button>
                        <button
                            class="p-2 hover:bg-gray-100 text-blue flex-1 rounded-br border-l text-xs text-center"
                            @click="save"
                            @disabled="saving"
                        >
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import HasPreferences from '../../../../vendor/statamic/cms/resources/js/components/data-list/HasPreferences.js'

export default {
    props: {
        widgets: Array,
    },

    mixins: [HasPreferences],

    data() {
        return {
            open: false,
            saving: true,

            preferencesPrefix: 'simple_commerce',

            selectedWidgets: {},
        }
    },

    mounted() {
        this.setInitialState()
    },

    methods: {
        setInitialState() {
            if (this.getPreference('overview_widgets')) {
                this.selectedWidgets = this.getPreference('overview_widgets')

                this.saving = false

                return
            }

            let selectedWidgets = {}

            this.widgets.forEach((widget) => {
                selectedWidgets[`${widget.handle}`] = true
            })

            this.selectedWidgets = selectedWidgets

            this.saving = false
        },

        setSharedStateColumns() {
            this.$emit('selectedWidgets', this.selectedWidgets)
        },

        save() {
            this.saving = true

            this.setPreference('overview_widgets', this.selectedWidgets)

            this.open = false
        },

        reset() {
            this.saving = true

            this.setInitialState()
        },
    },
}
</script>
