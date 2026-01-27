<template>
    <POSLayout>
        <Head title="Transaksi" />

        <div class="h-[calc(100vh-4rem)] flex flex-col lg:flex-row">
            <!-- Mobile Tab Switcher -->
            <!-- Tab Switcher -->
            <div
                class="lg:hidden flex border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900"
            >
               <!-- Existing tabs -->
               <button
                    @click="mobileView = 'products'"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition-colors',
                        mobileView === 'products'
                            ? 'text-primary-600 border-b-2 border-primary-500'
                            : 'text-slate-500',
                    ]"
                >
                    <IconShoppingCart :size="18" />
                    <span>Produk</span>
                </button>
                <button
                    @click="mobileView = 'cart'"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition-colors relative',
                        mobileView === 'cart'
                            ? 'text-primary-600 border-b-2 border-primary-500'
                            : 'text-slate-500',
                    ]"
                >
                    <IconReceipt :size="18" />
                    <span>Keranjang</span>
                    <span
                        v-if="cartCount > 0"
                        class="absolute top-2 right-1/4 w-5 h-5 flex items-center justify-center text-xs font-bold bg-primary-500 text-white rounded-full"
                    >
                        {{ cartCount }}
                    </span>
                </button>
            </div>

            <!-- Warehouse Selector (Desktop) - SHOW ONLY IF SELECTED -->
            <div v-if="selectedWarehouseId" class="hidden lg:flex absolute top-4 right-4 z-50">
                <div class="relative">
                    <button
                        class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-sm cursor-default"
                    >
                        <IconBuildingWarehouse :size="18" class="text-primary-500" />
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                             {{ warehouses.find(w => w.id === selectedWarehouseId)?.name || 'Gudang' }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Left Panel - Products -->
            <div
                :class="[
                    'flex-1 bg-slate-100 dark:bg-slate-950 overflow-hidden',
                    mobileView !== 'products' ? 'hidden lg:flex lg:flex-col' : 'flex flex-col',
                ]"
            >
                <ProductGrid
                    :products="allProducts"
                    :categories="categories"
                    :selected-category="selectedCategory"
                    @update:selected-category="selectedCategory = $event"
                    :sale-type="saleType"
                    @update:sale-type="saleType = $event"
                    :search-query="searchQuery"
                    @update:search-query="searchQuery = $event"
                    :is-searching="isSearching"
                    @add-to-cart="handleAddToCart"
                    :adding-product-id="addingProductId"
                    :search-input-ref="searchInputRef"
                />
            </div>

            <!-- Right Panel - Cart & Payment -->
            <div
                :class="[
                    'w-full lg:w-[420px] xl:w-[480px] flex flex-col bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800',
                    mobileView !== 'cart' ? 'hidden lg:flex' : 'flex',
                ]"
                style="height: calc(100vh - 4rem)"
            >
                <!-- Customer Select -->
                <div class="p-3 border-b border-slate-200 dark:border-slate-800 flex-shrink-0">
                    <CustomerSelect
                        :customers="customers"
                        :selected="selectedCustomer"
                        @update:selected="selectedCustomer = $event"
                        placeholder="Pilih pelanggan..."
                        :error="errors?.customer_id"
                        label="Pelanggan"
                    />
                </div>

                <!-- Held Transactions -->
                <HeldTransactions
                    v-if="heldCarts.length > 0"
                    :held-carts="heldCarts"
                    :has-active-cart="carts.length > 0"
                />

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto min-h-0">
                    <div v-if="carts.length > 0" class="p-3 border-b border-slate-200 dark:border-slate-800">
                        <HoldButton
                            :has-items="carts.length > 0"
                            :on-hold="handleHoldCart"
                            :is-holding="isHolding"
                        />
                    </div>

                    <div class="p-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-3">
                            <h3
                                class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2"
                            >
                                <IconShoppingCart :size="16" />
                                Keranjang
                            </h3>
                            <span
                                v-if="carts.length > 0"
                                class="px-2 py-0.5 text-xs font-bold bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300 rounded-full"
                            >
                                {{ cartCount }} item
                            </span>
                        </div>

                        <div v-if="carts.length > 0" class="space-y-2 max-h-[200px] overflow-y-auto pr-1">
                            <div
                                v-for="item in carts"
                                :key="item.id"
                                class="flex items-center gap-2 p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 group"
                            >
                                <div
                                    class="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-700 overflow-hidden flex-shrink-0"
                                >
                                    <img
                                        v-if="item.product?.image"
                                        :src="getProductImageUrl(item.product.image)"
                                        :alt="item.product.title"
                                        class="w-full h-full object-cover"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <IconShoppingCart :size="14" class="text-slate-400" />
                                    </div>
                                </div>
                                 <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">
                                        {{ item.bundle ? '[BUNDLE] ' + item.bundle.name : (item.product?.title || 'Produk') }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ formatPrice(item.bundle ? item.bundle.sell_price : (item.product?.sell_price || 0)) }} × {{ item.qty }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="handleUpdateQty(item.id, Math.max(1, item.qty - 1))"
                                        :disabled="item.qty <= 1"
                                        class="w-6 h-6 rounded flex items-center justify-center bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 disabled:opacity-50 text-xs"
                                    >
                                        -
                                    </button>
                                    <span class="w-6 text-center text-xs font-medium">{{ item.qty }}</span>
                                    <button
                                        @click="handleUpdateQty(item.id, item.qty + 1)"
                                        class="w-6 h-6 rounded flex items-center justify-center bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 text-xs"
                                    >
                                        +
                                    </button>
                                    <button
                                        @click="handleRemoveFromCart(item.id)"
                                        class="w-6 h-6 rounded flex items-center justify-center text-slate-400 hover:text-danger-500 hover:bg-danger-50 dark:hover:bg-danger-950/50 ml-1"
                                    >
                                        <IconTrash :size="12" />
                                    </button>
                                </div>
                                <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 w-16 text-right">
                                    {{ formatPrice(item.price) }}
                                </p>
                            </div>
                        </div>
                        <div v-else class="py-6 text-center">
                            <IconShoppingCart
                                :size="32"
                                class="mx-auto text-slate-300 dark:text-slate-600 mb-2"
                            />
                            <p class="text-sm text-slate-400">Keranjang kosong</p>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="p-3 space-y-4">
                        <!-- Payment Method -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-2">
                                Metode Pembayaran
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="method in paymentOptions"
                                    :key="method.value"
                                    @click="paymentMethod = method.value"
                                    :class="[
                                        'p-3 rounded-xl border-2 transition-all flex items-center gap-2',
                                        paymentMethod === method.value
                                            ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/30'
                                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600',
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'w-8 h-8 rounded-lg flex items-center justify-center',
                                            paymentMethod === method.value
                                                ? 'bg-primary-500 text-white'
                                                : 'bg-slate-100 dark:bg-slate-800 text-slate-500'
                                        ]"
                                    >
                                        <IconCash v-if="method.value === 'cash'" :size="16" />
                                        <IconCreditCard v-else :size="16" />
                                    </div>
                                    <div class="text-left">
                                        <p
                                            :class="[
                                                'text-sm font-semibold',
                                                paymentMethod === method.value
                                                    ? 'text-primary-700 dark:text-primary-300'
                                                    : 'text-slate-700 dark:text-slate-300',
                                            ]"
                                        >
                                            {{ method.label }}
                                        </p>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Quick Amounts -->
                        <div v-if="paymentMethod === 'cash'">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-2">
                                Nominal Cepat
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="amt in [10000, 20000, 50000, 100000]"
                                    :key="amt"
                                    @click="cashInput = String(amt)"
                                    :class="[
                                        'py-2 px-1 rounded-lg text-xs font-semibold transition-all',
                                        Number(cashInput) === amt
                                            ? 'bg-primary-500 text-white'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200',
                                    ]"
                                >
                                    {{ formatPrice(amt) }}
                                </button>
                            </div>
                        </div>

                        <!-- Discount Input -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-2">
                                Diskon
                            </label>
                            <div class="flex gap-2 mb-2">
                                <button
                                    @click="discountType = 'amount'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        discountType === 'amount'
                                            ? 'bg-primary-500 text-white'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Nominal
                                </button>
                                <button
                                    @click="discountType = 'percent'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        discountType === 'percent'
                                            ? 'bg-primary-500 text-white'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Persentase
                                </button>
                            </div>
                            <div class="relative">
                                <span
                                    v-if="discountType === 'amount'"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                >
                                    Rp
                                </span>
                                <span
                                    v-else
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                >
                                    %
                                </span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    v-model="discountInput"
                                    @input="handleDiscountInput"
                                    :placeholder="discountType === 'amount' ? '0' : '0'"
                                    :class="[
                                        'w-full h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500',
                                        discountType === 'amount' ? 'pl-10 pr-4' : 'pl-4 pr-10',
                                    ]"
                                />
                            </div>
                            <p v-if="discountType === 'percent' && discountAmount > 0" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                = {{ formatPrice(discountAmount) }}
                            </p>
                        </div>

                        <!-- Event Discount Input -->
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-2">
                                Diskon Event
                            </label>
                            <div class="flex gap-2 mb-2">
                                <button
                                    @click="eventDiscountType = 'amount'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        eventDiscountType === 'amount'
                                            ? 'bg-purple-500 text-white'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Nominal
                                </button>
                                <button
                                    @click="eventDiscountType = 'percent'"
                                    :class="[
                                        'flex-1 py-2 px-3 rounded-lg text-xs font-medium transition-colors',
                                        eventDiscountType === 'percent'
                                            ? 'bg-purple-500 text-white'
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Persentase
                                </button>
                            </div>
                            <div class="relative">
                                <span
                                    v-if="eventDiscountType === 'amount'"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                >
                                    Rp
                                </span>
                                <span
                                    v-else
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"
                                >
                                    %
                                </span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    v-model="eventDiscountInput"
                                    @input="handleEventDiscountInput"
                                    :placeholder="eventDiscountType === 'amount' ? '0' : '0'"
                                    :class="[
                                        'w-full h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500',
                                        eventDiscountType === 'amount' ? 'pl-10 pr-4' : 'pl-4 pr-10',
                                    ]"
                                />
                            </div>
                            <p v-if="eventDiscountType === 'percent' && eventDiscountAmount > 0" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                = {{ formatPrice(eventDiscountAmount) }}
                            </p>
                        </div>

                        <!-- Cash Input -->
                        <div v-if="paymentMethod === 'cash'">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-2">
                                Jumlah Bayar (Rp)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">
                                    Rp
                                </span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    v-model="cashInput"
                                    @input="cashInput = cashInput.replace(/[^\d]/g, '')"
                                    placeholder="0"
                                    class="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-base font-semibold focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary & Submit -->
                <div
                    class="flex-shrink-0 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 p-3"
                >
                    <div class="flex justify-between items-center mb-2 text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-medium">{{ formatPrice(subtotal) }}</span>
                    </div>
                    <div v-if="discount > 0" class="flex justify-between items-center mb-2 text-sm">
                        <span class="text-slate-500">Diskon</span>
                        <span class="text-danger-500">-{{ formatPrice(discount) }}</span>
                    </div>
                    <div v-if="eventDiscount > 0" class="flex justify-between items-center mb-2 text-sm">
                        <span class="text-slate-500">Diskon Event</span>
                        <span class="text-purple-500">-{{ formatPrice(eventDiscount) }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-semibold text-slate-800 dark:text-white">Total</span>
                        <span class="text-xl font-bold text-primary-600 dark:text-primary-400">
                            {{ formatPrice(payable) }}
                        </span>
                    </div>

                    <div
                        v-if="paymentMethod === 'cash' && cash >= payable && payable > 0"
                        class="flex justify-between items-center mb-3 p-2 rounded-lg bg-success-50 dark:bg-success-950/30"
                    >
                        <span class="text-sm text-success-700 dark:text-success-400">Kembalian</span>
                        <span class="font-bold text-success-600">{{ formatPrice(cash - payable) }}</span>
                    </div>

                    <button
                        @click="handleSubmitTransaction"
                        :disabled="
                            !carts.length ||
                            !selectedCustomer ||
                            (paymentMethod === 'cash' && cash < payable) ||
                            isSubmitting
                        "
                        :class="[
                            'w-full h-12 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all',
                            carts.length &&
                            selectedCustomer &&
                            (paymentMethod !== 'cash' || cash >= payable)
                                ? 'bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white shadow-lg shadow-primary-500/30'
                                : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed',
                        ]"
                    >
                        <div
                            v-if="isSubmitting"
                            class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"
                        />
                        <template v-else>
                            <IconReceipt :size="18" />
                            <span>
                                {{
                                    !carts.length
                                        ? 'Keranjang Kosong'
                                        : !selectedCustomer
                                          ? 'Pilih Pelanggan'
                                          : paymentMethod === 'cash' && cash < payable
                                            ? `Kurang ${formatPrice(payable - cash)}`
                                            : 'Selesaikan Transaksi'
                                }}
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- Numpad Modal -->
        <NumpadModal
            :is-open="numpadOpen"
            @close="numpadOpen = false"
            @confirm="handleNumpadConfirm"
            title="Jumlah Bayar"
            :initial-value="Number(cashInput) || 0"
            :is-currency="true"
        />

        <!-- Keyboard Shortcuts Help -->
        <Teleport to="body">
            <div
                v-if="showShortcuts"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div class="absolute inset-0 bg-slate-900/60" @click="showShortcuts = false" />
                <div
                    class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-6 max-w-sm w-full"
                >
                    <h3
                        class="text-lg font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2"
                    >
                        <IconKeyboard :size="24" />
                        Keyboard Shortcuts
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="[key, desc] in [
                                ['F1', 'Buka Numpad'],
                                ['F2', 'Selesaikan Transaksi'],
                                ['F3', 'Toggle Produk/Keranjang'],
                                ['F4', 'Tampilkan Bantuan'],
                                ['Esc', 'Tutup Modal'],
                            ]"
                            :key="key"
                            class="flex items-center justify-between"
                        >
                            <span class="text-slate-600 dark:text-slate-400">{{ desc }}</span>
                            <kbd
                                class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded text-sm font-mono font-bold text-slate-700 dark:text-slate-300"
                            >
                                {{ key }}
                            </kbd>
                        </div>
                    </div>
                    <button
                        @click="showShortcuts = false"
                        class="mt-6 w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white rounded-xl font-medium"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- Warehouse Selection Modal (Forced) -->
        <Teleport to="body">
            <div
                v-if="showWarehouseModal"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
            >
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="p-6 border-b border-slate-200 dark:border-slate-800 text-center">
                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <IconBuildingWarehouse :size="32" class="text-primary-600 dark:text-primary-400" />
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Pilih Gudang</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Pilih gudang aktif untuk memulai transaksi.
                        </p>
                    </div>
                    <div class="p-6 space-y-3">
                        <button
                            v-for="warehouse in warehouses"
                            :key="warehouse.id"
                            @click="selectWarehouse(warehouse.id)"
                            class="w-full flex items-center justify-between p-4 rounded-xl border-2 border-slate-100 dark:border-slate-800 hover:border-primary-500 hover:bg-primary-50 dark:hover:bg-primary-950/30 transition-all group"
                        >
                            <span class="font-semibold text-slate-700 dark:text-slate-200 group-hover:text-primary-700 dark:group-hover:text-primary-300">
                                {{ warehouse.name }}
                            </span>
                            <IconArrowRight :size="20" class="text-slate-400 group-hover:text-primary-500" />
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </POSLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
    IconShoppingCart,
    IconReceipt,
    IconKeyboard,
    IconTrash,
    IconCash,
    IconCreditCard,
    IconBuildingWarehouse,
    IconArrowRight,
} from '@tabler/icons-vue';
import POSLayout from '@/Layouts/POSLayout.vue';
import ProductGrid from '@/Components/POS/ProductGrid.vue';
import CustomerSelect from '@/Components/POS/CustomerSelect.vue';
import NumpadModal from '@/Components/POS/NumpadModal.vue';
import HeldTransactions from '@/Components/POS/HeldTransactions.vue';
import HoldButton from '@/Components/POS/HoldButton.vue';
import useBarcodeScanner from '@/Composables/useBarcodeScanner';
import { getProductImageUrl } from '@/Utils/imageUrl';

const props = defineProps({
    carts: {
        type: Array,
        default: () => [],
    },
    carts_total: {
        type: Number,
        default: 0,
    },
    heldCarts: {
        type: Array,
        default: () => [],
    },
    customers: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    paymentGateways: {
        type: Array,
        default: () => [],
    },
    defaultPaymentGateway: {
        type: String,
        default: 'cash',
    },
    warehouses: {
        type: Array,
        default: () => [],
    },
    selected_warehouse_id: {
        type: Number,
        default: null,
    },
});

const { errors } = usePage().props;
const toast = useToast();

const searchQuery = ref('');
const selectedCategory = ref(null);
const isSearching = ref(false);
const addingProductId = ref(null);
const removingItemId = ref(null);
const selectedCustomer = ref(null);

const discountInput = ref('');
const selectedWarehouseId = ref(props.selected_warehouse_id);
const showWarehouseModal = ref(!props.selected_warehouse_id); // Show modal if no warehouse selected
const showWarehouseSelector = ref(false); // Deprecated/Hidden if locked

const discountType = ref('amount'); // 'amount' or 'percent'
const eventDiscountInput = ref('');
const eventDiscountType = ref('amount'); // 'amount' or 'percent'
const cashInput = ref('');
const paymentMethod = ref(props.defaultPaymentGateway ?? 'cash');
const isSubmitting = ref(false);
const mobileView = ref('products');
const numpadOpen = ref(false);
const showShortcuts = ref(false);
const searchInputRef = ref(null);
const saleType = ref('all'); // 'all', 'product', 'bundle'

watch(
    () => props.defaultPaymentGateway,
    (newVal) => {
        paymentMethod.value = newVal ?? 'cash';
    }
);

const handleBarcodeScan = (barcode) => {
    const product = props.products.find(
        (p) => p.barcode?.toLowerCase() === barcode.toLowerCase()
    );

    if (product) {
        if (product.stock > 0) {
            handleAddToCart(product);
            toast.success(`${product.title} ditambahkan (barcode)`);
        } else {
            toast.error(`${product.title} stok habis`);
        }
    } else {
        toast.error(`Barcode / Kode Bundle tidak ditemukan: ${barcode}`);
    }
};

const { isScanning } = useBarcodeScanner(handleBarcodeScan, {
    enabled: true,
    minLength: 3,
});

const handleDiscountInput = (event) => {
    const value = event.target.value;
    if (discountType.value === 'amount') {
        discountInput.value = value.replace(/[^\d]/g, '');
    } else {
        // Allow decimal for percent (max 100)
        const numValue = value.replace(/[^\d.]/g, '');
        const parts = numValue.split('.');
        if (parts.length > 2) {
            discountInput.value = parts[0] + '.' + parts.slice(1).join('');
        } else {
            discountInput.value = numValue;
        }
        // Limit to max 100%
        if (Number(discountInput.value) > 100) {
            discountInput.value = '100';
        }
    }
};

const discountPercent = computed(() => {
    if (discountType.value === 'percent') {
        return Math.max(0, Math.min(100, Number(discountInput.value) || 0));
    }
    return null;
});

const discountAmount = computed(() => {
    if (discountType.value === 'amount') {
        return Math.max(0, Number(discountInput.value) || 0);
    } else {
        // Calculate discount from percent
        const percent = discountPercent.value;
        const subtotalValue = props.carts_total ?? 0;
        return Math.round((subtotalValue * percent) / 100);
    }
});



const eventDiscountPercent = computed(() => {
    if (eventDiscountType.value === 'percent') {
        return Math.max(0, Math.min(100, Number(eventDiscountInput.value) || 0));
    }
    return null;
});

const eventDiscountAmount = computed(() => {
    if (eventDiscountType.value === 'amount') {
        return Math.max(0, Number(eventDiscountInput.value) || 0);
    } else {
        // Calculate discount from percent
        const percent = eventDiscountPercent.value;
        const subtotalValue = props.carts_total ?? 0;
        return Math.round((subtotalValue * percent) / 100);
    }
});

const discount = computed(() => discountAmount.value);
const eventDiscount = computed(() => eventDiscountAmount.value);
const subtotal = computed(() => props.carts_total ?? 0);
const payable = computed(() => Math.max(subtotal.value - discount.value - eventDiscount.value, 0));
const isCashPayment = computed(() => paymentMethod.value === 'cash');
const cash = computed(() =>
    isCashPayment.value ? Math.max(0, Number(cashInput.value) || 0) : payable.value
);
const cartCount = computed(() =>
    props.carts.reduce((total, item) => total + Number(item.qty), 0)
);

const paymentOptions = computed(() => {
    const options = Array.isArray(props.paymentGateways)
        ? props.paymentGateways.filter(
              (gateway) => gateway?.value && gateway.value.toLowerCase() !== 'cash'
          )
        : [];

    return [
        {
            value: 'cash',
            label: 'Tunai',
            description: 'Pembayaran tunai langsung di kasir.',
        },
        ...options,
    ];
});

watch([isCashPayment, payable], () => {
    if (!isCashPayment.value && payable.value >= 0) {
        cashInput.value = String(payable.value);
    }
});

const allProducts = computed(() => {
    return props.products.filter((product) => {
        const matchesSaleType = 
            saleType.value === 'all' || product.type === saleType.value;
            
        const matchesCategory =
            saleType.value === 'bundle' || // Bundles ignore category filter since they don't have one
            !selectedCategory.value || 
            product.category_id === selectedCategory.value;

        const matchesSearch =
            !searchQuery.value ||
            product.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            product.barcode?.toLowerCase().includes(searchQuery.value.toLowerCase());
            
        return matchesSaleType && matchesCategory && matchesSearch;
    });
});

const handleAddToCart = async (product) => {
    if (!product?.id) return;

    addingProductId.value = product.id;

    router.post(
        route('transactions.addToCart'),
        {
            product_id: product.id,
            sell_price: product.sell_price,
            qty: 1,
            warehouse_id: selectedWarehouseId.value,
            type: product.type || 'product'
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.error) {
                    toast.error(page.props.flash.error);
                } else {
                    toast.success(`${product.title} ditambahkan`);
                }
                addingProductId.value = null;
            },
            onError: () => {
                toast.error('Gagal menambahkan produk');
                addingProductId.value = null;
            },
        }
    );
};

const updatingCartId = ref(null);

const handleUpdateQty = (cartId, newQty) => {
    if (newQty < 1) return;
    updatingCartId.value = cartId;

    router.patch(
        route('transactions.updateCart', cartId),
        { 
            qty: newQty,
            warehouse_id: selectedWarehouseId.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.error) {
                    toast.error(page.props.flash.error);
                }
                updatingCartId.value = null;
            },
            onError: (errors) => {
                toast.error(errors?.message || 'Gagal update quantity');
                updatingCartId.value = null;
            },
        }
    );
};

const handleNumpadConfirm = (value) => {
    cashInput.value = String(value);
};

const isHolding = ref(false);

const handleHoldCart = async (label = null) => {
    if (props.carts.length === 0) {
        toast.error('Keranjang kosong');
        return;
    }

    isHolding.value = true;

    router.post(
        route('transactions.hold'),
        { label },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Transaksi ditahan');
                isHolding.value = false;
            },
            onError: (errors) => {
                toast.error(errors?.message || 'Gagal menahan transaksi');
                isHolding.value = false;
            },
        }
    );
};

const handleRemoveFromCart = (cartId) => {
    removingItemId.value = cartId;

    router.delete(route('transactions.destroyCart', cartId), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Item dihapus dari keranjang');
            removingItemId.value = null;
        },
        onError: () => {
            toast.error('Gagal menghapus item');
            removingItemId.value = null;
        },
    });
};

const handleSubmitTransaction = () => {
    if (props.carts.length === 0) {
        toast.error('Keranjang masih kosong');
        return;
    }

    if (!selectedCustomer.value?.id) {
        toast.error('Pilih pelanggan terlebih dahulu');
        return;
    }

    if (isCashPayment.value && cash.value < payable.value) {
        toast.error('Jumlah pembayaran kurang dari total');
        return;
    }

    isSubmitting.value = true;

    router.post(
        route('transactions.store'),
        {
            customer_id: selectedCustomer.value.id,
            discount: discount.value,
            discount_type: discountType.value,
            discount_percent: discountType.value === 'percent' ? discountPercent.value : null,
            event_discount: eventDiscount.value,
            event_discount_type: eventDiscountType.value,
            event_discount_percent: eventDiscountType.value === 'percent' ? eventDiscountPercent.value : null,
            grand_total: payable.value,
            cash: isCashPayment.value ? cash.value : payable.value,
            change: isCashPayment.value ? Math.max(cash.value - payable.value, 0) : 0,
            payment_gateway: isCashPayment.value ? null : paymentMethod.value,
            warehouse_id: selectedWarehouseId.value,
        },
        {
            onSuccess: () => {
                discountInput.value = '';
                discountType.value = 'amount';
                eventDiscountInput.value = '';
                eventDiscountType.value = 'amount';
                cashInput.value = '';
                selectedCustomer.value = null;
                paymentMethod.value = props.defaultPaymentGateway ?? 'cash';
                isSubmitting.value = false;
                toast.success('Transaksi berhasil!');
            },
            onError: () => {
                isSubmitting.value = false;
                toast.error('Gagal menyimpan transaksi');
            },
        }
    );
};

const handleKeyDown = (e) => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

    switch (e.key) {
        case '/':
        case 'F5':
            e.preventDefault();
            if (searchInputRef.value) {
                searchInputRef.value.focus();
            }
            break;
        case 'F1':
            e.preventDefault();
            numpadOpen.value = true;
            break;
        case 'F2':
            e.preventDefault();
            if (props.carts.length > 0 && selectedCustomer.value) {
                handleSubmitTransaction();
            }
            break;
        case 'F3':
            e.preventDefault();
            mobileView.value = mobileView.value === 'products' ? 'cart' : 'products';
            break;
        case 'F4':
            e.preventDefault();
            showShortcuts.value = !showShortcuts.value;
            break;
        case 'Escape':
            numpadOpen.value = false;
            showShortcuts.value = false;
            searchQuery.value = '';
            break;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const formatPrice = (value = 0) =>
    value.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    });
const selectWarehouse = (id) => {
    router.visit(route('transactions.index', { warehouse_id: id }));
};
</script>


