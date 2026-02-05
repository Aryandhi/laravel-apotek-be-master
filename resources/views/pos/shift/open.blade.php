<x-pos-layout title="Buka Shift">
    <div class="mx-auto max-w-lg">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Buka Shift Baru</h2>
                <p class="mt-1 text-sm text-slate-500">Masukkan modal awal kas untuk memulai shift</p>
            </div>

            <form action="{{ route('pos.shift.store') }}" method="POST" class="p-6">
                @csrf

                @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <ul class="text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-6">
                    <!-- Opening Cash -->
                    <div>
                        <label for="opening_cash" class="block text-sm font-medium text-slate-700">
                            Modal Awal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-slate-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number"
                                   name="opening_cash"
                                   id="opening_cash"
                                   value="{{ old('opening_cash', 0) }}"
                                   min="0"
                                   step="any"
                                   required
                                   class="block w-full rounded-lg border-slate-300 pl-12 py-3 text-lg font-semibold text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                   placeholder="0">
                        </div>
                        <p class="mt-2 text-sm text-slate-500">Masukkan jumlah uang tunai di laci kas</p>
                    </div>

                    <!-- Quick Amount Buttons -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Cepat</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach([100000, 200000, 300000, 500000, 750000, 1000000] as $amount)
                            <button type="button"
                                    onclick="document.getElementById('opening_cash').value = {{ $amount }}"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                {{ number_format($amount / 1000) }}rb
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-700">
                            Catatan (Opsional)
                        </label>
                        <textarea name="notes"
                                  id="notes"
                                  rows="3"
                                  class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                                  placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Info -->
                    <div class="rounded-lg bg-blue-50 p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3 text-sm text-blue-700">
                                <p>Setelah shift dibuka, Anda dapat melakukan transaksi penjualan. Pastikan modal awal sesuai dengan uang fisik di laci kas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-end gap-3">
                    <a href="{{ route('pos.dashboard') }}"
                       class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition-colors">
                        Buka Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-pos-layout>
