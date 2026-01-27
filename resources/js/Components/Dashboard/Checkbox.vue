<template>
    <div>
        <div class="flex flex-row items-center gap-2">
            <input
                type="checkbox"
                :class="[
                    'rounded-md bg-white border-gray-200 dark:bg-gray-950 dark:border-gray-800 checked:bg-teal-500',
                ]"
                v-bind="$attrs"
            />
            <label v-if="label" class="text-sm text-gray-700 dark:text-gray-400">
                {{ label }}
            </label>
            <small v-if="errors" class="text-xs text-red-500">{{ errors }}</small>
        </div>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    errors: String,
});
</script>

