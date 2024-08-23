<template>
    <component v-if="icon" :is="icon" />
</template>

<script>
import { defineAsyncComponent } from 'vue';

export default {

    props: {
        name: String,
    },

    data() {
        return {
            icon: null,
        }
    },

    mounted() {
        this.icon = this.evaluateIcon();
    },

    watch: {
        name() {
            this.icon = this.evaluateIcon();
        }
    },

    methods: {
        evaluateIcon() {
            if (this.name.startsWith('<svg')) {
                return defineAsyncComponent(() => {
                    return new Promise(resolve => resolve({ template: this.name }));
                });
            }

            return defineAsyncComponent(() => {
                return import(`./../../svg/${this.name}.svg`);
            });
        },
    }
}
</script>