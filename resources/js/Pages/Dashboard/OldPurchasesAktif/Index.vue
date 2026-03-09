<template>
    <DashboardLayout>
        <Head title="Old Purchase Aktif" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Old Purchase Aktif</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data purchase yang sudah dipindahkan dan siap di-final.
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="showMoveModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-all"
                    >
                        <IconArrowRight :size="18" />
                        Pindahkan
                    </button>
                    <button
                        @click="showMapModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-500 text-white font-semibold hover:bg-blue-600 transition-all"
                    >
                        <IconLink :size="18" />
                        Mapping Barang
                    </button>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('old-purchases-aktif.index')" placeholder="Cari Supplier / No. Faktur..." />
                </div>
            </div>
        </div>

        <!-- Content -->
        <template v-if="oldPurchaseAktif.data.length > 0">
            <TableCard title="Daftar Old Purchase Aktif">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>No. Faktur</TableTh>
                            <TableTh>Supplier</TableTh>
                            <TableTh class="text-right">Total</TableTh>
                            <TableTh>Tanggal</TableTh>
                            <TableTh class="text-center">Status</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(purchase, i) in oldPurchaseAktif.data"
                            :key="purchase.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (oldPurchaseAktif.current_page - 1) * oldPurchaseAktif.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="font-bold text-slate-900 dark:text-white">
                                    {{ purchase.nomor_faktur || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <div class="flex items-center gap-2">
                                    <IconTruck :size="16" class="text-slate-400" />
                                    <span class="text-slate-800 dark:text-slate-200">
                                        {{ purchase.supplier }}
                                    </span>
                                </div>
                            </TableTd>
                            <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                {{ formatCurrency(purchase.harga_total) }}
                            </TableTd>
                            <TableTd class="text-slate-600 dark:text-slate-400">
                                {{ purchase.tanggal_faktur ? formatDate(purchase.tanggal_faktur) : '-' }}
                            </TableTd>
                            <TableTd class="text-center">
                                <span
                                    :class="purchase.is_final
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                    class="px-2 py-1 rounded-lg text-xs font-semibold"
                                >
                                    {{ purchase.is_final ? 'Final' : 'Pending' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <div class="flex justify-center gap-1">
                                    <button
                                        v-if="!purchase.is_final"
                                        @click="finalPurchase([purchase.id])"
                                        class="p-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50"
                                        title="Final"
                                    >
                                        <IconCheck :size="16" />
                                    </button>
                                    <Link
                                        :href="`/dashboard/old-purchases-aktif/${purchase.id}`"
                                        class="p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50"
                                        title="Detail"
                                    >
                                        <IconEye :size="16" />
                                    </Link>
                                    <button
                                        @click="deletePurchase(purchase.id)"
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
            <Pagination :data="oldPurchaseAktif" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Data purchase aktif akan muncul di sini." />
        </template>

        <!-- Move Modal -->
        <Modal v-model="showMoveModal" title="Pindahkan Purchase ke Aktif">
            <div class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Pilih purchase yang akan dipindahkan:
                </p>
                <div v-if="unfinalPurchases.length > 0" class="max-h-64 overflow-y-auto space-y-2">
                    <label
                        v-for="purchase in unfinalPurchases"
                        :key="purchase.id"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="purchase.id"
                            v-model="selectedPurchases"
                            class="w-4 h-4 text-primary-500 rounded"
                        />
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">
                                {{ purchase.nomor_faktur }}
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ purchase.supplier }} - {{ formatCurrency(purchase.harga_total) }}
                            </div>
                        </div>
                    </label>
                </div>
                <div v-else class="text-center py-4 text-slate-500">
                    Tidak ada purchase yang bisa dipindahkan
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button @click="showMoveModal = false" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700">
                        Batal
                    </button>
                    <button
                        @click="movePurchases"
                        :disabled="selectedPurchases.length === 0"
                        class="px-4 py-2 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-50"
                    >
                        Pindahkan
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Map Modal -->
        <Modal v-model="showMapModal" title="Mapping Barang">
            <div class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Mapping barang untuk item yang belum memiliki kode:
                </p>
                <div v-if="barangs.length > 0" class="max-h-64 overflow-y-auto space-y-2">
                    <div
                        v-for="detail in unmappedDetails"
                        :key="detail.id"
                        class="p-3 rounded-lg border border-slate-200 dark:border-slate-700"
                    >
                        <div class="font-semibold text-slate-900 dark:text-white mb-2">
                            {{ detail.nama }}
                        </div>
                        <select
                            v-model="detail.code_barang"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <option value="">Pilih Barang</option>
                            <option v-for="barang in barangs" :key="barang.kode_buku" :value="barang.kode_buku">
                                {{ barang.judul_buku }}
                            </option>
                        </select>
                    </div>
                </div>
                <div v-else class="text-center py-4 text-slate-500">
                    Semua item sudah ter-mapping
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button @click="showMapModal = false" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700">
                        Batal
                    </button>
                    <button
                        @click="saveMapping"
                        class="px-4 py-2 rounded-lg bg-blue-500 text-white font-semibold hover:bg-blue-600"
                    >
                        Simpan
                    </button>
                </div>
            </template>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Link, router } from '@inertiajs/vue3'
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
import { IconPlus, IconTruck, IconEye, IconTrash, IconCheck, IconArrowRight, IconLink } from '@tabler/icons-vue'

const props = defineProps({
    oldPurchaseAktif: Object,
    barangs: Array
})

const showMoveModal = ref(false)
const showMapModal = ref(false)
const selectedPurchases = ref([])
const unfinalPurchases = ref([])
const unmappedDetails = ref([])
const barangs = ref(props.barangs || [])

// Load unfinal purchases
const loadUnfinalPurchases = async () => {
    try {
        const response = await fetch(route('old-purchases-aktif.get-unfinal'))
        unfinalPurchases.value = await response.json()
    } catch (e) {
        console.error(e)
    }
}

// Load unmapped details
const loadUnmappedDetails = async () => {
    try {
        const response = await fetch(route('old-purchases-aktif.get-unmapped'))
        const data = await response.json()
        // Flatten the grouped data
        unmappedDetails.value = Object.values(data).flat()
    } catch (e) {
        console.error(e)
    }
}

const movePurchases = () => {
    router.post(route('old-purchases-aktif.store'), {
        old_purchase_ids: selectedPurchases.value
    }, {
        onSuccess: () => {
            showMoveModal.value = false
            selectedPurchases.value = []
            loadUnfinalPurchases()
        }
    })
}

const saveMapping = () => {
    const details = unmappedDetails.value.map(d => ({
        id: d.id,
        code_barang: d.code_barang
    })).filter(d => d.code_barang)

    router.post(route('old-purchases-aktif.map-barang'), {
        details
    }, {
        onSuccess: () => {
            showMapModal.value = false
            loadUnmappedDetails()
        }
    })
}

const finalPurchase = (ids) => {
    router.post(route('old-purchases-aktif.final'), {
        ids
    }, {
        onSuccess: () => {
            router.reload()
        }
    })
}

const deletePurchase = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus?')) {
        router.delete(route('old-purchases-aktif.destroy', id))
    }
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

// Initialize
loadUnfinalPurchases()
loadUnmappedDetails()
</script>
