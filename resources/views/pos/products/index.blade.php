<x-pos-layout title="Cari Produk">
    <div x-data="productSearch()" x-init="init()">
        <!-- Search Header -->
        <div class="mb-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-xl">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-model="search"
                           @input.debounce.300ms="fetchProducts()"
                           @keydown.enter.prevent="fetchProducts()"
                           class="block w-full rounded-xl border-slate-300 py-3 pl-11 pr-4 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                           placeholder="Cari nama produk, kode, barcode, atau nama generik...">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <kbd class="hidden rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500 sm:inline">F2</kbd>
                    </div>
                </div>

                <!-- Barcode Scanner Button -->
                <button type="button"
                        @click="openBarcodeScanner()"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                    </svg>
                    Scan Barcode
                </button>
            </div>

            <!-- Filters -->
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <select x-model="categoryId"
                        @change="fetchProducts()"
                        class="rounded-lg border-slate-300 py-2 pl-3 pr-8 text-sm text-slate-700 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox"
                           x-model="inStockOnly"
                           @change="fetchProducts()"
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Stok tersedia saja
                </label>

                <span class="text-sm text-slate-500" x-show="meta.total > 0">
                    <span x-text="meta.total"></span> produk ditemukan
                </span>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-12">
            <svg class="h-8 w-8 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Products Grid -->
        <div x-show="!loading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <template x-for="product in products" :key="product.id">
                <div @click="showProductDetail(product.id)"
                     class="cursor-pointer overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 hover:shadow-md hover:ring-emerald-500/20 transition-all">
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-slate-500" x-text="product.code"></p>
                                <h3 class="mt-1 font-medium text-slate-900 truncate" x-text="product.name"></h3>
                                <p class="text-sm text-slate-500 truncate" x-text="product.generic_name || '-'"></p>
                            </div>
                            <template x-if="product.requires_prescription">
                                <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                    Resep
                                </span>
                            </template>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <div>
                                <p class="text-lg font-bold text-emerald-600">
                                    Rp <span x-text="formatNumber(product.selling_price)"></span>
                                </p>
                                <p class="text-xs text-slate-500" x-text="'/' + (product.unit || 'pcs')"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium"
                                   :class="product.is_low_stock ? 'text-red-600' : 'text-slate-900'"
                                   x-text="product.total_stock + ' ' + (product.unit || 'pcs')"></p>
                                <p class="text-xs" :class="product.is_low_stock ? 'text-red-500' : 'text-slate-400'"
                                   x-text="product.is_low_stock ? 'Stok menipis' : 'Tersedia'"></p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600"
                                  x-text="product.category || 'Umum'"></span>
                            <button type="button"
                                    @click.stop="addToCart(product)"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-500 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && products.length === 0" class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <h3 class="mt-4 text-sm font-medium text-slate-900">Produk tidak ditemukan</h3>
            <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci pencarian atau filter</p>
        </div>

        <!-- Pagination -->
        <div x-show="!loading && meta.last_page > 1" class="mt-6 flex items-center justify-center gap-2">
            <button @click="prevPage()"
                    :disabled="meta.current_page === 1"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Sebelumnya
            </button>
            <span class="text-sm text-slate-500">
                Halaman <span x-text="meta.current_page"></span> dari <span x-text="meta.last_page"></span>
            </span>
            <button @click="nextPage()"
                    :disabled="meta.current_page === meta.last_page"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                Selanjutnya
            </button>
        </div>

        <!-- Product Detail Modal -->
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             x-cloak>
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="fixed inset-0 bg-slate-900/50 transition-opacity" @click="showModal = false"></div>

                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                    <template x-if="selectedProduct">
                        <div>
                            <div class="border-b border-slate-200 px-6 py-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-slate-500" x-text="selectedProduct.code"></p>
                                        <h3 class="text-lg font-semibold text-slate-900" x-text="selectedProduct.name"></h3>
                                        <p class="text-sm text-slate-500" x-text="selectedProduct.generic_name || '-'"></p>
                                    </div>
                                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="px-6 py-4">
                                <!-- Price & Stock -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-emerald-50 p-3">
                                        <p class="text-sm text-emerald-600">Harga Jual</p>
                                        <p class="text-xl font-bold text-emerald-700">Rp <span x-text="formatNumber(selectedProduct.selling_price)"></span></p>
                                        <p class="text-xs text-emerald-600" x-text="'per ' + (selectedProduct.unit || 'pcs')"></p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-sm text-slate-600">Total Stok</p>
                                        <p class="text-xl font-bold" :class="selectedProduct.is_low_stock ? 'text-red-600' : 'text-slate-900'" x-text="selectedProduct.total_stock"></p>
                                        <p class="text-xs text-slate-500" x-text="selectedProduct.unit || 'pcs'"></p>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="mt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Kategori</span>
                                        <span class="text-slate-900" x-text="selectedProduct.category || '-'"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Lokasi Rak</span>
                                        <span class="text-slate-900" x-text="selectedProduct.rack_location || '-'"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Barcode</span>
                                        <span class="text-slate-900 font-mono" x-text="selectedProduct.barcode || '-'"></span>
                                    </div>
                                    <template x-if="selectedProduct.requires_prescription">
                                        <div class="flex items-center gap-2 rounded-lg bg-red-50 p-2 text-sm text-red-700">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                            </svg>
                                            Memerlukan resep dokter
                                        </div>
                                    </template>
                                </div>

                                <!-- Batches -->
                                <template x-if="selectedProduct.batches && selectedProduct.batches.length > 0">
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-slate-900 mb-2">Batch Tersedia</h4>
                                        <div class="space-y-2 max-h-32 overflow-y-auto">
                                            <template x-for="batch in selectedProduct.batches" :key="batch.id">
                                                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                                    <div>
                                                        <span class="font-medium" x-text="batch.batch_number"></span>
                                                        <span class="text-slate-500 ml-2">Exp: <span x-text="batch.expired_date"></span></span>
                                                    </div>
                                                    <span class="font-medium" x-text="batch.stock + ' pcs'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Unit Conversions -->
                                <template x-if="selectedProduct.unit_conversions && selectedProduct.unit_conversions.length > 0">
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-slate-900 mb-2">Konversi Satuan</h4>
                                        <div class="space-y-2">
                                            <template x-for="conv in selectedProduct.unit_conversions" :key="conv.id">
                                                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                                    <span x-text="'1 ' + conv.unit_name + ' = ' + conv.conversion_value + ' ' + (selectedProduct.unit || 'pcs')"></span>
                                                    <span class="font-medium text-emerald-600" x-text="conv.selling_price ? 'Rp ' + formatNumber(conv.selling_price) : '-'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="border-t border-slate-200 px-6 py-4 flex justify-end gap-3">
                                <button @click="showModal = false"
                                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Tutup
                                </button>
                                <button @click="addToCart(selectedProduct); showModal = false"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                    Tambah ke Keranjang
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Barcode Scanner Modal -->
        <div x-show="showBarcodeModal"
             x-transition
             class="fixed inset-0 z-50 overflow-y-auto"
             x-cloak>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/50" @click="showBarcodeModal = false"></div>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-slate-900">Scan Barcode</h3>
                    <p class="mt-1 text-sm text-slate-500">Masukkan atau scan barcode produk</p>

                    <div class="mt-4">
                        <input type="text"
                               x-ref="barcodeInput"
                               x-model="barcodeValue"
                               @keydown.enter="searchByBarcode()"
                               class="block w-full rounded-lg border-slate-300 py-3 text-center text-lg font-mono tracking-wider focus:border-emerald-500 focus:ring-emerald-500"
                               placeholder="Scan atau ketik barcode..."
                               autofocus>
                    </div>

                    <div x-show="barcodeError" class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700" x-text="barcodeError"></div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showBarcodeModal = false"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>
                        <button @click="searchByBarcode()"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            Cari
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function productSearch() {
            return {
                search: '',
                categoryId: '',
                inStockOnly: false,
                products: [],
                meta: { current_page: 1, last_page: 1, total: 0 },
                loading: false,
                showModal: false,
                selectedProduct: null,
                showBarcodeModal: false,
                barcodeValue: '',
                barcodeError: '',

                init() {
                    this.fetchProducts();

                    // Keyboard shortcut F2
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'F2') {
                            e.preventDefault();
                            document.querySelector('input[type="text"]').focus();
                        }
                    });
                },

                async fetchProducts(page = 1) {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: page,
                            search: this.search,
                            category_id: this.categoryId,
                            in_stock: this.inStockOnly ? '1' : '0'
                        });

                        const response = await fetch(`{{ route('pos.products.search') }}?${params}`);
                        const data = await response.json();
                        this.products = data.data;
                        this.meta = data.meta;
                    } catch (error) {
                        console.error('Error fetching products:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async showProductDetail(productId) {
                    try {
                        const response = await fetch(`{{ url('pos/products') }}/${productId}`);
                        this.selectedProduct = await response.json();
                        this.showModal = true;
                    } catch (error) {
                        console.error('Error fetching product:', error);
                    }
                },

                openBarcodeScanner() {
                    this.showBarcodeModal = true;
                    this.barcodeValue = '';
                    this.barcodeError = '';
                    this.$nextTick(() => {
                        this.$refs.barcodeInput.focus();
                    });
                },

                async searchByBarcode() {
                    if (!this.barcodeValue) return;

                    this.barcodeError = '';
                    try {
                        const response = await fetch(`{{ route('pos.products.barcode') }}?barcode=${this.barcodeValue}`);
                        if (!response.ok) {
                            const data = await response.json();
                            this.barcodeError = data.error || 'Produk tidak ditemukan';
                            return;
                        }
                        const product = await response.json();
                        this.showBarcodeModal = false;
                        this.selectedProduct = product;
                        this.showModal = true;
                    } catch (error) {
                        this.barcodeError = 'Terjadi kesalahan saat mencari produk';
                    }
                },

                addToCart(product) {
                    console.log('[Products] addToCart called', product);

                    if (!product || !product.total_stock || product.total_stock <= 0) {
                        alert('Stok produk habis');
                        return;
                    }

                    // Get cart from localStorage
                    let cart = JSON.parse(localStorage.getItem('pos_cart') || '[]');

                    // Get batch info
                    const batch = product.batches && product.batches.length > 0 ? product.batches[0] : null;
                    const price = batch ? parseFloat(batch.selling_price) : parseFloat(product.selling_price || 0);
                    const batchId = batch ? batch.id : null;
                    const batchNumber = batch ? batch.batch_number : 'Auto';

                    // Check if product already in cart
                    const existingIndex = cart.findIndex(item => item.product_id === product.id);

                    if (existingIndex >= 0) {
                        if (cart[existingIndex].quantity < product.total_stock) {
                            cart[existingIndex].quantity++;
                            cart[existingIndex].subtotal = cart[existingIndex].quantity * cart[existingIndex].price;
                        } else {
                            alert('Stok tidak mencukupi');
                            return;
                        }
                    } else {
                        cart.push({
                            id: Date.now(),
                            product_id: product.id,
                            product_name: product.name,
                            batch_id: batchId,
                            batch_number: batchNumber,
                            requires_prescription: product.requires_prescription,
                            quantity: 1,
                            stock: product.total_stock,
                            unit: product.unit || 'pcs',
                            price: price,
                            subtotal: price
                        });
                    }

                    // Save to localStorage
                    localStorage.setItem('pos_cart', JSON.stringify(cart));

                    // Show success message
                    alert('Produk "' + product.name + '" berhasil ditambahkan ke keranjang!\n\nBuka menu Kasir untuk melanjutkan transaksi.');
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num);
                },

                prevPage() {
                    if (this.meta.current_page > 1) {
                        this.fetchProducts(this.meta.current_page - 1);
                    }
                },

                nextPage() {
                    if (this.meta.current_page < this.meta.last_page) {
                        this.fetchProducts(this.meta.current_page + 1);
                    }
                }
            }
        }
    </script>
    @endpush
</x-pos-layout>
