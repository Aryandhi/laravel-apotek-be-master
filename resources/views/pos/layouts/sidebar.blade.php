<div class="flex h-full flex-col bg-gradient-to-b from-emerald-600 to-emerald-700">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center gap-3 px-6">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
            </svg>
        </div>
        <div>
            <h1 class="text-lg font-bold text-white">Apotek POS</h1>
            <p class="text-xs text-emerald-200">Point of Sale</p>
        </div>
    </div>

    <!-- Shift info -->
    @php
        $activeShift = \App\Models\CashierShift::where('user_id', auth()->id())
            ->where('status', \App\Enums\ShiftStatus::Open)
            ->first();
    @endphp
    @if($activeShift)
    <a href="{{ route('pos.shift.index') }}" class="mx-4 block rounded-lg bg-white/10 px-4 py-3 hover:bg-white/20 transition-colors">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="h-2 w-2 animate-pulse rounded-full bg-green-400"></div>
                <span class="text-xs font-medium text-emerald-100">Shift Aktif</span>
            </div>
            <span class="text-xs text-emerald-200">{{ $activeShift->opening_time->diffForHumans(null, true) }}</span>
        </div>
        <p class="mt-1 text-sm text-white">Modal: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</p>
    </a>
    @else
    <a href="{{ route('pos.shift.create') }}" class="mx-4 flex items-center gap-3 rounded-lg border-2 border-dashed border-white/30 px-4 py-3 text-emerald-100 hover:border-white/50 hover:bg-white/10 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span class="text-sm font-medium">Buka Shift</span>
    </a>
    @endif

    <!-- Navigation -->
    <nav class="mt-6 flex-1 space-y-1 px-3">
        <a href="{{ route('pos.dashboard') }}"
           class="{{ request()->routeIs('pos.dashboard') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Dashboard
        </a>

        <a href="{{ route('pos.transactions.create') }}"
           class="{{ request()->routeIs('pos.transactions.create') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
            </svg>
            Penjualan Baru
            <span class="ml-auto rounded bg-white/20 px-2 py-0.5 text-xs">F1</span>
        </a>

        <a href="{{ route('pos.products.index') }}"
           class="{{ request()->routeIs('pos.products.*') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            Cari Produk
            <span class="ml-auto rounded bg-white/20 px-2 py-0.5 text-xs">F2</span>
        </a>

        <a href="{{ route('pos.transactions.index') }}"
           class="{{ request()->routeIs('pos.transactions.index') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
            </svg>
            Riwayat Transaksi
        </a>

        <hr class="my-4 border-emerald-500/30">

        <a href="{{ route('pos.customers.index') }}"
           class="{{ request()->routeIs('pos.customers.*') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
            Pelanggan
        </a>

        <a href="{{ route('pos.shift.index') }}"
           class="{{ request()->routeIs('pos.shift.*') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Kelola Shift
        </a>

        <hr class="my-4 border-emerald-500/30">

        <a href="{{ route('pos.settings.printer') }}"
           class="{{ request()->routeIs('pos.settings.*') ? 'bg-white/20 text-white' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
            </svg>
            Pengaturan Printer
        </a>
    </nav>

    <!-- Footer -->
    <div class="border-t border-emerald-500/30 p-4">
        <a href="{{ url('/admin') }}"
           class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-emerald-200 hover:bg-white/10 hover:text-white transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
            </svg>
            Admin Panel
        </a>
    </div>
</div>
