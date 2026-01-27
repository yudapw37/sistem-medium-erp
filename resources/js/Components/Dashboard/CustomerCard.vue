<template>
    <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-accent-400 to-accent-600 flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
                    {{ customer.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">
                            {{ customer.name }}
                        </h3>
                        <span v-if="customer.is_member" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 uppercase">
                            Member
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-2 mb-4">
            <div v-if="customer.no_telp" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <IconPhone :size="16" />
                <span>{{ customer.no_telp }}</span>
            </div>
            <div v-if="customer.address" class="flex items-start gap-2 text-sm text-slate-500 dark:text-slate-400">
                <IconMapPin :size="16" class="flex-shrink-0 mt-0.5" />
                <span class="line-clamp-2">{{ customer.address }}</span>
            </div>
        </div>

        <div class="flex gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <Link
                :href="route('customers.edit', customer.id)"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg bg-warning-100 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:text-warning-400 text-sm font-medium transition-colors"
            >
                <IconPencilCog :size="16" />
                <span>Edit</span>
            </Link>
            <Button
                type="delete"
                :icon="IconTrash"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg bg-danger-100 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:text-danger-400 text-sm font-medium"
                :url="route('customers.destroy', customer.id)"
                label="Hapus"
            />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { IconPhone, IconMapPin, IconPencilCog, IconTrash } from '@tabler/icons-vue';
import Button from './Button.vue';

defineProps({
    customer: Object,
});
</script>

