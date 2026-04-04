<template>
    <DashboardLayout>
        <Head title="Stock Awal" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Stock Awal</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Input stock awal produk berdasarkan data pembelian.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="handleSync"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-all shadow-lg shadow-amber-500/20"
                        title="Singkronkan data ke Stock Running"
                    >
                        <IconRefresh :size="18" />
                        Sync Ke Stock Running
                    </button>
                    <button
                        @click="showImportModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 text-white font-semibold hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20"
                    >
                        <IconUpload :size="18" />
                        Import Excel
                    </button>
                    <button
                        @click="showModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-all shadow-lg shadow-primary-500/20"
                    >
                        <IconPlus :size="18" />
                        Tambah Stock Awal
                    </button>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('stock-awal.index')" placeholder="Cari kode barang..." />
                </div>
            </div>
        </div>

        <!-- Content -->
        <template v-if="stockAwal.data.length > 0">
            <TableCard title="Daftar Stock Awal">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Kode Barang</TableTh>
                            <TableTh>Nama Barang</TableTh>
                            <TableTh class="text-right">Qty</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh class="text-center">Status</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(item, i) in stockAwal.data"
                            :key="item.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (stockAwal.current_page - 1) * stockAwal.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">
                                    {{ item.code_barang }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ item.barang?.judul_buku || '-' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-right font-bold text-slate-900 dark:text-white">
                                {{ item.qty }}
                            </TableTd>
                            <TableTd class="text-slate-600 dark:text-slate-400">
                                {{ formatDate(item.tanggal) }}
                            </TableTd>
                            <TableTd class="text-center">
                                <span 
                                    v-if="item.is_synced" 
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400"
                                    :title="'Synced at: ' + formatDateTime(item.synced_at)"
                                >
                                    Synced
                                </span>
                                <span 
                                    v-else 
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400"
                                >
                                    Pending
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <div class="flex justify-center gap-1">
                                    <button
                                        @click="editStock(item)"
                                        class="p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50"
                                        title="Edit"
                                    >
                                        <IconPencil :size="16" />
                                    </button>
                                    <button
                                        @click="deleteStock(item.id)"
                                        class="p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50"
                                        title="Hapus"
                                    >
                                        <IconTrash :size="16" />
                                    </button>
                                </div>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
            <Pagination v-if="stockAwal.links.length > 3" :links="stockAwal.links" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Belum ada stock awal yang diinput." />
        </template>

        <!-- Modal -->
        <Modal :show="showModal" @close="closeModal" :title="editMode ? 'Edit Stock Awal' : 'Tambah Stock Awal'">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Barang
                    </label>
                    <InputSelect
                        :data="barangs"
                        :selected="props.barangs.find(b => b.id === form.code_barang)"
                        :setSelected="(val) => form.code_barang = val.id"
                        display-key="judul_buku"
                        placeholder="Pilih Barang"
                        :searchable="true"
                        :disabled="editMode"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Quantity
                    </label>
                    <input
                        v-model="form.qty"
                        type="number"
                        min="0"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Tanggal
                    </label>
                    <input
                        v-model="form.tanggal"
                        type="date"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                        required
                    />
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t dark:border-slate-800">
                    <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-lg bg-primary-500 text-white font-semibold hover:bg-primary-600 shadow-lg shadow-primary-500/20 transition-all"
                    >
                        {{ editMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </Modal>
        
        <!-- Import Modal -->
        <Modal :show="showImportModal" @close="closeImportModal" title="Import Stock Awal">
            <form @submit.prevent="submitImport" class="space-y-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-2">Petunjuk Import:</h3>
                    <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-1 list-disc ml-4">
                        <li>Gunakan file Excel (.xlsx atau .xls)</li>
                        <li>Pastikan terdapat kolom <strong>KodeBuku</strong> dan <strong>StockScan</strong></li>
                        <li>Jika kode barang sama muncul beberapa kali, jumlahnya akan <strong>otomatis dijumlahkan</strong></li>
                        <li>Semua data akan di-set ke tanggal <strong>01 Desember 2021</strong></li>
                    </ul>
                    <a 
                        :href="route('stock-awal.template')" 
                        class="inline-flex items-center gap-1 mt-4 text-sm font-bold text-primary-500 hover:text-primary-600"
                    >
                        <IconDownload :size="16" />
                        Download Template Excel
                    </a>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        File Excel
                    </label>
                    <input
                        @input="importForm.file = $event.target.files[0]"
                        type="file"
                        accept=".xlsx, .xls"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                        required
                    />
                    <div v-if="importForm.errors.file" class="text-red-500 text-xs mt-1">{{ importForm.errors.file }}</div>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t dark:border-slate-800">
                    <button type="button" @click="closeImportModal" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-all">
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="importForm.processing"
                        class="px-4 py-2 rounded-lg bg-emerald-500 text-white font-semibold hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ importForm.processing ? 'Memproses...' : 'Import Sekarang' }}
                    </button>
                </div>
            </form>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router, useForm, Head } from '@inertiajs/vue3'
import TableCard from '@/Components/Dashboard/TableCard.vue'
import Table from '@/Components/Dashboard/Table.vue'
import TableThead from '@/Components/Dashboard/TableThead.vue'
import TableTh from '@/Components/Dashboard/TableTh.vue'
import TableTbody from '@/Components/Dashboard/TableTbody.vue'
import TableTd from '@/Components/Dashboard/TableTd.vue'
import TableEmpty from '@/Components/Dashboard/TableEmpty.vue'
import Pagination from '@/Components/Dashboard/Pagination.vue'
import Search from '@/Components/Dashboard/Search.vue'
import Modal from '@/Components/Dashboard/Modal.vue'
import InputSelect from '@/Components/Dashboard/InputSelect.vue'
import { IconPlus, IconPencil, IconTrash, IconUpload, IconDownload, IconRefresh } from '@tabler/icons-vue' // Added IconRefresh

const props = defineProps({
    stockAwal: Object,
    barangs: Array
})

const showModal = ref(false)
const showImportModal = ref(false)
const editMode = ref(false)
const editId = ref(null)

const form = reactive({
    code_barang: '',
    qty: 0,
    tanggal: new Date().toISOString().split('T')[0]
})

const importForm = useForm({
    file: null
})

const resetForm = () => {
    form.code_barang = ''
    form.qty = 0
    form.tanggal = new Date().toISOString().split('T')[0]
    editMode.value = false
    editId.value = null
}

const closeModal = () => {
    showModal.value = false
    resetForm()
}

const closeImportModal = () => {
    showImportModal.value = false
    importForm.reset()
}

const editStock = (item) => {
    editMode.value = true
    editId.value = item.id
    form.code_barang = item.code_barang
    form.qty = item.qty
    form.tanggal = item.tanggal
    showModal.value = true
}

const submitForm = () => {
    if (editMode.value) {
        router.put(route('stock-awal.update', editId.value), form, {
            onSuccess: () => closeModal()
        })
    } else {
        router.post(route('stock-awal.store'), form, {
            onSuccess: () => closeModal()
        })
    }
}

const submitImport = () => {
    importForm.post(route('stock-awal.import'), {
        onSuccess: () => closeImportModal(),
    })
}

const handleSync = () => {
    if (confirm('Apakah Anda yakin ingin menyinkronkan semua data yang pending ke Stock Running?')) {
        router.post(route('stock-awal.sync'))
    }
}

const deleteStock = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus?')) {
        router.delete(route('stock-awal.destroy', id))
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
