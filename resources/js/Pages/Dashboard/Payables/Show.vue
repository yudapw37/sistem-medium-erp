<template>
    <DashboardLayout>
        <Head :title="`Hutang - ${purchase.invoice}`" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Hutang</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Invoice: {{ purchase.invoice }}
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700"
                    label="Kembali"
                    :href="route('payables.index')"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Purchase Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-primary-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">Informasi Pembelian</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Supplier</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ purchase.supplier?.name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Tanggal</span>
                        <span class="text-slate-900 dark:text-white">{{ formatDate(purchase.created_at) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Total</span>
                        <span class="font-mono font-bold text-slate-900 dark:text-white">{{ formatCurrency(purchase.grand_total) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-500">Dibayar</span>
                        <span class="font-mono text-green-600">{{ formatCurrency(paidAmount) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-500 font-bold">Sisa</span>
                        <span class="font-mono text-xl font-bold" :class="remaining > 0 ? 'text-red-600' : 'text-green-600'">
                            {{ formatCurrency(remaining) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="bg-green-500 px-6 py-4">
                    <h3 class="text-lg font-bold text-white">Catat Pembayaran</h3>
                </div>
                <div class="p-6">
                    <form v-if="remaining > 0" @submit.prevent="submitPayment">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Tanggal <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="form.payment_date"
                                        required
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Metode <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.payment_method" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                                        <option value="">-- Pilih --</option>
                                        <option v-for="(label, key) in paymentMethods" :key="key" :value="key">{{ label }}</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Jumlah Bayar <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                                    <input
                                        type="text"
                                        :value="formatInputAmount(form.amount)"
                                        @input="e => form.amount = parseInputAmount(e.target.value)"
                                        required
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-right font-mono text-lg"
                                        placeholder="0"
                                    />
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Maksimal: {{ formatCurrency(remaining) }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" v-if="form.payment_method === 'transfer' || form.payment_method === 'giro'">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Bank</label>
                                    <input
                                        type="text"
                                        v-model="form.bank_name"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">No. Rekening</label>
                                    <input
                                        type="text"
                                        v-model="form.bank_account"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                                <textarea
                                    v-model="form.notes"
                                    rows="2"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900"
                                ></textarea>
                            </div>
                        </div>
                        <Button
                            type="submit"
                            :icon="IconCash"
                            label="Simpan Pembayaran"
                            class="bg-green-500 text-white mt-4 w-full"
                            :disabled="form.processing"
                        />
                    </form>
                    <div v-else class="text-center py-8">
                        <IconCircleCheck :size="48" class="mx-auto text-green-500 mb-4" />
                        <p class="text-lg font-bold text-green-600">Hutang Lunas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="mt-6">
            <TableCard title="Riwayat Pembayaran">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh>Referensi</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh>Metode</TableTh>
                            <TableTh class="text-right">Jumlah</TableTh>
                            <TableTh>Catatan</TableTh>
                            <TableTh>Oleh</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <template v-if="payments.length > 0">
                            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <TableTd class="font-mono text-primary-600">{{ payment.reference }}</TableTd>
                                <TableTd>{{ formatDate(payment.payment_date) }}</TableTd>
                                <TableTd>{{ paymentMethods[payment.payment_method] || payment.payment_method }}</TableTd>
                                <TableTd class="text-right font-mono text-green-600 font-bold">+{{ formatCurrency(payment.amount) }}</TableTd>
                                <TableTd class="text-slate-500 max-w-[200px] truncate">{{ payment.notes || '-' }}</TableTd>
                                <TableTd>{{ payment.user?.name }}</TableTd>
                            </tr>
                        </template>
                        <tr v-else>
                            <TableTd colspan="6" class="text-center py-8">
                                <p class="text-slate-500">Belum ada pembayaran</p>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { IconArrowLeft, IconCash, IconCircleCheck } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';

const props = defineProps({
    purchase: Object,
    payments: Array,
    paidAmount: Number,
    remaining: Number,
    paymentMethods: Object,
});

const form = useForm({
    payment_date: new Date().toISOString().slice(0, 10),
    amount: '',
    payment_method: '',
    bank_name: '',
    bank_account: '',
    notes: '',
});

const formatInputAmount = (val) => {
    if (!val) return '';
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

const parseInputAmount = (val) => {
    return val.replace(/\./g, '').replace(/[^0-9]/g, '');
};

const submitPayment = () => {
    form.post(route('payables.payment', props.purchase.id));
};

const formatDate = (val) => {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val || 0);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
