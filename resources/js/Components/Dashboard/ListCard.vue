<template>
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden"
    >
        <div class="p-5 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <div class="p-2 rounded-lg bg-primary-100 dark:bg-primary-900/30">
                    <component :is="icon" :size="18" class="text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ title }}</h3>
                    <p v-if="subtitle" class="text-xs text-slate-500 dark:text-slate-400">{{ subtitle }}</p>
                </div>
            </div>
        </div>
        <div class="p-5">
            <slot>
                <div class="flex h-32 items-center justify-center text-sm text-slate-400 dark:text-slate-500">
                    {{ emptyMessage }}
                </div>
            </slot>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    subtitle: String,
    icon: [Object, Function],
    emptyMessage: String,
});
</script>

