<x-pos-layout title="Kelola Shift">
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

    @if (session('error'))
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
        <div class="flex">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            <p class="ml-3 text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- Current Shift Status -->
    @if($currentShift)
    <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
        <div class="border-b border-slate-200 bg-emerald-50 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                        <div class="h-3 w-3 animate-pulse rounded-full bg-emerald-500"></div>
                    </div>
                    <div>
                        <h2 class="font-semibold text-emerald-800">Shift Aktif</h2>
                        <p class="text-sm text-emerald-600">Dimulai {{ $currentShift->opening_time->diffForHumans() }}</p>
                    </div>
                </div>
                <a href="{{ route('pos.shift.close') }}"
                   class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500 transition-colors">
                    Tutup Shift
                </a>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-sm text-slate-500">Waktu Buka</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $currentShift->opening_time->format('H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Modal Awal</p>
                    <p class="mt-1 font-semibold text-slate-900">Rp {{ number_format($currentShift->opening_cash, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Kas Saat Ini</p>
                    <p class="mt-1 font-semibold text-emerald-600">Rp {{ number_format($currentShift->calculateExpectedCash(), 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Durasi</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $currentShift->opening_time->diffForHumans(null, true) }}</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
        <div class="p-8 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">Tidak Ada Shift Aktif</h3>
            <p class="mt-2 text-sm text-slate-500">Buka shift baru untuk mulai melakukan transaksi</p>
            <a href="{{ route('pos.shift.create') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Buka Shift Baru
            </a>
        </div>
    </div>
    @endif

    <!-- Recent Shifts -->
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Shift</h2>
        </div>

        @if($recentShifts->isEmpty())
        <div class="p-8 text-center">
            <p class="text-sm text-slate-500">Belum ada riwayat shift</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Modal Awal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Kas Akhir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Selisih</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @foreach($recentShifts as $shift)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900">
                            {{ $shift->opening_time->format('d M Y') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">
                            {{ $shift->opening_time->format('H:i') }}
                            @if($shift->closing_time)
                            - {{ $shift->closing_time->format('H:i') }}
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900">
                            Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-900">
                            @if($shift->actual_cash)
                            Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if($shift->difference !== null)
                                @if($shift->difference == 0)
                                <span class="text-slate-500">Rp 0</span>
                                @elseif($shift->difference > 0)
                                <span class="text-emerald-600">+Rp {{ number_format($shift->difference, 0, ',', '.') }}</span>
                                @else
                                <span class="text-red-600">-Rp {{ number_format(abs($shift->difference), 0, ',', '.') }}</span>
                                @endif
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                            @if($shift->status->value === 'open')
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                Selesai
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-pos-layout>
