<template>
    <DashboardLayout>
        <Head title="Resume Order Aktif" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Resume Order Aktif</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Order yang sudah di-sync dari Resume Old Order. Gunakan Sync Stock untuk finalisasi ke stok.
                    </p>
                </div>
            </div>
        </div>

        <!-- Semester Navigation -->
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Tahun:</label>
                    <select
                        v-model="selectedYear"
                        @change="navigateToSemester"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-semibold focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1">
                    <button
                        @click="selectedSemester = 1; navigateToSemester()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="selectedSemester === 1
                            ? 'bg-white dark:bg-slate-700 text-primary-600 dark:text-primary-400 shadow-sm'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                    >
                        Semester 1
                        <span class="text-xs text-slate-400 ml-1">(Jan - Jun)</span>
                    </button>
                    <button
                        @click="selectedSemester = 2; navigateToSemester()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="selectedSemester === 2
                            ? 'bg-white dark:bg-slate-700 text-primary-600 dark:text-primary-400 shadow-sm'
                            : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'"
                    >
                        Semester 2
                        <span class="text-xs text-slate-400 ml-1">(Jul - Des)</span>
                    </button>
                </div>

                <div class="ml-auto text-xs text-slate-400">
                    Menampilkan {{ monthsData.length }} bulan
                </div>
            </div>
        </div>

        <!-- Monthly Cards -->
        <div class="space-y-4" v-if="monthsData.length > 0">
            <div
                v-for="(item, index) in monthsData"
                :key="`${item.year}-${item.month}`"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-300"
                :class="{ 'ring-2 ring-primary-500/30': expandedMonth === index }"
            >
                <!-- Month Header -->
                <button
                    @click="toggleExpand(index, item)"
                    class="w-full text-left p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center">
                                <IconCalendar :size="20" class="text-blue-500" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900 dark:text-white">
                                    {{ getMonthName(item.month) }} {{ item.year }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                        {{ item.total_orders }} order
                                    </span>
                                    <span v-if="item.final_orders > 0"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        <IconLock :size="12" />
                                        {{ item.final_orders }} final
                                    </span>
                                    <span v-if="item.unfinal_orders > 0"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                        {{ item.unfinal_orders }} belum final
                                    </span>
                                </div>
                            </div>
                        </div>
                        <IconChevronDown
                            :size="20"
                            class="text-slate-400 transition-transform duration-300"
                            :class="{ 'rotate-180': expandedMonth === index }"
                        />
                    </div>

                    <!-- Summary Rows -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- All Orders -->
                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                                Semua Order Aktif
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Order</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                                        {{ formatNumber(item.total_orders) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Barang</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                                        {{ formatNumber(item.total_barang) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Nominal</div>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        {{ formatCurrency(item.total_nominal) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Final Orders -->
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800/30">
                            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-1">
                                <IconLock :size="14" />
                                Final (Stock Tersinkronisasi)
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Order</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatNumber(item.final_orders) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Barang</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatNumber(item.final_barang) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Nominal</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatCurrency(item.final_nominal) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </button>

                <!-- Expanded Detail -->
                <transition
                    enter-active-class="transition-all duration-500 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[9999px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[9999px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-if="expandedMonth === index" class="border-t border-slate-200 dark:border-slate-800 overflow-hidden">
                        <!-- Loading State -->
                        <div v-if="loadingDetail" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-500 border-t-transparent"></div>
                            <span class="ml-3 text-sm text-slate-500">Memuat data order aktif...</span>
                        </div>

                        <!-- Order List -->
                        <div v-else class="p-4">
                            <!-- Toolbar -->
                            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="relative w-full sm:w-72">
                                    <IconSearch :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Cari No Order..."
                                        class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-slate-400"
                                    />
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Sync Stock Button -->
                                    <button
                                        v-if="item.unfinal_orders > 0"
                                        @click.stop="syncStock(item, index)"
                                        :disabled="syncingMonth !== null"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all shadow-lg"
                                        :class="syncingMonth === index
                                            ? 'bg-amber-400 cursor-wait shadow-amber-400/20'
                                            : 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/20'"
                                    >
                                        <IconPackage
                                            :size="18"
                                            :class="{ 'animate-bounce': syncingMonth === index }"
                                        />
                                        {{ syncingMonth === index ? 'Syncing Stock...' : 'Sync Stock' }}
                                    </button>
                                    <!-- Unfinal Button -->
                                    <button
                                        v-if="item.final_orders > 0"
                                        @click.stop="unfinalStock(item, index)"
                                        :disabled="syncingMonth !== null"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all shadow-lg"
                                        :class="syncingMonth === index
                                            ? 'bg-red-400 cursor-wait shadow-red-400/20'
                                            : 'bg-red-500 hover:bg-red-600 shadow-red-500/20'"
                                    >
                                        <IconLockOpen :size="18" />
                                        Unfinal
                                    </button>
                                    <span v-if="item.total_orders > 0 && item.unfinal_orders === 0 && item.final_orders === item.total_orders"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                                        <IconCircleCheck :size="16" /> Semua final
                                    </span>
                                </div>
                            </div>

                            <!-- Sync Result Alert -->
                            <div v-if="syncResult && syncResultMonth === index"
                                class="mb-4 p-3 rounded-xl border text-sm"
                                :class="syncResult.finalized > 0
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300'
                                    : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'"
                            >
                                <div class="flex items-center gap-2">
                                    <IconCircleCheck :size="16" class="text-emerald-500" />
                                    <span>
                                        Sync stock selesai:
                                        <strong>{{ syncResult.finalized }}</strong> order di-finalisasi,
                                        <strong>{{ syncResult.already_final }}</strong> sudah final sebelumnya.
                                    </span>
                                </div>
                            </div>

                            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 z-10 bg-white dark:bg-slate-900">
                                        <tr class="border-b border-slate-200 dark:border-slate-700">
                                            <th class="py-3 px-3 w-10"></th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Code Order</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Customer</th>
                                            <th class="text-center py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Total Barang</th>
                                            <th class="text-right py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Nominal</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Tanggal Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="order in filteredOrders" :key="order.id">
                                            <tr
                                                @click="toggleOrder(order.id)"
                                                class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer"
                                                :class="{ 'bg-blue-50/30 dark:bg-blue-900/10': expandedOrderId === order.id }"
                                            >
                                                <td class="py-3 px-3 text-center">
                                                    <IconChevronDown :size="16" class="text-slate-400 transition-transform duration-200"
                                                        :class="{ 'rotate-180': expandedOrderId === order.id }" />
                                                </td>
                                                <td class="py-3 px-3">
                                                    <span v-if="order.is_final"
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                                        <IconLock :size="12" />
                                                        Final
                                                    </span>
                                                    <span v-else
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                                        Belum Final
                                                    </span>
                                                </td>
                                                <td class="py-3 px-3">
                                                    <span class="font-bold text-slate-900 dark:text-white">{{ order.old_order_id }}</span>
                                                </td>
                                                <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                                    {{ order.customer?.nama || order.nama_penerima || '-' }}
                                                </td>
                                                <td class="py-3 px-3 text-center">
                                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-semibold">
                                                        {{ order.total_barang }}
                                                    </span>
                                                </td>
                                                <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                                    {{ formatCurrency(order.computed_nominal) }}
                                                </td>
                                                <td class="py-3 px-3 text-slate-500 text-xs">
                                                    {{ order.order_date ? new Date(order.order_date).toLocaleDateString('id-ID') : '-' }}
                                                </td>
                                            </tr>

                                            <!-- Order Items Detail Row -->
                                            <tr v-if="expandedOrderId === order.id">
                                                <td colspan="7" class="p-0 bg-slate-50/50 dark:bg-slate-800/30">
                                                    <div class="px-10 py-4 border-b border-slate-200 dark:border-slate-800">
                                                        <div class="mb-2 text-xs font-bold text-slate-500 uppercase flex items-center gap-2">
                                                            <IconPackage :size="14" />
                                                            Daftar Barang dalam Order
                                                        </div>
                                                        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                                                            <table class="w-full text-xs">
                                                                <thead>
                                                                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                                                                        <th class="text-left py-2 px-3 text-slate-500 font-semibold">Kode</th>
                                                                        <th class="text-left py-2 px-3 text-slate-500 font-semibold">Nama Barang</th>
                                                                        <th class="text-center py-2 px-3 text-slate-500 font-semibold w-20">Qty</th>
                                                                        <th class="text-right py-2 px-3 text-slate-500 font-semibold w-32">Harga</th>
                                                                        <th class="text-right py-2 px-3 text-slate-500 font-semibold w-32">Total</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr v-for="item in order.details" :key="item.id"
                                                                        class="border-b border-slate-100 dark:border-slate-800/50 last:border-0 hover:bg-blue-50/50 dark:hover:bg-blue-900/10">
                                                                        <td class="py-2 px-3 font-mono text-slate-500">{{ item.code_barang || '-' }}</td>
                                                                        <td class="py-2 px-3 text-slate-700 dark:text-slate-200">{{ item.nama_barang }}</td>
                                                                        <td class="py-2 px-3 text-center font-bold text-slate-900 dark:text-white">{{ item.jumlah }}</td>
                                                                        <td class="py-2 px-3 text-right text-slate-500">{{ formatCurrency(item.harga) }}</td>
                                                                        <td class="py-2 px-3 text-right font-bold text-slate-900 dark:text-white">{{ formatCurrency(item.jumlah * item.harga) }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="filteredOrders.length === 0 && expandedOrders.length > 0" class="text-center py-8 text-slate-400 text-sm">
                                Tidak ditemukan order dengan nomor "{{ searchQuery }}".
                            </div>
                            <div v-else-if="expandedOrders.length === 0" class="text-center py-8 text-slate-400 text-sm">
                                Tidak ada order aktif di bulan ini.
                            </div>
                            <div v-if="expandedOrders.length > 0" class="mt-2 text-xs text-slate-400 text-right">
                                Menampilkan {{ filteredOrders.length }} order
                                ({{ expandedOrders.filter(o => o.is_final).length }} final,
                                {{ expandedOrders.filter(o => !o.is_final).length }} belum final)
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

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
                Belum ada order aktif di periode ini. Sync dari halaman Resume Old Order dulu.
            </p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import {
    IconCalendar,
    IconChevronDown,
    IconCircleCheck,
    IconDatabaseOff,
    IconSearch,
    IconLock,
    IconLockOpen,
    IconPackage,
} from '@tabler/icons-vue';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    months: Array,
    availableYears: Array,
    filters: Object,
});

const monthsData = reactive([...props.months]);

const selectedYear = ref(props.filters.year);
const selectedSemester = ref(props.filters.semester);

const expandedMonth = ref(null);
const expandedOrders = ref([]);
const loadingDetail = ref(false);
const searchQuery = ref('');
const syncingMonth = ref(null);
const syncResult = ref(null);
const syncResultMonth = ref(null);
const expandedOrderId = ref(null);

const toggleOrder = (orderId) => {
    if (expandedOrderId.value === orderId) {
        expandedOrderId.value = null;
    } else {
        expandedOrderId.value = orderId;
    }
};

const filteredOrders = computed(() => {
    if (!searchQuery.value) return expandedOrders.value;
    const q = searchQuery.value.toLowerCase();
    return expandedOrders.value.filter(order =>
        String(order.old_order_id).toLowerCase().includes(q)
    );
});

const monthNames = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const getMonthName = (month) => monthNames[month] || '';

const navigateToSemester = () => {
    router.get(route('old-orders-aktif.resume'), {
        year: selectedYear.value,
        semester: selectedSemester.value,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const toggleExpand = async (index, item) => {
    if (expandedMonth.value === index) {
        expandedMonth.value = null;
        expandedOrders.value = [];
        searchQuery.value = '';
        syncResult.value = null;
        syncResultMonth.value = null;
        return;
    }

    expandedMonth.value = index;
    loadingDetail.value = true;
    expandedOrders.value = [];
    syncResult.value = null;
    syncResultMonth.value = null;

    try {
        const response = await axios.get(route('old-orders-aktif.resume-detail', {
            year: item.year,
            month: item.month
        }));
        expandedOrders.value = response.data;
    } catch (error) {
        console.error('Error fetching orders:', error);
    } finally {
        loadingDetail.value = false;
    }
};

const syncStock = async (item, index) => {
    if (syncingMonth.value !== null) return;

    if (!confirm(`Yakin ingin FINALISASI STOCK untuk semua order belum final di ${getMonthName(item.month)} ${item.year}?\n\nAksi ini akan mengurangi stok barang sesuai detail order.`)) return;

    syncingMonth.value = index;
    syncResult.value = null;
    syncResultMonth.value = null;

    try {
        const response = await axios.post(route('old-orders-aktif.sync-stock', {
            year: item.year,
            month: item.month
        }));

        syncResult.value = response.data;
        syncResultMonth.value = index;

        // Update month card counts
        monthsData[index].final_orders = response.data.final_orders + response.data.already_final + response.data.finalized - response.data.already_final;
        monthsData[index].unfinal_orders = response.data.unfinal_orders;

        // Reload detail to show updated statuses
        const detailResponse = await axios.get(route('old-orders-aktif.resume-detail', {
            year: item.year,
            month: item.month
        }));
        expandedOrders.value = detailResponse.data;
    } catch (error) {
        console.error('Error syncing stock:', error);
        alert('Gagal sync stock: ' + (error.response?.data?.message || error.message));
    } finally {
        syncingMonth.value = null;
    }
};

const unfinalStock = async (item, index) => {
    if (syncingMonth.value !== null) return;
    if (!confirm(`Yakin ingin UNFINAL semua order di ${getMonthName(item.month)} ${item.year}?\n\nStok keluar yang sudah dikurangi akan ditambah kembali.`)) return;
    syncingMonth.value = index; syncResult.value = null; syncResultMonth.value = null;
    try {
        const response = await axios.post(route('old-orders-aktif.unfinal-stock', { year: item.year, month: item.month }));
        syncResult.value = { finalized: 0, already_final: 0, unfinalized: response.data.unfinalized }; syncResultMonth.value = index;
        const detailResponse = await axios.get(route('old-orders-aktif.resume-detail', { year: item.year, month: item.month }));
        expandedOrders.value = detailResponse.data;
        monthsData[index].unfinal_orders = monthsData[index].total_orders;
        monthsData[index].final_orders = 0;
    } catch (error) {
        console.error('Error unfinal stock:', error);
        alert('Gagal unfinal: ' + (error.response?.data?.message || error.message));
    } finally { syncingMonth.value = null; }
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

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
