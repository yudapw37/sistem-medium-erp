<template>
    <DashboardLayout>
        <Head title="Kategori" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Kategori</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ categories.total || categories.data?.length || 0 }} kategori terdaftar
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Kategori"
                    :href="route('categories.create')"
                />
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
            <div class="w-full sm:w-80">
                <Search :url="route('categories.index')" placeholder="Cari kategori..." />
            </div>
            <div class="flex items-center gap-2">
                <button
                    @click="viewMode = 'grid'"
                    :class="[
                        'p-2.5 rounded-lg transition-colors',
                        viewMode === 'grid'
                            ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400'
                            : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                    ]"
                    title="Grid View"
                >
                    <IconLayoutGrid :size="20" />
                </button>
                <button
                    @click="viewMode = 'list'"
                    :class="[
                        'p-2.5 rounded-lg transition-colors',
                        viewMode === 'list'
                            ? 'bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400'
                            : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                    ]"
                    title="List View"
                >
                    <IconList :size="20" />
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-if="categories.data.length > 0">
            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid'"
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"
            >
                <CategoryCard
                    v-for="category in categories.data"
                    :key="category.id"
                    :category="category"
                />
            </div>

            <!-- List View -->
            <template v-else>
                <TableCard title="Data Kategori">
                    <Table>
                        <TableThead>
                            <tr>
                                <TableTh class="w-10">No</TableTh>
                                <TableTh>Kategori</TableTh>
                                <TableTh>Deskripsi</TableTh>
                                <TableTh></TableTh>
                            </tr>
                        </TableThead>
                        <TableTbody>
                            <tr
                                v-for="(category, i) in categories.data"
                                :key="category.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-center">
                                    {{ ++i + (categories.current_page - 1) * categories.per_page }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0"
                                        >
                                            <img
                                                v-if="category.image"
                                                :src="category.image"
                                                :alt="category.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="w-full h-full flex items-center justify-center"
                                            >
                                                <IconCategory :size="20" class="text-slate-400" />
                                            </div>
                                        </div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                            {{ category.name }}
                                        </p>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                                        {{ category.description || '-' }}
                                    </p>
                                </TableTd>
                                <TableTd>
                                    <div class="flex gap-2">
                                        <Button
                                            type="edit"
                                            :icon="IconPencilCog"
                                            class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                            :href="route('categories.edit', category.id)"
                                        />
                                        <Button
                                            type="delete"
                                            :icon="IconTrash"
                                            class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                            :url="route('categories.destroy', category.id)"
                                        />
                                    </div>
                                </TableTd>
                            </tr>
                        </TableTbody>
                    </Table>
                </TableCard>
            </template>
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
                Belum Ada Kategori
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan kategori pertama Anda.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Kategori"
                :href="route('categories.create')"
            />
        </div>

        <Pagination v-if="categories?.links && categories.links.length > 3" :links="categories.links" />
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconPencilCog,
    IconTrash,
    IconLayoutGrid,
    IconList,
    IconCategory,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import CategoryCard from '@/Components/Dashboard/CategoryCard.vue';

defineProps({
    categories: Object,
});

const viewMode = ref('grid');

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


