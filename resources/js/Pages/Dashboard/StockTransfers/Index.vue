<template>
    <DashboardLayout>
        <Head title="Mutasi Stok Antar Gudang" />

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Mutasi Stok</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Perpindahan stok barang antar gudang.</p>
            </div>
            <Button
                type="link"
                :icon="IconPlus"
                label="Buat Mutasi Baru"
                class="bg-primary-500 hover:bg-primary-600 text-white shadow-lg shadow-primary-500/30"
                :href="route('stock-transfers.create')"
            />
        </div>

        <!-- Filter -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 mb-6 flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <IconSearch :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="filters.q"
                    @input="search"
                    type="text"
                    placeholder="Cari kode mutasi..."
                    class="w-full pl-9 pr-4 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 outline-none focus:border-primary-500"
                />
            </div>
            <select
                v-model="filters.status"
                @change="search"
                class="h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm text-slate-800 dark:text-slate-200 outline-none focus:border-primary-500"
            >
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="finalized">Selesai</option>
            </select>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                        <tr class="text-left text-slate-500 dark:text-slate-400">
                            <th class="px-4 py-3 font-semibold">Kode</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Dari Gudang</th>
                            <th class="px-4 py-3 font-semibold">Ke Gudang</th>
                            <th class="px-4 py-3 font-semibold">Dibuat Oleh</th>
                            <th class="px-4 py-3 font-semibold text-center">Status</th>
                            <th class="px-4 py-3 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="transfers.data.length === 0">
                            <td colspan="7" class="py-12 text-center text-slate-400 italic">Belum ada mutasi stok</td>
                        </tr>
                        <tr
                            v-for="t in transfers.data" :key="t.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                        >
                            <td class="px-4 py-3 font-mono font-semibold text-primary-600 dark:text-primary-400">{{ t.code }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ formatDate(t.date) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-lg text-xs font-semibold">
                                    {{ t.from_warehouse?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg text-xs font-semibold">
                                    {{ t.to_warehouse?.name || '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ t.user?.name || '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="t.status === 'finalized'
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                >
                                    {{ t.status === 'finalized' ? 'Selesai' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <Link :href="route('stock-transfers.show', t.id)" class="p-1.5 rounded-lg text-slate-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                        <IconEye :size="16" />
                                    </Link>
                                    <Link v-if="t.status === 'draft'" :href="route('stock-transfers.edit', t.id)" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                        <IconEdit :size="16" />
                                    </Link>
                                    <button v-if="t.status === 'draft'" @click="confirmFinalize(t)" class="p-1.5 rounded-lg text-slate-400 hover:text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                        <IconCheck :size="16" />
                                    </button>
                                    <button v-if="t.status === 'draft'" @click="confirmDelete(t)" class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <IconTrash :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="transfers.last_page > 1" class="px-4 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <p class="text-sm text-slate-500">
                    Menampilkan {{ transfers.from }}–{{ transfers.to }} dari {{ transfers.total }} data
                </p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in transfers.links" :key="link.label"
                        :href="link.url || '#'"
                        class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                        :class="link.active
                            ? 'bg-primary-500 text-white'
                            : link.url
                                ? 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                                : 'text-slate-300 dark:text-slate-600 cursor-not-allowed'"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- Confirm Finalize Modal -->
        <div v-if="finalizeTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-2xl w-full max-w-sm mx-4 border border-slate-200 dark:border-slate-700">
                <div class="w-12 h-12 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                    <IconCheck :size="24" class="text-green-600" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Finalisasi Mutasi?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                    Mutasi <strong class="text-slate-700 dark:text-slate-300">{{ finalizeTarget?.code }}</strong> akan diproses. Stok akan bergerak dan jurnal akan terbuat. Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex gap-3">
                    <button @click="finalizeTarget = null" class="flex-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">
                        Batal
                    </button>
                    <button @click="doFinalize" class="flex-1 h-10 rounded-xl bg-green-500 hover:bg-green-600 text-white text-sm font-semibold shadow-lg shadow-green-500/30 transition-colors">
                        Ya, Finalisasi
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirm Delete Modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-2xl w-full max-w-sm mx-4 border border-slate-200 dark:border-slate-700">
                <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
                    <IconTrash :size="24" class="text-red-500" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Hapus Mutasi?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                    Mutasi <strong class="text-slate-700 dark:text-slate-300">{{ deleteTarget?.code }}</strong> akan dihapus permanen.
                </p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="flex-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">
                        Batal
                    </button>
                    <button @click="doDelete" class="flex-1 h-10 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold shadow-lg shadow-red-500/30 transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { IconPlus, IconSearch, IconEye, IconEdit, IconCheck, IconTrash } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import { debounce } from 'lodash';

const props = defineProps({
    transfers: Object,
    filters: Object,
});

const filters = ref({ ...props.filters });

const search = debounce(() => {
    router.get(route('stock-transfers.index'), filters.value, { preserveState: true, replace: true });
}, 400);

const finalizeTarget = ref(null);
const deleteTarget   = ref(null);

const confirmFinalize = (t) => finalizeTarget.value = t;
const confirmDelete   = (t) => deleteTarget.value = t;

const doFinalize = () => {
    router.post(route('stock-transfers.finalize', finalizeTarget.value.id), {}, {
        onSuccess: () => finalizeTarget.value = null,
    });
};

const doDelete = () => {
    router.delete(route('stock-transfers.destroy', deleteTarget.value.id), {
        onSuccess: () => deleteTarget.value = null,
    });
};

const formatDate = (d) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
</script>
