<template>
    <DashboardLayout>
        <Head title="Upload Old Purchase PDF" />

        <!-- Header -->
        <div class="mb-6">
            <Link
                :href="route('old-purchases.index')"
                class="inline-flex items-center gap-2 text-slate-500 hover:text-primary-500 transition-colors mb-2"
            >
                <IconArrowLeft :size="18" />
                <span>Kembali ke Daftar</span>
            </Link>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Upload PDF Faktur Pajak</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Pilih file PDF untuk mengekstrak data pembelian secara otomatis.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upload Card -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop"
                        :class="[
                            'relative h-64 rounded-xl border-2 border-dashed transition-all flex flex-col items-center justify-center text-center p-6',
                            isDragging 
                                ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/10' 
                                : 'border-slate-200 dark:border-slate-800 hover:border-primary-500/50'
                        ]"
                    >
                        <input
                            type="file"
                            @change="handleFileSelect"
                            accept=".pdf"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            ref="fileInput"
                        />
                        
                        <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                            <IconFileDescription :size="32" class="text-slate-400" />
                        </div>
                        
                        <h3 class="text-lg font-medium text-slate-800 dark:text-slate-200 mb-1">
                            {{ selectedFile ? selectedFile.name : 'Pilih File PDF' }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Drag & drop file di sini atau klik untuk memilih
                        </p>
                        <p v-if="selectedFile" class="mt-2 text-xs font-bold text-primary-500">
                            {{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col gap-3">
                        <button
                            @click="uploadAndParse"
                            :disabled="!selectedFile || isParsing"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary-500 text-white font-bold hover:bg-primary-600 disabled:opacity-50 transition-all shadow-lg shadow-primary-500/25"
                        >
                            <span v-if="isParsing">Mengekstrak Data...</span>
                            <template v-else>
                                <IconSearch :size="20" />
                                Ekstrak Data PDF
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="lg:col-span-2">
                <template v-if="parsedData.length > 0">
                    <div class="space-y-6">
                        <div 
                            v-for="(purchase, idx) in parsedData" 
                            :key="idx"
                            class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden"
                        >
                            <div class="bg-slate-50/50 dark:bg-slate-800/50 p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center text-sm">
                                <span class="font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Halaman {{ purchase.pdf_page }}</span>
                                <span class="px-2.5 py-1 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 font-bold">
                                    {{ purchase.nomor_faktur || 'Tanpa No. Faktur' }}
                                </span>
                            </div>
                            
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div class="space-y-3">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Supplier</span>
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ purchase.supplier }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tanggal</span>
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                                {{ purchase.tanggal_faktur ? new Date(purchase.tanggal_faktur).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-2 bg-slate-50 dark:bg-slate-800/30 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-slate-500">Harga Jual (Total)</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(purchase.harga_total) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-slate-500">PPN</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ formatCurrency(purchase.ppn) }}</span>
                                        </div>
                                        <div class="pt-2 mt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                            <span class="text-xs font-bold text-slate-500">Subtotal (Net)</span>
                                            <span class="text-sm font-black text-primary-600 dark:text-primary-400">{{ formatCurrency(purchase.subtotal) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Detail Item</h4>
                                <div class="border rounded-xl overflow-hidden dark:border-slate-800">
                                    <table class="w-full text-xs text-left">
                                        <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500">
                                            <tr>
                                                <th class="p-3">Nama Barang</th>
                                                <th class="p-3 text-center w-20">Qty</th>
                                                <th class="p-3 text-right">Harga Satuan</th>
                                                <th class="p-3 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <tr v-for="(item, iIdx) in purchase.items" :key="iIdx">
                                                <td class="p-3 text-slate-800 dark:text-slate-300 leading-relaxed font-medium">{{ item.nama }}</td>
                                                <td class="p-3 text-center text-slate-600 dark:text-slate-400 font-bold bg-slate-50/30 dark:bg-slate-800/20">{{ item.qty }}</td>
                                                <td class="p-3 text-right text-slate-600 dark:text-slate-400">{{ formatCurrency(item.harga_satuan) }}</td>
                                                <td class="p-3 text-right font-bold text-slate-900 dark:text-white">{{ formatCurrency(item.total) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-6 left-0 right-0 p-4 bg-white/80 dark:bg-slate-900/80 backdrop-blur rounded-2xl border border-primary-500/20 shadow-2xl flex items-center justify-between">
                            <div class="hidden sm:block">
                                <p class="text-xs text-slate-500">Total Data: <span class="font-bold text-primary-600">{{ parsedData.length }} Faktur</span></p>
                            </div>
                            <button
                                @click="saveParsedData"
                                :disabled="isStoring"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-bold hover:bg-slate-800 dark:hover:bg-white disabled:opacity-50 transition-all"
                            >
                                <span v-if="isStoring">Menyimpan...</span>
                                <template v-else>
                                    <IconCheck :size="20" />
                                    Simpan Semua Data ke Database
                                </template>
                            </button>
                        </div>
                    </div>
                </template>

                <div v-else class="h-full flex flex-col items-center justify-center py-20 bg-slate-50/50 dark:bg-slate-900/30 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <IconFileSearch :size="48" class="text-slate-300 mb-4" />
                    <h4 class="text-slate-500 font-medium">Pratinjau Data akan muncul di sini</h4>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconFileDescription,
    IconSearch,
    IconFileSearch,
    IconCheck
} from '@tabler/icons-vue';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const selectedFile = ref(null);
const isDragging = ref(false);
const isParsing = ref(false);
const isStoring = ref(false);
const parsedData = ref([]);
const filename = ref("");

const handleFileSelect = (e) => {
    const file = e.target.files[0];
    if (file && file.type === 'application/pdf') {
        selectedFile.value = file;
    } else {
        Swal.fire('Error', 'Harap pilih file PDF', 'error');
    }
};

const handleDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer.files[0];
    if (file && file.type === 'application/pdf') {
        selectedFile.value = file;
    } else {
        Swal.fire('Error', 'Harap pilih file PDF', 'error');
    }
};

const uploadAndParse = async () => {
    if (!selectedFile.value) return;
    
    isParsing.value = true;
    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const response = await axios.post(route('old-purchases.parse'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        parsedData.value = response.data.data;
        filename.value = response.data.filename;
        Swal.fire({
            title: 'Berhasil!',
            text: `Data ditemukan: ${parsedData.value.length} faktur.`,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } catch (error) {
        console.error('Error parsing PDF:', error);
        Swal.fire('Error', 'Gagal mengekstrak data PDF. Periksa format file.', 'error');
    } finally {
        isParsing.value = false;
    }
};

const saveParsedData = async () => {
    if (parsedData.value.length === 0) return;
    
    isStoring.value = true;
    try {
        await router.post(route('old-purchases.store'), {
            purchases: parsedData.value,
            filename: filename.value
        });
    } catch (error) {
        console.error('Error storing data:', error);
        Swal.fire('Error', 'Gagal menyimpan data ke database.', 'error');
        isStoring.value = false;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Route helper
const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};
</script>
