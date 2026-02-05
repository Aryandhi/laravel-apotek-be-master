<x-pos-layout title="Riwayat Transaksi">
    <div x-data="transactionHistory()" x-init="loadTransactions()" class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Riwayat Transaksi</h1>
                <p class="mt-1 text-sm text-slate-500">Daftar transaksi pada shift saat ini</p>
            </div>
            <a href="{{ route('pos.transactions.create') }}"
               class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Transaksi Baru
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input type="text"
                               x-model="searchQuery"
                               @input.debounce.300ms="loadTransactions"
                               class="block w-full rounded-lg border-slate-300 pl-10 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                               placeholder="Cari invoice atau pelanggan...">
                    </div>
                </div>
                <button type="button"
                        @click="loadTransactions"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Pelanggan</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-if="loading">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-8 w-8 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="mt-2 text-sm text-slate-500">Memuat data...</p>
                                </td>
                            </tr>
                        </template>

                        <template x-if="!loading && transactions.length === 0">
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-slate-900">Belum ada transaksi</p>
                                    <p class="mt-1 text-sm text-slate-500">Mulai transaksi baru untuk melihat riwayat</p>
                                </td>
                            </tr>
                        </template>

                        <template x-for="transaction in transactions" :key="transaction.id">
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-900" x-text="transaction.invoice_number"></span>
                                        <template x-if="transaction.is_prescription">
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Resep</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500" x-text="transaction.time"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900" x-text="transaction.customer"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 text-right" x-text="formatCurrency(transaction.total)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="{
                                              'bg-emerald-100 text-emerald-700': transaction.status_color === 'success',
                                              'bg-amber-100 text-amber-700': transaction.status_color === 'warning',
                                              'bg-red-100 text-red-700': transaction.status_color === 'danger',
                                              'bg-blue-100 text-blue-700': transaction.status_color === 'info'
                                          }"
                                          x-text="transaction.status">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <button type="button"
                                            @click="viewDetail(transaction.id)"
                                            class="text-emerald-600 hover:text-emerald-900 font-medium">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <template x-if="meta.last_page > 1">
                <div class="border-t border-slate-200 bg-slate-50 px-6 py-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-500">
                            Halaman <span x-text="meta.current_page"></span> dari <span x-text="meta.last_page"></span>
                            (<span x-text="meta.total"></span> transaksi)
                        </p>
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="prevPage"
                                    :disabled="meta.current_page <= 1"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Prev
                            </button>
                            <button type="button"
                                    @click="nextPage"
                                    :disabled="meta.current_page >= meta.last_page"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Detail Modal -->
        <div x-show="showDetailModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/50" @click="showDetailModal = false"></div>
                <div x-show="showDetailModal"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative w-full max-w-2xl transform rounded-xl bg-white shadow-2xl">

                    <template x-if="detailLoading">
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-8 w-8 animate-spin text-emerald-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </template>

                    <template x-if="!detailLoading && saleDetail">
                        <div>
                            <div class="border-b border-slate-200 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900" x-text="'Invoice: ' + saleDetail.invoice_number"></h3>
                                        <p class="mt-1 text-sm text-slate-500" x-text="saleDetail.date"></p>
                                    </div>
                                    <button type="button" @click="showDetailModal = false" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-500">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="p-6 space-y-6">
                                <!-- Customer & Cashier Info -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-xs font-medium text-slate-500 uppercase">Pelanggan</p>
                                        <p class="mt-1 font-medium text-slate-900" x-text="saleDetail.customer || 'Umum'"></p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 p-3">
                                        <p class="text-xs font-medium text-slate-500 uppercase">Kasir</p>
                                        <p class="mt-1 font-medium text-slate-900" x-text="saleDetail.cashier"></p>
                                    </div>
                                </div>

                                <!-- Items -->
                                <div>
                                    <h4 class="text-sm font-medium text-slate-700 mb-3">Item Pembelian</h4>
                                    <div class="rounded-lg border border-slate-200 overflow-hidden">
                                        <table class="min-w-full divide-y divide-slate-200">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-500">Produk</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-slate-500">Qty</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-slate-500">Harga</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-slate-500">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200">
                                                <template x-for="item in saleDetail.items" :key="item.product_name">
                                                    <tr>
                                                        <td class="px-4 py-2">
                                                            <p class="text-sm font-medium text-slate-900" x-text="item.product_name"></p>
                                                            <p class="text-xs text-slate-500" x-text="'Batch: ' + item.batch_number"></p>
                                                        </td>
                                                        <td class="px-4 py-2 text-center text-sm text-slate-900" x-text="item.quantity + ' ' + (item.unit || '')"></td>
                                                        <td class="px-4 py-2 text-right text-sm text-slate-900" x-text="formatCurrency(item.price)"></td>
                                                        <td class="px-4 py-2 text-right text-sm font-medium text-slate-900" x-text="formatCurrency(item.subtotal)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div class="rounded-lg bg-slate-50 p-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Subtotal</span>
                                        <span class="font-medium text-slate-900" x-text="formatCurrency(saleDetail.subtotal)"></span>
                                    </div>
                                    <template x-if="saleDetail.discount > 0">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-slate-600">Diskon</span>
                                            <span class="font-medium text-red-600" x-text="'- ' + formatCurrency(saleDetail.discount)"></span>
                                        </div>
                                    </template>
                                    <div class="flex justify-between pt-2 border-t border-slate-200">
                                        <span class="font-semibold text-slate-900">Total</span>
                                        <span class="text-lg font-bold text-emerald-600" x-text="formatCurrency(saleDetail.total)"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Dibayar</span>
                                        <span class="font-medium text-slate-900" x-text="formatCurrency(saleDetail.paid_amount)"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-600">Kembalian</span>
                                        <span class="font-medium text-emerald-600" x-text="formatCurrency(saleDetail.change_amount)"></span>
                                    </div>
                                </div>

                                <!-- Payment Methods -->
                                <div>
                                    <h4 class="text-sm font-medium text-slate-700 mb-2">Metode Pembayaran</h4>
                                    <template x-for="payment in saleDetail.payments" :key="payment.method">
                                        <div class="flex justify-between text-sm py-1">
                                            <span class="text-slate-600" x-text="payment.method"></span>
                                            <span class="font-medium text-slate-900" x-text="formatCurrency(payment.amount)"></span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Notes -->
                                <template x-if="saleDetail.notes">
                                    <div>
                                        <h4 class="text-sm font-medium text-slate-700 mb-2">Catatan</h4>
                                        <p class="text-sm text-slate-600" x-text="saleDetail.notes"></p>
                                    </div>
                                </template>
                            </div>

                            <div class="border-t border-slate-200 px-6 py-4 flex justify-end gap-3">
                                <button type="button"
                                        @click="printReceipt(saleDetail.id)"
                                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    <svg class="inline h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                                    </svg>
                                    Cetak Struk
                                </button>
                                <button type="button"
                                        @click="showDetailModal = false"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function transactionHistory() {
            return {
                searchQuery: '',
                transactions: [],
                meta: { current_page: 1, last_page: 1, total: 0 },
                loading: false,
                currentPage: 1,
                showDetailModal: false,
                saleDetail: null,
                detailLoading: false,

                async loadTransactions() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: this.currentPage,
                            search: this.searchQuery
                        });
                        const response = await fetch(`{{ route('pos.transactions.history') }}?${params}`);
                        const data = await response.json();
                        this.transactions = data.data;
                        this.meta = data.meta;
                    } catch (error) {
                        console.error('Error loading transactions:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                nextPage() {
                    if (this.currentPage < this.meta.last_page) {
                        this.currentPage++;
                        this.loadTransactions();
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.loadTransactions();
                    }
                },

                async viewDetail(id) {
                    this.showDetailModal = true;
                    this.detailLoading = true;
                    this.saleDetail = null;

                    try {
                        const response = await fetch(`{{ url('pos/transactions') }}/${id}`);
                        this.saleDetail = await response.json();
                    } catch (error) {
                        console.error('Error loading detail:', error);
                    } finally {
                        this.detailLoading = false;
                    }
                },

                printReceipt(id) {
                    window.open(`{{ url('pos/receipts') }}/${id}/print`, '_blank');
                },

                formatCurrency(amount) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
                }
            }
        }
    </script>
    @endpush
</x-pos-layout>
