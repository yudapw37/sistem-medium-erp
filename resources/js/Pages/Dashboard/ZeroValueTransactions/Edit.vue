<template>
    <DashboardLayout>
        <Head title="Edit Transaksi Nilai Nol" />

        <div class="mb-6">
            <Link
                :href="route('zero-value-transactions.index')"
                class="inline-flex items-center text-sm text-slate-500 hover:text-primary-600 transition-colors mb-2"
            >
                <IconArrowLeft :size="16" class="mr-1" />
                Kembali ke Daftar
            </Link>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Edit Transaksi Nilai Nol</h1>
            <p class="text-sm text-slate-500">{{ transaction.code }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <Card title="Detail Transaksi">
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Tipe Transaksi <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.type"
                                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                        @change="handleTypeChange"
                                    >
                                        <option value="out">Stok Keluar (Rusak/Expired/Hibah)</option>
                                        <option value="in">Stok Masuk (Bonus/Hadiah)</option>
                                    </select>
                                    <InputError :message="form.errors.type" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Alasan <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.reason"
                                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    >
                                        <option value="">Pilih Alasan</option>
                                        <option v-for="key in currentReasons" :key="key" :value="key">
                                            {{ reasonLabels[key] }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.reason" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Gudang <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.warehouse_id"
                                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    >
                                        <option value="">Pilih Gudang</option>
                                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id">
                                            {{ warehouse.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.warehouse_id" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                        Tanggal <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.date"
                                        type="date"
                                        class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    />
                                    <InputError :message="form.errors.date" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                    Catatan
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    placeholder="Opsional..."
                                ></textarea>
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>
                    </Card>

                    <Card title="Daftar Produk">
                        <div class="p-6">
                            <!-- Search Product -->
                            <div class="mb-4 relative">
                                <div class="relative">
                                    <IconSearch :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="search"
                                        type="text"
                                        :disabled="!form.warehouse_id"
                                        :placeholder="!form.warehouse_id ? 'Pilih gudang terlebih dahulu...' : 'Cari produk berdasarkan nama atau kode...'"
                                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
                                        @input="searchProduct"
                                    />
                                </div>

                                <!-- Search Results -->
                                <div
                                    v-if="searchResults.length > 0"
                                    class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden max-h-64 overflow-y-auto"
                                >
                                    <button
                                        v-for="product in searchResults"
                                        :key="product.id"
                                        type="button"
                                        class="w-full text-left px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors border-b border-slate-100 dark:border-slate-700 last:border-0"
                                        @click="addProduct(product)"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="font-medium text-slate-900 dark:text-white">{{ product.title }}</p>
                                                <p class="text-xs text-slate-500">{{ product.barcode }} | {{ product.category?.name }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-semibold text-slate-500">Stok: {{ product.warehouse_stock }}</p>
                                                <p class="text-sm font-bold text-primary-600">Rp {{ formatNumber(product.buy_price) }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Item Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-semibold border-b border-slate-100 dark:border-slate-700">
                                        <tr>
                                            <th class="px-3 py-3 text-left">Produk</th>
                                            <th class="px-3 py-3 text-center w-32">Qty</th>
                                            <th class="px-3 py-3 text-right">HPP (Satuan)</th>
                                            <th class="px-3 py-3 text-right">Subtotal</th>
                                            <th class="px-3 py-3 text-center w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td class="px-3 py-4">
                                                <div class="font-medium text-slate-900 dark:text-white">{{ item.title }}</div>
                                                <div class="text-xs text-slate-500">{{ item.barcode }}</div>
                                            </td>
                                            <td class="px-3 py-4">
                                                <input
                                                    v-model.number="item.qty"
                                                    type="number"
                                                    min="1"
                                                    class="w-full px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-center"
                                                />
                                            </td>
                                            <td class="px-3 py-4 text-right">
                                                <input
                                                    v-model.number="item.buy_price"
                                                    type="number"
                                                    min="0"
                                                    class="w-full px-2 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-right"
                                                />
                                            </td>
                                            <td class="px-3 py-4 text-right">
                                                Rp {{ formatNumber(item.qty * item.buy_price) }}
                                            </td>
                                            <td class="px-3 py-4 text-center">
                                                <button
                                                    type="button"
                                                    class="text-red-500 hover:text-red-700"
                                                    @click="removeItem(index)"
                                                >
                                                    <IconTrash :size="18" />
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="form.items.length === 0">
                                            <td colspan="5" class="px-3 py-8 text-center text-slate-400 italic">
                                                Belum ada produk yang ditambahkan
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <InputError :message="form.errors.items" />
                        </div>
                    </Card>
                </div>

                <!-- Summary Sidebar -->
                <div class="space-y-6">
                    <Card title="Ringkasan">
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm text-slate-500">
                                    <span>Total Item</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ totalItems }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-slate-500">
                                    <span>Total Qty</span>
                                    <span class="font-medium text-slate-900 dark:text-white">{{ totalQty }}</span>
                                </div>
                                <div class="pt-3 border-t border-slate-100 dark:border-slate-700">
                                    <div class="flex justify-between text-base font-bold">
                                        <span class="text-slate-900 dark:text-white">Total Nilai (HPP)</span>
                                        <span class="text-primary-600">Rp {{ formatNumber(totalValue) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 space-y-3">
                                <Button
                                    type="submit"
                                    class="w-full bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30 py-3"
                                    label="Update Draft"
                                    :disabled="form.processing"
                                    :icon="IconDeviceFloppy"
                                />
                                <Button
                                    type="link"
                                    :href="route('zero-value-transactions.index')"
                                    class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 text-center block rounded-xl font-semibold"
                                    label="Batal"
                                />
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconSearch,
    IconTrash,
    IconDeviceFloppy,
} from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from '@/Components/Dashboard/TableCard.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    transaction: Object,
    warehouses: Array,
    reasonLabels: Object,
    outReasons: Array,
    inReasons: Array,
});

const form = useForm({
    type: props.transaction.type,
    reason: props.transaction.reason,
    warehouse_id: props.transaction.warehouse_id,
    date: props.transaction.date.substr(0, 10),
    notes: props.transaction.notes || '',
    items: props.transaction.details.map((detail) => ({
        product_id: detail.product_id,
        title: detail.product.title,
        barcode: detail.product.barcode,
        qty: detail.qty,
        buy_price: detail.buy_price,
    })),
});

const search = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

const currentReasons = computed(() => {
    return form.type === 'in' ? props.inReasons : props.outReasons;
});

const handleTypeChange = () => {
    form.reason = '';
};

const searchProduct = async () => {
    if (search.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.post(route('zero-value-transactions.searchProduct'), {
            search: search.value,
            warehouse_id: form.warehouse_id,
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Error searching products:', error);
    } finally {
        isSearching.value = false;
    }
};

const addProduct = (product) => {
    const existingItem = form.items.find((i) => i.product_id === product.id);

    if (existingItem) {
        existingItem.qty++;
    } else {
        form.items.push({
            product_id: product.id,
            title: product.title,
            barcode: product.barcode,
            qty: 1,
            buy_price: product.buy_price,
        });
    }

    search.value = '';
    searchResults.value = [];
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const totalItems = computed(() => form.items.length);
const totalQty = computed(() => form.items.reduce((acc, item) => acc + item.qty, 0));
const totalValue = computed(() => form.items.reduce((acc, item) => acc + (item.qty * item.buy_price), 0));

const formatNumber = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const submit = () => {
    form.put(route('zero-value-transactions.update', props.transaction.id));
};
</script>
