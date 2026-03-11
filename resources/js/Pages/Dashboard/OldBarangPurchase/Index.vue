<template>
    <DashboardLayout>
        <Head title="Old Barang Purchase" />

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Old Barang Purchase</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Data master barang purchase dari tabel old_ms_barang_purchase.
            </p>
        </div>

        <!-- Toolbar -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center flex-wrap">
                <div class="w-full sm:w-64">
                    <Search :url="route('old-barang-purchase.index')" placeholder="Cari barang..." />
                </div>
                <button
                    @click="syncToPurchaseDetails"
                    :disabled="isSyncing"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 text-white font-medium text-sm hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="isSyncing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <IconRefresh v-else :size="18" />
                    <span>{{ isSyncing ? 'Menyinkronkan...' : 'Sinkronkan ke Purchase Details' }}</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <template v-if="barangPurchases.data.length > 0">
            <TableCard title="Daftar Old Barang Purchase">
                <Table>
                    <TableThead>
                        <tr>
                            <TableTh class="w-10">No</TableTh>
                            <TableTh>Nama Barang</TableTh>
                            <TableTh>Code Barang</TableTh>
                            <TableTh>Nama Barang Master</TableTh>
                            <TableTh class="w-24">Aksi</TableTh>
                        </tr>
                    </TableThead>
                    <TableTbody>
                        <tr
                            v-for="(item, i) in barangPurchases.data"
                            :key="item.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <TableTd class="text-center">
                                {{ ++i + (barangPurchases.current_page - 1) * barangPurchases.per_page }}
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-800 dark:text-slate-200">
                                    {{ item.nama_barang || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="font-mono font-bold text-slate-900 dark:text-white">
                                    {{ item.code_barang || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <span class="text-slate-600 dark:text-slate-400">
                                    {{ item.nama_barang_master || '-' }}
                                </span>
                            </TableTd>
                            <TableTd>
                                <button
                                    @click="openMappingModal(item)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-colors"
                                >
                                    <IconLink :size="14" />
                                    Mapping
                                </button>
                            </TableTd>
                        </tr>
                    </TableTbody>
                </Table>
            </TableCard>
            <Pagination :links="barangPurchases.links" />
        </template>
        <template v-else>
            <TableEmpty title="Belum ada data" description="Belum ada data barang purchase." />
        </template>

        <!-- Mapping Modal -->
        <div v-if="showMappingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeMappingModal"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Mapping Barang Master</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Pilih barang master dari daftar</p>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Nama Barang (Purchase)
                        </label>
                        <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800 rounded-lg text-slate-800 dark:text-slate-200">
                            {{ selectedItem?.nama_barang }}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Pilih Barang Master
                        </label>
                        <div class="relative">
                            <input
                                v-model="barangSearch"
                                @input="searchBarang"
                                type="text"
                                placeholder="Cari barang master..."
                                class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            />
                            <div v-if="isSearching" class="absolute right-3 top-3">
                                <div class="w-4 h-4 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>

                        <!-- Dropdown Options -->
                        <div v-if="barangOptions.length > 0" class="mt-2 border border-slate-200 dark:border-slate-700 rounded-lg max-h-48 overflow-y-auto">
                            <button
                                v-for="barang in barangOptions"
                                :key="barang.id"
                                @click="selectBarang(barang)"
                                class="w-full px-4 py-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800 border-b border-slate-100 dark:border-slate-800 last:border-b-0 transition-colors"
                                :class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedBarang?.id === barang.id }"
                            >
                                <div class="font-medium text-slate-800 dark:text-slate-200">{{ barang.judul_buku }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    ID: {{ barang.id }} | Kategori: {{ barang.kategori || '-' }} | Barcode: {{ barang.barcode || '-' }}
                                </div>
                            </button>
                        </div>
                        <div v-else-if="barangSearch && !isSearching" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada hasil pencarian
                        </div>

                        <!-- Selected -->
                        <div v-if="selectedBarang" class="mt-4 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                            <div class="text-xs font-medium text-primary-600 dark:text-primary-400 mb-1">Terpilih:</div>
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ selectedBarang.judul_buku }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ selectedBarang.id }}</div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                    <button
                        @click="closeMappingModal"
                        class="px-4 py-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        @click="saveMapping"
                        :disabled="!selectedBarang || isSaving"
                        class="px-6 py-2 rounded-lg bg-primary-500 text-white font-medium hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center gap-2"
                    >
                        <span v-if="isSaving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ isSaving ? 'Menyimpan...' : 'Simpan' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Search from '@/Components/Dashboard/Search.vue'
import TableCard from '@/Components/Dashboard/TableCard.vue'
import Table from '@/Components/Dashboard/Table.vue'
import TableThead from '@/Components/Dashboard/TableThead.vue'
import TableTh from '@/Components/Dashboard/TableTh.vue'
import TableTbody from '@/Components/Dashboard/TableTbody.vue'
import TableTd from '@/Components/Dashboard/TableTd.vue'
import TableEmpty from '@/Components/Dashboard/TableEmpty.vue'
import Pagination from '@/Components/Dashboard/Pagination.vue'
import { IconLink, IconRefresh } from '@tabler/icons-vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const props = defineProps({
    barangPurchases: Object
})

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
}

// Modal state
const showMappingModal = ref(false)
const selectedItem = ref(null)
const selectedBarang = ref(null)
const barangSearch = ref('')
const barangOptions = ref([])
const isSearching = ref(false)
const isSyncing = ref(false)
const isSaving = ref(false)

const openMappingModal = (item) => {
    selectedItem.value = item
    selectedBarang.value = null
    barangSearch.value = ''
    barangOptions.value = []
    showMappingModal.value = true
}

const closeMappingModal = () => {
    showMappingModal.value = false
    selectedItem.value = null
    selectedBarang.value = null
    barangSearch.value = ''
    barangOptions.value = []
}

const searchBarang = async () => {
    if (barangSearch.value.length < 2) {
        barangOptions.value = []
        return
    }

    isSearching.value = true
    try {
        const response = await axios.get(route('old-barang-purchase.barang-options'), {
            params: { q: barangSearch.value }
        })
        barangOptions.value = response.data
    } catch (error) {
        console.error('Error searching barang:', error)
        Swal.fire('Error', 'Gagal mencari barang master.', 'error')
    } finally {
        isSearching.value = false
    }
}

const selectBarang = (barang) => {
    selectedBarang.value = barang
    barangSearch.value = barang.judul_buku
    barangOptions.value = []
}

const saveMapping = async () => {
    if (!selectedItem.value || !selectedBarang.value) return

    isSaving.value = true
    try {
        await axios.put(route('old-barang-purchase.mapping', selectedItem.value.id), {
            old_barang_id: selectedBarang.value.id
        })

        Swal.fire({
            title: 'Berhasil!',
            text: 'Mapping berhasil disimpan.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        })

        // Reload the page to refresh data
        window.location.reload()
    } catch (error) {
        console.error('Error saving mapping:', error)
        Swal.fire('Error', 'Gagal menyimpan mapping.', 'error')
    } finally {
        isSaving.value = false
    }
}

const syncToPurchaseDetails = async () => {
    const result = await Swal.fire({
        title: 'Sinkronkan Data?',
        text: 'akan mengupdate code_barang di tabel old_purchase_details berdasarkan mapping yang sudah dilakukan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Sinkronkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#10b981',
    })

    if (!result.isConfirmed) return

    isSyncing.value = true
    try {
        const response = await axios.post(route('old-barang-purchase.sync'))

        Swal.fire({
            title: 'Berhasil!',
            text: response.data.message,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false
        })
    } catch (error) {
        console.error('Error syncing:', error)
        Swal.fire('Error', error.response?.data?.message || 'Gagal menyinkronkan data.', 'error')
    } finally {
        isSyncing.value = false
    }
}
</script>
