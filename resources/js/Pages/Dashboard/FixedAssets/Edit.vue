<template>
    <DashboardLayout>
        <Head title="Edit Aset Tetap" />

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Aset Tetap</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ asset.code }} - {{ asset.name }}
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
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:text-white"
                            >
                                <option value="active">Aktif</option>
                                <option value="disposed">Dibuang</option>
                                <option value="sold">Dijual</option>
                            </select>
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
                            />
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
                            />
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
                                Akun Aset Tetap
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
                        </div>

                        <!-- Depreciation Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Beban Penyusutan
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
                        </div>

                        <!-- Accumulated Depreciation Account -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Akun Akumulasi Penyusutan
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
                        </div>
                    </div>
                </div>

                <!-- Current Values (Read-only) -->
                <div class="mb-6 border-t border-slate-200 dark:border-slate-700 pt-6">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Nilai Saat Ini</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Akumulasi Penyusutan</p>
                            <p class="text-lg font-bold text-orange-600">Rp {{ Number(asset.accumulated_depreciation).toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Nilai Buku</p>
                            <p class="text-lg font-bold text-green-600">Rp {{ Number(asset.book_value).toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <p class="text-sm text-slate-500 dark:text-slate-400">Penyusutan/Bulan</p>
                            <p class="text-lg font-bold text-slate-700 dark:text-slate-300">Rp {{ calculateMonthlyDepreciation() }}</p>
                        </div>
                    </div>
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
    asset: Object,
    assetAccounts: Array,
    expenseAccounts: Array,
    cashAccounts: Array,
});

const form = useForm({
    code: props.asset.code,
    name: props.asset.name,
    category: props.asset.category || '',
    description: props.asset.description || '',
    acquisition_date: props.asset.acquisition_date?.split('T')[0] || '',
    acquisition_cost: props.asset.acquisition_cost,
    useful_life: props.asset.useful_life,
    salvage_value: props.asset.salvage_value,
    depreciation_method: props.asset.depreciation_method,
    status: props.asset.status,
    disposal_date: props.asset.disposal_date?.split('T')[0] || '',
    disposal_value: props.asset.disposal_value || '',
    asset_account_id: props.asset.asset_account_id || '',
    depreciation_account_id: props.asset.depreciation_account_id || '',
    accumulated_depreciation_account_id: props.asset.accumulated_depreciation_account_id || '',
});

const calculateMonthlyDepreciation = () => {
    const depreciable = props.asset.acquisition_cost - props.asset.salvage_value;
    const monthly = depreciable / (props.asset.useful_life * 12);
    return Number(monthly).toLocaleString('id-ID');
};

const submit = () => {
    form.put(route('fixed-assets.update', props.asset.id));
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
