<x-filament-widgets::widget>
    <x-filament::section class="h-full min-h-[22rem]">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/20">
                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stok Menipis</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($lowStockProductsCount, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="mt-5 border-t border-gray-100 pt-3 dark:border-white/10">
            @if($lowStockProductsCount > 0)
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-amber-700 dark:text-amber-400">
                        <span>Lihat detail stok menipis</span>
                        <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </summary>

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Ambang stok menipis: &lt; {{ $lowStockThreshold }}</p>

                    <div class="mt-3 max-h-72 overflow-auto rounded-lg border border-amber-100 bg-white dark:border-amber-500/30 dark:bg-gray-900">
                        <table class="min-w-full divide-y divide-amber-100 dark:divide-amber-500/20">
                            <thead class="bg-amber-50 dark:bg-amber-500/10">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Nama Obat</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">No Batch</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Stok</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-50 dark:divide-amber-500/10">
                                @foreach($lowStockItems as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $item->batch_number }}</td>
                                        <td class="px-3 py-2 text-right text-sm font-semibold text-gray-800 dark:text-white">{{ $item->stock }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Ambang stok menipis: &lt; {{ $lowStockThreshold }}</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
