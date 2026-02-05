<div class="space-y-6">
    {{-- Period Info --}}
    <div class="text-sm text-gray-500 dark:text-gray-400">
        Periode: {{ \Carbon\Carbon::parse($period['start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($period['end'])->format('d M Y') }}
    </div>

    {{-- Main Stats Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Penjualan --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-500/20">
                    <svg style="width: 24px; height: 24px;" class="text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Penjualan</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($sales['total'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-sm flex-wrap">
                <span class="text-gray-500 dark:text-gray-400">{{ number_format($sales['transactions']) }} transaksi</span>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="text-gray-500 dark:text-gray-400">Avg: Rp {{ number_format($sales['average'], 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Total Pembelian --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-500/20">
                    <svg style="width: 24px; height: 24px;" class="text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pembelian</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($purchases['total'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                {{ number_format($purchases['count']) }} purchase order
            </div>
        </div>

        {{-- Laba Kotor --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg {{ $profit['gross'] >= 0 ? 'bg-green-100 dark:bg-green-500/20' : 'bg-red-100 dark:bg-red-500/20' }}">
                    <svg style="width: 24px; height: 24px;" class="{{ $profit['gross'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Laba Kotor</p>
                    <p class="text-xl font-bold truncate {{ $profit['gross'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        Rp {{ number_format($profit['gross'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $profit['margin'] >= 20 ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : ($profit['margin'] >= 10 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400') }}">
                    Margin: {{ $profit['margin'] }}%
                </span>
            </div>
        </div>

        {{-- Nilai Stok --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-4">
                <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-500/20">
                    <svg style="width: 24px; height: 24px;" class="text-purple-600 dark:text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nilai Stok</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white truncate">Rp {{ number_format($stock['value'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-4 text-sm flex-wrap">
                @if($stock['low_count'] > 0)
                    <span class="inline-flex items-center gap-1 text-yellow-600 dark:text-yellow-400">
                        <svg style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                        </svg>
                        {{ $stock['low_count'] }} stok menipis
                    </span>
                @endif
                @if($stock['expiring_count'] > 0)
                    <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400">
                        <svg style="width: 16px; height: 16px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                        </svg>
                        {{ $stock['expiring_count'] }} kadaluarsa
                    </span>
                @endif
                @if($stock['low_count'] == 0 && $stock['expiring_count'] == 0)
                    <span class="text-gray-500 dark:text-gray-400">Stok aman</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Secondary Stats --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Top Products --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Produk Terlaris</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">5 produk dengan penjualan tertinggi</p>

            <div class="mt-4 space-y-3">
                @forelse($top_products as $index => $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                {{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $item->product?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->product?->code }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-2">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($item->total_qty) }} pcs</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data penjualan</p>
                @endforelse
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Ringkasan Keuangan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Overview keuangan periode ini</p>

            <div class="mt-4 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Penjualan</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($sales['total'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total Diskon</span>
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">- Rp {{ number_format($sales['discount'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <span class="text-sm text-gray-500 dark:text-gray-400">HPP (Estimasi)</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">Rp {{ number_format($sales['total'] - $profit['gross'], 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Laba Kotor</span>
                    <span class="text-sm font-bold {{ $profit['gross'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        Rp {{ number_format($profit['gross'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
