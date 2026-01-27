<template>
    <DashboardLayout>
        <Head title="Dashboard" />
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <Widget
                title="Kategori"
                subtitle="Total Kategori"
                color="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                :icon="IconCategory"
                :total="20"
            />
            <Widget
                title="Produk"
                subtitle="Total Produk"
                color="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                :icon="IconBox"
                :total="30"
            />
            <Widget
                title="Transaksi"
                subtitle="Total Transaksi"
                color="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                :icon="IconMoneybag"
                :total="45"
            />
            <Widget
                title="Pengguna"
                subtitle="Total Pengguna"
                color="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                :icon="IconUsers"
                :total="2"
            />
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { IconBox, IconCategory, IconMoneybag, IconUsers } from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Widget from '@/Components/Dashboard/Widget.vue';
</script>

