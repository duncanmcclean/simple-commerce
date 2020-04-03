<template>
    <dropdown-item
        v-if="type === 'standard'"
        :text="text"
        :redirect="action"
    ></dropdown-item>

    <dropdown-item
        v-else-if="type === 'delete'"
        class="warning"
        :text="text"
        @click="isDeleting = true"
    >
        <confirmation-modal
            v-if="isDeleting"
            :title="modalTitle"
            :bodyText="modalText"
            buttonText="Confirm"
            :danger="true"
            @confirm="doAction"
            @cancel="isDeleting = false"
        ></confirmation-modal>
    </dropdown-item>
</template>

<script>
    import axios from 'axios'

    export default {
        name: "ActionItem",

        props: {
            type: {
                type: String, // either 'standard' or 'delete'
                required: true
            },
            text: {
                type: String,
                required: true
            },
            action: {
                type: String,
                required: true
            },
            method: {
                type: String,
                required: false
            },
            modalTitle: {
                type: String,
                required: false,
            },
            modalText: {
                type: String,
                required: false,
            }
        },

        data() {
            return {
                isDeleting: false
            }
        },

        methods: {
            doAction() {
                this.isDeleting = true;

                axios({
                    method: this.method,
                    url: this.action,
                }).then(response => {
                    window.location.reload();
                    this.$toast.success('Successfully deleted.');
                }).catch(error => {
                    this.$toast.error('Something went wrong.');
                })
            },

            close() {
                this.$parent.close();
            }
        }
    }
</script>

<style scoped>

</style>
