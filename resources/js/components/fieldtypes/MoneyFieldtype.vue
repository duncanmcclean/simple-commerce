<template>
    <div>
        <text-input
            :value="value"
            :prepend="meta.symbol"
            :is-read-only="readOnly"
            placeholder="00.00"
            @input="update"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],
}
</script>
