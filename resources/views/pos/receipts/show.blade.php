<x-pos-layout title="Struk {{ $sale->invoice_number }}">
    <div class="mx-auto max-w-2xl">
        <!-- Header Actions -->
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('pos.transactions.index') }}"
               class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900">
                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali
            </a>
            <div class="flex gap-2">
                <a href="{{ route('pos.receipts.print', $sale) }}" target="_blank"
                   class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                    </svg>
                    Cetak Struk
                </a>
                <a href="{{ route('pos.transactions.create') }}"
                   class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Transaksi Baru
                </a>
            </div>
        </div>

        <!-- Receipt Preview Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Preview Struk</h2>
                <p class="mt-1 text-sm text-slate-500">Tampilan struk untuk printer thermal 80mm</p>
            </div>

            <!-- Receipt Content -->
            <div class="p-6">
                <div class="mx-auto bg-white border border-slate-200 shadow-sm" style="width: 302px; font-family: 'Courier New', monospace; font-size: 12px;">
                    <div class="p-4">
                        <!-- Store Header -->
                        <div class="text-center mb-3">
                            <div class="font-bold text-base">{{ $store?->name ?? 'APOTEK' }}</div>
                            @if($store?->address)
                                <div class="text-xs">{{ $store->address }}</div>
                            @endif
                            @if($store?->phone)
                                <div class="text-xs">Telp: {{ $store->phone }}</div>
                            @endif
                            @if($store?->sia_number)
                                <div class="text-xs">SIA: {{ $store->sia_number }}</div>
                            @endif
                        </div>

                        <div class="border-t border-dashed border-slate-400 my-2"></div>

                        <!-- Invoice Info -->
                        <div class="text-xs space-y-0.5">
                            <div>No: {{ $sale->invoice_number }}</div>
                            <div>Tgl: {{ $sale->created_at->format('d/m/Y H:i') }}</div>
                            <div>Kasir: {{ $sale->user?->name ?? '-' }}</div>
                            @if($sale->customer)
                                <div>Pelanggan: {{ $sale->customer->name }}</div>
                            @endif
                        </div>

                        <div class="border-t border-dashed border-slate-400 my-2"></div>

                        <!-- Items -->
                        <div class="space-y-2">
                            @foreach($sale->items as $item)
                                <div>
                                    <div class="truncate">{{ $item->product?->name ?? 'Produk' }}</div>
                                    <div class="flex justify-between text-xs">
                                        <span class="pl-2">{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-dashed border-slate-400 my-2"></div>

                        <!-- Totals -->
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($sale->discount > 0)
                                <div class="flex justify-between">
                                    <span>Diskon</span>
                                    <span>- Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between font-bold border-t border-slate-300 pt-1">
                                <span>TOTAL</span>
                                <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-slate-400 my-2"></div>

                        <!-- Payments -->
                        <div class="space-y-1 text-xs">
                            @foreach($sale->payments as $payment)
                                <div class="flex justify-between">
                                    <span>{{ $payment->paymentMethod?->name ?? 'Tunai' }}</span>
                                    <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                            @if($sale->change_amount > 0)
                                <div class="flex justify-between">
                                    <span>Kembali</span>
                                    <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-dashed border-slate-400 my-2"></div>

                        <!-- Footer -->
                        <div class="text-center text-xs">
                            @if($store?->pharmacist_name)
                                <div>Apoteker: {{ $store->pharmacist_name }}</div>
                                @if($store->pharmacist_sipa)
                                    <div>SIPA: {{ $store->pharmacist_sipa }}</div>
                                @endif
                            @endif
                            <div class="mt-2">{{ $store?->receipt_footer ?? 'Terima Kasih' }}</div>
                            <div>Semoga Lekas Sembuh</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h3 class="font-semibold text-slate-900">Detail Transaksi</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $sale->status->color() === 'success' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $sale->status->color() === 'warning' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $sale->status->color() === 'danger' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $sale->status->color() === 'info' ? 'bg-blue-100 text-blue-700' : '' }}
                            ">
                                {{ $sale->status->label() }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Tipe</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            @if($sale->is_prescription)
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                    Resep Dokter
                                </span>
                            @else
                                <span class="text-slate-600">Penjualan Bebas</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Tanggal</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sale->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Kasir</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ $sale->user?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Pelanggan</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            @if($sale->customer)
                                <a href="{{ route('pos.customers.edit', $sale->customer) }}" class="text-emerald-600 hover:text-emerald-700">
                                    {{ $sale->customer->name }}
                                </a>
                                @if($sale->customer->phone)
                                    <span class="text-slate-500 text-xs">({{ $sale->customer->phone }})</span>
                                @endif
                            @else
                                <span class="text-slate-500">Umum</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if($sale->notes)
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <dt class="text-sm text-slate-500">Catatan</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $sale->notes }}</dd>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-pos-layout>
