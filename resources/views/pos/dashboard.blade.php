<x-pos-layout title="Dashboard">
    <!-- Flash Messages -->
    @if (session('success'))
    <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 p-4">
        <div class="flex">
            <svg class="h-5 w-5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Shift Status Banner -->
    @if(!$currentShift)
    <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100">
                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-amber-800">Shift Belum Dibuka</h3>
                    <p class="text-sm text-amber-700">Buka shift terlebih dahulu untuk memulai transaksi</p>
                </div>
            </div>
            <a href="{{ route('pos.shift.create') }}"
               class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                Buka Shift
            </a>
        </div>
    </div>
    @else
    <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                    <div class="h-3 w-3 animate-pulse rounded-full bg-emerald-500"></div>
                </div>
                <div>
                    <h3 class="font-semibold text-emerald-800">Shift Aktif</h3>
                    <p class="text-sm text-emerald-700">Dimulai: {{ $currentShift->opening_time->format('d M Y, H:i') }} | Modal: Rp {{ number_format($currentShift->opening_cash, 0, ',', '.') }}</p>
                </div>
            </div>
            <a href="{{ route('pos.transactions.create') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                Mulai Transaksi
            </a>
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Today's Revenue -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-slate-500">Penjualan Hari Ini</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">
                            Rp {{ number_format($todaySales->total_revenue ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-emerald-600">{{ $todaySales->total_transactions ?? 0 }}</span>
                    <span class="text-slate-500">transaksi</span>
                </div>
            </div>
        </div>

        <!-- Current Shift Revenue -->
        @if($currentShift && $shiftSales)
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-slate-500">Shift Ini</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">
                            Rp {{ number_format($shiftSales->total_revenue ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-blue-600">{{ $shiftSales->total_transactions ?? 0 }}</span>
                    <span class="text-slate-500">transaksi</span>
                </div>
            </div>
        </div>
        @else
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $todaySales->total_transactions ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-3">
                <div class="text-sm text-slate-500">
                    Hari ini
                </div>
            </div>
        </div>
        @endif

        <!-- Low Stock -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100">
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-slate-500">Stok Menipis</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $lowStockProducts }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-3">
                <span class="text-sm text-slate-500">Produk perlu restock</span>
            </div>
        </div>

        <!-- Expiring -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-slate-500">Segera Expired</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $expiringBatches }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-5 py-3">
                <span class="text-sm text-slate-500">Dalam 30 hari</span>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Payment Breakdown -->
            @if($todayPayments->count() > 0)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Pembayaran Hari Ini</h3>
                <div class="space-y-3">
                    @foreach($todayPayments as $payment)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $payment->is_cash ? 'bg-emerald-100' : 'bg-blue-100' }}">
                                    @if($payment->is_cash)
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-sm font-medium text-slate-900">{{ $payment->name }}</span>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Transactions -->
            @if($recentSales->count() > 0)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">Transaksi Terakhir</h3>
                    <a href="{{ route('pos.transactions.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr>
                                <th class="py-2 text-left text-xs font-medium text-slate-500 uppercase">Invoice</th>
                                <th class="py-2 text-left text-xs font-medium text-slate-500 uppercase">Waktu</th>
                                <th class="py-2 text-left text-xs font-medium text-slate-500 uppercase">Pembayaran</th>
                                <th class="py-2 text-right text-xs font-medium text-slate-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentSales as $sale)
                                <tr>
                                    <td class="py-3 text-sm font-medium text-slate-900">{{ $sale->invoice_number }}</td>
                                    <td class="py-3 text-sm text-slate-500">{{ $sale->created_at->format('H:i') }}</td>
                                    <td class="py-3 text-sm text-slate-500">{{ $sale->payments->pluck('paymentMethod.name')->join(', ') }}</td>
                                    <td class="py-3 text-sm font-medium text-slate-900 text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Top Products -->
            @if($topProducts->count() > 0)
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Produk Terlaris</h3>
                <div class="space-y-3">
                    @foreach($topProducts as $index => $product)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-medium text-emerald-600">{{ $index + 1 }}</span>
                                <span class="text-sm font-medium text-slate-900">{{ Str::limit($product->name, 25) }}</span>
                            </div>
                            <span class="text-sm text-slate-500">{{ $product->total_qty }} pcs</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="{{ route('pos.transactions.create') }}"
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Transaksi Baru</p>
                            <p class="text-xs text-slate-500">F1</p>
                        </div>
                    </a>
                    <a href="{{ route('pos.products.index') }}"
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Cari Produk</p>
                            <p class="text-xs text-slate-500">F2</p>
                        </div>
                    </a>
                    <a href="{{ route('pos.transactions.index') }}"
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100">
                            <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Riwayat Transaksi</p>
                            <p class="text-xs text-slate-500">F4</p>
                        </div>
                    </a>
                    @if($currentShift)
                    <a href="{{ route('pos.shift.close') }}"
                       class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-red-50 transition-colors">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100">
                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Tutup Shift</p>
                            <p class="text-xs text-slate-500">Akhiri shift kerja</p>
                        </div>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-pos-layout>
