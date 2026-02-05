<x-filament-widgets::widget>
    <x-filament::section heading="Menu Laporan">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Laporan Penjualan --}}
            <a href="{{ route('filament.admin.pages.reports.sales') }}"
               class="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-emerald-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-emerald-500/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 transition group-hover:bg-emerald-200 dark:bg-emerald-500/20 dark:group-hover:bg-emerald-500/30">
                        <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Laporan Penjualan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Data transaksi penjualan</p>
                    </div>
                </div>
            </a>

            {{-- Laporan Stok --}}
            <a href="{{ route('filament.admin.pages.reports.stock') }}"
               class="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-blue-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-blue-500/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 transition group-hover:bg-blue-200 dark:bg-blue-500/20 dark:group-hover:bg-blue-500/30">
                        <x-heroicon-o-cube class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Laporan Stok</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Stok & kadaluarsa produk</p>
                    </div>
                </div>
            </a>

            {{-- Laporan Laba Rugi --}}
            <a href="{{ route('filament.admin.pages.reports.profit-loss') }}"
               class="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-purple-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-purple-500/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-purple-100 transition group-hover:bg-purple-200 dark:bg-purple-500/20 dark:group-hover:bg-purple-500/30">
                        <x-heroicon-o-chart-bar class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Laporan Laba Rugi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Analisis keuntungan</p>
                    </div>
                </div>
            </a>

            {{-- Laporan Pembelian --}}
            <a href="{{ route('filament.admin.pages.reports.purchase') }}"
               class="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-orange-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-orange-500/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-orange-100 transition group-hover:bg-orange-200 dark:bg-orange-500/20 dark:group-hover:bg-orange-500/30">
                        <x-heroicon-o-shopping-cart class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Laporan Pembelian</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Data pembelian & hutang</p>
                    </div>
                </div>
            </a>

            {{-- Produk Terlaris --}}
            <a href="{{ route('filament.admin.pages.reports.top-products') }}"
               class="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-yellow-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-yellow-500/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-yellow-100 transition group-hover:bg-yellow-200 dark:bg-yellow-500/20 dark:group-hover:bg-yellow-500/30">
                        <x-heroicon-o-star class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Produk Terlaris</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ranking penjualan</p>
                    </div>
                </div>
            </a>

            {{-- Mutasi Stok --}}
            <a href="{{ route('filament.admin.pages.reports.stock-movement') }}"
               class="group rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 transition hover:shadow-md hover:ring-cyan-500/20 dark:bg-gray-900 dark:ring-white/10 dark:hover:ring-cyan-500/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-100 transition group-hover:bg-cyan-200 dark:bg-cyan-500/20 dark:group-hover:bg-cyan-500/30">
                        <x-heroicon-o-arrows-right-left class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Mutasi Stok</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat pergerakan stok</p>
                    </div>
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
