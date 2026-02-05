<x-pos-layout title="Kasir">
    <div x-data="posKasir()" x-init="init()" class="flex h-[calc(100vh-4rem)] gap-4">
        <!-- Left Panel: Products -->
        <div class="flex flex-1 flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <!-- Search Bar -->
            <div class="border-b border-slate-200 p-4">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-model="searchQuery"
                           @input.debounce.300ms="filterProducts"
                           @keydown.enter.prevent="handleBarcodeEnter"
                           x-ref="searchInput"
                           class="block w-full rounded-lg border-slate-300 bg-slate-50 pl-10 pr-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 focus:bg-white"
                           placeholder="Cari produk...">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="border-b border-slate-200 px-4 py-3">
                <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
                    <button type="button"
                            @click="selectedCategory = null; filterProducts()"
                            :class="selectedCategory === null ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="flex-shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-colors">
                        Semua
                    </button>
                    @foreach($categories as $category)
                        <button type="button"
                                @click="selectedCategory = {{ $category->id }}; filterProducts()"
                                :class="selectedCategory === {{ $category->id }} ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="flex-shrink-0 rounded-full px-4 py-2 text-sm font-medium transition-colors whitespace-nowrap">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-auto p-4">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="products-grid">
                    @foreach($products as $product)
                        <button type="button"
                                @click="addProductToCartById({{ $product['id'] }})"
                                data-product-id="{{ $product['id'] }}"
                                data-category="{{ $product['category'] }}"
                                data-name="{{ strtolower($product['name']) }}"
                                data-code="{{ strtolower($product['code'] ?? '') }}"
                                data-barcode="{{ strtolower($product['barcode'] ?? '') }}"
                                class="product-card group relative flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white text-left transition-all hover:border-emerald-300 hover:shadow-md">
                            <!-- Product Image -->
                            <div class="relative aspect-square bg-slate-100 overflow-hidden">
                                @if($product['image_url'])
                                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="h-full w-full object-cover group-hover:scale-105 transition-transform">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200">
                                        <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                        </svg>
                                    </div>
                                @endif

                                @if($product['requires_prescription'])
                                    <span class="absolute top-2 right-2 rounded bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white">Resep</span>
                                @endif

                                @if($product['total_stock'] <= 0)
                                    <div class="absolute inset-0 flex items-center justify-center bg-slate-900/60">
                                        <span class="rounded bg-red-500 px-2 py-1 text-xs font-bold text-white">HABIS</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Product Info -->
                            <div class="flex flex-1 flex-col p-3">
                                <h3 class="text-sm font-medium text-slate-900 line-clamp-2">{{ $product['name'] }}</h3>
                                <div class="mt-auto pt-2 flex items-end justify-between">
                                    <span class="text-sm font-bold text-emerald-600">Rp {{ number_format($product['selling_price'], 0, ',', '.') }}</span>
                                    <span class="text-xs text-slate-500">{{ $product['total_stock'] }}</span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Panel: Cart -->
        <div class="w-96 flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <!-- Cart Header -->
            <div class="border-b border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-semibold text-slate-900">Keranjang</h2>
                            <p class="text-sm text-slate-500"><span x-text="cart.length"></span> item</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="clearCart"
                            x-show="cart.length > 0"
                            class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </div>

                <!-- Customer Selection -->
                <div class="mt-3 relative" x-data="{ showCustomerDropdown: false, customerSearch: '' }">
                    <button type="button"
                            @click="showCustomerDropdown = !showCustomerDropdown"
                            class="w-full flex items-center justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm hover:bg-slate-100 transition-colors">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span x-text="selectedCustomer ? selectedCustomer.name : 'Pelanggan Umum'" :class="selectedCustomer ? 'text-slate-900 font-medium' : 'text-slate-500'"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <template x-if="selectedCustomer">
                                <button type="button" @click.stop="selectedCustomer = null" class="p-0.5 rounded hover:bg-slate-200">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </template>
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </button>

                    <!-- Customer Dropdown -->
                    <div x-show="showCustomerDropdown"
                         @click.outside="showCustomerDropdown = false"
                         x-transition
                         class="absolute left-0 right-0 top-full mt-1 z-20 rounded-lg bg-white shadow-lg ring-1 ring-slate-900/10 overflow-hidden">
                        <div class="p-2 border-b border-slate-100">
                            <input type="text"
                                   x-model="customerSearch"
                                   @input.debounce.300ms="searchCustomers"
                                   placeholder="Cari pelanggan..."
                                   class="w-full rounded border-slate-200 text-sm py-1.5 px-2 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                            <button type="button"
                                    @click="selectedCustomer = null; showCustomerDropdown = false"
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2"
                                    :class="!selectedCustomer ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700'">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                Pelanggan Umum
                            </button>
                            <template x-for="customer in filteredCustomers" :key="customer.id">
                                <button type="button"
                                        @click="selectedCustomer = customer; showCustomerDropdown = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                                        :class="selectedCustomer?.id === customer.id ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700'">
                                    <div>
                                        <span class="font-medium" x-text="customer.name"></span>
                                        <span class="text-slate-400 text-xs" x-show="customer.phone" x-text="' - ' + customer.phone"></span>
                                    </div>
                                </button>
                            </template>
                            <template x-if="filteredCustomers.length === 0 && customerSearch">
                                <div class="px-3 py-4 text-center text-sm text-slate-500">
                                    Tidak ditemukan
                                </div>
                            </template>
                        </div>
                        <div class="p-2 border-t border-slate-100">
                            <a href="{{ route('pos.customers.create') }}" target="_blank"
                               class="flex items-center justify-center gap-2 w-full rounded-lg border border-dashed border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:border-emerald-500 hover:text-emerald-600 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Tambah Pelanggan Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-auto">
                <!-- Empty Cart -->
                <template x-if="cart.length === 0">
                    <div class="flex h-full flex-col items-center justify-center p-6 text-slate-400">
                        <svg class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        <p class="text-center">Keranjang kosong<br><span class="text-sm">Pilih produk untuk menambahkan</span></p>
                    </div>
                </template>

                <!-- Cart Items List -->
                <div class="divide-y divide-slate-100">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-slate-900 truncate" x-text="item.product_name"></h4>
                                    <p class="text-sm text-slate-500">
                                        <span x-text="formatCurrency(item.price)"></span> / <span x-text="item.unit"></span>
                                    </p>
                                    <p class="text-xs text-slate-400" x-text="'Batch: ' + item.batch_number"></p>
                                </div>
                                <span class="font-semibold text-emerald-600" x-text="formatCurrency(item.subtotal)"></span>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="mt-3 flex items-center justify-between">
                                <button type="button"
                                        @click="removeFromCart(index)"
                                        class="text-xs text-red-500 hover:text-red-700">
                                    Hapus
                                </button>
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                            @click="decrementQuantity(index)"
                                            :disabled="item.quantity <= 1"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    </button>
                                    <span class="w-10 text-center font-semibold text-slate-900" x-text="item.quantity"></span>
                                    <button type="button"
                                            @click="incrementQuantity(index)"
                                            :disabled="item.quantity >= item.stock"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Cart Footer -->
            <div class="border-t border-slate-200 p-4 space-y-4">
                <!-- Totals -->
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Subtotal</span>
                        <span class="font-medium text-slate-900" x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold text-slate-900">Total</span>
                        <span class="text-xl font-bold text-emerald-600" x-text="formatCurrency(total)"></span>
                    </div>
                </div>

                <!-- Pay Button -->
                <button type="button"
                        @click="openPaymentModal"
                        :disabled="cart.length === 0"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-4 text-base font-semibold text-white shadow-lg hover:bg-emerald-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    Bayar
                </button>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/50" @click="showPaymentModal = false"></div>
                <div x-show="showPaymentModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-md transform rounded-2xl bg-white shadow-2xl">

                    <!-- Modal Header -->
                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900">Pembayaran</h3>
                            <button type="button" @click="showPaymentModal = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Total -->
                        <div class="rounded-xl bg-emerald-50 p-4 text-center">
                            <p class="text-sm text-emerald-600">Total Pembayaran</p>
                            <p class="text-3xl font-bold text-emerald-700" x-text="formatCurrency(total)"></p>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Metode Pembayaran</label>
                            <div class="grid grid-cols-2 gap-2">
                                @php
                                    $xenditMethods = ['QRIS', 'GOPAY', 'OVO', 'DANA', 'SHOPEEPAY', 'LINKAJA'];
                                @endphp
                                @foreach($paymentMethods as $method)
                                    @php
                                        $isXenditMethod = $xenditEnabled && in_array($method->code, $xenditMethods);
                                    @endphp
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="payment_method" value="{{ $method->id }}" x-model.number="selectedPaymentMethod" class="peer sr-only">
                                        <div class="flex items-center justify-center gap-2 rounded-lg border-2 border-slate-200 p-3 text-sm font-medium text-slate-700 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 {{ $isXenditMethod ? 'peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700' : '' }}">
                                            @if($method->is_cash)
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                                </svg>
                                            @elseif($isXenditMethod)
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                                </svg>
                                            @endif
                                            {{ $method->name }}
                                            @if($isXenditMethod)
                                                <span class="text-[10px] bg-blue-500 text-white px-1 py-0.5 rounded">Xendit</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Cash Input (only for cash payment) -->
                        <div x-show="isCashPayment" x-transition>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Uang Diterima</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-slate-500 font-medium">Rp</span>
                                </div>
                                <input type="number"
                                       x-model.number="paidAmount"
                                       min="0"
                                       class="block w-full rounded-xl border-slate-300 pl-12 py-4 text-xl font-bold text-right focus:border-emerald-500 focus:ring-emerald-500"
                                       placeholder="0">
                            </div>

                            <!-- Quick Amount -->
                            <div class="mt-3 grid grid-cols-4 gap-2">
                                <button type="button" @click="paidAmount = total" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Uang Pas</button>
                                <button type="button" @click="paidAmount = 50000" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">50rb</button>
                                <button type="button" @click="paidAmount = 100000" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">100rb</button>
                                <button type="button" @click="paidAmount = 200000" class="rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">200rb</button>
                            </div>

                            <!-- Change -->
                            <div class="mt-4 rounded-xl p-4" :class="changeAmount >= 0 ? 'bg-emerald-50' : 'bg-red-50'">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium" :class="changeAmount >= 0 ? 'text-emerald-700' : 'text-red-700'">Kembalian</span>
                                    <span class="text-2xl font-bold" :class="changeAmount >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="formatCurrency(Math.abs(changeAmount))"></span>
                                </div>
                                <p x-show="changeAmount < 0" class="mt-1 text-sm text-red-600">Uang kurang!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-slate-200 px-6 py-4">
                        <!-- Xendit Notice -->
                        <div x-show="shouldUseXendit" class="mb-3 rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-sm text-blue-700">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                                <span>Pembayaran <strong x-text="selectedPaymentMethodName"></strong> akan diproses via Xendit</span>
                            </div>
                        </div>
                        <button type="button"
                                @click="shouldUseXendit ? processXenditPayment() : processPayment()"
                                :disabled="!canProcess || isProcessing"
                                :class="shouldUseXendit ? 'bg-blue-600 hover:bg-blue-500' : 'bg-emerald-600 hover:bg-emerald-500'"
                                class="flex w-full items-center justify-center gap-2 rounded-xl px-6 py-4 text-base font-semibold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isProcessing">
                                <span x-show="shouldUseXendit">Bayar via Xendit</span>
                                <span x-show="!shouldUseXendit">Proses Pembayaran</span>
                            </span>
                            <span x-show="isProcessing" class="flex items-center gap-2">
                                <svg class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Xendit Payment Modal -->
        @if($xenditEnabled)
        <div x-show="showXenditModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-hidden"
             style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="closeXenditModal"></div>
            <div class="relative h-full w-full flex items-center justify-center p-2">
                <div x-show="showXenditModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     :class="xenditStatus === 'pending' ? 'max-w-4xl h-[95vh]' : 'max-w-md h-auto'"
                     class="relative w-full transform rounded-2xl bg-white shadow-2xl flex flex-col overflow-hidden">

                    <!-- Modal Header -->
                    <div class="border-b border-slate-200 px-6 py-4 flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-slate-900">Pembayaran Xendit</h3>
                                <template x-if="xenditStatus === 'pending'">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Pembayaran
                                    </span>
                                </template>
                            </div>
                            <button type="button" @click="closeXenditModal" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="flex-1 min-h-0 flex flex-col">
                        <!-- Creating Invoice -->
                        <div x-show="xenditStatus === 'creating'" class="text-center py-16">
                            <svg class="h-12 w-12 mx-auto animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-4 text-slate-600">Membuat invoice pembayaran...</p>
                        </div>

                        <!-- Invoice Created - Embedded Payment Page -->
                        <template x-if="xenditStatus === 'pending'">
                            <div class="flex-1 min-h-0 flex flex-col">
                                <!-- Iframe Container - takes all available space -->
                                <div class="flex-1 min-h-0">
                                    <iframe
                                        x-show="xenditInvoiceUrl"
                                        :src="xenditInvoiceUrl"
                                        class="w-full h-full border-0"
                                        style="min-height: 500px;"
                                        allow="payment"
                                        sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-top-navigation"
                                    ></iframe>
                                </div>
                                <!-- Footer with status and actions -->
                                <div class="border-t border-slate-200 px-4 py-2 bg-slate-50 flex-shrink-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-sm text-slate-600">
                                            <svg class="h-4 w-4 animate-pulse text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <span>Status diperbarui otomatis</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a :href="xenditInvoiceUrl"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                                Buka di tab baru
                                            </a>
                                            <button type="button"
                                                    @click="checkXenditStatus"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                                Cek Status
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Payment Success -->
                        <div x-show="xenditStatus === 'paid'" class="text-center py-12 px-6">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </div>
                            <h4 class="mt-4 text-xl font-bold text-slate-900">Pembayaran Berhasil!</h4>
                            <p class="mt-2 text-slate-500">Pembayaran telah diterima via Xendit</p>
                        </div>

                        <!-- Payment Failed -->
                        <div x-show="xenditStatus === 'failed' || xenditStatus === 'expired'" class="text-center py-12 px-6">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100">
                                <svg class="h-10 w-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <h4 class="mt-4 text-xl font-bold text-slate-900" x-text="xenditStatus === 'expired' ? 'Invoice Kedaluwarsa' : 'Pembayaran Gagal'"></h4>
                            <p class="mt-2 text-slate-500">Silakan coba lagi atau gunakan metode pembayaran lain</p>
                            <button type="button"
                                    @click="retryXenditPayment"
                                    class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-500">
                                Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Success Modal -->
        <div x-show="showSuccessModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/50"></div>
                <div x-show="showSuccessModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative w-full max-w-sm transform rounded-2xl bg-white p-8 shadow-2xl text-center">

                    <!-- Success Icon -->
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
                        <svg class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>

                    <h3 class="mt-6 text-xl font-bold text-slate-900">Pembayaran Berhasil!</h3>
                    <p class="mt-2 text-slate-500">Invoice: <span class="font-semibold text-slate-900" x-text="lastSale?.invoice_number"></span></p>

                    <!-- Summary -->
                    <div class="mt-6 rounded-xl bg-slate-50 p-4 text-left space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Total</span>
                            <span class="font-semibold text-slate-900" x-text="formatCurrency(lastSale?.total)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Dibayar</span>
                            <span class="font-semibold text-slate-900" x-text="formatCurrency(lastSale?.paid_amount)"></span>
                        </div>
                        <div class="flex justify-between pt-3 border-t border-slate-200">
                            <span class="font-semibold text-emerald-600">Kembalian</span>
                            <span class="text-xl font-bold text-emerald-600" x-text="formatCurrency(lastSale?.change_amount)"></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <button type="button"
                                @click="printReceipt"
                                class="flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                            </svg>
                            Cetak
                        </button>
                        <button type="button"
                                @click="newTransaction"
                                class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Transaksi Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        console.log('[POS] Script loaded');

        let posKasirInstance = null;
        const categoriesMap = @json($categories->pluck('name', 'id'));
        const productsData = @json($products->values());

        console.log('[POS] productsData count:', productsData.length);
        console.log('[POS] First product:', productsData[0]);


        function posKasir() {
            console.log('[POS] posKasir function called');
            return {
                // State
                searchQuery: '',
                selectedCategory: null,
                loading: false,
                productsData: productsData,
                cart: [],
                selectedPaymentMethod: {{ $paymentMethods->where('is_cash', true)->first()?->id ?? $paymentMethods->first()?->id ?? 'null' }},
                paidAmount: 0,
                isProcessing: false,
                showPaymentModal: false,
                showSuccessModal: false,
                lastSale: null,
                paymentMethods: @json($paymentMethods),
                // Customer state
                selectedCustomer: null,
                customers: @json($customers),
                filteredCustomers: @json($customers),
                // Xendit state
                xenditEnabled: {{ $xenditEnabled ? 'true' : 'false' }},
                showXenditModal: false,
                xenditStatus: null, // creating, pending, paid, failed, expired
                xenditInvoiceUrl: null,
                xenditTransactionId: null,
                xenditPollInterval: null,

                // Init
                init() {
                    posKasirInstance = this;
                    console.log('[POS] Alpine init called');
                    console.log('[POS] this.productsData:', this.productsData?.length);
                    console.log('[POS] this.cart:', this.cart);
                    this.$refs.searchInput?.focus();

                    // Load cart from localStorage (if any items added from Products page)
                    this.loadCartFromStorage();

                    // Keyboard shortcuts
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'F2') {
                            e.preventDefault();
                            this.$refs.searchInput?.focus();
                        }
                        if (e.key === 'Escape') {
                            if (this.showPaymentModal) {
                                this.showPaymentModal = false;
                            } else if (this.showSuccessModal) {
                                this.newTransaction();
                            }
                        }
                    });

                    // Watch for localStorage changes (if user adds from another tab)
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'pos_cart') {
                            this.loadCartFromStorage();
                        }
                    });
                },

                // Load cart from localStorage
                loadCartFromStorage() {
                    const savedCart = localStorage.getItem('pos_cart');
                    if (savedCart) {
                        try {
                            const items = JSON.parse(savedCart);
                            if (items.length > 0) {
                                // Merge with current cart
                                items.forEach(item => {
                                    const existingIndex = this.cart.findIndex(c => c.product_id === item.product_id);
                                    if (existingIndex < 0) {
                                        this.cart.push(item);
                                    }
                                });
                                console.log('[POS] Loaded cart from storage:', this.cart);
                                // Clear localStorage after loading
                                localStorage.removeItem('pos_cart');
                            }
                        } catch (e) {
                            console.error('[POS] Failed to parse cart from storage:', e);
                        }
                    }
                },

                // Save cart to localStorage
                saveCartToStorage() {
                    localStorage.setItem('pos_cart', JSON.stringify(this.cart));
                },

                // Computed
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
                },

                get total() {
                    return this.subtotal;
                },

                get changeAmount() {
                    return this.paidAmount - this.total;
                },

                get isCashPayment() {
                    const method = this.paymentMethods.find(m => m.id == this.selectedPaymentMethod);
                    return method?.is_cash ?? false;
                },

                get canProcess() {
                    if (this.cart.length === 0) return false;
                    if (!this.selectedPaymentMethod) return false;
                    if (this.isCashPayment && this.paidAmount < this.total) return false;
                    return true;
                },

                // Check if selected payment method should use Xendit
                get shouldUseXendit() {
                    if (!this.xenditEnabled) return false;
                    const method = this.paymentMethods.find(m => m.id == this.selectedPaymentMethod);
                    if (!method || !method.code) return false;
                    // QRIS and E-wallets should use Xendit when enabled
                    const xenditMethods = ['QRIS', 'GOPAY', 'OVO', 'DANA', 'SHOPEEPAY', 'LINKAJA'];
                    return xenditMethods.includes(method.code);
                },

                get selectedPaymentMethodName() {
                    const method = this.paymentMethods.find(m => m.id == this.selectedPaymentMethod);
                    return method?.name || '';
                },

                // Methods
                filterProducts() {
                    const cards = document.querySelectorAll('.product-card');
                    const search = this.searchQuery.toLowerCase();
                    const categoryName = this.selectedCategory ? categoriesMap[this.selectedCategory] : null;

                    cards.forEach(card => {
                        let show = true;

                        // Filter by category
                        if (categoryName && card.dataset.category !== categoryName) {
                            show = false;
                        }

                        // Filter by search
                        if (show && search) {
                            const name = card.dataset.name || '';
                            const code = card.dataset.code || '';
                            const barcode = card.dataset.barcode || '';
                            if (!name.includes(search) && !code.includes(search) && !barcode.includes(search)) {
                                show = false;
                            }
                        }

                        card.style.display = show ? '' : 'none';
                    });
                },

                // Handle barcode scan or enter key in search
                handleBarcodeEnter() {
                    const search = this.searchQuery.toLowerCase().trim();
                    if (!search) return;

                    // Find product by exact barcode match first
                    let product = this.productsData.find(p =>
                        (p.barcode && p.barcode.toLowerCase() === search) ||
                        (p.code && p.code.toLowerCase() === search)
                    );

                    // If no exact match, find first visible product
                    if (!product) {
                        const visibleCard = document.querySelector('.product-card:not([style*="display: none"])');
                        if (visibleCard) {
                            const productId = parseInt(visibleCard.dataset.productId);
                            product = this.productsData.find(p => p.id === productId);
                        }
                    }

                    if (product) {
                        this.addProductToCart(product);
                        this.searchQuery = '';
                        this.filterProducts();
                    }
                },

                // Find product by ID and add to cart
                addProductToCartById(productId) {
                    console.log('[POS] addProductToCartById called with ID:', productId);
                    console.log('[POS] productsData length:', this.productsData?.length);
                    console.log('[POS] productsData sample IDs:', this.productsData?.slice(0, 5).map(p => p.id));

                    const product = this.productsData.find(p => p.id === productId);
                    console.log('[POS] Found product:', product);

                    if (!product) {
                        console.log('[POS] Product not found with ID:', productId);
                        alert('Produk tidak ditemukan. Silakan refresh halaman.');
                        return;
                    }
                    this.addProductToCart(product);
                },

                addProductToCart(product) {
                    console.log('[POS] addProductToCart called', product);

                    if (!product) {
                        console.log('[POS] Product is null/undefined');
                        return;
                    }

                    console.log('[POS] Product details:', {
                        id: product.id,
                        name: product.name,
                        total_stock: product.total_stock,
                        batch: product.batch,
                        selling_price: product.selling_price
                    });

                    // Check total stock first (across all batches)
                    if (!product.total_stock || product.total_stock <= 0) {
                        console.log('[POS] Product out of stock');
                        alert('Stok produk habis');
                        return;
                    }

                    // If no batch specified, we'll use total_stock for quantity limit
                    // The backend will handle multi-batch allocation
                    const batch = product.batch;
                    const price = batch ? parseFloat(batch.selling_price) : parseFloat(product.selling_price || 0);
                    const batchId = batch ? batch.id : null;
                    const batchNumber = batch ? batch.batch_number : 'Auto';
                    const availableStock = product.total_stock; // Use total stock for limit

                    console.log('[POS] Calculated values:', { price, batchId, batchNumber, availableStock });

                    // Check if product already in cart (by product_id only, since we now support multi-batch)
                    const existingIndex = this.cart.findIndex(item => item.product_id === product.id);
                    console.log('[POS] Existing cart index:', existingIndex);

                    if (existingIndex >= 0) {
                        // Check if we can add more based on total stock
                        if (this.cart[existingIndex].quantity < availableStock) {
                            this.cart[existingIndex].quantity++;
                            this.cart[existingIndex].subtotal = this.cart[existingIndex].quantity * this.cart[existingIndex].price;
                            console.log('[POS] Incremented quantity for existing item');
                        } else {
                            console.log('[POS] Cannot add more - max stock reached');
                            alert('Stok tidak mencukupi');
                        }
                    } else {
                        const newItem = {
                            id: Date.now(),
                            product_id: product.id,
                            product_name: product.name,
                            batch_id: batchId, // Can be null - backend will auto-allocate
                            batch_number: batchNumber,
                            requires_prescription: product.requires_prescription,
                            quantity: 1,
                            stock: availableStock, // Use total stock as limit
                            unit: product.unit || 'pcs',
                            price: price,
                            subtotal: price
                        };
                        console.log('[POS] New cart item:', newItem);
                        this.cart.push(newItem);
                        console.log('[POS] Cart after push:', this.cart);
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                },

                incrementQuantity(index) {
                    if (this.cart[index].quantity < this.cart[index].stock) {
                        this.cart[index].quantity++;
                        this.cart[index].subtotal = this.cart[index].quantity * this.cart[index].price;
                    }
                },

                decrementQuantity(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                        this.cart[index].subtotal = this.cart[index].quantity * this.cart[index].price;
                    }
                },

                clearCart() {
                    if (this.cart.length === 0) return;
                    if (confirm('Kosongkan keranjang?')) {
                        this.cart = [];
                    }
                },

                searchCustomers() {
                    const search = this.customerSearch?.toLowerCase() || '';
                    if (!search) {
                        this.filteredCustomers = this.customers;
                        return;
                    }
                    this.filteredCustomers = this.customers.filter(c =>
                        c.name.toLowerCase().includes(search) ||
                        (c.phone && c.phone.includes(search))
                    );
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return;
                    this.paidAmount = this.total;
                    this.showPaymentModal = true;
                },

                async processPayment() {
                    console.log('[POS] processPayment called');
                    console.log('[POS] canProcess:', this.canProcess);
                    console.log('[POS] isProcessing:', this.isProcessing);

                    if (!this.canProcess || this.isProcessing) {
                        console.log('[POS] Cannot process - returning');
                        return;
                    }

                    this.isProcessing = true;

                    const payload = {
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            batch_id: item.batch_id,
                            quantity: item.quantity,
                            price: item.price,
                            discount: 0
                        })),
                        customer_id: this.selectedCustomer?.id || null,
                        discount: 0,
                        payments: [{
                            payment_method_id: this.selectedPaymentMethod,
                            amount: this.isCashPayment ? this.paidAmount : this.total
                        }],
                        notes: ''
                    };

                    console.log('[POS] Payment payload:', JSON.stringify(payload, null, 2));

                    try {
                        const url = '{{ route("pos.transactions.store") }}';
                        console.log('[POS] Sending to URL:', url);

                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload)
                        });

                        console.log('[POS] Response status:', response.status);
                        console.log('[POS] Response ok:', response.ok);

                        const responseText = await response.text();
                        console.log('[POS] Response text:', responseText);

                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('[POS] Failed to parse JSON:', parseError);
                            console.error('[POS] Raw response:', responseText);
                            alert('Error: Server returned invalid response');
                            return;
                        }

                        console.log('[POS] Parsed data:', data);

                        if (response.ok && data.success) {
                            console.log('[POS] Payment successful!');
                            this.lastSale = data.sale;
                            this.showPaymentModal = false;
                            this.showSuccessModal = true;
                        } else {
                            console.error('[POS] Payment failed:', data.error || data.message);
                            alert(data.error || data.message || 'Terjadi kesalahan');
                        }
                    } catch (error) {
                        console.error('[POS] Payment exception:', error);
                        console.error('[POS] Error name:', error.name);
                        console.error('[POS] Error message:', error.message);
                        console.error('[POS] Error stack:', error.stack);
                        alert('Terjadi kesalahan saat memproses pembayaran: ' + error.message);
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async printReceipt() {
                    if (!this.lastSale) return;

                    // Try QZ Tray first (thermal printer)
                    if (window.QZTrayPrinter && QZTrayPrinter.isConnected() && QZTrayPrinter.getPrinter()) {
                        try {
                            await QZTrayPrinter.printReceipt(this.lastSale.id);
                            console.log('[POS] Printed via QZ Tray');
                            return;
                        } catch (error) {
                            console.error('[POS] QZ Tray print failed:', error);
                            // Fall back to browser print
                        }
                    }

                    // Fallback to browser PDF print
                    window.open(`{{ url('pos/receipts') }}/${this.lastSale.id}/print`, '_blank');
                },

                newTransaction() {
                    this.showSuccessModal = false;
                    this.cart = [];
                    this.paidAmount = 0;
                    this.lastSale = null;
                    this.selectedCustomer = null;
                    this.$refs.searchInput.focus();
                },

                formatCurrency(amount) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
                },

                // Xendit Payment Methods
                async processXenditPayment() {
                    console.log('[XENDIT] processXenditPayment called');
                    console.log('[XENDIT] cart:', this.cart);
                    console.log('[XENDIT] isProcessing:', this.isProcessing);
                    console.log('[XENDIT] selectedPaymentMethod:', this.selectedPaymentMethod);
                    console.log('[XENDIT] total:', this.total);

                    if (this.cart.length === 0 || this.isProcessing) {
                        console.log('[XENDIT] Early return - cart empty or processing');
                        return;
                    }

                    this.isProcessing = true;
                    this.showPaymentModal = false;
                    this.showXenditModal = true;
                    this.xenditStatus = 'creating';

                    // First, create the sale with pending payment
                    const payload = {
                        items: this.cart.map(item => ({
                            product_id: item.product_id,
                            batch_id: item.batch_id,
                            quantity: item.quantity,
                            price: item.price,
                            discount: 0
                        })),
                        customer_id: this.selectedCustomer?.id || null,
                        discount: 0,
                        payments: [{
                            payment_method_id: this.selectedPaymentMethod,
                            amount: this.total
                        }],
                        notes: 'Pembayaran via Xendit'
                    };

                    console.log('[XENDIT] Sale payload:', JSON.stringify(payload, null, 2));

                    try {
                        // Create sale first
                        console.log('[XENDIT] Creating sale...');
                        const saleResponse = await fetch('{{ route("pos.transactions.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        console.log('[XENDIT] Sale response status:', saleResponse.status);
                        const saleDataText = await saleResponse.text();
                        console.log('[XENDIT] Sale response text:', saleDataText);

                        let saleData;
                        try {
                            saleData = JSON.parse(saleDataText);
                        } catch (e) {
                            console.error('[XENDIT] Failed to parse sale response:', e);
                            throw new Error('Invalid response from server: ' + saleDataText.substring(0, 200));
                        }

                        console.log('[XENDIT] Sale data:', saleData);

                        if (!saleResponse.ok || !saleData.success) {
                            throw new Error(saleData.error || saleData.message || 'Gagal membuat transaksi');
                        }

                        this.lastSale = saleData.sale;
                        console.log('[XENDIT] Sale created, ID:', saleData.sale.id);

                        // Create Xendit invoice
                        console.log('[XENDIT] Creating Xendit invoice...');
                        const xenditResponse = await fetch('{{ route("pos.xendit.invoice") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                sale_id: saleData.sale.id
                            })
                        });

                        console.log('[XENDIT] Xendit response status:', xenditResponse.status);
                        const xenditDataText = await xenditResponse.text();
                        console.log('[XENDIT] Xendit response text:', xenditDataText);

                        let xenditData;
                        try {
                            xenditData = JSON.parse(xenditDataText);
                        } catch (e) {
                            console.error('[XENDIT] Failed to parse xendit response:', e);
                            throw new Error('Invalid response from Xendit API: ' + xenditDataText.substring(0, 200));
                        }

                        console.log('[XENDIT] Xendit data:', xenditData);

                        if (!xenditResponse.ok || !xenditData.success) {
                            throw new Error(xenditData.message || xenditData.error || 'Gagal membuat invoice Xendit');
                        }

                        this.xenditInvoiceUrl = xenditData.data.invoice_url;
                        this.xenditTransactionId = xenditData.data.transaction_id;
                        this.xenditStatus = 'pending';

                        console.log('[XENDIT] Invoice created!');
                        console.log('[XENDIT] Invoice URL:', this.xenditInvoiceUrl);
                        console.log('[XENDIT] Transaction ID:', this.xenditTransactionId);

                        // Start polling for payment status
                        this.startXenditPoll();

                    } catch (error) {
                        console.error('[XENDIT] Payment error:', error);
                        console.error('[XENDIT] Error stack:', error.stack);
                        this.xenditStatus = 'failed';
                        alert('Error: ' + error.message);
                    } finally {
                        this.isProcessing = false;
                    }
                },

                startXenditPoll() {
                    // Poll every 5 seconds
                    this.xenditPollInterval = setInterval(() => {
                        this.checkXenditStatus();
                    }, 5000);
                },

                stopXenditPoll() {
                    if (this.xenditPollInterval) {
                        clearInterval(this.xenditPollInterval);
                        this.xenditPollInterval = null;
                    }
                },

                async checkXenditStatus() {
                    if (!this.xenditTransactionId) return;

                    try {
                        const response = await fetch(`{{ url('pos/xendit/status') }}/${this.xenditTransactionId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();

                        if (data.is_paid) {
                            this.stopXenditPoll();
                            this.xenditStatus = 'paid';

                            // Show success after delay
                            setTimeout(() => {
                                this.showXenditModal = false;
                                this.showSuccessModal = true;
                            }, 2000);
                        } else if (data.is_expired) {
                            this.stopXenditPoll();
                            this.xenditStatus = 'expired';
                        }
                    } catch (error) {
                        console.error('[POS] Check status error:', error);
                    }
                },

                closeXenditModal() {
                    this.stopXenditPoll();
                    this.showXenditModal = false;

                    // If payment was successful, show success modal
                    if (this.xenditStatus === 'paid') {
                        this.showSuccessModal = true;
                    }
                },

                retryXenditPayment() {
                    this.xenditStatus = null;
                    this.xenditInvoiceUrl = null;
                    this.xenditTransactionId = null;
                    this.showXenditModal = false;
                    this.showPaymentModal = true;
                }
            }
        }
    </script>
    @endpush
</x-pos-layout>
