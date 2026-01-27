<template>
    <DashboardLayout>
        <Head title="Detail Transaksi" />

        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Detail Transaksi</h1>
                        <span
                            :class="[
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                sale.status === 'finalized'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            ]"
                        >
                            {{ sale.status === 'finalized' ? 'Finalized' : 'Draft' }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                         No. Transaksi: {{ sale.invoice }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button
                        @click="showPrintModal = true"
                        :icon="IconPrinter"
                        class="bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/30"
                        label="Cetak"
                    />
                    <Button
                        v-if="sale.status === 'draft'"
                        @click="handleFinalize"
                        :icon="IconCheck"
                        class="bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-600/30"
                        label="Finalisasi"
                    />
                    <Button
                        v-if="sale.status === 'draft'"
                        type="link"
                        :icon="IconEdit"
                        class="bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-500/30"
                        label="Edit"
                        :href="route('sales.edit', sale.id)"
                    />
                    <Button
                        type="link"
                        :icon="IconArrowLeft"
                        class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        label="Kembali"
                        :href="route('sales.index')"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Details Card -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Ringkasan</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Tanggal</p>
                            <p class="font-medium text-slate-900 dark:text-white">{{ new Date(sale.created_at).toLocaleString('id-ID') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Customer</p>
                            <p class="font-medium text-slate-900 dark:text-white">{{ sale.customer?.name || '-' }}</p>
                            <p class="text-sm text-slate-500">{{ sale.customer?.phone || '' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Gudang</p>
                            <p class="font-medium text-slate-900 dark:text-white">{{ sale.warehouse?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Dibuat Oleh</p>
                            <p class="font-medium text-slate-900 dark:text-white">{{ sale.user?.name }}</p>
                        </div>
                        <div v-if="sale.finalized_at">
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Selesai Pada</p>
                            <p class="font-medium text-green-600 dark:text-green-400">{{ new Date(sale.finalized_at).toLocaleString('id-ID') }}</p>
                        </div>
                        <div v-if="sale.is_preorder" class="p-3 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-xl mb-4">
                            <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs uppercase mb-2">
                                <IconReceipt :size="16" />
                                Informasi Pre-Order
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <p class="text-[10px] text-slate-500 uppercase">Status PO</p>
                                    <p class="text-sm font-bold" :class="sale.preorder_status === 'ready' ? 'text-success-600' : 'text-amber-600'">
                                        {{ sale.preorder_status === 'ready' ? 'STOK SIAP' : 'MENUNGGU STOK' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-500 uppercase">Est. Ready</p>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ sale.estimated_ready_date ? new Date(sale.estimated_ready_date).toLocaleDateString('id-ID') : '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-500 uppercase">DP / Terbayar</p>
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ formatCurrency(sale.paid_amount) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider">Tipe Pembayaran</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 uppercase mt-1">
                                    {{ sale.payment_type }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider">Jenis Pengiriman</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 uppercase mt-1">
                                    {{ sale.shipping_type === 'pickup' ? 'Ambil di Gudang' : (sale.shipping_type === 'cod' ? 'COD' : 'Jasa Kirim') }}
                                </span>
                            </div>
                        </div>

                        <div v-if="sale.shipping_type !== 'pickup'" class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">PENGIRIM</h4>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ sale.sender_name || '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ sale.sender_phone || '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">PENERIMA</h4>
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ sale.shipping_name || '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ sale.shipping_phone || '-' }}</p>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-50 dark:border-slate-800/50">
                                <p class="text-xs text-slate-500 uppercase tracking-wider">Alamat Pengiriman</p>
                                <p class="text-sm text-slate-900 dark:text-white mt-1">{{ sale.shipping_address || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-semibold text-slate-900 dark:text-white">Daftar Item</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-6 py-4">Produk</th>
                                    <th class="px-6 py-4 text-right">Harga Jual</th>
                                    <th class="px-6 py-4 text-right">Diskon</th>
                                    <th class="px-6 py-4 text-center">Qty</th>
                                    <th class="px-6 py-4 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="detail in sale.details" :key="detail.id" class="text-sm">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900 dark:text-white flex items-center gap-2">
                                                <span v-if="detail.bundle_id" class="text-purple-600 font-bold">[B]</span>
                                                {{ detail.bundle_id ? detail.bundle.name : detail.product.title }}
                                            </span>
                                            <span class="text-xs text-slate-500">{{ detail.bundle_id ? detail.bundle.code : detail.product.barcode }}</span>
                                            
                                            <!-- Bundle Components -->
                                            <div v-if="detail.bundle_id" class="mt-2 pl-4 border-l-2 border-slate-100 dark:border-slate-800 space-y-1">
                                                <div v-for="item in detail.bundle.items" :key="item.id" class="text-[10px] text-slate-500 flex items-center gap-1">
                                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                    {{ item.product.title }} ({{ item.qty }}x)
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">
                                        {{ formatCurrency(detail.sell_price) }}
                                    </td>
                                    <td class="px-6 py-4 text-right" :class="detail.discount > 0 ? 'text-danger-500' : 'text-slate-400'">
                                        {{ detail.discount > 0 ? '-' + formatCurrency(detail.discount) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-slate-900 dark:text-white font-medium">
                                        {{ detail.qty }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-slate-900 dark:text-white font-bold">
                                        {{ formatCurrency((detail.sell_price * detail.qty) - (detail.discount || 0)) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-slate-100 dark:border-slate-800">
                                    <td colspan="4" class="px-6 py-2 text-right text-slate-500 font-medium">Subtotal Item</td>
                                    <td class="px-6 py-2 text-right font-semibold text-slate-900 dark:text-white">
                                        {{ formatCurrency(sale.details.reduce((total, detail) => total + (detail.sell_price * detail.qty) - (detail.discount || 0), 0)) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-2 text-right text-slate-500 font-medium">Ongkos Kirim</td>
                                    <td class="px-6 py-2 text-right font-semibold text-slate-900 dark:text-white">
                                        {{ formatCurrency(sale.shipping_cost) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-2 text-right text-slate-500 font-medium">Biaya Lainnya</td>
                                    <td class="px-6 py-2 text-right font-semibold text-slate-900 dark:text-white">
                                        {{ formatCurrency(sale.other_cost) }}
                                    </td>
                                </tr>
                                <tr v-if="sale.discount > 0">
                                    <td colspan="4" class="px-6 py-2 text-right text-slate-500 font-medium">Diskon</td>
                                    <td class="px-6 py-2 text-right font-semibold text-danger-500">
                                        -{{ formatCurrency(sale.discount) }}
                                    </td>
                                </tr>
                                <tr v-if="sale.event_discount > 0">
                                    <td colspan="4" class="px-6 py-2 text-right text-slate-500 font-medium">Diskon Event</td>
                                    <td class="px-6 py-2 text-right font-semibold text-purple-600">
                                        -{{ formatCurrency(sale.event_discount) }}
                                    </td>
                                </tr>
                                <tr class="bg-slate-50 dark:bg-slate-800/30">
                                    <td colspan="4" class="px-6 py-4 text-right font-bold text-slate-600 dark:text-slate-400">TOTAL AKHIR</td>
                                    <td class="px-6 py-4 text-right font-black text-primary-600 dark:text-primary-400 text-lg">
                                        {{ formatCurrency(sale.grand_total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print Modal -->
        <Teleport to="body">
            <div v-if="showPrintModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60" @click="showPrintModal = false"></div>
                <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">Cetak Dokumen</h3>
                    
                    <!-- Print Type Selector -->
                    <div class="flex gap-3 mb-6">
                        <button
                            v-for="type in printTypes"
                            :key="type.id"
                            @click="selectedPrintType = type.id"
                            :class="[
                                'flex-1 p-4 rounded-xl border-2 transition-all text-left',
                                selectedPrintType === type.id
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                    : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'
                            ]"
                        >
                            <component :is="type.icon" :size="24" class="mb-2" :class="selectedPrintType === type.id ? 'text-primary-600' : 'text-slate-500'" />
                            <p class="font-semibold text-slate-800 dark:text-white">{{ type.name }}</p>
                            <p class="text-xs text-slate-500">{{ type.description }}</p>
                        </button>
                    </div>
                    
                    <!-- Preview -->
                    <div class="bg-slate-100 dark:bg-slate-800 p-4 rounded-xl mb-4 overflow-x-auto max-h-96">
                        <SalesReceipt58mm 
                            v-if="selectedPrintType === '58mm'"
                            ref="receiptRef"
                            :sale="sale" 
                            :storeName="storeName"
                            :storePhone="storePhone"
                        />
                        <SalesReceipt 
                            v-else-if="selectedPrintType === '80mm'"
                            ref="receiptRef"
                            :sale="sale" 
                            :storeName="storeName"
                            :storeAddress="storeAddress"
                            :storePhone="storePhone"
                        />
                        <SalesInvoice
                            v-else
                            ref="receiptRef"
                            :sale="sale" 
                            :storeName="storeName"
                            :storeAddress="storeAddress"
                            :storePhone="storePhone"
                        />
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <Button
                            type="button"
                            label="Tutup"
                            class="bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300"
                            @click="showPrintModal = false"
                        />
                        <Button
                            type="button"
                            :icon="IconPrinter"
                            label="Cetak"
                            class="bg-blue-600 hover:bg-blue-700 text-white"
                            @click="handlePrint"
                        />
                    </div>
                </div>
            </div>
        </Teleport>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { IconArrowLeft, IconCheck, IconEdit, IconPrinter, IconReceipt, IconFileText } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import SalesReceipt from '@/Components/Receipt/SalesReceipt.vue';
import SalesReceipt58mm from '@/Components/Receipt/SalesReceipt58mm.vue';
import SalesInvoice from '@/Components/Receipt/SalesInvoice.vue';

const props = defineProps({
    sale: Object,
});

const showPrintModal = ref(false);
const receiptRef = ref(null);
const selectedPrintType = ref('80mm');

const printTypes = [
    { id: '58mm', name: 'Struk 58mm', description: 'Thermal printer kecil', icon: IconReceipt },
    { id: '80mm', name: 'Struk 80mm', description: 'Thermal printer standar', icon: IconReceipt },
    { id: 'invoice', name: 'Invoice A4', description: 'Kertas ukuran penuh', icon: IconFileText },
];

// Store info
const storeName = 'TOKO ANDA';
const storeAddress = '';
const storePhone = '';

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

const handleFinalize = () => {
    if (confirm('Finalisasi transaksi? Stok akan dikurangi dan transaksi akan dikunci.')) {
        router.post(route('sales.finalize', props.sale.id));
    }
};

const handlePrint = () => {
    const printWindow = window.open('', '_blank');
    const receiptHtml = receiptRef.value?.$el?.outerHTML || '';
    
    // Dynamic page size based on print type
    const pageStyles = {
        '58mm': '@page { size: 58mm auto; margin: 0; }',
        '80mm': '@page { size: 80mm auto; margin: 0; }',
        'invoice': '@page { size: A4; margin: 10mm; }',
    };
    
    const isInvoice = selectedPrintType.value === 'invoice';
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${isInvoice ? 'Invoice' : 'Struk'} - ${props.sale.invoice}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: ${isInvoice ? 'Arial, sans-serif' : 'monospace'}; font-size: ${isInvoice ? '11pt' : '10pt'}; }
                .sales-receipt, .sales-receipt-58 { padding: 2mm; }
                .sales-invoice { padding: 10mm; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-left { text-align: left; }
                .text-xs { font-size: 9pt; }
                .text-sm { font-size: 10pt; }
                .text-lg { font-size: 14pt; }
                .text-xl { font-size: 16pt; }
                .text-2xl { font-size: 18pt; }
                .font-bold { font-weight: bold; }
                .font-semibold { font-weight: 600; }
                .font-medium { font-weight: 500; }
                .mb-1 { margin-bottom: 2mm; }
                .mb-2, .mb-6 { margin-bottom: 4mm; }
                .mb-8 { margin-bottom: 8mm; }
                .my-1 { margin-top: 2mm; margin-bottom: 2mm; }
                .mt-2, .mt-8 { margin-top: 4mm; }
                .p-4, .p-8 { padding: 4mm; }
                .py-2, .py-3 { padding-top: 2mm; padding-bottom: 2mm; }
                .px-4 { padding-left: 4mm; padding-right: 4mm; }
                .pl-2 { padding-left: 4mm; }
                .pb-4 { padding-bottom: 4mm; }
                .pt-4 { padding-top: 4mm; }
                .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                .flex { display: flex; }
                .grid { display: grid; }
                .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
                .gap-8 { gap: 8mm; }
                .justify-between { justify-content: space-between; }
                .justify-end { justify-content: flex-end; }
                .items-start { align-items: flex-start; }
                .uppercase { text-transform: uppercase; }
                .border-b, .border-t { border-style: solid; border-color: #e2e8f0; }
                .border-b { border-bottom-width: 1px; }
                .border-b-2 { border-bottom-width: 2px; }
                .border-t { border-top-width: 1px; }
                .border-slate-200 { border-color: #e2e8f0; }
                .border-slate-800 { border-color: #1e293b; }
                .bg-slate-100 { background-color: #f1f5f9; }
                .bg-slate-800 { background-color: #1e293b; color: white; }
                .bg-slate-50 { background-color: #f8fafc; }
                .bg-yellow-50 { background-color: #fefce8; }
                .text-slate-500, .text-slate-600 { color: #64748b; }
                .text-slate-700, .text-slate-800 { color: #334155; }
                .text-red-500 { color: #ef4444; }
                .text-green-600 { color: #16a34a; }
                .text-yellow-600 { color: #ca8a04; }
                .text-purple-500, .text-purple-600 { color: #9333ea; }
                .text-primary-600 { color: #0891b2; }
                .rounded-lg { border-radius: 4mm; }
                .w-80 { width: 80mm; }
                .w-full { width: 100%; }
                pre { font-family: monospace; margin: 0; white-space: pre-wrap; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 2mm; }
                @media print { ${pageStyles[selectedPrintType.value]} }
            </style>
        </head>
        <body>${receiptHtml}</body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.onload = () => {
        printWindow.print();
        printWindow.onafterprint = () => printWindow.close();
    };
};
</script>

