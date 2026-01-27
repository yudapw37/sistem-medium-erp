<template>
    <div
        :class="[
            sidebarOpen ? 'w-[260px]' : 'w-[80px]',
            'hidden md:flex flex-col min-h-screen border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 transition-all duration-300 ease-in-out',
        ]"
    >
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 border-b border-slate-100 dark:border-slate-800">
            <div v-if="sidebarOpen" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">K</span>
                </div>
                <span class="text-xl font-bold text-slate-800 dark:text-white">Medium ERP</span>
            </div>
            <div v-else class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                <span class="text-white font-bold text-sm">K</span>
            </div>
        </div>



        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-3 scrollbar-thin">
            <div v-for="(section, index) in menuNavigation" :key="index">
                <div
                    v-if="section.details.some((detail) => detail.permissions === true)"
                    class="mb-2"
                >
                    <!-- Section Title -->
                    <div v-if="sidebarOpen" class="px-4 py-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-600">
                            {{ section.title }}
                        </span>
                    </div>

                    <!-- Menu Items -->
                    <div :class="sidebarOpen ? '' : 'flex flex-col items-center'">
                        <template v-for="(detail, idx) in section.details" :key="idx">
                            <LinkItemDropdown
                                v-if="detail.hasOwnProperty('subdetails') && detail.permissions"
                                :title="detail.title"
                                :icon="detail.icon"
                                :data="detail.subdetails"
                                :access="detail.permissions"
                                :sidebar-open="sidebarOpen"
                            />
                            <LinkItem
                                v-else-if="detail.permissions"
                                :title="detail.title"
                                :icon="detail.icon"
                                :href="detail.href"
                                :access="detail.permissions"
                                :sidebar-open="sidebarOpen"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Footer -->
        <div class="mt-auto">
            <!-- Version -->
            <div v-if="sidebarOpen" class="pb-4 px-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                <p class="text-[10px] text-slate-400 dark:text-slate-600 text-center">
                    Medium ERP v2.0
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import useMenu from '@/Utils/Menu';
import LinkItem from './LinkItem.vue';
import LinkItemDropdown from './LinkItemDropdown.vue';

defineProps({
    sidebarOpen: Boolean,
});

const { auth } = usePage().props;
const menuNavigation = useMenu();
</script>

