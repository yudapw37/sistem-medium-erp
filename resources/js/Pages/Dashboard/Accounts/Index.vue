<template>
    <DashboardLayout>
        <Head title="Chart of Accounts" />

        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Chart of Accounts</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Daftar akun akuntansi perusahaan
                    </p>
                </div>
                <Button
                    type="link"
                    :icon="IconPlus"
                    class="bg-primary-500 hover:bg-primary-600 text-white"
                    label="Tambah Akun"
                    :href="route('accounts.create')"
                />
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-4">
            <div class="flex flex-col sm:flex-row gap-4 items-center">
                <div class="w-full sm:w-64">
                    <Search :url="route('accounts.index')" placeholder="Cari kode/nama akun..." />
                </div>
                <div class="w-full sm:w-48">
                    <select
                        v-model="selectedType"
                        @change="handleFilter"
                        class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="asset">Aset</option>
                        <option value="liability">Kewajiban</option>
                        <option value="equity">Ekuitas</option>
                        <option value="revenue">Pendapatan</option>
                        <option value="expense">Beban</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Accounts Tree Table -->
        <TableCard title="Daftar Akun">
            <Table>
                <TableThead>
                    <tr>
                        <TableTh>Kode</TableTh>
                        <TableTh>Nama Akun</TableTh>
                        <TableTh>Tipe</TableTh>
                        <TableTh>Level</TableTh>
                        <TableTh>Status</TableTh>
                        <TableTh class="text-center">Aksi</TableTh>
                    </tr>
                </TableThead>
                <TableTbody>
                    <template v-if="accounts.length > 0">
                        <template v-for="account in accounts" :key="account.id">
                            <AccountRow 
                                :account="account" 
                                :level="0"
                                @delete="handleDelete"
                            />
                        </template>
                    </template>
                    <tr v-else>
                        <TableTd colspan="6" class="text-center py-12">
                            <p class="text-slate-500">Belum ada akun</p>
                        </TableTd>
                    </tr>
                </TableTbody>
            </Table>
        </TableCard>
    </DashboardLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, h } from 'vue';
import { IconPlus, IconEdit, IconTrash, IconChevronRight, IconChevronDown } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Button from '@/Components/Dashboard/Button.vue';
import Search from '@/Components/Dashboard/Search.vue';
import Table from '@/Components/Dashboard/Table.vue';
import TableCard from '@/Components/Dashboard/TableCard.vue';
import TableThead from '@/Components/Dashboard/TableThead.vue';
import TableTbody from '@/Components/Dashboard/TableTbody.vue';
import TableTd from '@/Components/Dashboard/TableTd.vue';
import TableTh from '@/Components/Dashboard/TableTh.vue';

const props = defineProps({
    accounts: Array,
    allAccounts: Array,
    filters: Object,
});

const selectedType = ref(props.filters?.type || '');

const handleFilter = () => {
    router.get(route('accounts.index'), {
        q: props.filters?.q,
        type: selectedType.value,
    }, { preserveState: true });
};

const handleDelete = (id) => {
    if (confirm('Hapus akun ini?')) {
        router.delete(route('accounts.destroy', id));
    }
};

const route = (name, params) => {
    if (typeof window !== 'undefined' && window.route) {
        return window.route(name, params);
    }
    return '#';
};

const getTypeLabel = (type) => {
    const types = {
        asset: 'Aset',
        liability: 'Kewajiban',
        equity: 'Ekuitas',
        revenue: 'Pendapatan',
        expense: 'Beban',
    };
    return types[type] || type;
};

const getTypeColor = (type) => {
    const colors = {
        asset: 'bg-blue-100 text-blue-700',
        liability: 'bg-red-100 text-red-700',
        equity: 'bg-purple-100 text-purple-700',
        revenue: 'bg-green-100 text-green-700',
        expense: 'bg-orange-100 text-orange-700',
    };
    return colors[type] || 'bg-slate-100 text-slate-700';
};

// Recursive AccountRow component
const AccountRow = {
    name: 'AccountRow',
    props: {
        account: Object,
        level: Number,
    },
    emits: ['delete'],
    setup(props, { emit }) {
        const expanded = ref(true);
        const hasChildren = props.account.children && props.account.children.length > 0;

        const toggleExpand = () => {
            expanded.value = !expanded.value;
        };

        const route = (name, params) => {
            if (typeof window !== 'undefined' && window.route) {
                return window.route(name, params);
            }
            return '#';
        };

        return () => {
            const rows = [];
            
            // Main row
            rows.push(
                h('tr', { 
                    key: props.account.id,
                    class: 'hover:bg-slate-50 dark:hover:bg-slate-800/50',
                }, [
                    h(TableTd, {}, () => [
                        h('span', { 
                            class: 'font-mono font-bold text-slate-900 dark:text-white',
                            style: { paddingLeft: `${props.level * 20}px` }
                        }, [
                            hasChildren ? h('button', {
                                onClick: toggleExpand,
                                class: 'mr-1 text-slate-400 hover:text-slate-600'
                            }, [
                                h(expanded.value ? IconChevronDown : IconChevronRight, { size: 16 })
                            ]) : h('span', { class: 'inline-block w-5' }),
                            props.account.code
                        ])
                    ]),
                    h(TableTd, {}, () => props.account.name),
                    h(TableTd, {}, () => h('span', {
                        class: ['px-2 py-1 rounded-full text-xs font-semibold', getTypeColor(props.account.type)]
                    }, getTypeLabel(props.account.type))),
                    h(TableTd, { class: 'text-center' }, () => props.account.level),
                    h(TableTd, {}, () => h('span', {
                        class: props.account.is_active 
                            ? 'text-green-600' 
                            : 'text-red-600'
                    }, props.account.is_active ? 'Aktif' : 'Nonaktif')),
                    h(TableTd, { class: 'text-center' }, () => h('div', { class: 'flex items-center justify-center gap-1' }, [
                        h('button', {
                            onClick: () => router.visit(route('accounts.edit', props.account.id)),
                            class: 'p-1.5 rounded-lg hover:bg-amber-50 text-amber-600',
                            title: 'Edit'
                        }, [h(IconEdit, { size: 18 })]),
                        h('button', {
                            onClick: () => emit('delete', props.account.id),
                            class: 'p-1.5 rounded-lg hover:bg-red-50 text-red-600',
                            title: 'Hapus'
                        }, [h(IconTrash, { size: 18 })])
                    ]))
                ])
            );

            // Children rows (recursive)
            if (hasChildren && expanded.value) {
                props.account.children.forEach(child => {
                    rows.push(h(AccountRow, {
                        key: child.id,
                        account: child,
                        level: props.level + 1,
                        onDelete: (id) => emit('delete', id)
                    }));
                });
            }

            return rows;
        };
    }
};
</script>
