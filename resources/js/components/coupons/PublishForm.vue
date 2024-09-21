<template>

    <div>
        <breadcrumb v-if="breadcrumbs" :url="breadcrumbs[0].url" :title="breadcrumbs[0].text" />

        <div class="flex items-center mb-6">
            <h1 class="flex-1">
                <div class="flex items-center">
                    <span v-html="$options.filters.striptags(__(title))" />
                </div>
            </h1>

            <dropdown-list v-if="itemActions.length" class="rtl:ml-4 ltr:mr-4">
                <data-list-inline-actions
                    :item="values.id"
                    :url="itemActionUrl"
                    :actions="itemActions"
                    :is-dirty="isDirty"
                    @started="actionStarted"
                    @completed="actionCompleted"
                />
            </dropdown-list>

            <div class="hidden md:flex items-center">
                <save-button-options
                    v-if="!readOnly"
                    :show-options="!isInline"
                    button-class="btn-primary"
                    :preferences-prefix="preferencesPrefix"
                >
                    <button
                        class="btn-primary"
                        :disabled="!canSave"
                        @click.prevent="save"
                        v-text="`Save`"
                    />
                </save-button-options>
            </div>

            <slot name="action-buttons-right" />
        </div>

        <publish-container
            v-if="fieldset"
            ref="container"
            :name="publishContainer"
            :blueprint="fieldset"
            :values="values"
            :reference="initialReference"
            :meta="meta"
            :errors="errors"
            :track-dirty-state="trackDirtyState"
            @updated="values = $event"
        >
            <div slot-scope="{ container, components, setFieldMeta }">
                <component
                    v-for="component in components"
                    :key="component.id"
                    :is="component.name"
                    :container="container"
                    v-bind="component.props"
                    v-on="component.events"
                />

                <transition name="live-preview-tabs-drop">
                    <publish-tabs
                        v-show="tabsVisible"
                        :read-only="readOnly"
                        :syncable="hasOrigin"
                        @updated="setFieldValue"
                        @meta-updated="setFieldMeta"
                        @synced="syncField"
                        @desynced="desyncField"
                        @focus="container.$emit('focus', $event)"
                        @blur="container.$emit('blur', $event)"
                    >
                        <template #actions="{ shouldShowSidebar }">
                            <div class="card p-0 mb-5">
                                <header class="publish-section-header @container">
                                    <div class="publish-section-header-inner">
                                        <label class="text-base font-semibold">Summary</label>
                                    </div>
                                </header>
                                <div class="px-4 @lg:px-6 pt-4 pb-3">
<!--                                    TODO: Make these translatable-->
                                    <ul class="list-disc ltr:pl-3 flex flex-col gap-y-1.5 text-sm">
                                        <li v-if="values.type === 'fixed' && values.value?.value">
                                            <span class="font-semibold" v-text="formatCurrency(values.value.value)"></span> off entire order
                                        </li>

                                        <li v-if="values.type === 'percentage' && values.value?.value">
                                            <span class="font-semibold" v-text="`${values.value.value}%`"></span> off entire order
                                        </li>

                                        <li v-if="values.minimum_cart_value">
                                            Redeemable when items total is above <span v-text="formatCurrency(this.values.minimum_cart_value)"></span>
                                        </li>

                                        <li v-if="values.customer_eligibility === 'all'">
                                            {{ __(`Redeemable by all customers`) }}
                                        </li>

                                        <li v-if="values.customer_eligibility === 'specific_customers'">
                                            Only redeemable by specific customers
                                        </li>

                                        <li v-if="values.maximum_uses">
                                            Can only be used {{ values.maximum_uses }} times
                                        </li>

                                        <li v-if="values.products.length > 0">
                                            Can only be used when certain products are part of the order
                                        </li>

                                        <li v-if="values.valid_from?.date">
                                            Redeemable after {{ values.valid_from.date }}
                                        </li>

                                        <li v-if="values.expires_at?.date">
                                            Redeemable until {{ values.expires_at.date }}
                                        </li>
                                    </ul>

                                    <ul v-if="!isCreating" class="list-disc ltr:pl-3 flex flex-col gap-y-1.5 text-sm mt-3 pt-3 border-t dark:border-dark-900">
                                        <li>Redeemed {{ values.redeemed_count }} times</li>
                                    </ul>
                                </div>
                            </div>
                        </template>
                    </publish-tabs>
                </transition>
            </div>
        </publish-container>

        <div class="md:hidden mt-6 flex items-center">
            <button
                v-if="!readOnly"
                class="btn-lg btn-primary w-full"
                :disabled="!canSave"
                @click.prevent="save">
                Save
            </button>
        </div>
    </div>

</template>


<script>
import SaveButtonOptions from '../../../../vendor/statamic/cms/resources/js/components/publish/SaveButtonOptions.vue'
import HasPreferences from '../../../../vendor/statamic/cms/resources/js/components/data-list/HasPreferences'
import HasHiddenFields from '../../../../vendor/statamic/cms/resources/js/components/publish/HasHiddenFields'
import HasActions from '../../../../vendor/statamic/cms/resources/js/components/publish/HasActions'

export default {

    mixins: [
        HasPreferences,
        HasHiddenFields,
        HasActions,
    ],

    components: {
        SaveButtonOptions,
    },

    props: {
        publishContainer: String,
        initialReference: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        initialReadOnly: Boolean,
        breadcrumbs: Array,
        initialActions: Object,
        method: String,
        isCreating: Boolean,
        isInline: Boolean,
        createAnotherUrl: String,
        initialListingUrl: String,
    },

    data() {
        return {
            actions: this.initialActions,
            saving: false,
            trackDirtyState: true,
            fieldset: this.initialFieldset,
            title: this.initialTitle,
            values: _.clone(this.initialValues),
            meta: _.clone(this.initialMeta),
            error: null,
            errors: {},
            tabsVisible: true,
            state: 'new',
            preferencesPrefix: `simple-commerce.coupons`,
            readOnly: this.initialReadOnly,

            saveKeyBinding: null,
            quickSaveKeyBinding: null,
            quickSave: false,
        }
    },

    computed: {

        hasErrors() {
            return this.error || Object.keys(this.errors).length;
        },

        somethingIsLoading() {
            return !this.$progress.isComplete();
        },

        canSave() {
            return !this.readOnly && !this.somethingIsLoading;
        },

        listingUrl() {
            return `${this.initialListingUrl}`;
        },

        isBase() {
            return this.publishContainer === 'base';
        },

        isDirty() {
            return this.$dirty.has(this.publishContainer);
        },

        afterSaveOption() {
            return this.getPreference('after_save');
        },

        direction() {
            return this.$config.get('direction', 'ltr');
        },

    },

    watch: {

        saving(saving) {
            this.$progress.loading(`${this.publishContainer}-coupon-publish-form`, saving);
        },

        title(title) {
            if (this.isBase) {
                const arrow = this.direction === 'ltr' ? '‹' : '›';
                document.title = `${title} ${arrow} ${this.breadcrumbs[1].text} ${arrow} ${this.breadcrumbs[0].text} ${arrow} ${__('Statamic')}`;
            }
        },

    },

    methods: {

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        save() {
            if (!this.canSave) {
                this.quickSave = false;
                return;
            }

            this.saving = true;
            this.clearErrors();

            setTimeout(() => this.runBeforeSaveHook(), 151); // 150ms is the debounce time for fieldtype updates
        },

        runBeforeSaveHook() {
            this.$refs.container.saving();

            Statamic.$hooks.run('coupon.saving', {
                values: this.values,
                container: this.$refs.container,
                storeName: this.publishContainer,
            })
                .then(this.performSaveRequest)
                .catch(error => {
                    this.saving = false;
                    this.$toast.error(error || 'Something went wrong');
                });
        },

        performSaveRequest() {
            // Once the hook has completed, we need to make the actual request.
            // We build the payload here because the before hook may have modified values.
            const payload = {
                ...this.visibleValues, ...{
                    _blueprint: this.fieldset.handle,
                }
            };

            this.$axios[this.method](this.actions.save, payload).then(response => {
                this.saving = false;
                if (!response.data.saved) {
                    return this.$toast.error(__(`Couldn't save coupon`));
                }
                this.title = response.data.data.title;
                this.$toast.success(__('Saved'));
                this.$refs.container.saved();
                this.runAfterSaveHook(response);
            }).catch(error => this.handleAxiosError(error));
        },

        runAfterSaveHook(response) {
            // Once the save request has completed, we want to run the "after" hook.
            // Devs can do what they need and we'll wait for them, but they can't cancel anything.
            Statamic.$hooks
                .run('coupon.saved', {
                    reference: this.initialReference,
                    response
                })
                .then(() => {
                    let nextAction = this.quickSave ? 'continue_editing' : this.afterSaveOption;

                    // If the user has opted to create another coupon, redirect them to create page.
                    if (!this.isInline && nextAction === 'create_another') {
                        window.location = this.createAnotherUrl;
                    }

                    // If the user has opted to go to listing (default/null option), redirect them there.
                    else if (!this.isInline && nextAction === null) {
                        window.location = this.listingUrl;
                    }

                        // Otherwise, leave them on the edit form and emit an event. We need to wait until after
                        // the hooks are resolved because if this form is being shown in a stack, we only
                    // want to close it once everything's done.
                    else {
                        clearTimeout(this.trackDirtyStateTimeout);
                        this.trackDirtyState = false;
                        this.values = this.resetValuesFromResponse(response.data.data.values);
                        this.trackDirtyStateTimeout = setTimeout(() => (this.trackDirtyState = true), 350);
                        this.$nextTick(() => this.$emit('saved', response));
                    }

                    this.quickSave = false;
                }).catch(e => console.error(e));
        },

        handleAxiosError(e) {
            this.saving = false;
            if (e.response && e.response.status === 422) {
                const { message, errors } = e.response.data;
                this.error = message;
                this.errors = errors;
                this.$toast.error(message);
                this.$reveal.invalid();
            } else if (e.response) {
                this.$toast.error(e.response.data.message);
            } else {
                this.$toast.error(e || 'Something went wrong');
            }
        },

        setFieldValue(handle, value) {
            this.$refs.container.setFieldValue(handle, value);
        },

        afterActionSuccessfullyCompleted(response) {
            if (response.data) {
                this.title = response.data.title;
                this.values = this.resetValuesFromResponse(response.data.values);
                this.itemActions = response.data.itemActions;
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'GBP',
            }).format(amount);
        },

    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], e => {
            e.preventDefault();
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            this.quickSave = true;
            this.save();
        });
    },

    created() {
        window.history.replaceState({}, document.title, document.location.href.replace('created=true', ''));
    },

    unmounted() {
        clearTimeout(this.trackDirtyStateTimeout);
    },

    destroyed() {
        this.saveKeyBinding.destroy();
        this.quickSaveKeyBinding.destroy();
    }

}
</script>