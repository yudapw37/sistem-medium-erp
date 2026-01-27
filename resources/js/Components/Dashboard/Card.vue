<template>
    <component :is="form ? 'form' : 'div'" :onSubmit="form ? form : undefined">
        <!-- Header -->
        <div
            :class="[
                'px-5 py-4 rounded-t-2xl border border-b-0 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800',
                className,
            ]"
        >
            <div class="flex items-center gap-2.5">
                <div
                    v-if="icon"
                    class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center text-primary-600 dark:text-primary-400"
                >
                    <component :is="icon" />
                </div>
                <h3 class="font-semibold text-base text-slate-800 dark:text-slate-200">
                    {{ title }}
                </h3>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-white dark:bg-slate-900 px-5 py-5 border-x border-slate-200 dark:border-slate-800">
            <slot />
        </div>

        <!-- Footer -->
        <div
            v-if="$slots.footer"
            :class="[
                'px-5 py-4 rounded-b-2xl border border-t-0 bg-slate-50 dark:bg-slate-800/50 border-slate-200 dark:border-slate-800',
                className,
            ]"
        >
            <slot name="footer" />
        </div>
    </component>
</template>

<script setup>
defineProps({
    icon: [Object, Function],
    title: String,
    className: String,
    form: Function,
});
</script>

