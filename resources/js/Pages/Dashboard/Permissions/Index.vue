<template>
    <DashboardLayout>
        <Head title="Hak Akses" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <IconKey :size="28" class="text-primary-500" />
                        Hak Akses
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ permissions.total || permissions.data?.length || 0 }} hak akses terdaftar
                    </p>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4 w-full sm:w-80">
            <Search :url="route('permissions.index')" placeholder="Cari hak akses..." />
        </div>

        <!-- Permissions Grid -->
        <template v-if="permissions.data.length > 0">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                <div
                    v-for="(permission, i) in permissions.data"
                    :key="permission.id || i"
                    class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 hover:shadow-md hover:border-primary-300 dark:hover:border-primary-700 transition-all"
                >
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/50 flex items-center justify-center">
                            <IconShield :size="16" class="text-primary-600 dark:text-primary-400" />
                        </div>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 truncate">
                            {{ permission.name }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800"
        >
            <div
                class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4"
            >
                <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                Belum Ada Hak Akses
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">Hak akses tidak ditemukan.</p>
        </div>

        <Pagination v-if="permissions?.links && permissions.links.length > 3" :links="permissions.links" />
    </DashboardLayout>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { IconDatabaseOff, IconKey, IconShield } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';

const { permissions } = usePage().props;

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


