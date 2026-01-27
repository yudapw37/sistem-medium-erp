<template>
    <DashboardLayout>
        <Head title="Edit Produk" />

        <div class="mb-6">
            <Link
                :href="route('products.index')"
                class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600 mb-3"
            >
                <IconArrowLeft :size="16" />
                Kembali ke Produk
            </Link>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <IconPackage :size="28" class="text-primary-500" />
                Edit Produk
            </h1>
            <p class="text-sm text-slate-500 mt-1">{{ product.title }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left - Image -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <IconPhoto :size="18" />
                            Gambar Produk
                        </h3>
                        <div class="aspect-square rounded-xl bg-slate-100 dark:bg-slate-800 border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center overflow-hidden mb-4">
                            <img
                                v-if="imagePreview"
                                :src="imagePreview"
                                alt="Preview"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="text-center p-6">
                                <IconPhoto :size="48" class="mx-auto text-slate-400 mb-2" />
                                <p class="text-sm text-slate-500">Belum ada gambar</p>
                            </div>
                        </div>
                        <Input
                            type="file"
                            label="Ganti Gambar"
                            @change="handleImageChange"
                            :errors="errors.image"
                            accept="image/*"
                        />
                    </div>
                </div>

                <!-- Right - Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <IconBarcode :size="18" />
                            Informasi Dasar
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <InputSelect
                                    label="Kategori"
                                    :data="categories"
                                    :selected="selectedCategory"
                                    :set-selected="setSelectedCategoryHandler"
                                    placeholder="Pilih kategori"
                                    :errors="errors.category_id"
                                    :searchable="true"
                                    display-key="name"
                                />
                            </div>
                            <Input
                                type="text"
                                label="Barcode / SKU"
                                v-model="form.barcode"
                                :errors="errors.barcode"
                                placeholder="Kode produk"
                            />
                            <Input
                                type="text"
                                label="Nama Produk"
                                v-model="form.title"
                                :errors="errors.title"
                                placeholder="Nama produk"
                            />
                            <div class="md:col-span-2">
                                <Textarea
                                    label="Deskripsi"
                                    placeholder="Deskripsi produk"
                                    :errors="errors.description"
                                    v-model="form.description"
                                    :rows="3"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                            <IconCurrencyDollar :size="18" />
                            Harga & Stok
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <Input
                                type="number"
                                label="Harga Beli"
                                v-model="form.buy_price"
                                :errors="errors.buy_price"
                                placeholder="0"
                            />
                            <Input
                                type="number"
                                label="Harga Jual"
                                v-model="form.sell_price"
                                :errors="errors.sell_price"
                                placeholder="0"
                            />
                            <Input
                                type="number"
                                label="Berat (gram)"
                                v-model="form.weight"
                                :errors="errors.weight"
                                placeholder="0"
                            />
                            <Input
                                type="number"
                                label="Stok"
                                v-model="form.stock"
                                :errors="errors.stock"
                                placeholder="0"
                            />
                            <div class="md:col-span-4 flex items-center gap-2 mt-2">
                                <Checkbox 
                                    v-model:checked="form.is_sellable" 
                                    id="is_sellable"
                                />
                                <label for="is_sellable" class="text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                                    Dapat Dijual (Tampil di POS)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Product Units -->
                    <ProductUnitManager
                        v-if="units && units.length > 0"
                        :product-id="product.id"
                        :units="units"
                        :product-units="productUnits || []"
                    />

                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('products.index')"
                            class="px-6 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium transition-colors"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary-500 hover:bg-primary-600 text-white font-medium transition-colors disabled:opacity-50"
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
    IconPackage,
    IconDeviceFloppy,
    IconArrowLeft,
    IconPhoto,
    IconBarcode,
    IconCurrencyDollar,
} from '@tabler/icons-vue';
import { getProductImageUrl } from '@/Utils/imageUrl';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Textarea from '@/Components/Dashboard/TextArea.vue';
import InputSelect from '@/Components/Dashboard/InputSelect.vue';
import Checkbox from '@/Components/Dashboard/Checkbox.vue';
import ProductUnitManager from '@/Components/Dashboard/ProductUnitManager.vue';

const props = defineProps({
    categories: Array,
    product: Object,
    units: Array,
    productUnits: Array,
});

const { errors } = usePage().props;
const toast = useToast();

const form = useForm({
    image: '',
    barcode: props.product.barcode,
    title: props.product.title,
    category_id: props.product.category_id,
    description: props.product.description,
    buy_price: props.product.buy_price,
    sell_price: props.product.sell_price,
    weight: props.product.weight ?? 0,
    stock: props.product.stock,
    is_sellable: props.product.is_sellable,
    _method: 'PUT',
});

const selectedCategory = ref(null);
const imagePreview = ref(
    props.product.image ? getProductImageUrl(props.product.image) : null
);

onMounted(() => {
    if (props.product.category_id) {
        selectedCategory.value = props.categories.find(
            (cat) => cat.id === props.product.category_id
        );
    }
});

const setSelectedCategoryHandler = (value) => {
    selectedCategory.value = value;
    form.category_id = value?.id || '';
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('products.update', props.product.id), {
        onSuccess: () => toast.success('Produk berhasil diperbarui'),
        onError: () => toast.error('Gagal memperbarui produk'),
    });
};
</script>


