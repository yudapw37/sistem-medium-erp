<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <IconRuler2 :size="18" />
                Satuan Produk
            </h3>
            <button
                type="button"
                @click="addUnit"
                class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1"
            >
                <IconPlus :size="16" /> Tambah Satuan
            </button>
        </div>

        <div v-if="localUnits.length === 0" class="text-center py-6 text-slate-500">
            <IconRuler2 :size="32" class="mx-auto mb-2 text-slate-400" />
            <p class="text-sm">Belum ada satuan. Tambahkan satuan untuk menggunakan multi-barcode.</p>
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="(item, index) in localUnits"
                :key="index"
                class="border border-slate-200 dark:border-slate-700 rounded-xl p-4"
            >
                <div class="flex items-center gap-4 mb-3">
                    <div class="flex-1">
                        <label class="text-xs text-slate-500 mb-1 block">Satuan</label>
                        <select
                            v-model="item.unit_id"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800"
                        >
                            <option value="">Pilih Satuan</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div class="w-24">
                        <label class="text-xs text-slate-500 mb-1 block">Konversi</label>
                        <input
                            v-model.number="item.conversion_rate"
                            type="number"
                            step="0.01"
                            min="1"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800"
                        />
                    </div>
                    <button
                        type="button"
                        @click="removeUnit(index)"
                        class="p-2 text-red-500 hover:bg-red-50 rounded-lg"
                    >
                        <IconTrash :size="16" />
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">Barcode</label>
                        <input
                            v-model="item.barcode"
                            type="text"
                            placeholder="Barcode"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">Harga Beli</label>
                        <input
                            v-model.number="item.buy_price"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 mb-1 block">Harga Jual</label>
                        <input
                            v-model.number="item.sell_price"
                            type="number"
                            min="0"
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg dark:bg-slate-800"
                        />
                    </div>
                    <div class="flex items-end gap-4">
                        <label class="flex items-center gap-2 text-xs">
                            <input type="checkbox" v-model="item.is_base" @change="setAsBase(index)" class="rounded" />
                            <span>Base</span>
                        </label>
                        <label class="flex items-center gap-2 text-xs">
                            <input type="checkbox" v-model="item.is_default" class="rounded" />
                            <span>Default</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="localUnits.length > 0" class="mt-4 flex justify-end">
            <button
                type="button"
                @click="saveUnits"
                :disabled="saving"
                class="px-4 py-2 text-sm bg-primary-500 hover:bg-primary-600 text-white rounded-lg disabled:opacity-50"
            >
                {{ saving ? 'Menyimpan...' : 'Simpan Satuan' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { IconRuler2, IconPlus, IconTrash } from '@tabler/icons-vue';

const props = defineProps({
    productId: Number,
    units: Array,
    productUnits: Array,
});

const localUnits = ref([...props.productUnits.map(pu => ({
    unit_id: pu.unit_id,
    conversion_rate: parseFloat(pu.conversion_rate),
    barcode: pu.barcode || '',
    sell_price: pu.sell_price,
    buy_price: pu.buy_price,
    is_base: pu.is_base,
    is_default: pu.is_default,
}))]);

const saving = ref(false);

const addUnit = () => {
    localUnits.value.push({
        unit_id: '',
        conversion_rate: 1,
        barcode: '',
        sell_price: 0,
        buy_price: 0,
        is_base: localUnits.value.length === 0, // First one is base
        is_default: localUnits.value.length === 0,
    });
};

const removeUnit = (index) => {
    localUnits.value.splice(index, 1);
};

const setAsBase = (index) => {
    // Ensure only one base
    localUnits.value.forEach((item, i) => {
        if (i !== index) {
            item.is_base = false;
        }
    });
    localUnits.value[index].conversion_rate = 1;
};

const saveUnits = () => {
    saving.value = true;
    router.post(route('products.sync-units', props.productId), {
        units: localUnits.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            saving.value = false;
        },
        onError: () => {
            saving.value = false;
        },
    });
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
