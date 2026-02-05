<x-pos-layout title="Pelanggan">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Daftar Pelanggan</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola data pelanggan apotek</p>
        </div>
        <a href="{{ route('pos.customers.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            Tambah Pelanggan
        </a>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <form action="{{ route('pos.customers.index') }}" method="GET" class="flex gap-3">
            <div class="relative flex-1 max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari nama, telepon, atau email..."
                       class="block w-full rounded-lg border-slate-300 pl-10 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <button type="submit"
                    class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200 transition-colors">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('pos.customers.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

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

    <!-- Customer Table -->
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5 overflow-hidden">
        @if($customers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Pelanggan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Kontak</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider hidden sm:table-cell">Transaksi</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Total Belanja</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($customers as $customer)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                                            <span class="text-sm font-semibold text-emerald-700">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $customer->name }}</p>
                                            @if($customer->birth_date)
                                                <p class="text-xs text-slate-500">{{ $customer->birth_date->format('d M Y') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell">
                                    <div class="space-y-1">
                                        @if($customer->phone)
                                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                                </svg>
                                                {{ $customer->phone }}
                                            </div>
                                        @endif
                                        @if($customer->email)
                                            <div class="flex items-center gap-2 text-sm text-slate-600">
                                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                                </svg>
                                                {{ $customer->email }}
                                            </div>
                                        @endif
                                        @if(!$customer->phone && !$customer->email)
                                            <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center hidden sm:table-cell">
                                    <span class="text-sm font-medium text-slate-900">{{ $customer->sales_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-right hidden lg:table-cell">
                                    <span class="text-sm font-semibold text-slate-900">Rp {{ number_format($customer->sales_sum_total ?? 0, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                onclick="showCustomerDetail({{ $customer->id }})"
                                                class="rounded-lg p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="Lihat Detail">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('pos.customers.edit', $customer) }}"
                                           class="rounded-lg p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                                           title="Edit">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $customers->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="h-16 w-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                @if(request('search'))
                    <p class="text-slate-500 mb-2">Tidak ada pelanggan yang cocok dengan "{{ request('search') }}"</p>
                    <a href="{{ route('pos.customers.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                        Lihat semua pelanggan
                    </a>
                @else
                    <p class="text-slate-500 mb-4">Belum ada data pelanggan</p>
                    <a href="{{ route('pos.customers.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Pelanggan Pertama
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Customer Detail Modal -->
    <div id="customerModal" class="fixed inset-0 z-50 hidden" x-data="{ open: false }">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeCustomerModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg transform rounded-xl bg-white shadow-xl transition-all">
                    <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-900">Detail Pelanggan</h3>
                        <button type="button" onclick="closeCustomerModal()" class="rounded-lg p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div id="customerModalContent" class="p-6">
                        <div class="flex justify-center py-8">
                            <svg class="animate-spin h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showCustomerDetail(customerId) {
            const modal = document.getElementById('customerModal');
            const content = document.getElementById('customerModalContent');

            modal.classList.remove('hidden');
            content.innerHTML = `
                <div class="flex justify-center py-8">
                    <svg class="animate-spin h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            `;

            fetch(`/pos/customers/${customerId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(customer => {
                content.innerHTML = `
                    <div class="space-y-6">
                        <!-- Customer Info -->
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                                <span class="text-2xl font-bold text-emerald-700">${customer.name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div>
                                <h4 class="text-xl font-bold text-slate-900">${customer.name}</h4>
                                <p class="text-sm text-slate-500">${customer.birth_date ? 'Lahir: ' + customer.birth_date : ''}</p>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-500 uppercase">Telepon</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">${customer.phone || '-'}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-medium text-slate-500 uppercase">Email</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">${customer.email || '-'}</p>
                            </div>
                        </div>

                        ${customer.address ? `
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-medium text-slate-500 uppercase">Alamat</p>
                            <p class="mt-1 text-sm text-slate-900">${customer.address}</p>
                        </div>
                        ` : ''}

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="rounded-lg bg-blue-50 p-4 text-center">
                                <p class="text-2xl font-bold text-blue-700">${customer.sales_count}</p>
                                <p class="text-xs font-medium text-blue-600">Transaksi</p>
                            </div>
                            <div class="rounded-lg bg-emerald-50 p-4 text-center">
                                <p class="text-lg font-bold text-emerald-700">Rp ${Number(customer.total_spent).toLocaleString('id-ID')}</p>
                                <p class="text-xs font-medium text-emerald-600">Total Belanja</p>
                            </div>
                        </div>

                        ${customer.recent_sales.length > 0 ? `
                        <!-- Recent Transactions -->
                        <div>
                            <h5 class="text-sm font-semibold text-slate-700 mb-3">Transaksi Terakhir</h5>
                            <div class="space-y-2">
                                ${customer.recent_sales.map(sale => `
                                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                                        <div>
                                            <p class="text-sm font-medium text-slate-900">${sale.invoice_number}</p>
                                            <p class="text-xs text-slate-500">${sale.date}</p>
                                        </div>
                                        <span class="text-sm font-semibold text-slate-900">Rp ${Number(sale.total).toLocaleString('id-ID')}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}

                        <div class="flex gap-3">
                            <a href="/pos/customers/${customer.id}/edit" class="flex-1 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white text-center hover:bg-emerald-700 transition-colors">
                                Edit Pelanggan
                            </a>
                            <button type="button" onclick="closeCustomerModal()" class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                Tutup
                            </button>
                        </div>
                    </div>
                `;
            })
            .catch(err => {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-600">Gagal memuat data pelanggan</p>
                    </div>
                `;
            });
        }

        function closeCustomerModal() {
            document.getElementById('customerModal').classList.add('hidden');
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeCustomerModal();
        });
    </script>
    @endpush
</x-pos-layout>
