<template>
    <DashboardLayout>
        <Head title="Riwayat Pembayaran Hutang" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Riwayat Pembayaran Hutang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Lihat seluruh transaksi pembayaran hutang kepada supplier.
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Invoice</label>
                    <input
                        v-model="filters.q"
                        type="text"
                        placeholder="Cari invoice..."
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                        @input="handleSearch"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Supplier</label>
                    <select
                        v-model="filters.supplier_id"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                        @change="handleSearch"
                    >
                        <option value="">Semua Supplier</option>
                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                            {{ supplier.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Dari Tanggal</label>
                    <input
                        v-model="filters.start_date"
                        type="date"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                        @change="handleSearch"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Sampai Tanggal</label>
                    <input
                        v-model="filters.end_date"
                        type="date"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200"
                        @change="handleSearch"
                    />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">No. Referensi</th>
                            <th class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Tanggal</th>
                            <th class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Invoice Supplier</th>
                            <th class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">Metode</th>
                            <th class="px-6 py-4 text-sm font-semibold text-right text-slate-700 dark:text-slate-300">Nominal</th>
                            <th class="px-6 py-4 text-sm font-semibold text-slate-700 dark:text-slate-300">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="payments.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500 italic">
                                Belum ada data pembayaran.
                            </td>
                        </tr>
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ payment.reference }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ formatDate(payment.payment_date) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-medium text-slate-900 dark:text-white">{{ payment.purchase?.invoice }}</span>
                                    <span class="text-xs text-slate-500">{{ payment.supplier?.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm capitalize text-slate-600 dark:text-slate-400">{{ payment.payment_method }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-right text-danger-600 dark:text-danger-400">{{ formatCurrency(payment.amount) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">{{ payment.user?.name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                <Pagination :links="payments.links" />
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Pagination from '@/Components/Dashboard/Pagination.vue';
import { debounce } from 'lodash';

const props = defineProps({
    payments: Object,
    suppliers: Array,
    paymentMethods: Array,
    filters: Object
});

const filters = reactive({
    q: props.filters.q || '',
    supplier_id: props.filters.supplier_id || '',
    payment_method: props.filters.payment_method || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

const handleSearch = debounce(() => {
    router.get(route('payables.payment-history'), filters, {
        preserveState: true,
        replace: true,
    });
}, 300);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const route = (name, params) => window.route(name, params);
</script>
