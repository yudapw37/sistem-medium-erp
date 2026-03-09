<template>
    <DashboardLayout>
        <Head title="Resume Purchase Aktif" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Resume Purchase Aktif</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Purchase yang sudah di-sync dari Resume Old Purchase. Gunakan Sync Stock untuk finalisasi ke stok.
                    </p>
                </div>
            </div>
        </div>

        <!-- Semester Navigation -->
        <div class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400">Tahun:</label>
                    <select v-model="selectedYear" @change="navigateToSemester"
                        class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm font-semibold focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
                <div class="flex items-center bg-slate-100 dark:bg-slate-800 rounded-xl p-1">
                    <button @click="selectedSemester = 1; navigateToSemester()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="selectedSemester === 1 ? 'bg-white dark:bg-slate-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'">
                        Semester 1 <span class="text-xs text-slate-400 ml-1">(Jan - Jun)</span>
                    </button>
                    <button @click="selectedSemester = 2; navigateToSemester()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="selectedSemester === 2 ? 'bg-white dark:bg-slate-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'">
                        Semester 2 <span class="text-xs text-slate-400 ml-1">(Jul - Des)</span>
                    </button>
                </div>
                <div class="ml-auto text-xs text-slate-400">
                    Menampilkan {{ monthsData.length }} bulan
                </div>
            </div>
        </div>

        <!-- Monthly Cards -->
        <div class="space-y-4" v-if="monthsData.length > 0">
            <div v-for="(item, index) in monthsData" :key="`${item.year}-${item.month}`"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transition-all duration-300"
                :class="{ 'ring-2 ring-primary-500/30': expandedMonth === index }">

                <!-- Month Header -->
                <button @click="toggleExpand(index, item)" class="w-full text-left p-5 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center">
                                <IconCalendar :size="20" class="text-indigo-500" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-900 dark:text-white">
                                    {{ getMonthName(item.month) }} {{ item.year }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400">
                                        {{ item.total_purchases }} purchase
                                    </span>
                                    <span v-if="item.final_purchases > 0"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        <IconLock :size="12" /> {{ item.final_purchases }} final
                                    </span>
                                    <span v-if="item.unfinal_purchases > 0"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                        {{ item.unfinal_purchases }} belum final
                                    </span>
                                </div>
                            </div>
                        </div>
                        <IconChevronDown :size="20" class="text-slate-400 transition-transform duration-300"
                            :class="{ 'rotate-180': expandedMonth === index }" />
                    </div>

                    <!-- Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-4">
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Semua Purchase Aktif</div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah</div>
                                    <div class="text-lg font-bold text-slate-900 dark:text-white">{{ formatNumber(item.total_purchases) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Nominal</div>
                                    <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ formatCurrency(item.total_nominal) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800/30">
                            <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-3 flex items-center gap-1">
                                <IconLock :size="14" /> Final (Stock Tersinkronisasi)
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="text-xs text-slate-400">Jumlah</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ formatNumber(item.final_purchases) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-400">Nominal</div>
                                    <div class="text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ formatCurrency(item.final_nominal) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </button>

                <!-- Expanded Detail -->
                <transition enter-active-class="transition-all duration-500 ease-out" enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-[9999px] opacity-100" leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="max-h-[9999px] opacity-100" leave-to-class="max-h-0 opacity-0">
                    <div v-if="expandedMonth === index" class="border-t border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div v-if="loadingDetail" class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-2 border-primary-500 border-t-transparent"></div>
                            <span class="ml-3 text-sm text-slate-500">Memuat data purchase aktif...</span>
                        </div>
                        <div v-else class="p-4">
                            <!-- Toolbar -->
                            <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="relative w-full sm:w-72">
                                    <IconSearch :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input v-model="searchQuery" type="text" placeholder="Cari No Faktur..."
                                        class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder:text-slate-400" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <button v-if="item.unfinal_purchases > 0"
                                        @click.stop="syncStock(item, index)" :disabled="syncingMonth !== null"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all shadow-lg"
                                        :class="syncingMonth === index ? 'bg-amber-400 cursor-wait shadow-amber-400/20' : 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/20'">
                                        <IconPackage :size="18" :class="{ 'animate-bounce': syncingMonth === index }" />
                                        {{ syncingMonth === index ? 'Syncing Stock...' : 'Sync Stock' }}
                                    </button>
                                    <button v-if="item.final_purchases > 0"
                                        @click.stop="unfinalStock(item, index)" :disabled="syncingMonth !== null"
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-bold transition-all shadow-lg"
                                        :class="syncingMonth === index ? 'bg-red-400 cursor-wait shadow-red-400/20' : 'bg-red-500 hover:bg-red-600 shadow-red-500/20'">
                                        <IconLockOpen :size="18" />
                                        Unfinal
                                    </button>
                                    <span v-if="item.total_purchases > 0 && item.unfinal_purchases === 0 && item.final_purchases === item.total_purchases"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                                        <IconCircleCheck :size="16" /> Semua final
                                    </span>
                                </div>
                            </div>

                            <!-- Sync Result -->
                            <div v-if="syncResult && syncResultMonth === index"
                                class="mb-4 p-3 rounded-xl border text-sm bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300">
                                <div class="flex items-center gap-2">
                                    <IconCircleCheck :size="16" class="text-emerald-500" />
                                    <span>Sync stock selesai: <strong>{{ syncResult.finalized }}</strong> purchase di-finalisasi,
                                        <strong>{{ syncResult.already_final }}</strong> sudah final sebelumnya.</span>
                                </div>
                            </div>

                            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 z-10 bg-white dark:bg-slate-900">
                                        <tr class="border-b border-slate-200 dark:border-slate-700">
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">No Faktur</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Supplier</th>
                                            <th class="text-right py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Nominal</th>
                                            <th class="text-left py-3 px-3 text-xs font-semibold text-slate-500 uppercase">Tanggal Faktur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="purchase in filteredPurchases" :key="purchase.id"
                                            class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                            <td class="py-3 px-3">
                                                <span v-if="purchase.is_final"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                                    <IconLock :size="12" /> Final
                                                </span>
                                                <span v-else
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                                    Belum Final
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ purchase.nomor_faktur }}</td>
                                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300">{{ purchase.supplier || '-' }}</td>
                                            <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">{{ formatCurrency(purchase.harga_total) }}</td>
                                            <td class="py-3 px-3 text-slate-500">{{ purchase.purchase_date ? new Date(purchase.purchase_date).toLocaleDateString('id-ID') : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="filteredPurchases.length === 0 && expandedPurchases.length > 0" class="text-center py-8 text-slate-400 text-sm">
                                Tidak ditemukan purchase dengan pencarian "{{ searchQuery }}".
                            </div>
                            <div v-else-if="expandedPurchases.length === 0" class="text-center py-8 text-slate-400 text-sm">
                                Tidak ada purchase aktif di bulan ini.
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <IconDatabaseOff :size="32" class="text-slate-400" :stroke-width="1.5" />
            </div>
            <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">Tidak Ada Data</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Belum ada purchase aktif di periode ini. Sync dari halaman Resume Old Purchase dulu.
            </p>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import {
    IconCalendar, IconChevronDown, IconCircleCheck, IconDatabaseOff,
    IconSearch, IconLock, IconLockOpen, IconPackage,
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
const expandedPurchases = ref([]);
const loadingDetail = ref(false);
const searchQuery = ref('');
const syncingMonth = ref(null);
const syncResult = ref(null);
const syncResultMonth = ref(null);

const filteredPurchases = computed(() => {
    if (!searchQuery.value) return expandedPurchases.value;
    const q = searchQuery.value.toLowerCase();
    return expandedPurchases.value.filter(p =>
        String(p.nomor_faktur || '').toLowerCase().includes(q) ||
        String(p.supplier || '').toLowerCase().includes(q)
    );
});

const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const getMonthName = (month) => monthNames[month] || '';

const navigateToSemester = () => {
    router.get(route('old-purchases-aktif.resume'), {
        year: selectedYear.value, semester: selectedSemester.value,
    }, { preserveState: false, preserveScroll: false });
};

const toggleExpand = async (index, item) => {
    if (expandedMonth.value === index) {
        expandedMonth.value = null; expandedPurchases.value = []; searchQuery.value = '';
        syncResult.value = null; syncResultMonth.value = null; return;
    }
    expandedMonth.value = index; loadingDetail.value = true;
    expandedPurchases.value = []; syncResult.value = null; syncResultMonth.value = null;
    try {
        const response = await axios.get(route('old-purchases-aktif.resume-detail', { year: item.year, month: item.month }));
        expandedPurchases.value = response.data;
    } catch (error) { console.error('Error fetching purchases:', error); }
    finally { loadingDetail.value = false; }
};

const syncStock = async (item, index) => {
    if (syncingMonth.value !== null) return;
    if (!confirm(`Yakin ingin FINALISASI STOCK untuk semua purchase belum final di ${getMonthName(item.month)} ${item.year}?\n\nAksi ini akan menambah stok masuk sesuai detail purchase.`)) return;
    syncingMonth.value = index; syncResult.value = null; syncResultMonth.value = null;
    try {
        const response = await axios.post(route('old-purchases-aktif.sync-stock', { year: item.year, month: item.month }));
        syncResult.value = response.data; syncResultMonth.value = index;
        // Reload detail
        const detailResponse = await axios.get(route('old-purchases-aktif.resume-detail', { year: item.year, month: item.month }));
        expandedPurchases.value = detailResponse.data;
        // Update counts
        monthsData[index].final_purchases = monthsData[index].total_purchases;
        monthsData[index].unfinal_purchases = 0;
    } catch (error) {
        console.error('Error syncing stock:', error);
        alert('Gagal sync stock: ' + (error.response?.data?.message || error.message));
    } finally { syncingMonth.value = null; }
};

const unfinalStock = async (item, index) => {
    if (syncingMonth.value !== null) return;
    if (!confirm(`Yakin ingin UNFINAL semua purchase di ${getMonthName(item.month)} ${item.year}?\n\nStok masuk yang sudah ditambahkan akan dikurangi kembali.`)) return;
    syncingMonth.value = index; syncResult.value = null; syncResultMonth.value = null;
    try {
        const response = await axios.post(route('old-purchases-aktif.unfinal-stock', { year: item.year, month: item.month }));
        syncResult.value = { finalized: 0, already_final: 0, unfinalized: response.data.unfinalized }; syncResultMonth.value = index;
        const detailResponse = await axios.get(route('old-purchases-aktif.resume-detail', { year: item.year, month: item.month }));
        expandedPurchases.value = detailResponse.data;
        monthsData[index].unfinal_purchases = monthsData[index].total_purchases;
        monthsData[index].final_purchases = 0;
    } catch (error) {
        console.error('Error unfinal stock:', error);
        alert('Gagal unfinal: ' + (error.response?.data?.message || error.message));
    } finally { syncingMonth.value = null; }
};

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value || 0);
const route = (name, params) => { if (typeof window !== 'undefined' && window.route) return window.route(name, params); return '#'; };
</script>
