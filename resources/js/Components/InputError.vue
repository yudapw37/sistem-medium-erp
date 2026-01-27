<template>
    <p
        v-if="message"
        :class="['text-sm text-red-600', className]"
        v-bind="$attrs"
    >
        {{ message }}
    </p>
</template>

<script setup>
defineProps({
    message: String,
    className: {
        type: String,
        default: '',
    },
});
</script>

