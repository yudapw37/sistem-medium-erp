<template>
    <tr>
        <td :colspan="colSpan">
            <div class="flex items-center justify-center h-96">
                <div class="text-center">
                    <slot />
                    <div class="mt-5">{{ message }}</div>
                </div>
            </div>
        </td>
    </tr>
</template>

<script setup>
defineProps({
    colSpan: Number,
    message: String,
});
</script>

