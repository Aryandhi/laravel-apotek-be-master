<x-pos-layout title="Tutup Shift">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tutup Shift</h1>
            <p class="mt-1 text-sm text-slate-500">Rekap penjualan dan tutup shift Anda</p>
        </div>
        <a href="{{ route('pos.shift.report', $currentShift) }}" target="_blank"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
            Cetak Laporan
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Waktu Buka</p>
            <p class="mt-1 text-lg font-bold text-slate-900">{{ $currentShift->opening_time->format('H:i') }}</p>
            <p class="text-xs text-slate-500">{{ $currentShift->opening_time->format('d M Y') }}</p>
        </div>
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Durasi</p>
            <p class="mt-1 text-lg font-bold text-slate-900">{{ $currentShift->opening_time->diffForHumans(null, true) }}</p>
            <p class="text-xs text-slate-500">Shift aktif</p>
        </div>
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Transaksi</p>
            <p class="mt-1 text-lg font-bold text-emerald-600">{{ $salesSummary->total_transactions ?? 0 }}</p>
            <p class="text-xs text-slate-500">Transaksi selesai</p>
        </div>
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Penjualan</p>
            <p class="mt-1 text-lg font-bold text-emerald-600">Rp {{ number_format($salesSummary->total_sales ?? 0, 0, ',', '.') }}</p>
            @if(($salesSummary->total_discount ?? 0) > 0)
                <p class="text-xs text-slate-500">Diskon: Rp {{ number_format($salesSummary->total_discount, 0, ',', '.') }}</p>
            @else
                <p class="text-xs text-slate-500">Pendapatan kotor</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
        <!-- Main Content: 3 Columns on XL -->
        <div class="xl:col-span-3 space-y-6">
            <!-- Two Column Grid for Payment & Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Breakdown -->
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                    <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-4">Rincian Pembayaran</h3>
                    @if($paymentBreakdown->count() > 0)
                        <div class="space-y-3">
                            @foreach($paymentBreakdown as $payment)
                                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $payment->is_cash ? 'bg-emerald-100' : 'bg-blue-100' }}">
                                            @if($payment->is_cash)
                                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">{{ $payment->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $payment->transaction_count }} transaksi</p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-bold text-slate-900">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-slate-900">Total</span>
                                <span class="text-lg font-bold text-emerald-600">Rp {{ number_format($paymentBreakdown->sum('total_amount'), 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <svg class="h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                            <p class="text-sm text-slate-500">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>

                <!-- Top Products -->
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                    <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider mb-4">Produk Terlaris</h3>
                    @if($topProducts->count() > 0)
                        <div class="space-y-3">
                            @foreach($topProducts as $index => $product)
                                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $index < 3 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600' }} text-sm font-bold">{{ $index + 1 }}</span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate">{{ $product->name }}</p>
                                            <p class="text-xs text-slate-500">Rp {{ number_format($product->total_sales, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1">
                                        <span class="text-sm font-bold text-emerald-700">{{ $product->total_qty }}</span>
                                        <span class="text-xs text-emerald-600">pcs</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <svg class="h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                            <p class="text-sm text-slate-500">Belum ada produk terjual</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions - Full Width -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-slate-500 uppercase tracking-wider">Transaksi Terakhir</h3>
                    <span class="text-xs text-slate-400">{{ $recentSales->count() }} dari {{ $salesSummary->total_transactions ?? 0 }} transaksi</span>
                </div>
                @if($recentSales->count() > 0)
                    <div class="overflow-x-auto -mx-6">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden sm:table-cell">Pelanggan</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Pembayaran</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($recentSales as $sale)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-3">
                                            <span class="text-sm font-medium text-slate-900">{{ $sale->invoice_number }}</span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="text-sm text-slate-600">{{ $sale->created_at->format('H:i') }}</span>
                                        </td>
                                        <td class="px-6 py-3 hidden sm:table-cell">
                                            <span class="text-sm text-slate-600">{{ $sale->customer?->name ?? 'Umum' }}</span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($sale->payments as $payment)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $payment->paymentMethod->is_cash ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                                        {{ $payment->paymentMethod->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <span class="text-sm font-bold text-slate-900">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-16 w-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        <p class="text-sm text-slate-500">Belum ada transaksi pada shift ini</p>
                        <a href="{{ route('pos.transactions.create') }}" class="mt-3 text-sm font-medium text-emerald-600 hover:text-emerald-700">
                            Mulai Transaksi Pertama
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Sidebar: Close Form -->
        <div class="xl:col-span-1">
            <div class="sticky top-24 rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-4">
                    <h2 class="text-lg font-semibold text-white">Perhitungan Kas</h2>
                    <p class="text-sm text-slate-300">Hitung kas sebelum tutup shift</p>
                </div>

                <!-- Cash Calculation -->
                <div class="p-6 border-b border-slate-200 bg-slate-50">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Modal Awal</span>
                            <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($currentShift->opening_cash, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Penjualan Tunai</span>
                            <span class="text-sm font-semibold text-emerald-600">+ Rp {{ number_format($cashSalesTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-slate-300 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-slate-900">Kas Diharapkan</span>
                                <span class="text-xl font-bold text-slate-900">Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('pos.shift.close') }}" method="POST" class="p-6" x-data="{
                    actualCash: {{ old('actual_cash', $expectedCash) }},
                    expectedCash: {{ $expectedCash }},
                    get difference() { return this.actualCash - this.expectedCash },
                }">
                    @csrf

                    @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3">
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="space-y-4">
                        <!-- Actual Cash -->
                        <div>
                            <label for="actual_cash" class="block text-sm font-semibold text-slate-700 mb-2">
                                Kas Aktual <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-slate-500 font-medium">Rp</span>
                                </div>
                                <input type="number"
                                       name="actual_cash"
                                       id="actual_cash"
                                       x-model="actualCash"
                                       min="0"
                                       step="any"
                                       required
                                       class="block w-full rounded-lg border-slate-300 pl-12 py-3 text-xl font-bold focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Masukkan jumlah uang tunai di laci</p>
                        </div>

                        <!-- Difference Preview -->
                        <div class="rounded-lg p-4 transition-colors"
                             :class="difference == 0 ? 'bg-slate-100' : (difference > 0 ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200')">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold"
                                      :class="difference == 0 ? 'text-slate-700' : (difference > 0 ? 'text-emerald-700' : 'text-red-700')">
                                    Selisih
                                </span>
                                <span class="text-lg font-bold"
                                      :class="difference == 0 ? 'text-slate-700' : (difference > 0 ? 'text-emerald-700' : 'text-red-700')">
                                    <span x-text="difference >= 0 ? '+' : ''"></span>Rp <span x-text="new Intl.NumberFormat('id-ID').format(difference)"></span>
                                </span>
                            </div>
                            <p class="mt-1 text-xs"
                               :class="difference == 0 ? 'text-slate-500' : (difference > 0 ? 'text-emerald-600' : 'text-red-600')">
                                <span x-show="difference == 0">Kas sesuai dengan harapan</span>
                                <span x-show="difference > 0">Kas lebih dari yang diharapkan</span>
                                <span x-show="difference < 0">Kas kurang dari yang diharapkan</span>
                            </p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-slate-700 mb-2">
                                Catatan
                            </label>
                            <textarea name="notes"
                                      id="notes"
                                      rows="3"
                                      class="block w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                      placeholder="Catatan opsional untuk shift ini...">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Warning -->
                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4">
                            <div class="flex gap-3">
                                <svg class="h-5 w-5 text-amber-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-amber-800">Perhatian</p>
                                    <p class="mt-1 text-xs text-amber-700">Setelah shift ditutup, Anda tidak dapat melakukan transaksi hingga membuka shift baru.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 space-y-3">
                        <button type="submit"
                                class="w-full rounded-lg bg-red-600 px-4 py-3 text-sm font-bold text-white shadow-sm hover:bg-red-500 transition-colors flex items-center justify-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" />
                            </svg>
                            Tutup Shift
                        </button>
                        <a href="{{ route('pos.dashboard') }}"
                           class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 text-center hover:bg-slate-50 transition-colors flex items-center justify-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-pos-layout>
