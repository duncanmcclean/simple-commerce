<template>

    <div>
        <breadcrumb v-if="breadcrumbs" :url="breadcrumbs[0].url" :title="breadcrumbs[0].text" />

        <div class="flex items-center mb-6">
            <h1 class="flex-1">
                <div class="flex items-center">
                    <span v-html="$options.filters.striptags(__(title))" />
                </div>
            </h1>

            <dropdown-list class="rtl:ml-4 ltr:mr-4">
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
                    :show-options="!revisionsEnabled && !isInline"
                    button-class="btn-primary"
                    :preferences-prefix="preferencesPrefix"
                >
                    <button
                        class="btn-primary"
                        :disabled="!canSave"
                        @click.prevent="save"
                        v-text="saveText"
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
<!--                        <template #actions="{ shouldShowSidebar }">-->
<!--                            <div class="card p-0 mb-5">-->
<!--                                <div-->
<!--                                    class="flex items-center justify-between px-4 py-2"-->
<!--                                    :class="{ 'border-t dark:border-dark-900': true }"-->
<!--                                >-->
<!--                                    TODO: show order statuses here-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </template>-->
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

        <publish-actions
            v-if="confirmingPublish"
            :actions="actions"
            :published="published"
            :collection="collectionHandle"
            :reference="initialReference"
            :publish-container="publishContainer"
            @closed="confirmingPublish = false"
            @saving="saving = true"
            @saved="publishActionCompleted"
        />
    </div>

</template>


<script>
import PublishActions from './PublishActions.vue'
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
        PublishActions,
        SaveButtonOptions,
    },

    props: {
        publishContainer: String,
        initialReference: String,
        initialFieldset: Object,
        initialValues: Object,
        initialMeta: Object,
        initialTitle: String,
        collectionHandle: String,
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
            preferencesPrefix: `collections.${this.collectionHandle}`,
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
            return ! this.$progress.isComplete();
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

        saveText() {
            switch(true) {
                default:
                    return __('Save & Publish');
            }
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
            this.$progress.loading(`${this.publishContainer}-order-publish-form`, saving);
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
            if (! this.canSave) {
                this.quickSave = false;
                return;
            }

            this.saving = true;
            this.clearErrors();

            setTimeout(() => this.runBeforeSaveHook(), 151); // 150ms is the debounce time for fieldtype updates
        },

        runBeforeSaveHook() {
            this.$refs.container.saving();

            Statamic.$hooks.run('order.saving', {
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
            const payload = { ...this.visibleValues, ...{
                    _blueprint: this.fieldset.handle,
                }};

            this.$axios[this.method](this.actions.save, payload).then(response => {
                this.saving = false;
                if (! response.data.saved) {
                    return this.$toast.error(__(`Couldn't save order`));
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
                .run('order.saved', {
                    reference: this.initialReference,
                    response
                })
                .then(() => {
                    let nextAction = this.quickSave ? 'continue_editing' : this.afterSaveOption;

                    // If the user has opted to create another order, redirect them to create page.
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

        publishActionCompleted({ published, isWorkingCopy, response }) {
            this.saving = false;
            if (published !== undefined) {
                this.$refs.container.setFieldValue('published', published);
                this.initialPublished = published;
            }
            this.$refs.container.saved();
            this.confirmingPublish = false;
            this.title = response.data.data.title;
            this.values = this.resetValuesFromResponse(response.data.data.values);
            this.$nextTick(() => this.$emit('saved', response));
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

    },

    mounted() {
        this.saveKeyBinding = this.$keys.bindGlobal(['mod+return'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
            this.save();
        });

        this.quickSaveKeyBinding = this.$keys.bindGlobal(['mod+s'], e => {
            e.preventDefault();
            if (this.confirmingPublish) return;
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