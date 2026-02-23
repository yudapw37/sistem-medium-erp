<template>
    <DashboardLayout>
        <Head title="Resume Old Order" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Resume Old Order</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Ringkasan order lama per bulan.
                    </p>
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
                <!-- Month Header (Clickable) -->
                <button
                    @click="toggleExpand(index, item)"
                    class="w-full text-left p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-500/10 flex items-center justify-center">
                                <IconCalendar :size="20" class="text-primary-500" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900 dark:text-white">
                                    {{ getMonthName(item.month) }} {{ item.year }}
                                </h3>
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
                        <!-- Row 1: All Statuses -->
                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                                Semua Order
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Order</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                                        {{ formatNumber(item.total_orders) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Buku</div>
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

                        <!-- Row 2: Status True Only -->
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800/30">
                            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-1">
                                <IconCircleCheck :size="14" />
                                Order Aktif (Status ✓)
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Order</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatNumber(item.true_orders) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Buku</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatNumber(item.true_barang) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Nominal</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatCurrency(item.true_nominal) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </button>

                <!-- Expanded Detail -->
                <transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[2000px] opacity-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[2000px] opacity-100"
                    leave-to-class="max-h-0 opacity-0"
                >
                    <div v-if="expandedMonth === index" class="border-t border-slate-200 dark:border-slate-800 overflow-hidden">
                        <!-- Loading State -->
                        <div v-if="loadingDetail" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-500 border-t-transparent"></div>
                            <span class="ml-3 text-sm text-slate-500">Memuat data order...</span>
                        </div>

                        <!-- Order List -->
                        <div v-else class="p-4">
                            <!-- Search and Export -->
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
                                <a
                                    :href="route('old-orders.resume-export-excel', { year: item.year, month: item.month })"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold transition-all shadow-lg shadow-emerald-500/20"
                                >
                                    <IconFileSpreadsheet :size="18" />
                                    Export Excel
                                </a>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 dark:border-slate-700">
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Code Order</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Customer</th>
                                            <th class="text-center py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Total Barang</th>
                                            <th class="text-right py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Nominal</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="order in filteredOrders"
                                            :key="order.id"
                                            class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                            :class="{ 'opacity-50': !order.resume_status }"
                                        >
                                            <td class="py-3 px-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        :checked="order.resume_status"
                                                        @change="toggleStatus(order, index)"
                                                        class="sr-only peer"
                                                        :disabled="togglingId === order.id"
                                                    />
                                                    <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 transition-colors"></div>
                                                </label>
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="font-bold text-slate-900 dark:text-white">{{ order.id }}</span>
                                            </td>
                                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                                {{ order.customer?.nama || '-' }}
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs font-semibold">
                                                    {{ order.total_barang }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                                {{ formatCurrency(order.computed_nominal) }}
                                            </td>
                                            <td class="py-3 px-3 text-slate-500">
                                                {{ order.created_at ? new Date(order.created_at).toLocaleDateString('id-ID') : '-' }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="filteredOrders.length === 0 && expandedOrders.length > 0" class="text-center py-8 text-slate-400 text-sm">
                                Tidak ditemukan order dengan nomor "{{ searchQuery }}".
                            </div>
                            <div v-else-if="expandedOrders.length === 0" class="text-center py-8 text-slate-400 text-sm">
                                Tidak ada order di bulan ini.
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
                Tidak ditemukan data old order.
            </p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import {
    IconCalendar,
    IconChevronDown,
    IconCircleCheck,
    IconDatabaseOff,
    IconSearch,
    IconFileSpreadsheet,
} from '@tabler/icons-vue';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    months: Array,
});

// Make months data reactive so we can update summaries
const monthsData = reactive([...props.months]);

const expandedMonth = ref(null);
const expandedOrders = ref([]);
const loadingDetail = ref(false);
const togglingId = ref(null);
const searchQuery = ref('');

const filteredOrders = computed(() => {
    if (!searchQuery.value) return expandedOrders.value;
    const q = searchQuery.value.toLowerCase();
    return expandedOrders.value.filter(order =>
        String(order.id).toLowerCase().includes(q)
    );
});

const monthNames = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const getMonthName = (month) => monthNames[month] || '';

const toggleExpand = async (index, item) => {
    if (expandedMonth.value === index) {
        expandedMonth.value = null;
        expandedOrders.value = [];
        searchQuery.value = '';
        return;
    }

    expandedMonth.value = index;
    loadingDetail.value = true;
    expandedOrders.value = [];

    try {
        const response = await axios.get(route('old-orders.resume-detail', {
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

const toggleStatus = async (order, monthIndex) => {
    togglingId.value = order.id;
    const newStatus = !order.resume_status;

    try {
        const response = await axios.put(route('old-orders.toggle-status', order.id), {
            resume_status: newStatus,
        });

        // Update local order status
        order.resume_status = newStatus;

        // Update the monthly summary with fresh data from server
        if (response.data.summary) {
            const summary = response.data.summary;
            monthsData[monthIndex].true_orders = summary.true_orders;
            monthsData[monthIndex].true_barang = summary.true_barang;
            monthsData[monthIndex].true_nominal = summary.true_nominal;
        }
    } catch (error) {
        console.error('Error toggling status:', error);
    } finally {
        togglingId.value = null;
    }
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
