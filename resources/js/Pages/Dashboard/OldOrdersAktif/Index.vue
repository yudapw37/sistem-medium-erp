<template>
    <DashboardLayout>
        <Head title="Old Order Aktif" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Old Order Aktif</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Data order yang sudah dipindahkan dan siap di-final.
                    </p>
                </div>
                <button
                    @click="showMoveModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition-all"
                >
                    <IconArrowRight :size="18" />
                    Pindahkan
                </button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('old-orders-aktif.index')" placeholder="Cari Penerima / Customer..." />
                </div>
            </div>
        </div>

        <!-- Content -->
        <template v-if="oldOrderAktif.data.length > 0">
            <TableCard title="Daftar Old Order Aktif">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Penerima</TableTh>
                            <TableTh>Alamat</TableTh>
                            <TableTh class="text-right">Total Barang</TableTh>
                            <TableTh class="text-right">Total Harga</TableTh>
                            <TableTh class="text-center">Status</TableTh>
                            <TableTh class="text-center">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(order, i) in oldOrderAktif.data"
                            :key="order.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (oldOrderAktif.current_page - 1) * oldOrderAktif.per_page }}
                            </TableTd>
                            <TableTd>
                                <div>
                                    <div class="font-semibold text-slate-900 dark:text-white">
                                        {{ order.nama_penerima || '-' }}
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        {{ order.telephone_penerima }}
                                    </div>
                                </div>
                            </TableTd>
                            <TableTd>
                                <div class="max-w-xs truncate text-slate-600 dark:text-slate-400">
                                    {{ order.alamat }}, {{ order.kecamatan }}, {{ order.kab_kota }}
                                </div>
                            </TableTd>
                            <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                {{ order.total_barang }}
                            </TableTd>
                            <TableTd class="text-right text-slate-900 dark:text-white font-bold">
                                {{ formatCurrency(order.total_harga) }}
                            </TableTd>
                            <TableTd class="text-center">
                                <span
                                    :class="order.is_final
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                    class="px-2 py-1 rounded-lg text-xs font-semibold"
                                >
                                    {{ order.is_final ? 'Final' : 'Pending' }}
                                </span>
                            </TableTd>
                            <TableTd class="text-center">
                                <div class="flex justify-center gap-1">
                                    <button
                                        v-if="!order.is_final"
                                        @click="finalOrder([order.id])"
                                        class="p-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50"
                                        title="Final"
                                    >
                                        <IconCheck :size="16" />
                                    </button>
                                    <Link
                                        :href="`/dashboard/old-orders-aktif/${order.id}`"
                                        class="p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-900/50"
                                        title="Detail"
                                    >
                                        <IconEye :size="16" />
                                    </Link>
                                    <button
                                        @click="deleteOrder(order.id)"
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
            <Pagination :data="oldOrderAktif" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Data order aktif akan muncul di sini." />
        </template>

        <!-- Move Modal -->
        <Modal v-model="showMoveModal" title="Pindahkan Order ke Aktif">
            <div class="space-y-4">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Pilih order (resume_status = true) yang akan dipindahkan:
                </p>
                <div v-if="unfinalOrders.length > 0" class="max-h-64 overflow-y-auto space-y-2">
                    <label
                        v-for="order in unfinalOrders"
                        :key="order.id"
                        class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="order.id"
                            v-model="selectedOrders"
                            class="w-4 h-4 text-primary-500 rounded"
                        />
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">
                                {{ order.nama_penerima }}
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ order.kab_kota }} - {{ order.total_barang }} barang - {{ formatCurrency(order.total_harga) }}
                            </div>
                        </div>
                    </label>
                </div>
                <div v-else class="text-center py-4 text-slate-500">
                    Tidak ada order dengan resume_status=true yang bisa dipindahkan
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <button @click="showMoveModal = false" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-700">
                        Batal
                    </button>
                    <button
                        @click="moveOrders"
                        :disabled="selectedOrders.length === 0"
                        class="px-4 py-2 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 disabled:opacity-50"
                    >
                        Pindahkan
                    </button>
                </div>
            </template>
        </Modal>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
import { IconArrowRight, IconEye, IconTrash, IconCheck } from '@tabler/icons-vue'

const props = defineProps({
    oldOrderAktif: Object
})

const showMoveModal = ref(false)
const selectedOrders = ref([])
const unfinalOrders = ref([])

const loadUnfinalOrders = async () => {
    try {
        const response = await fetch(route('old-orders-aktif.get-unfinal'))
        unfinalOrders.value = await response.json()
    } catch (e) {
        console.error(e)
    }
}

const moveOrders = () => {
    router.post(route('old-orders-aktif.store'), {
        order_ids: selectedOrders.value
    }, {
        onSuccess: () => {
            showMoveModal.value = false
            selectedOrders.value = []
            loadUnfinalOrders()
        }
    })
}

const finalOrder = (ids) => {
    router.post(route('old-orders-aktif.final'), {
        ids
    }, {
        onSuccess: () => {
            router.reload()
        }
    })
}

const deleteOrder = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus?')) {
        router.delete(route('old-orders-aktif.destroy', id))
    }
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0)
}

onMounted(() => {
    loadUnfinalOrders()
})
</script>
