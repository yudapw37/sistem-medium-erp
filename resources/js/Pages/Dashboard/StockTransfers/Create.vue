<template>
    <DashboardLayout>
        <Head title="Buat Mutasi Stok" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buat Mutasi Stok</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Pindahkan stok barang dari satu gudang ke gudang lain.</p>
            </div>
            <Button
                type="link"
                :icon="IconArrowLeft"
                label="Kembali"
                class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                :href="route('stock-transfers.index')"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Settings -->
            <div class="lg:col-span-1 space-y-5">

                <!-- Gudang Asal -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs flex items-center justify-center font-bold">A</span>
                        Gudang Asal
                    </h3>
                    <InputSelect
                        label="Pilih Gudang Asal"
                        :data="warehouses"
                        :selected="fromWarehouse"
                        :set-selected="handleFromWarehouse"
                        placeholder="Pilih gudang sumber..."
                        :searchable="true"
                        :errors="form.errors.from_warehouse_id"
                    />
                    <p v-if="form.errors.from_warehouse_id" class="text-xs text-red-500 mt-1">{{ form.errors.from_warehouse_id }}</p>
                </div>

                <!-- Gudang Tujuan -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-green-500 text-white text-xs flex items-center justify-center font-bold">B</span>
                        Gudang Tujuan
                    </h3>
                    <InputSelect
                        label="Pilih Gudang Tujuan"
                        :data="availableToWarehouses"
                        :selected="toWarehouse"
                        :set-selected="handleToWarehouse"
                        placeholder="Pilih gudang tujuan..."
                        :searchable="true"
                        :errors="form.errors.to_warehouse_id"
                    />
                </div>

                <!-- Tanggal -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Tanggal Mutasi</h3>
                    <input
                        type="date" v-model="form.date"
                        class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                    />
                </div>

                <!-- Catatan -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Catatan</h3>
                    <textarea
                        v-model="form.notes"
                        placeholder="Keterangan (opsional)"
                        rows="3"
                        class="w-full px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none resize-none text-sm"
                    ></textarea>
                </div>

                <!-- Cari Produk -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-3">Cari Produk</h3>
                    <div v-if="!fromWarehouse" class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-700 dark:text-amber-400">
                        Pilih Gudang Asal terlebih dahulu untuk melihat stok.
                    </div>
                    <div v-else class="relative">
                        <div class="flex items-center gap-2">
                            <IconSearch :size="18" class="text-slate-400 flex-shrink-0" />
                            <input
                                type="text" v-model="searchQuery" @input="handleSearch"
                                placeholder="Scan barcode / nama produk..."
                                class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none text-sm"
                            />
                        </div>
                        <div v-if="searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-64 overflow-y-auto">
                            <div
                                v-for="p in searchResults" :key="p.id"
                                @click="addProduct(p)"
                                class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700 cursor-pointer border-b border-slate-100 dark:border-slate-700 last:border-0"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ p.title }}</p>
                                        <p class="text-xs text-slate-400">{{ p.barcode }}</p>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-1 rounded-lg" :class="p.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                                        Stok: {{ p.stock }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Item List -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 h-full flex flex-col">

                    <!-- Header ring gudang -->
                    <div class="flex items-center gap-3 mb-5 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="w-7 h-7 rounded-full bg-orange-500 text-white text-xs flex items-center justify-center font-bold">A</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ fromWarehouse?.name || '—' }}</span>
                        </div>
                        <IconArrowRight :size="20" class="text-slate-400" />
                        <div class="flex items-center gap-2 flex-1 justify-end">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ toWarehouse?.name || '—' }}</span>
                            <span class="w-7 h-7 rounded-full bg-green-500 text-white text-xs flex items-center justify-center font-bold">B</span>
                        </div>
                    </div>

                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Daftar Barang yang Dimutasi</h3>

                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-700 text-left text-slate-500">
                                    <th class="pb-3 pl-2">Produk</th>
                                    <th class="pb-3 text-center">Stok Gudang A</th>
                                    <th class="pb-3 text-center w-28">Qty Pindah</th>
                                    <th class="pb-3 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-if="form.items.length === 0">
                                    <td colspan="4" class="py-10 text-center text-slate-400 italic">
                                        Belum ada produk dipilih
                                    </td>
                                </tr>
                                <tr v-for="(item, idx) in form.items" :key="idx" class="group">
                                    <td class="py-3 pl-2">
                                        <p class="font-medium text-slate-900 dark:text-white">{{ item.title }}</p>
                                        <p class="text-xs text-slate-400">{{ item.barcode }}</p>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="text-xs font-bold px-2 py-1 rounded-lg"
                                            :class="item.stock > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'">
                                            {{ item.stock }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <input
                                            type="number" v-model="item.qty"
                                            :max="item.stock" min="1"
                                            class="w-24 px-2 py-1 text-center text-sm rounded-lg border border-slate-200 bg-slate-50 focus:border-primary-500 focus:ring-0 dark:bg-slate-800 dark:border-slate-700 dark:text-white"
                                        />
                                        <p v-if="item.qty > item.stock" class="text-xs text-red-500 mt-1">Melebihi stok!</p>
                                    </td>
                                    <td class="py-3 text-center">
                                        <button @click="removeItem(idx)" class="text-slate-400 hover:text-red-500 transition-colors">
                                            <IconTrash :size="16" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-slate-500">Total jenis barang</span>
                            <span class="text-lg font-bold text-slate-900 dark:text-white">{{ form.items.length }} produk</span>
                        </div>
                        <div class="flex justify-end gap-3">
                            <Button
                                type="button" label="Batal"
                                class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300"
                                @click="router.visit(route('stock-transfers.index'))"
                            />
                            <Button
                                type="submit" label="Simpan Draft"
                                class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                                :processing="form.processing"
                                :disabled="!canSubmit"
                                @click="submit"
                            />
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
import { IconArrowLeft, IconArrowRight, IconSearch, IconTrash } from '@tabler/icons-vue';
import axios from 'axios';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import { debounce } from 'lodash';

const props = defineProps({ warehouses: Array });

const form = useForm({
    from_warehouse_id: '',
    to_warehouse_id: '',
    date: new Date().toISOString().split('T')[0],
    notes: '',
    items: [],
});

const fromWarehouse    = ref(null);
const toWarehouse      = ref(null);
const searchQuery      = ref('');
const searchResults    = ref([]);

const availableToWarehouses = computed(() =>
    props.warehouses.filter(w => w.id !== fromWarehouse.value?.id)
);

const canSubmit = computed(() =>
    form.from_warehouse_id &&
    form.to_warehouse_id &&
    form.items.length > 0 &&
    form.items.every(i => i.qty > 0 && i.qty <= i.stock)
);

const handleFromWarehouse = (val) => {
    fromWarehouse.value        = val;
    form.from_warehouse_id     = val?.id || '';
    form.items                 = [];
    searchResults.value        = [];
    if (toWarehouse.value?.id === val?.id) {
        toWarehouse.value      = null;
        form.to_warehouse_id   = '';
    }
};

const handleToWarehouse = (val) => {
    toWarehouse.value      = val;
    form.to_warehouse_id   = val?.id || '';
};

const handleSearch = debounce(async () => {
    if (!searchQuery.value || !fromWarehouse.value) { searchResults.value = []; return; }
    try {
        const { data } = await axios.post(route('stock-transfers.searchProduct'), {
            q: searchQuery.value,
            warehouse_id: fromWarehouse.value.id,
        });
        searchResults.value = data;
    } catch (e) { console.error(e); }
}, 300);

const addProduct = (p) => {
    const existing = form.items.find(i => i.product_id === p.id);
    if (existing) {
        existing.qty = Math.min(existing.qty + 1, p.stock);
    } else {
        form.items.push({ product_id: p.id, title: p.title, barcode: p.barcode, stock: p.stock, qty: 1 });
    }
    searchQuery.value = '';
    searchResults.value = [];
};

const removeItem = (idx) => form.items.splice(idx, 1);

const submit = () => form.post(route('stock-transfers.store'));
</script>
