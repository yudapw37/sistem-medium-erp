<template>
    <DashboardLayout>
        <Head title="Produk" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Produk</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ products.total }} produk terdaftar
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconCirclePlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                    label="Tambah Produk"
                    :href="route('products.create')"
                />
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
            <div class="w-full sm:w-80">
                <Search :url="route('products.index')" placeholder="Cari produk..." />
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
        <template v-if="products.data.length > 0">
            <!-- Grid View -->
            <div
                v-if="viewMode === 'grid'"
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"
            >
                <ProductCard
                    v-for="product in products.data"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <!-- List View -->
            <template v-else>
                <TableCard title="Data Produk">
                    <Table>
                        <TableThead>
                            <tr>
                                <TableTh class="w-10">No</TableTh>
                                <TableTh>Produk</TableTh>
                                <TableTh>Kategori</TableTh>
                                <TableTh>Harga Beli</TableTh>
                                <TableTh>Harga Jual</TableTh>
                                <TableTh>Stok</TableTh>
                                <TableTh></TableTh>
                            </tr>
                        </TableThead>
                        <TableTbody>
                            <tr
                                v-for="(product, i) in products.data"
                                :key="product.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            >
                                <TableTd class="text-center">
                                    {{ ++i + (products.current_page - 1) * products.per_page }}
                                </TableTd>
                                <TableTd>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0"
                                        >
                                            <img
                                                v-if="product.image"
                                                :src="`/storage/products/${product.image}`"
                                                :alt="product.title"
                                                class="w-full h-full object-cover"
                                            />
                                            <div
                                                v-else
                                                class="w-full h-full flex items-center justify-center"
                                            >
                                                <IconPackage :size="16" class="text-slate-400" />
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                                {{ product.title }}
                                            </p>
                                            <p class="text-xs text-slate-500">{{ product.barcode }}</p>
                                        </div>
                                    </div>
                                </TableTd>
                                <TableTd>
                                    <span
                                        class="px-2 py-0.5 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded"
                                    >
                                        {{ product.category?.name }}
                                    </span>
                                </TableTd>
                                <TableTd>{{ formatCurrency(product.buy_price) }}</TableTd>
                                <TableTd class="font-semibold text-primary-600 dark:text-primary-400">
                                    {{ formatCurrency(product.sell_price) }}
                                </TableTd>
                                <TableTd>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-xs font-medium rounded',
                                            product.stock === 0
                                                ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/50 dark:text-danger-400'
                                                : product.stock <= 5
                                                  ? 'bg-warning-100 text-warning-700 dark:bg-warning-900/50 dark:text-warning-400'
                                                  : 'bg-success-100 text-success-700 dark:bg-success-900/50 dark:text-success-400',
                                        ]"
                                    >
                                        {{ product.stock }}
                                    </span>
                                </TableTd>
                                <TableTd>
                                    <div class="flex gap-2">
                                        <Button
                                            type="edit"
                                            :icon="IconPencilCog"
                                            class="border bg-warning-100 border-warning-200 text-warning-600 hover:bg-warning-200 dark:bg-warning-900/50 dark:border-warning-800 dark:text-warning-400"
                                            :href="route('products.edit', product.id)"
                                        />
                                        <Button
                                            type="delete"
                                            :icon="IconTrash"
                                            class="border bg-danger-100 border-danger-200 text-danger-600 hover:bg-danger-200 dark:bg-danger-900/50 dark:border-danger-800 dark:text-danger-400"
                                            :url="route('products.destroy', product.id)"
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
                Belum Ada Produk
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                Tambahkan produk pertama Anda untuk memulai.
            </p>
            <Button
                type="link"
                :icon="IconCirclePlus"
                class="bg-primary-500 hover:bg-primary-600 text-white"
                label="Tambah Produk"
                :href="route('products.create')"
            />
        </div>

        <Pagination v-if="products?.links && products.links.length > 3" :links="products.links" />
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    IconCirclePlus,
    IconDatabaseOff,
    IconPencilCog,
    IconTrash,
    IconLayoutGrid,
    IconList,
    IconPackage,
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
import ProductCard from '@/Components/Dashboard/ProductCard.vue';

defineProps({
    products: Object,
});

const viewMode = ref('grid');

const formatCurrency = (value = 0) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>


