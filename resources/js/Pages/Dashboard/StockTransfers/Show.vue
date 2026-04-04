<template>
    <DashboardLayout>
        <Head :title="`Mutasi ${transfer.code}`" />

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white font-mono">{{ transfer.code }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Detail mutasi stok antar gudang.</p>
            </div>
            <div class="flex gap-3">
                <Button
                    v-if="transfer.status === 'draft'"
                    type="link" :icon="IconEdit" label="Edit"
                    class="bg-amber-500 hover:bg-amber-600 text-white shadow shadow-amber-500/30"
                    :href="route('stock-transfers.edit', transfer.id)"
                />
                <Button
                    type="link" :icon="IconArrowLeft" label="Kembali"
                    class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300"
                    :href="route('stock-transfers.index')"
                />
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
            <!-- Gudang Asal -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-orange-500/20">
                <p class="text-xs text-white/70 uppercase tracking-wide font-semibold mb-1">Gudang Asal</p>
                <p class="text-xl font-bold">{{ transfer.from_warehouse?.name }}</p>
                <IconArrowRight :size="20" class="mt-2 opacity-60" />
            </div>

            <!-- Gudang Tujuan -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-5 text-white shadow-lg shadow-green-500/20">
                <p class="text-xs text-white/70 uppercase tracking-wide font-semibold mb-1">Gudang Tujuan</p>
                <p class="text-xl font-bold">{{ transfer.to_warehouse?.name }}</p>
                <IconArrowDownRight :size="20" class="mt-2 opacity-60" />
            </div>

            <!-- Info -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Status</span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                            :class="transfer.status === 'finalized'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'">
                            {{ transfer.status === 'finalized' ? '✓ Selesai' : '⏳ Draft' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Tanggal</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(transfer.date) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Dibuat oleh</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ transfer.user?.name }}</span>
                    </div>
                    <div v-if="transfer.finalized_at" class="flex items-center justify-between">
                        <span class="text-xs text-slate-400">Difinalisasi</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ formatDate(transfer.finalized_at) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barang -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-semibold text-slate-900 dark:text-white">Daftar Barang yang Dimutasi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                        <tr class="text-left text-slate-500 dark:text-slate-400">
                            <th class="px-6 py-3 font-semibold">Produk</th>
                            <th class="px-6 py-3 font-semibold">Barcode</th>
                            <th class="px-6 py-3 font-semibold text-center">Qty Dipindah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="d in transfer.details" :key="d.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ d.product?.title }}</td>
                            <td class="px-6 py-3 font-mono text-slate-500 text-xs">{{ d.product?.barcode || '-' }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full text-sm font-bold">
                                    {{ d.qty }} unit
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Catatan -->
        <div v-if="transfer.notes" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-2">Catatan</h3>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ transfer.notes }}</p>
        </div>

        <!-- Info Jurnal -->
        <div v-if="transfer.status === 'finalized'" class="mt-5 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-2xl">
            <p class="text-xs text-blue-700 dark:text-blue-400 font-medium">
                <strong>📒 Jurnal Akuntansi</strong> — Mutasi ini telah membuat jurnal otomatis:
                <strong>Debit Persediaan (Gudang {{transfer.to_warehouse?.name}})</strong> dan
                <strong>Kredit Persediaan (Gudang {{ transfer.from_warehouse?.name }})</strong>
                senilai total harga beli barang yang dipindahkan. Dapat dilihat di menu Jurnal Umum.
            </p>
        </div>

        <!-- Finalize button for draft -->
        <div v-if="transfer.status === 'draft'" class="mt-5">
            <button
                @click="showConfirm = true"
                class="w-full h-12 rounded-2xl bg-green-500 hover:bg-green-600 text-white font-bold text-sm shadow-lg shadow-green-500/30 transition-all flex items-center justify-center gap-2"
            >
                <IconCheck :size="20" />
                Finalisasi Mutasi — Pindahkan Stok & Buat Jurnal
            </button>
        </div>

        <!-- Confirm Modal -->
        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 shadow-2xl w-full max-w-sm mx-4 border border-slate-200 dark:border-slate-700">
                <div class="w-12 h-12 rounded-2xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center mb-4">
                    <IconCheck :size="24" class="text-green-600" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Finalisasi Mutasi?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                    Stok dari <strong>{{ transfer.from_warehouse?.name }}</strong> akan BERKURANG, 
                    stok di <strong>{{ transfer.to_warehouse?.name }}</strong> akan BERTAMBAH, 
                    dan jurnal akuntansi akan dibuat otomatis. Tidak dapat dibatalkan.
                </p>
                <div class="flex gap-3">
                    <button @click="showConfirm = false" class="flex-1 h-10 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">Batal</button>
                    <button @click="doFinalize" class="flex-1 h-10 rounded-xl bg-green-500 hover:bg-green-600 text-white text-sm font-semibold shadow-lg shadow-green-500/30">Ya, Finalisasi</button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconArrowRight, IconArrowDownRight, IconEdit, IconCheck } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';

const props = defineProps({ transfer: Object });

const showConfirm = ref(false);

const doFinalize = () => {
    router.post(route('stock-transfers.finalize', props.transfer.id), {}, {
        onSuccess: () => showConfirm.value = false,
    });
};

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
    : '-';
</script>
