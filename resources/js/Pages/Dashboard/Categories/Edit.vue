<template>
    <DashboardLayout>
        <Head title="Edit Kategori" />

        <div class="mb-6">
            <Link
                :href="route('categories.index')"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600 mb-3"
            >
                <IconArrowLeft :size="16" />
                Kembali ke Kategori
            </Link>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <IconCategory :size="28" class="text-primary-500" />
                Edit Kategori
            </h1>
            <p class="text-sm text-slate-500 mt-1">{{ category.name }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="max-w-2xl">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3 flex items-center gap-2">
                                <IconPhoto :size="16" />
                                Gambar
                            </h3>
                            <div class="aspect-video rounded-xl bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden mb-3">
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="w-full h-full object-cover"
                                />
                                <IconPhoto v-else :size="32" class="text-slate-400" />
                            </div>
                            <Input
                                type="file"
                                @change="handleImageChange"
                                :errors="errors.image"
                                accept="image/*"
                            />
                        </div>

                        <div class="space-y-4">
                            <Input
                                type="text"
                                label="Nama Kategori"
                                placeholder="Masukkan nama"
                                :errors="errors.name"
                                v-model="form.name"
                            />
                            <Textarea
                                label="Deskripsi"
                                placeholder="Deskripsi kategori"
                                :errors="errors.description"
                                v-model="form.description"
                                :rows="4"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <Link
                            :href="route('categories.index')"
                            class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors disabled:opacity-50"
                        >
                            <IconDeviceFloppy :size="18" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconCategory,
    IconDeviceFloppy,
    IconArrowLeft,
    IconPhoto,
} from '@tabler/icons-vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Input from '@/Components/Dashboard/Input.vue';
import Textarea from '@/Components/Dashboard/TextArea.vue';

const props = defineProps({
    category: Object,
});

const { errors } = usePage().props;
const toast = useToast();

const form = useForm({
    id: props.category.id,
    name: props.category.name,
    description: props.category.description,
    image: '',
    _method: 'PUT',
});

const imagePreview = ref(
    props.category.image ? `/storage/categories/${props.category.image}` : null
);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('categories.update', props.category.id), {
        onSuccess: () => toast.success('Kategori berhasil diperbarui'),
        onError: () => toast.error('Gagal memperbarui kategori'),
    });
};
</script>


