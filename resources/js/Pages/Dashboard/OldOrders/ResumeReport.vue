<template>
    <DashboardLayout>
        <Head title="Laporan Resume Old Order" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Laporan Resume Old Order</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Rekapitulasi penjualan buku berdasarkan resume status.
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 mb-6">
            <form @submit.prevent="handleFilter" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Month Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Bulan</label>
                    <select
                        v-model="filter.month"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer"
                    >
                        <option v-for="(name, index) in monthNames" :key="index" :value="index + 1" v-show="name">
                            {{ name }}
                        </option>
                    </select>
                </div>

                <!-- Year Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Tahun</label>
                    <select
                        v-model="filter.year"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer"
                    >
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>
                </div>

                <!-- Resume Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Resume Status</label>
                    <select
                        v-model="filter.resume_status"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer"
                    >
                        <option :value="true">Aktif (Status ✓)</option>
                        <option :value="false">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Action -->
                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full h-10 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold transition-all flex items-center justify-center gap-2"
                        :disabled="loading"
                    >
                        <IconFilter :size="18" />
                        <span>Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Content -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                            <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="text-left py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Buku</th>
                            <th class="text-center py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Jumlah Buku</th>
                            <th class="text-right py-4 px-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Harga Buku</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, index) in items"
                            :key="index"
                            class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <td class="py-4 px-5 text-slate-500">{{ index + 1 }}</td>
                            <td class="py-4 px-5 font-semibold text-slate-900 dark:text-white">
                                <button
                                    @click="showBookDetails(item.nama_buku)"
                                    class="hover:text-primary-500 transition-colors text-left"
                                >
                                    {{ item.nama_buku }}
                                </button>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <span class="inline-flex items-center justify-center min-w-[32px] px-2 py-1 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-bold">
                                    {{ formatNumber(item.jumlah_buku) }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right font-bold text-slate-900 dark:text-white text-lg">
                                {{ formatCurrency(item.total_harga_buku) }}
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <IconDatabaseOff :size="48" class="opacity-20" />
                                    <span>Tidak ada data untuk periode ini.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="items.length > 0">
                        <tr class="bg-slate-50 dark:bg-slate-800/50 font-bold border-t-2 border-slate-200 dark:border-slate-700">
                            <td colspan="2" class="py-4 px-5 text-right uppercase text-slate-500">Total Keseluruhan</td>
                            <td class="py-4 px-5 text-center text-primary-600 dark:text-primary-400">
                                {{ formatNumber(totalQty) }}
                            </td>
                            <td class="py-4 px-5 text-right text-emerald-600 dark:text-emerald-400 text-xl">
                                {{ formatCurrency(totalAmount) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Detail Modal -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-4xl bg-white dark:bg-slate-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white leading-tight">Detail Penjualan Buku</h3>
                            <p class="text-sm text-primary-500 font-medium mt-1">{{ selectedBook }}</p>
                        </div>
                        <button
                            @click="closeModal"
                            class="p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 transition-colors"
                        >
                            <IconX :size="20" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                        <div v-if="loadingDetails" class="flex flex-col items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-10 w-10 border-4 border-primary-500 border-t-transparent shadow-sm"></div>
                            <span class="mt-4 text-sm font-medium text-slate-500">Memuat data transaksi...</span>
                        </div>

                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-slate-500 border-b border-slate-200 dark:border-slate-800">
                                            <th class="text-left pb-4 px-2 font-bold uppercase tracking-wider text-xs">No Order</th>
                                            <th class="text-left pb-4 px-2 font-bold uppercase tracking-wider text-xs">Tanggal</th>
                                            <th class="text-left pb-4 px-2 font-bold uppercase tracking-wider text-xs">Customer</th>
                                            <th class="text-center pb-4 px-2 font-bold uppercase tracking-wider text-xs">Qty</th>
                                            <th class="text-right pb-4 px-2 font-bold uppercase tracking-wider text-xs">Harga Satuan</th>
                                            <th class="text-right pb-4 px-2 font-bold uppercase tracking-wider text-xs">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <tr v-for="order in bookOrders" :key="order.order_id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                            <td class="py-4 px-2 font-bold text-slate-900 dark:text-white">{{ order.order_id }}</td>
                                            <td class="py-4 px-2 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                {{ new Date(order.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                            </td>
                                            <td class="py-4 px-2 text-slate-700 dark:text-slate-300">{{ order.nama_customer || '-' }}</td>
                                            <td class="py-4 px-2 text-center">
                                                <span class="font-bold text-slate-900 dark:text-white">{{ order.jumlah }}</span>
                                            </td>
                                            <td class="py-4 px-2 text-right text-slate-600 dark:text-slate-400">{{ formatCurrency(order.harga_satuan) }}</td>
                                            <td class="py-4 px-2 text-right font-bold text-primary-600 dark:text-primary-400">{{ formatCurrency(order.subtotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                        <div class="flex gap-6">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Qty</p>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatNumber(bookOrders.reduce((sum, o) => sum + parseInt(o.jumlah), 0)) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Nominal</p>
                                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(bookOrders.reduce((sum, o) => sum + parseFloat(o.subtotal), 0)) }}</p>
                            </div>
                        </div>
                        <button
                            @click="closeModal"
                            class="px-6 h-11 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold transition-all"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import {
    IconFilter,
    IconDatabaseOff,
    IconX,
} from '@tabler/icons-vue';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    items: Array,
    filters: Object,
});

const loading = ref(false);
const filter = reactive({
    month: props.filters.month || new Date().getMonth() + 1,
    year: props.filters.year || new Date().getFullYear(),
    resume_status: props.filters.resume_status !== undefined ? props.filters.resume_status : true,
});

// Modal Logic
const showModal = ref(false);
const loadingDetails = ref(false);
const selectedBook = ref('');
const bookOrders = ref([]);

const showBookDetails = async (namaBuku) => {
    selectedBook.value = namaBuku;
    showModal.value = true;
    loadingDetails.value = true;
    bookOrders.value = [];

    try {
        const response = await axios.get(route('old-orders.resume-report-detail'), {
            params: {
                nama_buku: namaBuku,
                month: filter.month,
                year: filter.year,
                resume_status: filter.resume_status,
            }
        });
        bookOrders.value = response.data;
    } catch (error) {
        console.error('Error fetching book details:', error);
    } finally {
        loadingDetails.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
};

const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const result = [];
    for (let y = currentYear; y >= currentYear - 5; y--) {
        result.push(y);
    }
    return result;
});

const totalQty = computed(() => {
    return props.items.reduce((sum, item) => sum + parseInt(item.jumlah_buku || 0), 0);
});

const totalAmount = computed(() => {
    return props.items.reduce((sum, item) => sum + parseFloat(item.total_harga_buku || 0), 0);
});

const handleFilter = () => {
    loading.value = true;
    router.get(route('old-orders.resume-report'), filter, {
        preserveState: true,
        onFinish: () => (loading.value = false),
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value || 0);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
