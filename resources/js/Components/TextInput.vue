<template>
    <input
        :type="type"
        :class="[
            'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm',
            className,
        ]"
        ref="inputRef"
        v-bind="$attrs"
    />
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'text',
    },
    className: {
        type: String,
        default: '',
    },
    isFocused: Boolean,
});

const inputRef = ref(null);

onMounted(() => {
    if (props.isFocused && inputRef.value) {
        inputRef.value.focus();
    }
});
</script>

