<template>
    <div>
        <text-input :type="inputType" :value="value" prepend="£" @input="update" />
    </div>
</template>

<script>
    export default {
        mixins: [Fieldtype],

        data() {
            return {
                //
            }
        },

        computed: {
            inputType() {
                return this.show;
            }
        }
    };
</script>
