<template>
    <div class="max-w-lg mt-4 mx-auto">

        <div class="rounded p-6 lg:px-20 lg:py-10 shadow bg-white dark:bg-dark-600 dark:shadow-dark">
            <header class="text-center mb-16">
                <h1 class="mb-6">{{ __('Create Tax Class') }}</h1>
<!--                <p class="text-gray" v-text="__('messages.collection_configure_intro')" />-->
            </header>
            <div class="mb-10">
                <label class="font-bold text-base mb-1" for="name">{{ __('Name') }}</label>
                <input type="text" v-model="name" class="input-text" autofocus tabindex="1">
<!--                <div class="text-2xs text-gray-600 mt-2 flex items-center">-->
<!--                    {{ __('messages.collection_configure_title_instructions') }}-->
<!--                </div>-->
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <button tabindex="4" class="btn-primary mx-auto btn-lg" :disabled="! canSubmit" @click="submit">
                {{ __('Create Tax Class')}}
            </button>
        </div>
    </div>
</template>

<script>
export default {

    props: {
        route: {
            type: String
        }
    },

    data() {
        return {
            name: null,
        }
    },

    computed: {
        canSubmit() {
            return Boolean(this.name);
        },
    },

    methods: {
        submit() {
            this.$axios.post(this.route, {name: this.name}).then(response => {
                window.location = response.data.redirect;
            }).catch(error => {
                this.$toast.error(error.response.data.message);
            });
        }
    },

    mounted() {
        this.$keys.bindGlobal(['return'], e => {
            if (this.canSubmit) {
                this.submit();
            }
        });
    }
}
</script>
