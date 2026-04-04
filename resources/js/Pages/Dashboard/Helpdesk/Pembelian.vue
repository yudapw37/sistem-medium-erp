<script setup>
import { onMounted, ref } from 'vue';
import mermaid from 'mermaid';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    IconShoppingBag,
    IconArrowRight,
    IconClipboardCheck,
    IconBook,
    IconArrowBackUp,
    IconTruck,
} from '@tabler/icons-vue';

const purchaseFlow = [
    {
        title: '1. Buat Purchase Order (Draft)',
        desc: 'Admin/Purchasing membuat PO dengan memilih Supplier, Gudang, metode bayar (Tunai/Tempo), serta menginput produk dan harga beli. Order disimpan sebagai Draft.',
        icon: IconShoppingBag,
        color: 'sky'
    },
    {
        title: '2. Finalisasi PO',
        desc: 'Saat Finalize ditekan: sistem menambah stok fisik ke Gudang tujuan, meng-update harga beli (buy_price) produk, dan membuat Jurnal Akuntansi otomatis.',
        icon: IconClipboardCheck,
        color: 'indigo'
    },
    {
        title: '3. Pemenuhan Pre-Order Otomatis',
        desc: 'Setelah stok masuk, sistem otomatis mengecek apakah ada order penjualan yang sedang menunggu stok (Pre-Order). Jika ada dan stok cukup, statusnya berubah ke "Siap Kirim".',
        icon: IconTruck,
        color: 'emerald'
    }
];

const returnFlow = [
    {
        title: '1. Input Return Pembelian',
        desc: 'Admin membuat "Return Pembelian Baru" sebagai Draft. Pilih Supplier, Gudang, dan PILIH PO/Invoice asli yang sedang diretur — agar sistem dapat menentukan apakah pembelian itu Tunai atau Kredit/Tempo.',
        icon: IconArrowBackUp,
        color: 'orange'
    },
    {
        title: '2. Finalisasi Return (Otomatis)',
        desc: 'Saat Finalize: ① Stok fisik berkurang di Gudang. ② Jurnal Pembalikan dibuat otomatis (Dr. Hutang Usaha/Kas, Cr. Persediaan). ③ Jika PO asli bersifat Tempo — PayablePayment dibuat otomatis untuk memangkas saldo Hutang ke Supplier.',
        icon: IconClipboardCheck,
        color: 'rose'
    }
];

const journalNotes = [
    {
        title: '📦 Saat Pembelian di-Finalize',
        desc: 'Debit Persediaan (Inventory) + Kredit Kas (jika Tunai) atau Kredit Hutang Usaha/AP (jika Tempo). Harga beli produk juga diperbarui ke nilai terbaru.'
    },
    {
        title: '↩️ Saat Return Pembelian di-Finalize',
        desc: 'Debit Hutang Usaha/Kas (membalik kredit asli) + Kredit Persediaan (membalik debit asli). Jurnal pembalikan ini dibuat OTOMATIS oleh sistem.'
    },
    {
        title: '💳 Pemotongan Hutang Otomatis (Tempo)',
        desc: 'Jika PO asli bersifat Tempo dan Admin memilih PO-nya saat membuat Return — sistem secara otomatis membuat PayablePayment untuk memangkas saldo Hutang Usaha ke Supplier tersebut.'
    }
];

onMounted(() => {
    mermaid.initialize({
        startOnLoad: false,
        theme: 'default',
        flowchart: {
            useMaxWidth: false,
            htmlLabels: true,
            nodeSpacing: 60,
            rankSpacing: 80,
        },
        fontSize: 16,
    });
    mermaid.run({ querySelector: '.mermaid' });
});

const dfdRef = ref(null);

const downloadPdf = async () => {
    const svgEl = dfdRef.value?.querySelector('svg');
    if (!svgEl) {
        alert('Diagram belum siap. Tunggu beberapa detik lalu coba lagi.');
        return;
    }

    const { default: jsPDF } = await import('jspdf');

    let svgW = svgEl.viewBox?.baseVal?.width || 0;
    let svgH = svgEl.viewBox?.baseVal?.height || 0;
    if (!svgW || !svgH) {
        const rect = svgEl.getBoundingClientRect();
        svgW = rect.width;
        svgH = rect.height;
    }
    if (!svgW || !svgH) {
        svgW = svgEl.scrollWidth;
        svgH = svgEl.scrollHeight;
    }

    const svgClone = svgEl.cloneNode(true);
    svgClone.setAttribute('width', svgW);
    svgClone.setAttribute('height', svgH);
    svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    svgClone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

    const svgData = new XMLSerializer().serializeToString(svgClone);
    const svgBase64 = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));

    await new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            const scale = 2;
            const canvas = document.createElement('canvas');
            canvas.width  = svgW * scale;
            canvas.height = svgH * scale;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.scale(scale, scale);
            ctx.drawImage(img, 0, 0, svgW, svgH);

            const imgData = canvas.toDataURL('image/png');
            const pxToMm  = 0.264583;
            const pageW   = Math.max(svgW * pxToMm, 50);
            const pageH   = Math.max(svgH * pxToMm, 50);

            const pdf = new jsPDF({
                orientation: pageW > pageH ? 'landscape' : 'portrait',
                unit: 'mm',
                format: [pageW, pageH],
            });

            pdf.addImage(imgData, 'PNG', 0, 0, pageW, pageH);
            pdf.save('DFD-Alur-Pembelian-Return.pdf');
            resolve();
        };
        img.onerror = (e) => {
            console.error('SVG render error:', e);
            alert('Gagal mengekspor diagram. Silakan coba lagi.');
            reject(e);
        };
        img.src = svgBase64;
    });
};
</script>

<template>
    <Head title="Helpdesk - Alur Pembelian & Return" />

    <DashboardLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 bg-indigo-500/10 rounded-2xl">
                    <IconShoppingBag :size="32" class="text-indigo-500" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">SOP Pembelian & Return Pembelian</h1>
                    <p class="text-slate-500 text-sm">Penjelasan lengkap alur pembuatan Purchase Order, finalisasi stok, penjurnalan otomatis, dan retur ke supplier.</p>
                </div>
            </div>

            <!-- Alur Pembelian -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5">
                    <IconShoppingBag :size="200" />
                </div>

                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                    <IconShoppingBag class="text-indigo-500" />
                    Alur Pembelian (Purchase Order)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template v-for="(step, idx) in purchaseFlow" :key="idx">
                        <div class="relative group">
                            <div v-if="idx < purchaseFlow.length - 1" class="hidden md:block absolute top-8 -right-4 z-10 text-slate-300 dark:text-slate-700">
                                <IconArrowRight :size="20" />
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl h-full border border-slate-100 dark:border-slate-700/50 hover:border-indigo-500/30 transition-all duration-300">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                                    :class="`bg-${step.color}-500/10 text-${step.color}-500`">
                                    <component :is="step.icon" :size="24" />
                                </div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-2">{{ step.title }}</h3>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 font-medium">
                                    {{ step.desc }}
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-2xl relative z-10">
                    <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed font-medium">
                        <strong>Info Pre-Order:</strong> Setelah PO di-finalize, sistem otomatis memeriksa semua pesanan penjualan yang statusnya <strong>"Waiting Stock"</strong>. Jika stok yang baru masuk sudah mencukupi kebutuhan pre-order tersebut, statusnya langsung berubah ke <strong>"Siap Kirim"</strong> — tanpa perlu tindakan manual apapun dari tim gudang.
                    </p>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Journal Info -->
                <section class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl p-8 text-white shadow-lg overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <IconBook :size="150" />
                    </div>

                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <IconBook />
                        Pencatatan Jurnal Otomatis (Saat Finalize)
                    </h2>

                    <div class="space-y-4 relative z-10">
                        <template v-for="(info, idx) in journalNotes" :key="idx">
                            <div class="bg-white/10 backdrop-blur-sm p-4 rounded-2xl border border-white/20 hover:bg-white/20 transition-all">
                                <h4 class="font-bold text-sm mb-1">{{ info.title }}</h4>
                                <p class="text-xs text-white/80 leading-relaxed">{{ info.desc }}</p>
                            </div>
                        </template>
                    </div>
                </section>

                <!-- Return Flow -->
                <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <IconArrowBackUp :size="150" />
                    </div>

                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <IconArrowBackUp class="text-rose-500" />
                        Alur Return Pembelian (ke Supplier)
                    </h2>

                    <div class="grid grid-cols-1 gap-6 relative z-10">
                        <template v-for="(step, idx) in returnFlow" :key="idx">
                            <div class="relative group">
                                <div v-if="idx > 0" class="absolute -top-5 left-6 w-0.5 h-6 bg-slate-200 dark:bg-slate-700"></div>
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/50 hover:border-rose-500/30 transition-all duration-300">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                                        :class="`bg-${step.color}-500/10 text-${step.color}-500`">
                                        <component :is="step.icon" :size="24" />
                                    </div>
                                    <h3 class="font-bold text-slate-800 dark:text-slate-200 mb-2">{{ step.title }}</h3>
                                    <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400 font-medium">
                                        {{ step.desc }}
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl">
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed font-medium">
                            <strong>✅ Terintegrasi Penuh:</strong> Return Pembelian kini memiliki automasi lengkap — stok berkurang, jurnal pembalikan terbuat, dan jika PO asli bersifat Tempo serta Admin memilih PO-nya, sistem <strong>otomatis memotong saldo Hutang ke Supplier</strong> tanpa input manual apapun.
                        </p>
                    </div>
                </section>
            </div>

            <!-- DFD Diagram -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Visualisasi Alur Pembelian & Return (DFD)
                    </h2>
                    <button
                        @click="downloadPdf"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/30 transition-all duration-200 active:scale-95"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4" />
                        </svg>
                        Download PDF
                    </button>
                </div>
                <div ref="dfdRef" class="w-full py-4 bg-white" style="min-height: 400px;">
                    <pre class="mermaid" style="width: 100%; font-size: 15px;">
flowchart TD
    AD("Admin / Purchasing") -->|"Buat PO + Pilih Tipe"| DR("Draft Purchase Order")
    SP("Supplier") -.-|"Sumber Barang"| AD

    DR -->|"Klik Finalize"| FN{"Proses Finalisasi PO"}

    FN -->|"Stok Masuk"| STK[("Gudang / ProductWarehouse")]
    STK -.-|"Stok Bertambah"| INV[("Persediaan / Inventory")]
    FN -.-|"Update"| PRD[("Produk buy_price")]

    FN -.-|"Trigger"| J1[["Jurnal Pembelian"]]
    J1 -.-|"Debit"| INV
    J1 -->|"Cek Tipe"| PT{"Cash atau Tempo?"}
    PT -->|"Cash"| CAS[("Kredit Kas")]
    PT -->|"Tempo"| AP[("Kredit Hutang Usaha")]

    FN -.-|"Auto Check"| PO{"Pre-Order Waiting Stock?"}
    PO -->|"Stok Cukup"| RDY["Status: Siap Kirim"]
    PO -->|"Stok Kurang"| WAIT["Tetap Menunggu"]

    SP -.-|"Barang Diretur"| AD2("Admin")
    AD2 -->|"Input Return + Pilih PO"| RTD("Draft Purchase Return")

    RTD -->|"Klik Finalize"| FNR{"Finalisasi Return"}

    FNR -->|"① Fisik"| STK
    STK -.-|"Stok Berkurang"| INV

    FNR -.-|"② Trigger"| J2[["Jurnal Pembalikan Return"]]
    J2 -.-|"Credit"| INV
    J2 -.-|"Debit"| AP
    J2 -.-|"Debit"| CAS

    FNR -->|"③ Cek Tipe PO"| CTR{"PO Asli Tempo?"}
    CTR -->|"Ya"| PAY["Auto PayablePayment"]
    CTR -->|"Tidak"| SKIP["Tidak Ada Potongan Hutang"]
    PAY -.-|"Memotong Saldo"| AP

    classDef actor fill:#818cf8,color:white,stroke:#6366f1,stroke-width:2px
    classDef process fill:#60a5fa,color:white,stroke:#3b82f6,stroke-width:2px
    classDef db fill:#fcd34d,stroke:#f59e0b,stroke-width:2px

    class AD,AD2,SP actor
    class DR,FN,FNR,RTD,PT,PO,CTR process
    class STK,INV,PRD,CAS,AP db
                    </pre>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>
