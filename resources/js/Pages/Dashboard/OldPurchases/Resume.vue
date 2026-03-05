<template>
    <DashboardLayout>
        <Head title="Resume Old Purchase" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Resume Old Purchase</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Ringkasan pembelian lama per bulan.
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
                <!-- Month Header -->
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                                Semua Pembelian
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Faktur</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white">
                                        {{ formatNumber(item.total_purchases) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-400">Total Nominal</div>
                                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        {{ formatCurrency(item.total_nominal) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800/30">
                            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-1">
                                <IconCircleCheck :size="14" />
                                Pembelian Aktif (Status ✓)
                            </div>
                            <div class="flex justify-between items-end">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah Faktur</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                        {{ formatNumber(item.true_purchases) }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-400">Total Nominal</div>
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
                        <div v-if="loadingDetail" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-500 border-t-transparent"></div>
                        </div>

                        <div v-else class="p-4">
                            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="relative w-full sm:w-72">
                                    <IconSearch :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Cari No Faktur / Supplier..."
                                        class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                                    />
                                </div>
                                <div class="flex gap-2 w-full sm:w-auto">
                                    <a
                                        :href="route('old-purchases.export-resume', { year: item.year, month: item.month })"
                                        target="_blank"
                                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold shadow-lg shadow-emerald-500/20"
                                    >
                                        <IconFileSpreadsheet :size="18" />
                                        Export Excel
                                    </a>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 dark:border-slate-700">
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">No Faktur</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Supplier</th>
                                            <th class="text-right py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Nominal</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="purchase in filteredPurchases"
                                            :key="purchase.id"
                                            class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                            :class="{ 'opacity-40': !purchase.resume_status }"
                                        >
                                            <td class="py-3 px-3">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        :checked="purchase.resume_status"
                                                        @change="toggleStatus(purchase, index)"
                                                        class="sr-only peer"
                                                        :disabled="togglingId === purchase.id"
                                                    />
                                                    <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 transition-colors"></div>
                                                </label>
                                            </td>
                                            <td class="py-3 px-3 font-medium">{{ purchase.nomor_faktur || '-' }}</td>
                                            <td class="py-3 px-3">{{ purchase.supplier }}</td>
                                            <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">
                                                {{ formatCurrency(purchase.harga_total) }}
                                            </td>
                                            <td class="py-3 px-3 text-slate-500">
                                                {{ formatDate(purchase.tanggal_faktur) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <IconDatabaseOff :size="48" class="text-slate-300 mb-4" />
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200">Tidak ada data pembelian.</h3>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    IconCalendar,
    IconChevronDown,
    IconCircleCheck,
    IconDatabaseOff,
    IconSearch,
    IconFileSpreadsheet
} from '@tabler/icons-vue';

const props = defineProps({
    months: Array,
});

const monthsData = reactive([...props.months]);
const expandedMonth = ref(null);
const expandedPurchases = ref([]);
const loadingDetail = ref(false);
const togglingId = ref(null);
const searchQuery = ref('');

const monthNames = [
    '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const getMonthName = (m) => monthNames[m] || m;

const filteredPurchases = computed(() => {
    if (!searchQuery.value) return expandedPurchases.value;
    const q = searchQuery.value.toLowerCase();
    return expandedPurchases.value.filter(p => 
        (p.nomor_faktur && p.nomor_faktur.toLowerCase().includes(q)) ||
        p.supplier.toLowerCase().includes(q)
    );
});

const toggleExpand = async (index, item) => {
    if (expandedMonth.value === index) {
        expandedMonth.value = null;
        return;
    }
    expandedMonth.value = index;
    loadingDetail.value = true;
    try {
        const res = await axios.get(route('old-purchases.resume-detail', { year: item.year, month: item.month }));
        expandedPurchases.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loadingDetail.value = false;
    }
};

const toggleStatus = async (purchase, monthIndex) => {
    togglingId.value = purchase.id;
    const newStatus = !purchase.resume_status;
    try {
        const res = await axios.put(route('old-purchases.toggle-status', purchase.id), {
            resume_status: newStatus
        });
        purchase.resume_status = newStatus;
        if (res.data.summary) {
            monthsData[monthIndex].true_purchases = res.data.summary.true_purchases;
            monthsData[monthIndex].true_nominal = res.data.summary.true_nominal;
        }
    } catch (e) {
        console.error(e);
    } finally {
        togglingId.value = null;
    }
};

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID') : '-';

const route = (name, params) => window.route(name, params);
</script>
