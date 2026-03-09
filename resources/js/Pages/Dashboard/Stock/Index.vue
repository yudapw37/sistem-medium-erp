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
                <button
                    @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-500 text-white font-semibold hover:bg-primary-600 transition-all"
                >
                    <IconPlus :size="18" />
                    Tambah Stock Awal
                </button>
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
            <Pagination :data="stockAwal" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Belum ada stock awal yang diinput." />
        </template>

        <!-- Modal -->
        <Modal v-model="showModal" :title="editMode ? 'Edit Stock Awal' : 'Tambah Stock Awal'">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Barang
                    </label>
                    <select
                        v-model="form.code_barang"
                        :disabled="editMode"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                        required
                    >
                        <option value="">Pilih Barang</option>
                        <option v-for="barang in barangs" :key="barang.kode_buku" :value="barang.kode_buku">
                            {{ barang.judul_buku }} ({{ barang.kode_buku }})
                        </option>
                    </select>
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
            </form>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button @click="closeModal" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700">
                        Batal
                    </button>
                    <button
                        @click="submitForm"
                        class="px-4 py-2 rounded-lg bg-primary-500 text-white font-semibold hover:bg-primary-600"
                    >
                        {{ editMode ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </template>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { router } from '@inertiajs/vue3'
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
import { IconPlus, IconPencil, IconTrash } from '@tabler/icons-vue'

const props = defineProps({
    stockAwal: Object,
    barangs: Array
})

const showModal = ref(false)
const editMode = ref(false)
const editId = ref(null)

const form = reactive({
    code_barang: '',
    qty: 0,
    tanggal: new Date().toISOString().split('T')[0]
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

const deleteStock = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus?')) {
        router.delete(route('stock-awal.destroy', id))
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
