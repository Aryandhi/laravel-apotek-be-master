<x-pos-layout title="Pengaturan Printer">
    <div x-data="printerSettings()" x-init="init()" class="max-w-2xl mx-auto">
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <!-- Header -->
            <div class="border-b border-slate-200 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100">
                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-slate-900">Pengaturan Printer Thermal</h2>
                        <p class="text-sm text-slate-500">Konfigurasi printer untuk cetak struk</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- QZ Tray Status -->
                <div class="rounded-lg border p-4" :class="qzConnected ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full" :class="qzConnected ? 'bg-emerald-100' : 'bg-amber-100'">
                                <template x-if="qzConnected">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </template>
                                <template x-if="!qzConnected">
                                    <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                </template>
                            </div>
                            <div>
                                <p class="font-medium" :class="qzConnected ? 'text-emerald-900' : 'text-amber-900'">
                                    QZ Tray <span x-text="qzConnected ? 'Terhubung' : 'Tidak Terhubung'"></span>
                                </p>
                                <p class="text-sm" :class="qzConnected ? 'text-emerald-700' : 'text-amber-700'" x-show="!qzConnected">
                                    Pastikan QZ Tray sudah terinstall dan berjalan
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="reconnect"
                                    :disabled="connecting"
                                    class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors"
                                    :class="qzConnected ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-100' : 'border-amber-300 text-amber-700 hover:bg-amber-100'">
                                <svg class="h-4 w-4" :class="connecting ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                <span x-text="connecting ? 'Menghubungkan...' : 'Hubungkan Ulang'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- QZ Tray Download Link -->
                <div x-show="!qzConnected" class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                    <div class="flex items-start gap-3">
                        <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <div>
                            <p class="font-medium text-blue-900">Belum punya QZ Tray?</p>
                            <p class="text-sm text-blue-700 mt-1">
                                Download dan install QZ Tray untuk mencetak struk ke printer thermal.
                            </p>
                            <a href="https://qz.io/download/" target="_blank" rel="noopener"
                               class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-blue-700 hover:text-blue-800">
                                Download QZ Tray
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Printer Selection -->
                <div x-show="qzConnected">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Printer</label>
                    <div class="relative">
                        <select x-model="selectedPrinter"
                                @change="savePrinter"
                                class="block w-full rounded-lg border-slate-300 py-3 pl-4 pr-10 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">-- Pilih Printer --</option>
                            <template x-for="printer in printers" :key="printer">
                                <option :value="printer" x-text="printer"></option>
                            </template>
                        </select>
                    </div>
                    <p class="mt-1.5 text-sm text-slate-500">
                        Pilih printer thermal yang akan digunakan untuk mencetak struk
                    </p>

                    <!-- Refresh Printers -->
                    <button type="button"
                            @click="loadPrinters"
                            :disabled="loadingPrinters"
                            class="mt-3 inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
                        <svg class="h-4 w-4" :class="loadingPrinters ? 'animate-spin' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span x-text="loadingPrinters ? 'Memuat...' : 'Refresh Daftar Printer'"></span>
                    </button>
                </div>

                <!-- Test Print -->
                <div x-show="qzConnected && selectedPrinter" class="border-t border-slate-200 pt-6">
                    <h3 class="font-medium text-slate-900 mb-3">Test Print</h3>
                    <p class="text-sm text-slate-500 mb-4">
                        Cetak halaman test untuk memastikan printer terhubung dengan benar.
                    </p>
                    <button type="button"
                            @click="testPrint"
                            :disabled="printing"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg class="h-4 w-4" :class="printing ? 'animate-pulse' : ''" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                        <span x-text="printing ? 'Mencetak...' : 'Cetak Test Page'"></span>
                    </button>
                </div>

                <!-- Print Result Message -->
                <div x-show="printMessage" x-transition class="rounded-lg p-4" :class="printSuccess ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200'">
                    <div class="flex items-center gap-2">
                        <template x-if="printSuccess">
                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </template>
                        <template x-if="!printSuccess">
                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        </template>
                        <span :class="printSuccess ? 'text-emerald-700' : 'text-red-700'" x-text="printMessage"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-6 rounded-xl bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="font-semibold text-slate-900">Cara Setup Printer Thermal</h3>
            </div>
            <div class="p-6">
                <ol class="list-decimal list-inside space-y-3 text-sm text-slate-600">
                    <li>
                        <strong>Install QZ Tray</strong> - Download dari <a href="https://qz.io/download/" target="_blank" class="text-emerald-600 hover:underline">qz.io/download</a>
                    </li>
                    <li>
                        <strong>Jalankan QZ Tray</strong> - Pastikan QZ Tray berjalan di background (icon muncul di system tray)
                    </li>
                    <li>
                        <strong>Hubungkan Printer</strong> - Pastikan printer thermal sudah terhubung ke komputer via USB
                    </li>
                    <li>
                        <strong>Pilih Printer</strong> - Di halaman ini, pilih printer thermal dari daftar
                    </li>
                    <li>
                        <strong>Test Print</strong> - Klik tombol Test Print untuk memastikan konfigurasi benar
                    </li>
                </ol>

                <div class="mt-4 rounded-lg bg-slate-50 p-4">
                    <p class="text-sm text-slate-600">
                        <strong>Printer yang didukung:</strong> Semua printer thermal ESC/POS compatible (Epson, Xprinter, dll)
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function printerSettings() {
            return {
                qzConnected: false,
                connecting: false,
                printers: [],
                selectedPrinter: '',
                loadingPrinters: false,
                printing: false,
                printMessage: '',
                printSuccess: false,

                async init() {
                    await this.checkConnection();
                    if (this.qzConnected) {
                        await this.loadPrinters();
                        this.selectedPrinter = QZTrayPrinter.getPrinter() || '';
                    }
                },

                async checkConnection() {
                    this.connecting = true;
                    try {
                        this.qzConnected = await QZTrayPrinter.init();
                    } catch (error) {
                        console.error('Connection check failed:', error);
                        this.qzConnected = false;
                    }
                    this.connecting = false;
                },

                async reconnect() {
                    this.connecting = true;
                    try {
                        // Force reconnect
                        if (typeof qz !== 'undefined' && qz.websocket.isActive()) {
                            await qz.websocket.disconnect();
                        }
                        QZTrayPrinter.initialized = false;
                        QZTrayPrinter.connected = false;

                        this.qzConnected = await QZTrayPrinter.init();
                        if (this.qzConnected) {
                            await this.loadPrinters();
                        }
                    } catch (error) {
                        console.error('Reconnect failed:', error);
                        this.qzConnected = false;
                    }
                    this.connecting = false;
                },

                async loadPrinters() {
                    if (!this.qzConnected) return;

                    this.loadingPrinters = true;
                    try {
                        this.printers = await QZTrayPrinter.getPrinters();
                    } catch (error) {
                        console.error('Failed to load printers:', error);
                        this.printers = [];
                    }
                    this.loadingPrinters = false;
                },

                savePrinter() {
                    if (this.selectedPrinter) {
                        QZTrayPrinter.setPrinter(this.selectedPrinter);
                        this.showMessage('Printer berhasil disimpan', true);
                    }
                },

                async testPrint() {
                    if (!this.selectedPrinter) {
                        this.showMessage('Pilih printer terlebih dahulu', false);
                        return;
                    }

                    this.printing = true;
                    this.printMessage = '';

                    try {
                        await QZTrayPrinter.printTest();
                        this.showMessage('Test print berhasil! Cek printer Anda.', true);
                    } catch (error) {
                        console.error('Test print failed:', error);
                        this.showMessage('Test print gagal: ' + error.message, false);
                    }

                    this.printing = false;
                },

                showMessage(message, success) {
                    this.printMessage = message;
                    this.printSuccess = success;

                    // Auto hide after 5 seconds
                    setTimeout(() => {
                        this.printMessage = '';
                    }, 5000);
                }
            };
        }
    </script>
    @endpush
</x-pos-layout>
