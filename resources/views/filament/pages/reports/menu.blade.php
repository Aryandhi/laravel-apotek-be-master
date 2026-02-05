<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
    {{-- Laporan Penjualan --}}
    <a href="{{ route('filament.admin.pages.reports.sales') }}"
       class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-emerald-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-emerald-500/30">
        <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-emerald-100 transition group-hover:bg-emerald-200 dark:bg-emerald-500/20 dark:group-hover:bg-emerald-500/30">
                <svg style="width: 24px; height: 24px;" class="text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Penjualan</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data transaksi penjualan</p>
            </div>
        </div>
    </a>

    {{-- Laporan Stok --}}
    <a href="{{ route('filament.admin.pages.reports.stock') }}"
       class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-blue-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-blue-500/30">
        <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-blue-100 transition group-hover:bg-blue-200 dark:bg-blue-500/20 dark:group-hover:bg-blue-500/30">
                <svg style="width: 24px; height: 24px;" class="text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Stok</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Stok & kadaluarsa produk</p>
            </div>
        </div>
    </a>

    {{-- Laporan Laba Rugi --}}
    <a href="{{ route('filament.admin.pages.reports.profit-loss') }}"
       class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-purple-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-purple-500/30">
        <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-purple-100 transition group-hover:bg-purple-200 dark:bg-purple-500/20 dark:group-hover:bg-purple-500/30">
                <svg style="width: 24px; height: 24px;" class="text-purple-600 dark:text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Laba Rugi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Analisis keuntungan per produk</p>
            </div>
        </div>
    </a>

    {{-- Laporan Pembelian --}}
    <a href="{{ route('filament.admin.pages.reports.purchase') }}"
       class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-orange-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-orange-500/30">
        <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-orange-100 transition group-hover:bg-orange-200 dark:bg-orange-500/20 dark:group-hover:bg-orange-500/30">
                <svg style="width: 24px; height: 24px;" class="text-orange-600 dark:text-orange-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Pembelian</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Data pembelian & hutang</p>
            </div>
        </div>
    </a>

    {{-- Produk Terlaris --}}
    <a href="{{ route('filament.admin.pages.reports.top-products') }}"
       class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-yellow-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-yellow-500/30">
        <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-yellow-100 transition group-hover:bg-yellow-200 dark:bg-yellow-500/20 dark:group-hover:bg-yellow-500/30">
                <svg style="width: 24px; height: 24px;" class="text-yellow-600 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Produk Terlaris</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ranking produk penjualan</p>
            </div>
        </div>
    </a>

    {{-- Mutasi Stok --}}
    <a href="{{ route('filament.admin.pages.reports.stock-movement') }}"
       class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-cyan-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-cyan-500/30">
        <div class="flex items-center gap-4">
            <div style="width: 48px; height: 48px; min-width: 48px;" class="flex items-center justify-center rounded-lg bg-cyan-100 transition group-hover:bg-cyan-200 dark:bg-cyan-500/20 dark:group-hover:bg-cyan-500/30">
                <svg style="width: 24px; height: 24px;" class="text-cyan-600 dark:text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Mutasi Stok</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Riwayat pergerakan stok</p>
            </div>
        </div>
    </a>
</div>
