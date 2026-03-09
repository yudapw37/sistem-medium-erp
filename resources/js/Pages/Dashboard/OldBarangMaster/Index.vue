<template>
    <DashboardLayout>
        <Head title="Old Barang Master" />

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Old Barang Master</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Data master barang dari tabel old_ms_barang.
            </p>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('old-barang-master.index')" placeholder="Cari barang..." />
                </div>
            </div>
        </div>

        <!-- Content -->
        <template v-if="barangs.data.length > 0">
            <TableCard title="Daftar Old Barang">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>ID</TableTh>
                            <TableTh>Judul Buku</TableTh>
                            <TableTh>Kategori</TableTh>
                            <TableTh>Barcode</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(item, i) in barangs.data"
                            :key="item.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (barangs.current_page - 1) * barangs.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">
                                    {{ item.id }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ item.judul_buku || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ item.kategori || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="font-mono text-slate-600 dark:text-slate-400">
                                    {{ item.barcode || '-' }}
                                </span>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
            <Pagination :data="barangs" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Belum ada data barang." />
        </template>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Search from '@/Components/Dashboard/Search.vue'
import TableCard from '@/Components/Dashboard/TableCard.vue'
import Table from '@/Components/Dashboard/Table.vue'
import TableThead from '@/Components/Dashboard/TableThead.vue'
import TableTh from '@/Components/Dashboard/TableTh.vue'
import TableTbody from '@/Components/Dashboard/TableTbody.vue'
import TableTd from '@/Components/Dashboard/TableTd.vue'
import TableEmpty from '@/Components/Dashboard/TableEmpty.vue'
import Pagination from '@/Components/Dashboard/Pagination.vue'

const props = defineProps({
    barangs: Object
})

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
}
</script>
