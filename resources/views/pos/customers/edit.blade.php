<x-pos-layout title="Edit Pelanggan">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('pos.customers.index') }}"
               class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Edit Pelanggan</h1>
                <p class="text-sm text-slate-500">{{ $customer->name }}</p>
            </div>
        </div>

        <!-- Stats Card -->
        <div class="mb-6 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $customer->sales()->count() }}</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Transaksi</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 text-center">
                <p class="text-lg font-bold text-emerald-600">Rp {{ number_format($customer->sales()->sum('total'), 0, ',', '.') }}</p>
                <p class="text-xs font-medium text-slate-500 mt-1">Total Belanja</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <form action="{{ route('pos.customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-5">
                    @if ($errors->any())
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4">
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan:</h3>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $customer->name) }}"
                               required
                               class="block w-full rounded-lg border-slate-300 shadow-sm text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('name')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone & Email Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1.5">
                                No. Telepon
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                    </svg>
                                </div>
                                <input type="tel"
                                       name="phone"
                                       id="phone"
                                       value="{{ old('phone', $customer->phone) }}"
                                       placeholder="08xxxxxxxxxx"
                                       class="block w-full rounded-lg border-slate-300 pl-10 shadow-sm text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Email
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       value="{{ old('email', $customer->email) }}"
                                       placeholder="email@contoh.com"
                                       class="block w-full rounded-lg border-slate-300 pl-10 shadow-sm text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                    </div>

                    <!-- Birth Date -->
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tanggal Lahir
                        </label>
                        <div class="relative sm:max-w-xs">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <input type="date"
                                   name="birth_date"
                                   id="birth_date"
                                   value="{{ old('birth_date', $customer->birth_date?->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="block w-full rounded-lg border-slate-300 pl-10 shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Alamat
                        </label>
                        <textarea name="address"
                                  id="address"
                                  rows="3"
                                  placeholder="Alamat lengkap pelanggan (opsional)"
                                  class="block w-full rounded-lg border-slate-300 shadow-sm text-sm placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500">{{ old('address', $customer->address) }}</textarea>
                    </div>
                </div>

                <!-- Form Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 rounded-b-xl">
                    <a href="{{ route('pos.customers.index') }}"
                       class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-pos-layout>
