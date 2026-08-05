<x-filament-widgets::widget>
    <x-filament::section class="h-full min-h-[22rem]">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 dark:bg-red-500/20">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kadaluarsa &lt; 30 Hari</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($expiringSoonCount, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="mt-5 border-t border-gray-100 pt-3 dark:border-white/10">
            @if($expiringSoonCount > 0)
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium text-red-700 dark:text-red-400">
                        <span>Lihat detail kadaluarsa</span>
                        <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </summary>

                    <div class="mt-3 max-h-72 overflow-auto rounded-lg border border-red-100 bg-white dark:border-red-500/30 dark:bg-gray-900">
                        <table class="min-w-full divide-y divide-red-100 dark:divide-red-500/20">
                            <thead class="bg-red-50 dark:bg-red-500/10">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">Nama Obat</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">No Batch</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">Stok</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">Tanggal Expired</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-50 dark:divide-red-500/10">
                                @foreach($expiringSoonItems as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $item->batch_number }}</td>
                                        <td class="px-3 py-2 text-right text-sm font-semibold text-gray-800 dark:text-white">{{ $item->stock }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $item->expired_date?->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ada item yang akan kadaluarsa dalam 30 hari.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
