<template>
    <div
        :class="[
            'group bg-white dark:bg-slate-900 rounded-2xl border-2 overflow-hidden hover:shadow-lg transition-all duration-200',
            isSelected
                ? 'border-primary-500 dark:border-primary-600 hover:border-primary-300 dark:hover:border-primary-700'
                : 'border-slate-200 dark:border-slate-800 hover:border-primary-300 dark:hover:border-primary-700',
        ]"
    >
        <div class="p-4 flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-lg font-bold">
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                        {{ user.name }}
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1">
                        <IconMail :size="14" />
                        {{ user.email }}
                    </p>
                </div>
            </div>
            <Checkbox :value="user.id" @change="onSelect" :checked="isSelected" />
        </div>

        <div class="px-4 pb-3">
            <div class="flex flex-wrap gap-1.5">
                <span
                    v-for="(role, index) in user.roles"
                    :key="index"
                    class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-accent-100 dark:bg-accent-900/50 text-accent-700 dark:text-accent-400"
                >
                    <IconShield :size="12" />
                    {{ role.name }}
                </span>
            </div>
        </div>

        <div class="flex border-t border-slate-100 dark:border-slate-800">
            <Link
                :href="route('users.edit', user.id)"
                class="flex-1 flex items-center justify-center gap-1.5 py-3 text-warning-600 hover:bg-warning-50 dark:hover:bg-warning-950/50 text-sm font-medium transition-colors"
            >
                <IconPencilCog :size="16" />
                <span>Edit</span>
            </Link>
            <div class="w-px bg-slate-100 dark:bg-slate-800" />
            <button
                @click="onDelete(user.id)"
                class="flex-1 flex items-center justify-center gap-1.5 py-3 text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950/50 text-sm font-medium transition-colors"
            >
                <IconTrash :size="16" />
                <span>Hapus</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { IconMail, IconShield, IconPencilCog, IconTrash } from '@tabler/icons-vue';
import Checkbox from './Checkbox.vue';

defineProps({
    user: Object,
    isSelected: Boolean,
    onSelect: Function,
    onDelete: Function,
});
</script>

