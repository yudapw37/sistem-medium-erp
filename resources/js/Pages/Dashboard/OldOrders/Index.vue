<template>
    <DashboardLayout>
        <Head title="Old Order" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Old Order</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data order dari sistem lama.
                    </p>
                </div>
                <button
                    @click="showBulkPrintModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-all shadow-lg shadow-primary-500/25"
                >
                    <IconPrinter :size="18" />
                    Cetak Massal
                </button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('old-orders.index')" placeholder="Cari Code Order..." />
                </div>
                <div class="flex items-center gap-2">
                    <input
                        type="date"
                        v-model="filterForm.start_date"
                        @change="handleFilter"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    />
                    <span class="text-slate-400">-</span>
                    <input
                        type="date"
                        v-model="filterForm.end_date"
                        @change="handleFilter"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    />
                </div>
                <button
                    v-if="filterForm.start_date || filterForm.end_date"
                    @click="clearFilters"
                    class="h-10 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-sm hover:bg-slate-200 dark:hover:bg-slate-700 transition-all"
                >
                    Reset
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-if="orders.data.length > 0">
            <TableCard title="Daftar Old Order">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Code Order</TableTh>
                            <TableTh>Customer</TableTh>
                            <TableTh class="text-center">Total Barang</TableTh>
                            <TableTh class="text-right">Total Harga</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(order, i) in orders.data"
                            :key="order.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (orders.current_page - 1) * orders.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ order.id }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconUser :size="16" class="text-slate-400" />
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ order.customer?.nama || '-' }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd class="text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-semibold">
                                    {{ order.total_barang }}
                                </span>
                            </TableTd>
                            <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                {{ formatCurrency(order.total_harga) }}
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ order.created_at ? new Date(order.created_at).toLocaleDateString('id-ID') : '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        @click="router.visit(route('old-orders.show', order.id))"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                        title="Detail"
                                    >
                                        <IconEye :size="18" />
                                    </button>
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </template>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800"
        >
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                Tidak Ada Data
            </h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Tidak ditemukan data old order.
            </p>
        </div>

        <Pagination v-if="orders?.links && orders.links.length > 3" :links="orders.links" />

        <!-- Bulk Print Modal -->
        <Modal :show="showBulkPrintModal" @close="closeModal" title="Cetak Massal Invoice" maxWidth="2xl">
            <div class="p-6">
                <!-- Step 1: Pick Date -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Pilih Tanggal Order
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="date"
                            v-model="bulkPrintDate"
                            class="flex-1 h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                        />
                        <button
                            @click="fetchOrdersByDate"
                            :disabled="!bulkPrintDate || loadingOrders"
                            class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-sm font-semibold hover:bg-slate-800 dark:hover:bg-slate-700 disabled:opacity-50 transition-all"
                        >
                            {{ loadingOrders ? 'Loading...' : 'Tampilkan Order' }}
                        </button>
                    </div>
                </div>

                <!-- Step 2: Select Orders -->
                <div v-if="bulkOrders.length > 0" class="mt-6 border-t border-slate-100 dark:border-slate-800 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                            Daftar Order ({{ bulkOrders.length }} ditemukan)
                        </h3>
                        <div class="flex items-center gap-2">
                            <Checkbox :model-value="isAllSelected" @update:model-value="toggleAll" />
                            <span class="text-xs text-slate-500">Pilih Semua</span>
                        </div>
                    </div>

                    <div class="max-h-60 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        <div
                            v-for="order in bulkOrders"
                            :key="order.id"
                            class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all"
                        >
                            <div class="flex flex-col">
                                <span class="font-bold text-sm text-slate-900 dark:text-white">{{ order.id }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-500">{{ order.customer?.nama || '-' }}</span>
                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-400">
                                        {{ formatCurrency(order.total_harga) }}
                                    </span>
                                </div>
                            </div>
                            <Checkbox v-model="selectedOrderIds" :value="order.id" />
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button
                            @click="closeModal"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all"
                        >
                            Batal
                        </button>
                        <button
                            @click="handleBulkPrint"
                            :disabled="selectedOrderIds.length === 0"
                            class="px-5 py-2.5 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 disabled:opacity-50 transition-all shadow-lg shadow-primary-500/25 flex items-center gap-2"
                        >
                            <IconPrinter :size="18" />
                            Cetak ({{ selectedOrderIds.length }})
                        </button>
                    </div>
                </div>

                <!-- Empty State within Modal -->
                <div v-else-if="hasSearched && !loadingOrders" class="py-12 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                        <IconDatabaseOff :size="24" class="text-slate-400" />
                    </div>
                    <p class="text-sm text-slate-500">Tidak ada order pada tanggal yang dipilih.</p>
                </div>
            </div>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    IconUser,
    IconEye,
    IconDatabaseOff,
    IconPrinter,
} from '@tabler/icons-vue';
import { ref, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import Modal from '@/Components/Dashboard/Modal.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const filterForm = ref({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
});

const handleFilter = () => {
    router.get(
        route('old-orders.index'),
        {
            q: props.filters?.q,
            start_date: filterForm.value.start_date,
            end_date: filterForm.value.end_date,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    filterForm.value.start_date = '';
    filterForm.value.end_date = '';
    router.get(route('old-orders.index'), {}, { preserveState: true });
};

// Bulk Print Logic
const showBulkPrintModal = ref(false);
const bulkPrintDate = ref('');
const bulkOrders = ref([]);
const loadingOrders = ref(false);
const hasSearched = ref(false);
const selectedOrderIds = ref([]);

const closeModal = () => {
    showBulkPrintModal.value = false;
    bulkPrintDate.value = '';
    bulkOrders.value = [];
    selectedOrderIds.value = [];
    hasSearched.value = false;
};

const fetchOrdersByDate = async () => {
    if (!bulkPrintDate.value) return;
    
    loadingOrders.value = true;
    hasSearched.value = true;
    try {
        const response = await axios.get(route('old-orders.by-date'), {
            params: { date: bulkPrintDate.value }
        });
        bulkOrders.value = response.data;
        // Default select all
        selectedOrderIds.value = response.data.map(o => o.id);
    } catch (error) {
        console.error('Error fetching orders:', error);
    } finally {
        loadingOrders.value = false;
    }
};

const isAllSelected = computed(() => {
    return bulkOrders.value.length > 0 && selectedOrderIds.value.length === bulkOrders.value.length;
});

const toggleAll = (checked) => {
    if (checked) {
        selectedOrderIds.value = bulkOrders.value.map(o => o.id);
    } else {
        selectedOrderIds.value = [];
    }
};

const handleBulkPrint = async () => {
    if (selectedOrderIds.value.length === 0) return;

    try {
        const response = await axios.post(route('old-orders.bulk-print'), {
            ids: selectedOrderIds.value
        }, {
            responseType: 'blob'
        });

        // Create a blob URL and open it in a new tab
        const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        setTimeout(() => window.URL.revokeObjectURL(url), 100);
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Gagal membuat PDF. Silakan coba lagi.');
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
