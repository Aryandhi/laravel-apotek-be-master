@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} - {{ config('app.name', 'Apotek POS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- QZ Tray Library for Thermal Printing -->
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-100 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-full">
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/80 lg:hidden"
             @click="sidebarOpen = false"
             x-cloak>
        </div>

        <!-- Mobile sidebar -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 w-72 bg-white lg:hidden"
             x-cloak>
            @include('pos.layouts.sidebar')
        </div>

        <!-- Desktop sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-72 lg:flex-col">
            @include('pos.layouts.sidebar')
        </div>

        <!-- Main content -->
        <div class="lg:pl-72">
            <!-- Top navigation -->
            <div class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-slate-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <!-- Mobile menu button -->
                <button type="button"
                        class="-m-2.5 p-2.5 text-slate-700 lg:hidden"
                        @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-slate-200 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <!-- Page title -->
                    <div class="flex items-center">
                        <h1 class="text-lg font-semibold text-slate-900">{{ $title }}</h1>
                    </div>

                    <!-- Right side -->
                    <div class="flex flex-1 items-center justify-end gap-x-4 lg:gap-x-6">
                        <!-- Current time -->
                        <div class="hidden items-center gap-2 text-sm text-slate-500 sm:flex" x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID'), 1000)">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span x-text="time"></span>
                        </div>

                        <div class="h-6 w-px bg-slate-200" aria-hidden="true"></div>

                        <!-- User dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button type="button"
                                    class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                    @click="open = !open">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-white">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <span class="hidden sm:block">{{ auth()->user()->name }}</span>
                                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-lg bg-white py-2 shadow-lg ring-1 ring-slate-900/5"
                                 @click.away="open = false"
                                 x-cloak>
                                <div class="border-b border-slate-100 px-4 py-2">
                                    <p class="text-xs text-slate-500">Role</p>
                                    <p class="text-sm font-medium text-slate-700">{{ auth()->user()->role->label() }}</p>
                                </div>
                                <a href="{{ route('pos.dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    Dashboard
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')

    <!-- QZ Tray Integration -->
    <script>
        window.QZTrayPrinter = {
            connected: false,
            printerName: null,
            initialized: false,

            async init() {
                if (this.initialized) return this.connected;
                if (typeof qz === 'undefined') {
                    console.warn('[QZ] QZ Tray library not loaded');
                    return false;
                }

                try {
                    qz.security.setCertificatePromise(function(resolve, reject) {
                        resolve();
                    });

                    qz.security.setSignaturePromise(function(toSign) {
                        return function(resolve, reject) {
                            resolve();
                        };
                    });

                    if (!qz.websocket.isActive()) {
                        await qz.websocket.connect();
                    }

                    this.connected = true;
                    this.initialized = true;
                    this.printerName = localStorage.getItem('qz_printer_name');
                    console.log('[QZ] Connected to QZ Tray');
                    return true;
                } catch (error) {
                    console.error('[QZ] Failed to connect:', error);
                    this.connected = false;
                    this.initialized = true;
                    return false;
                }
            },

            isConnected() {
                return this.connected && qz?.websocket?.isActive();
            },

            async getPrinters() {
                if (!await this.init()) throw new Error('QZ Tray tidak terhubung');
                return await qz.printers.find();
            },

            setPrinter(printerName) {
                this.printerName = printerName;
                localStorage.setItem('qz_printer_name', printerName);
                console.log('[QZ] Printer set to:', printerName);
            },

            getPrinter() {
                return this.printerName;
            },

            prepareCommands(commands) {
                if (typeof commands === 'string') return [commands];
                if (Array.isArray(commands)) {
                    return commands.map(cmd => {
                        if (typeof cmd === 'string') {
                            return cmd.replace(/\\x([0-9A-Fa-f]{2})/g, (match, hex) => {
                                return String.fromCharCode(parseInt(hex, 16));
                            });
                        }
                        return cmd;
                    });
                }
                return commands;
            },

            async printRaw(commands) {
                if (!await this.init()) {
                    throw new Error('QZ Tray tidak terhubung. Pastikan QZ Tray sudah terinstall dan berjalan.');
                }
                if (!this.printerName) {
                    throw new Error('Printer belum dipilih. Silakan pilih printer di pengaturan.');
                }

                const config = qz.configs.create(this.printerName, { encoding: 'UTF-8' });
                const data = this.prepareCommands(commands);
                await qz.print(config, data);
                console.log('[QZ] Print successful');
                return true;
            },

            async printReceipt(saleId) {
                const response = await fetch(`{{ url('pos/receipts') }}/${saleId}/escpos-json`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) throw new Error('Gagal mengambil data struk');
                const data = await response.json();
                if (!data.success) throw new Error(data.message || 'Gagal mengambil data struk');

                await this.printRaw(data.data.commands);
                return true;
            },

            async printTest() {
                const commands = [
                    '\x1B\x40',
                    '\x1B\x61\x01',
                    '\x1B\x21\x10',
                    'TEST PRINT\n',
                    '\x1B\x21\x00',
                    '================================\n',
                    '\x1B\x61\x00',
                    'Printer: ' + (this.printerName || 'Unknown') + '\n',
                    'Waktu: ' + new Date().toLocaleString('id-ID') + '\n',
                    '================================\n',
                    '\x1B\x61\x01',
                    'QZ Tray Connected!\n',
                    '\n\n\n',
                    '\x1D\x56\x00',
                ];
                return await this.printRaw(commands);
            },

            async disconnect() {
                if (qz?.websocket?.isActive()) {
                    await qz.websocket.disconnect();
                }
                this.connected = false;
                this.initialized = false;
            }
        };

        // Auto-init
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof qz !== 'undefined') {
                QZTrayPrinter.init().catch(err => console.warn('[QZ] Auto-init failed:', err));
            }
        });
    </script>
</body>
</html>