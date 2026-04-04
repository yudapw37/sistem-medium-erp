<template>
    <DashboardLayout>
        <Head title="Stock Running" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Running</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Monitoring stock berjalan (stock awal + masuk - keluar).
                    </p>
                </div>
                <Link
                    :href="route('stock-awal.index')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-all"
                >
                    <IconPlus :size="18" />
                    Input Stock Awal
                </Link>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('stock-running.index')" placeholder="Cari kode / nama barang..." />
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                <div class="text-sm text-slate-500 dark:text-slate-400">Total Produk</div>
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ stockRunning.total }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                <div class="text-sm text-slate-500 dark:text-slate-400">Total Stock Awal</div>
                <div class="text-2xl font-bold text-blue-600">{{ totalStockAwal }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                <div class="text-sm text-slate-500 dark:text-slate-400">Total Stock Masuk</div>
                <div class="text-2xl font-bold text-green-600">{{ totalStockMasuk }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
                <div class="text-sm text-slate-500 dark:text-slate-400">Total Stock Keluar</div>
                <div class="text-2xl font-bold text-red-600">{{ totalStockKeluar }}</div>
            </div>
        </div>

        <!-- Content -->
        <template v-if="stockRunning.data.length > 0">
            <TableCard title="Daftar Stock Running">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Kode Barang</TableTh>
                            <TableTh>Nama Barang</TableTh>
                            <TableTh class="text-right">Stock Awal</TableTh>
                            <TableTh class="text-right">Masuk</TableTh>
                            <TableTh class="text-right">Keluar</TableTh>
                            <TableTh class="text-right">Saldo</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(item, i) in stockRunning.data"
                            :key="item.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (stockRunning.current_page - 1) * stockRunning.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">
                                    {{ item.code_barang }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ item.barang?.judul_buku || '-' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-right text-blue-600 font-medium">
                                {{ item.stock_awal }}
                            </TableTd>
                            <TableTd class="text-right text-green-600 font-medium">
                                +{{ item.stock_masuk }}
                            </TableTd>
                            <TableTd class="text-right text-red-600 font-medium">
                                -{{ item.stock_keluar }}
                            </TableTd>
                            <TableTd class="text-right">
                                <span
                                    :class="item.stock_saldo > 0
                                        ? 'text-green-600 bg-green-100 dark:bg-green-900/30'
                                        : 'text-red-600 bg-red-100 dark:bg-red-900/30'"
                                    class="px-3 py-1 rounded-lg font-bold"
                                >
                                    {{ item.stock_saldo }}
                                </span>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
            <Pagination v-if="stockRunning.links.length > 3" :links="stockRunning.links" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Belum ada stock yang tercatat." />
        </template>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link } from '@inertiajs/vue3'
import TableCard from '@/Components/Dashboard/TableCard.vue'
import Table from '@/Components/Dashboard/Table.vue'
import TableThead from '@/Components/Dashboard/TableThead.vue'
import TableTh from '@/Components/Dashboard/TableTh.vue'
import TableTbody from '@/Components/Dashboard/TableTbody.vue'
import TableTd from '@/Components/Dashboard/TableTd.vue'
import TableEmpty from '@/Components/Dashboard/TableEmpty.vue'
import Pagination from '@/Components/Dashboard/Pagination.vue'
import Search from '@/Components/Dashboard/Search.vue'
import { IconPlus } from '@tabler/icons-vue'

const props = defineProps({
    stockRunning: Object
})

const totalStockAwal = computed(() => {
    return props.stockRunning.data.reduce((sum, item) => sum + item.stock_awal, 0)
})

const totalStockMasuk = computed(() => {
    return props.stockRunning.data.reduce((sum, item) => sum + item.stock_masuk, 0)
})

const totalStockKeluar = computed(() => {
    return props.stockRunning.data.reduce((sum, item) => sum + item.stock_keluar, 0)
})
</script>
