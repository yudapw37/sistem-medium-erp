<template>
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        >
            <div
                class="w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200"
            >
                <div
                    class="bg-gradient-to-r from-primary-500 to-primary-600 px-6 py-4 flex items-center justify-between"
                >
                    <div class="flex items-center gap-3 text-white">
                        <div
                            class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center"
                        >
                            <IconUserPlus :size="20" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg">Tambah Pelanggan</h3>
                            <p class="text-sm text-white/80">Daftarkan pelanggan baru</p>
                        </div>
                    </div>
                    <button
                        @click="handleClose"
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
                    >
                        <IconX :size="18" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Pelanggan <span class="text-danger-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            v-model="form.name"
                            placeholder="Masukkan nama lengkap"
                            :class="[
                                'w-full h-11 px-4 rounded-xl border bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-4 transition-all',
                                errors.name
                                    ? 'border-danger-500 focus:ring-danger-500/20'
                                    : 'border-slate-200 dark:border-slate-700 focus:ring-primary-500/20 focus:border-primary-500',
                            ]"
                            autofocus
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-danger-500">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            No. Telepon <span class="text-danger-500">*</span>
                        </label>
                        <input
                            type="tel"
                            name="no_telp"
                            v-model="form.no_telp"
                            placeholder="Contoh: 08123456789"
                            :class="[
                                'w-full h-11 px-4 rounded-xl border bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-4 transition-all',
                                errors.no_telp
                                    ? 'border-danger-500 focus:ring-danger-500/20'
                                    : 'border-slate-200 dark:border-slate-700 focus:ring-primary-500/20 focus:border-primary-500',
                            ]"
                        />
                        <p v-if="errors.no_telp" class="mt-1 text-xs text-danger-500">
                            {{ errors.no_telp }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Alamat <span class="text-danger-500">*</span>
                        </label>
                        <textarea
                            name="address"
                            v-model="form.address"
                            placeholder="Masukkan alamat lengkap"
                            rows="3"
                            :class="[
                                'w-full px-4 py-3 rounded-xl border bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:ring-4 transition-all resize-none',
                                errors.address
                                    ? 'border-danger-500 focus:ring-danger-500/20'
                                    : 'border-slate-200 dark:border-slate-700 focus:ring-primary-500/20 focus:border-primary-500',
                            ]"
                        />
                        <p v-if="errors.address" class="mt-1 text-xs text-danger-500">
                            {{ errors.address }}
                        </p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            @click="handleClose"
                            class="flex-1 h-11 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-medium hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex-1 h-11 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-semibold flex items-center justify-center gap-2 disabled:opacity-50 transition-colors"
                        >
                            <IconLoader2 v-if="isSubmitting" :size="18" class="animate-spin" />
                            <IconCheck v-else :size="18" />
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { IconUserPlus, IconX, IconLoader2, IconCheck } from '@tabler/icons-vue';

const props = defineProps({
    isOpen: Boolean,
    onClose: Function,
    onSuccess: Function,
});

const toast = useToast();
const form = ref({
    name: '',
    no_telp: '',
    address: '',
});
const errors = ref({});
const isSubmitting = ref(false);

const handleChange = (field, value) => {
    form.value[field] = value;
    if (errors.value[field]) {
        errors.value[field] = null;
    }
};

const handleSubmit = async () => {
    const newErrors = {};
    if (!form.value.name.trim()) newErrors.name = 'Nama wajib diisi';
    if (!form.value.no_telp.trim()) newErrors.no_telp = 'No. telepon wajib diisi';
    if (!form.value.address.trim()) newErrors.address = 'Alamat wajib diisi';

    if (Object.keys(newErrors).length > 0) {
        errors.value = newErrors;
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(route('customers.storeAjax'), form.value);

        if (response.data.success) {
            toast.success('Pelanggan berhasil ditambahkan');
            form.value = { name: '', no_telp: '', address: '' };
            isSubmitting.value = false;
            props.onSuccess?.(response.data.customer);
            props.onClose();
        } else {
            errors.value = response.data.errors || {};
            toast.error(response.data.message || 'Gagal menambahkan pelanggan');
            isSubmitting.value = false;
        }
    } catch (err) {
        console.error('Add customer error:', err);
        if (err.response?.data?.errors) {
            errors.value = err.response.data.errors;
        }
        toast.error(err.response?.data?.message || 'Gagal menambahkan pelanggan');
        isSubmitting.value = false;
    }
};

const handleClose = () => {
    form.value = { name: '', no_telp: '', address: '' };
    errors.value = {};
    props.onClose();
};
</script>

