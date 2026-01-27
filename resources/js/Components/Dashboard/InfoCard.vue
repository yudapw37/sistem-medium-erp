<template>
    <div
        class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 transition-all hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700"
    >
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ title }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ value }}</p>
                <p v-if="subtitle" class="mt-1 text-xs text-slate-400 dark:text-slate-500 flex items-center gap-1">
                    <component :is="icon" :size="14" />
                    {{ subtitle }}
                </p>
            </div>
            <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800">
                <component :is="icon" :size="24" class="text-slate-600 dark:text-slate-400" :stroke-width="1.5" />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [Number, String],
    subtitle: String,
    icon: [Object, Function],
});
</script>

