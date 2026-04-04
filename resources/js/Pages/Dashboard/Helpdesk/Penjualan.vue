<script setup>
import { onMounted, ref } from 'vue';
import mermaid from 'mermaid';
import { Head } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { 
    IconShoppingCart, 
    IconArrowRight, 
    IconFileCertificate, 
    IconClipboardCheck, 
    IconBook,
    IconArrowBackUp
} from '@tabler/icons-vue';

const salesFlow = [
    { 
        title: '1. Pembuatan Order', 
        desc: 'Sales/Kasir menginput barang dan melakukan Checkout (Finalize). Status order menjadi "Pending Finance".',
        icon: IconShoppingCart,
        color: 'sky'
    },
    { 
        title: '2. Approval Finance', 
        desc: 'Finance memverifikasi pembayaran. Jika di-approve, sistem OTOMATIS membuat Jurnal Akuntansi (Double-Entry). Status lanjut ke Gudang',
        icon: IconFileCertificate,
        color: 'indigo'
    },
    { 
        title: '3. Approval Gudang', 
        desc: 'Gudang menyiapkan dan mengirim/menyerahkan barang. Bila di-approve, transaksi SELESAI (Completed).',
        icon: IconClipboardCheck,
        color: 'emerald'
    }
];

const returnFlow = [
    { 
        title: '1. Input Return', 
        desc: 'Admin membuat "Return Penjualan Baru". Jika retur ini akan memotong piutang/tagihan belum lunas, pastikan Anda MERUJUK/MEMILIH Invoice Penjualan terkait di form.',
        icon: IconArrowBackUp,
        color: 'orange'
    },
    { 
        title: '2. Finalisasi Return', 
        desc: 'Saat di-finalize, secara OTOMATIS sistem akan: 1) Mengembalikan fisik stok ke Gudang, 2) Membuat Jurnal Pembalikan (Reverse), dan 3) Memotong saldo Piutang pelanggan di Pembukuan.',
        icon: IconClipboardCheck,
        color: 'emerald'
    }
];

const journalNotes = [
    {
        title: 'Kas / Piutang (Debit)',
        desc: 'Akun Kas akan bertambah bila pembayaran lunas. Jika ngutang/kredit, maka akun Piutang Usaha yang bertambah. Diskon penjualan dicatat juga di kolom debit.'
    },
    {
        title: 'Pendapatan Penjualan (Kredit)',
        desc: 'Akun sales/pendapatan (Income) dicatat otomatis senilai harga kotor pesanan tersebut sebelum dikurangi diskon.'
    },
    {
        title: 'HPP / COGS (Debit) & Inventory (Kredit)',
        desc: 'Sistem OTOMATIS menyusutkan nilai persediaan aset (Inventory) dan mencatatnya sebagai beban HPP (Harga Pokok Penjualan) berdasarkan nilai beli/modal persis di detik saat Finance Approve.'
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

    // Resolve dimensions from multiple sources
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

    // Clone and set explicit dimensions on SVG
    const svgClone = svgEl.cloneNode(true);
    svgClone.setAttribute('width', svgW);
    svgClone.setAttribute('height', svgH);
    svgClone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    svgClone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

    // Serialize SVG → base64 data URI (no Blob/CORS issues)
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
            pdf.save('DFD-Alur-Penjualan-Retur.pdf');
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
    <Head title="Helpdesk - Alur Penjualan & Return" />

    <DashboardLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 bg-sky-500/10 rounded-2xl">
                    <IconShoppingCart :size="32" class="text-sky-500" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Standar Operasional (SOP) Penjualan & Jurnal</h1>
                    <p class="text-slate-500 text-sm">Penjelasan komprehensif terkait alur order, persetujuan (approval), retur, hingga penjurnalan otomatis.</p>
                </div>
            </div>

            <!-- Flow Penjualan -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-12 opacity-5">
                    <IconShoppingCart :size="200" />
                </div>
                
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-8 flex items-center gap-2">
                    <IconShoppingCart class="text-sky-500" />
                    Alur Order Biasa (Transaksi Baru)
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template v-for="(step, idx) in salesFlow" :key="idx">
                        <div class="relative group">
                            <!-- Arrow -->
                            <div v-if="idx < salesFlow.length - 1" class="hidden md:block absolute top-8 -right-4 z-10 text-slate-300 dark:text-slate-700">
                                <IconArrowRight :size="20" />
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl h-full border border-slate-100 dark:border-slate-700/50 hover:border-sky-500/30 transition-all duration-300">
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

                <div class="mt-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl relative z-10">
                    <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed font-medium">
                        <strong>Info Reject/Penolakan:</strong> Jika Finance menolak (Reject) pesanan, Transaksi menjadi batal layaknya Draft dan tak ada jurnal tercatat. Namun Jika Gudang yang me-reject (padahal uang sudah disetujui), maka Jurnal Akuntansi yang sempat terbentuk akan <strong>di-Reverse (Dibatalkan)</strong> otomatis oleh sistem untuk menjaga keakuratan Buku Besar, serta stok dikembalikan.
                    </p>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Journal Info -->
                <section class="bg-gradient-to-br from-indigo-500 to-sky-600 rounded-3xl p-8 text-white shadow-lg overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <IconBook :size="150" />
                    </div>
                    
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <IconBook />
                        Pencatatan Jurnal Otomatis
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

                <!-- Flow Return Info -->
                <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <IconArrowBackUp :size="150" />
                    </div>
                    
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <IconArrowBackUp class="text-orange-500" />
                        Alur Return Penjualan (Retur)
                    </h2>

                    <div class="grid grid-cols-1 gap-6 relative z-10">
                        <template v-for="(step, idx) in returnFlow" :key="idx">
                            <div class="relative group">
                                <!-- Line Arrow between items in vertical list -->
                                <div v-if="idx > 0" class="absolute -top-5 left-6 w-0.5 h-6 bg-slate-200 dark:bg-slate-700"></div>

                                <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/50 hover:border-orange-500/30 transition-all duration-300">
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
                </section>
            </div>

            <!-- Flow Diagram (Mermaid) -->
            <section class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden relative">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Visualisasi Alur Penjualan & Retur (DFD)
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
    S("Sales / Kasir") -->|"Input Item"| O("Draft Order / Pending Finance")
    C("Customer") -.->|"Membeli"| S

    O -->|"Request Approval"| F{"Finance"}
    F -->|"Reject"| RJ["Transaksi Batal"]
    F -->|"Approve"| PW("Pending Warehouse")

    PW -.->|"Trigger Sistem"| J1[["Pencatatan Jurnal Asli"]]
    J1 -.->|"Jurnal"| AR[("Kas / Piutang")]
    J1 -.->|"Jurnal"| INC[("Pendapatan / Sales")]
    J1 -.->|"Jurnal"| INV[("Persediaan / Inventory")]
    J1 -.->|"Jurnal"| COGS[("HPP")]

    PW -->|"Fisik Disiapkan"| W{"Gudang"}
    W -->|"Reject"| RV[["Sistem me-Reverse Jurnal"]]
    W -->|"Approve / Kirim"| CMP["Transaksi COMPLETED"]
    CMP -.->|"Selesai"| C
    RV -.->|"Update"| AR
    RV -.->|"Update"| INV

    C -.->|"Barang Diretur"| AD("Admin")
    AD -->|"Input Return + Invoice"| RET("Draft Sale Return")

    RET -->|"Klik Finalize"| FR{"Finalisasi Retur"}

    FR -->|"Fisik"| STK[("Gudang / Stock")]
    STK -.->|"Stock Bertambah"| INV

    FR -.->|"Trigger Sistem"| J2[["Jurnal Retur"]]
    J2 -.->|"Reverse"| INC
    J2 -.->|"Reverse"| COGS

    J2 -->|"Cek Tipe"| PTG{"Transaksi Tempo?"}
    PTG -->|"Ya"| POT["Potong Piutang (ReceivablePayment)"]
    PTG -->|"Tidak"| CS["Potong Saldo Kas"]

    POT -.->|"Mengurangi"| AR
    CS -.->|"Mengurangi"| AR

    classDef actor fill:#f87171,color:white,stroke:#ef4444,stroke-width:2px
    classDef process fill:#60a5fa,color:white,stroke:#3b82f6,stroke-width:2px
    classDef db fill:#fcd34d,stroke:#f59e0b,stroke-width:2px

    class S,F,W,AD actor
    class O,PW,CMP,RET,FR process
    class AR,INC,INV,COGS,STK db
                    </pre>
                </div>
            </section>
        </div>
    </DashboardLayout>
</template>
