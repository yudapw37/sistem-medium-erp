<template>
    <DashboardLayout>
        <Head title="Buat Bundle Baru" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buat Bundle Baru</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Buat paket bundling produk baru</p>
                </div>
                <Button type="link" :icon="IconArrowLeft" class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300" label="Kembali" :href="route('product-bundles.index')" />
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <!-- Bundle Info -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Informasi Bundle</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kode Bundle</label>
                            <input v-model="form.code" type="text" placeholder="BDL-001" class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200" />
                            <p v-if="form.errors.code" class="text-sm text-red-500 mt-1">{{ form.errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama Bundle</label>
                            <input v-model="form.name" type="text" placeholder="Paket Hemat" class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200" />
                            <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Harga Jual Bundle</label>
                            <input v-model="form.sell_price" type="number" min="0" placeholder="0" class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200" />
                            <p v-if="form.errors.sell_price" class="text-sm text-red-500 mt-1">{{ form.errors.sell_price }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Deskripsi</label>
                            <textarea v-model="form.description" rows="3" placeholder="Deskripsi bundle..." class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 resize-none"></textarea>
                        </div>
                        <div>
                            <InputSelect
                                label="Status"
                                :data="statusOptions"
                                :selected="selectedStatus"
                                :set-selected="setSelectedStatus"
                                placeholder="Pilih status"
                                :errors="form.errors.is_active"
                            />
                        </div>
                    </div>
                </div>

                <!-- Product Search -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Cari Produk</h3>
                    <div class="relative">
                        <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari produk..." class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200" />
                        <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-64 overflow-y-auto">
                            <div v-for="product in searchResults" :key="product.id" @click="addProduct(product)" class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer border-b last:border-0">
                                <p class="font-medium text-slate-900 dark:text-white text-sm">{{ product.title }}</p>
                                <p class="text-xs text-slate-500">{{ product.barcode }} - {{ formatCurrency(product.sell_price) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Item dalam Bundle</h3>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-sm text-slate-500">
                                <th class="pb-3">Produk</th>
                                <th class="pb-3 text-right">Harga Satuan</th>
                                <th class="pb-3 text-center w-24">Qty</th>
                                <th class="pb-3 text-right">Subtotal</th>
                                <th class="pb-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-if="form.items.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-500 italic text-sm">Belum ada item</td>
                            </tr>
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="py-3">
                                    <span class="font-medium text-slate-900 dark:text-white text-sm">{{ item.title }}</span>
                                </td>
                                <td class="py-3 text-right text-sm">{{ formatCurrency(item.sell_price) }}</td>
                                <td class="py-3 text-center">
                                    <input type="number" v-model="item.qty" min="1" class="w-16 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-white" />
                                </td>
                                <td class="py-3 text-right font-medium text-slate-900 dark:text-white text-sm">{{ formatCurrency(item.sell_price * item.qty) }}</td>
                                <td class="py-3 text-center">
                                    <button @click="removeItem(index)" class="text-slate-400 hover:text-danger-500"><IconTrash :size="18" /></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-600 dark:text-slate-400">Total Harga Ecer</span>
                            <span class="text-lg text-slate-700 dark:text-slate-300">{{ formatCurrency(totalRetailPrice) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-600 dark:text-slate-400">Harga Bundle</span>
                            <span class="text-lg font-bold text-primary-600">{{ formatCurrency(form.sell_price) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-600 dark:text-slate-400">Hemat</span>
                            <span class="text-lg font-bold text-success-600">{{ formatCurrency(totalRetailPrice - form.sell_price) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-slate-600 dark:text-slate-400">Total Berat</span>
                            <span class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ totalWeight }} gram</span>
                        </div>
                        <div class="flex justify-end gap-3">
                            <p v-if="form.errors.items" class="text-sm text-red-500 mr-auto">{{ form.errors.items }}</p>
                            <Button type="button" label="Batal" class="bg-white border border-slate-200 text-slate-700" @click="router.visit(route('product-bundles.index'))" />
                            <Button type="submit" label="Simpan Bundle" class="bg-primary-500 hover:bg-primary-600 text-white" :processing="form.processing" @click="submit" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconTrash } from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import { debounce } from 'lodash';

const props = defineProps({ products: Array });

const form = useForm({
    code: '',
    name: '',
    description: '',
    sell_price: 0,
    is_active: true,
    items: [],
});

const statusOptions = [
    { id: 1, name: 'Aktif', value: true },
    { id: 0, name: 'Nonaktif', value: false },
];

const selectedStatus = ref(statusOptions[0]);

const setSelectedStatus = (value) => {
    selectedStatus.value = value;
    form.is_active = value.value;
};

const searchQuery = ref('');
const searchResults = ref([]);

const handleSearch = debounce(async () => {
    if (!searchQuery.value) { searchResults.value = []; return; }
    try {
        const response = await axios.post(route('product-bundles.searchProduct'), { q: searchQuery.value });
        searchResults.value = response.data;
    } catch (error) { console.error(error); }
}, 300);

const addProduct = (product) => {
    const existing = form.items.find(item => item.product_id === product.id);
    if (existing) { existing.qty++; }
    else { form.items.push({ product_id: product.id, title: product.title, sell_price: product.sell_price, weight: product.weight ?? 0, qty: 1 }); }
    searchQuery.value = '';
    searchResults.value = [];
};

const removeItem = (index) => { form.items.splice(index, 1); };

const totalRetailPrice = computed(() => form.items.reduce((total, item) => total + (item.sell_price * item.qty), 0));
const totalWeight = computed(() => form.items.reduce((total, item) => total + ((item.weight ?? 0) * item.qty), 0));

const submit = () => { form.post(route('product-bundles.store')); };

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);

const route = (name, params) => (typeof window !== 'undefined' && window.route) ? window.route(name, params) : '#';
</script>
