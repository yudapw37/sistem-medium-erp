<template>
    <DashboardLayout>
        <Head title="Tambah Pajak" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Pajak</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Tambahkan jenis pajak baru
            </p>
        </div>

        <div class="max-w-2xl">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Code -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Kode <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.code"
                                type="text"
                                placeholder="PPN"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                            <span v-if="form.errors.code" class="text-sm text-red-500">{{ form.errors.code }}</span>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Nama <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="PPN 11%"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                            <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                        </div>

                        <!-- Rate -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tarif (%) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.rate"
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                placeholder="11"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                            <span v-if="form.errors.rate" class="text-sm text-red-500">{{ form.errors.rate }}</span>
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tipe <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.type"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="excluded">Diluar Harga (PPN ditambahkan ke harga)</option>
                                <option value="included">Termasuk Harga (PPN sudah termasuk dalam harga)</option>
                            </select>
                        </div>

                        <!-- Applies To -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Berlaku Untuk <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.applies_to"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="both">Penjualan & Pembelian</option>
                                <option value="sales">Hanya Penjualan</option>
                                <option value="purchases">Hanya Pembelian</option>
                            </select>
                        </div>

                        <!-- Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Hutang Pajak
                            </label>
                            <select
                                v-model="form.account_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Pilih Akun</option>
                                <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                                    {{ acc.code }} - {{ acc.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Options -->
                        <div class="md:col-span-2 flex flex-wrap gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.is_default"
                                    class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-300">Jadikan Default</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.is_active"
                                    class="w-4 h-4 text-primary-500 border-slate-300 rounded focus:ring-primary-500"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg disabled:opacity-50"
                        >
                            Simpan
                        </button>
                        <a
                            :href="route('taxes.index')"
                            class="px-6 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg"
                        >
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    accounts: Array,
});

const form = useForm({
    code: '',
    name: '',
    rate: 11,
    type: 'excluded',
    applies_to: 'both',
    account_id: '',
    is_default: false,
    is_active: true,
});

const submit = () => {
    form.post(route('taxes.store'));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
