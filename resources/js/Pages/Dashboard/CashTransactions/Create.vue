<template>
    <DashboardLayout>
        <Head :title="type === 'in' ? 'Kas Masuk' : 'Kas Keluar'" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ type === 'in' ? 'Kas Masuk' : 'Kas Keluar' }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Catat transaksi {{ type === 'in' ? 'penerimaan' : 'pengeluaran' }} kas
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconArrowLeft"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50"
                    label="Kembali"
                    :href="route('cash-transactions.index')"
                />
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Reference -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            No. Referensi
                        </label>
                        <input
                            type="text"
                            v-model="form.reference"
                            disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono"
                        />
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="form.date"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                        />
                        <p v-if="form.errors.date" class="text-red-500 text-sm mt-1">{{ form.errors.date }}</p>
                    </div>

                    <!-- Cash Account -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Akun Kas/Bank <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.cash_account_id"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                        >
                            <option value="">-- Pilih Akun Kas --</option>
                            <option v-for="acc in cashAccounts" :key="acc.id" :value="acc.id">
                                {{ acc.code }} - {{ acc.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.cash_account_id" class="text-red-500 text-sm mt-1">{{ form.errors.cash_account_id }}</p>
                    </div>

                    <!-- Counter Account -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Akun {{ type === 'in' ? 'Sumber Dana' : 'Tujuan Pembayaran' }} <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.account_id"
                            required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                        >
                            <option value="">-- Pilih Akun --</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                {{ acc.code }} - {{ acc.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.account_id" class="text-red-500 text-sm mt-1">{{ form.errors.account_id }}</p>
                    </div>

                    <!-- Amount with Currency Format -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium">Rp</span>
                            <input
                                type="text"
                                :value="formattedAmount"
                                @input="handleAmountInput"
                                @blur="formatOnBlur"
                                required
                                class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-right font-mono"
                                placeholder="0"
                            />
                        </div>
                        <p v-if="form.errors.amount" class="text-red-500 text-sm mt-1">{{ form.errors.amount }}</p>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Keterangan
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                            placeholder="Keterangan transaksi..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end mt-8 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <Button
                        type="submit"
                        :icon="IconCheck"
                        :label="type === 'in' ? 'Simpan Kas Masuk' : 'Simpan Kas Keluar'"
                        :class="type === 'in' ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-red-500 hover:bg-red-600 text-white'"
                        :disabled="form.processing"
                    />
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { IconArrowLeft, IconCheck } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({
    type: String,
    cashAccounts: Array,
    accounts: Array,
    reference: String,
});

const form = useForm({
    type: props.type,
    reference: props.reference,
    date: new Date().toISOString().slice(0, 10),
    cash_account_id: '',
    account_id: '',
    amount: '',
    description: '',
});

// Format number with thousand separators (dots)
const formatNumber = (value) => {
    if (!value) return '';
    return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// Parse formatted number back to raw number
const parseNumber = (value) => {
    if (!value) return '';
    return value.toString().replace(/\./g, '');
};

// Computed formatted amount for display
const formattedAmount = computed(() => {
    return formatNumber(form.amount);
});

// Handle input - only allow numbers
const handleAmountInput = (e) => {
    let value = e.target.value.replace(/\./g, '').replace(/[^0-9]/g, '');
    form.amount = value;
    e.target.value = formatNumber(value);
};

// Format on blur
const formatOnBlur = (e) => {
    e.target.value = formatNumber(form.amount);
};

const submit = () => {
    form.post(route('cash-transactions.store'));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) return window.route(name, params);
    return '#';
};
</script>
