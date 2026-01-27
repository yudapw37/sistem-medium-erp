<template>
    <DashboardLayout>
        <Head title="Tambah Aset Tetap" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tambah Aset Tetap</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Tambahkan aset tetap baru ke sistem
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <form @submit.prevent="submit">
                <!-- Info Section -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Informasi Aset</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Code -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Kode Aset <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.code"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="FA-001"
                            />
                            <span v-if="form.errors.code" class="text-sm text-red-500">{{ form.errors.code }}</span>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Nama Aset <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="Komputer Kasir"
                            />
                            <span v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</span>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Kategori
                            </label>
                            <input
                                v-model="form.category"
                                type="text"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="Elektronik"
                            />
                        </div>

                        <!-- Acquisition Date -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Tanggal Perolehan <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.acquisition_date"
                                type="date"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            />
                            <span v-if="form.errors.acquisition_date" class="text-sm text-red-500">{{ form.errors.acquisition_date }}</span>
                        </div>

                        <!-- Acquisition Cost -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Harga Perolehan <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.acquisition_cost"
                                type="number"
                                step="0.01"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="10000000"
                            />
                            <span v-if="form.errors.acquisition_cost" class="text-sm text-red-500">{{ form.errors.acquisition_cost }}</span>
                        </div>

                        <!-- Useful Life -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Masa Manfaat (Tahun) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.useful_life"
                                type="number"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="5"
                            />
                            <span v-if="form.errors.useful_life" class="text-sm text-red-500">{{ form.errors.useful_life }}</span>
                        </div>

                        <!-- Salvage Value -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Nilai Residu
                            </label>
                            <input
                                v-model="form.salvage_value"
                                type="number"
                                step="0.01"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="0"
                            />
                        </div>

                        <!-- Depreciation Method -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Metode Penyusutan <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.depreciation_method"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="straight_line">Garis Lurus</option>
                                <option value="declining_balance">Saldo Menurun</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Deskripsi
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                                placeholder="Deskripsi aset..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Account Section -->
                <div class="mb-6 border-t border-slate-200 dark:border-slate-700 pt-6">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Akun Akuntansi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Asset Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Aset Tetap <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.asset_account_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Pilih Akun</option>
                                <option v-for="acc in assetAccounts" :key="acc.id" :value="acc.id">
                                    {{ acc.code }} - {{ acc.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.asset_account_id" class="text-sm text-red-500">{{ form.errors.asset_account_id }}</span>
                        </div>

                        <!-- Cash Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Kas/Bank (Pembayaran) <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.cash_account_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Pilih Akun</option>
                                <option v-for="acc in cashAccounts" :key="acc.id" :value="acc.id">
                                    {{ acc.code }} - {{ acc.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.cash_account_id" class="text-sm text-red-500">{{ form.errors.cash_account_id }}</span>
                        </div>

                        <!-- Depreciation Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Beban Penyusutan <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.depreciation_account_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Pilih Akun</option>
                                <option v-for="acc in expenseAccounts" :key="acc.id" :value="acc.id">
                                    {{ acc.code }} - {{ acc.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.depreciation_account_id" class="text-sm text-red-500">{{ form.errors.depreciation_account_id }}</span>
                        </div>

                        <!-- Accumulated Depreciation Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Akumulasi Penyusutan <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.accumulated_depreciation_account_id"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="">Pilih Akun</option>
                                <option v-for="acc in assetAccounts" :key="acc.id" :value="acc.id">
                                    {{ acc.code }} - {{ acc.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.accumulated_depreciation_account_id" class="text-sm text-red-500">{{ form.errors.accumulated_depreciation_account_id }}</span>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>Info:</strong> Jurnal pembelian akan otomatis dibuat saat aset disimpan.
                        Debit ke akun aset tetap dan Credit ke akun kas/bank yang dipilih.
                    </p>
                </div>

                <div class="flex gap-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg disabled:opacity-50"
                    >
                        Simpan
                    </button>
                    <a
                        :href="route('fixed-assets.index')"
                        class="px-6 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    assetAccounts: Array,
    expenseAccounts: Array,
    cashAccounts: Array,
});

const form = useForm({
    code: '',
    name: '',
    category: '',
    description: '',
    acquisition_date: '',
    acquisition_cost: '',
    useful_life: 5,
    salvage_value: 0,
    depreciation_method: 'straight_line',
    asset_account_id: '',
    depreciation_account_id: '',
    accumulated_depreciation_account_id: '',
    cash_account_id: '',
});

const submit = () => {
    form.post(route('fixed-assets.store'));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
