<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Apotek POS - Sistem Point of Sale Modern untuk Apotek. Kelola stok, transaksi, dan laporan dengan mudah.">
    <title>Apotek POS - Sistem Point of Sale Apotek Modern</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 50%, #115e59 100%);
        }
        .phone-mockup {
            max-width: 280px;
        }
        .tablet-mockup {
            max-width: 500px;
        }
        .feature-card:hover {
            transform: translateY(-4px);
        }
        .screenshot-carousel {
            scroll-snap-type: x mandatory;
        }
        .screenshot-carousel > * {
            scroll-snap-align: center;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-teal-700">Apotek POS</span>
                </div>
                <div class="hidden md:flex items-center gap-8">
                    <a href="/pos/login" class="text-emerald-600 font-medium hover:text-emerald-700 transition-colors">POS</a>
                    <a href="#features" class="text-gray-600 hover:text-teal-600 transition-colors">Fitur</a>
                    <a href="#screenshots" class="text-gray-600 hover:text-teal-600 transition-colors">Screenshot</a>
                    <a href="#pricing" class="text-gray-600 hover:text-teal-600 transition-colors">Harga</a>
                    <a href="#contact" class="text-gray-600 hover:text-teal-600 transition-colors">Kontak</a>
                </div>
                <div class="flex items-center gap-3">
                    <a href="/api/documentation" class="hidden sm:inline-flex text-teal-600 hover:text-teal-700 font-medium transition-colors">
                        API Docs
                    </a>
                    <a href="#trial" class="bg-teal-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-teal-700 transition-colors">
                        Coba Gratis
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg pt-32 pb-20 lg:pt-40 lg:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-white">
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm mb-6">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        AFC Aplikatif Flutter Club - Batch 6
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        Sistem Point of Sale
                        <span class="text-teal-200">Apotek Modern</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-teal-100 mb-8 leading-relaxed">
                        Kelola apotek Anda dengan aplikasi Flutter yang lengkap. Tersedia untuk Phone dan Tablet dengan fitur Kasir, Manajemen Stok, Laporan, dan masih banyak lagi.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#trial" class="inline-flex items-center gap-2 bg-white text-teal-700 px-6 py-3 rounded-xl font-semibold hover:bg-teal-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Live Demo
                        </a>
                        <a href="#pricing" class="inline-flex items-center gap-2 bg-teal-500/30 backdrop-blur-sm text-white px-6 py-3 rounded-xl font-semibold hover:bg-teal-500/40 transition-colors border border-white/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Beli Sourcecode
                        </a>
                    </div>
                    <div class="mt-10 flex items-center gap-6 text-sm text-teal-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Flutter 3.x
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Laravel API
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Full Source
                        </div>
                    </div>
                </div>
                <div class="relative flex justify-center lg:justify-end">
                    <div class="relative transform translate-y-8 lg:translate-y-16">
                        <!-- Tablet Mockup - Hidden on mobile, shown on lg+ -->
                        <div class="tablet-mockup hidden lg:block bg-gray-900 rounded-3xl p-2 shadow-2xl transform rotate-2">
                            <img src="/images/tablet-2.jpeg" alt="Apotek POS Tablet View" class="rounded-2xl w-full">
                        </div>
                        <!-- Phone Mockup - Positioned absolute on lg+, static on mobile -->
                        <div class="phone-mockup lg:absolute lg:-bottom-10 lg:-left-16 bg-gray-900 rounded-3xl p-1.5 shadow-2xl transform lg:-rotate-6 max-w-[220px] mx-auto lg:max-w-none">
                            <img src="/images/phone-1.jpeg" alt="Apotek POS Phone View" class="rounded-2xl w-full">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Demo Kasir Section -->
    <section id="live-demo" class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-3xl p-8 sm:p-10 lg:p-12 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-emerald-400/20 rounded-full blur-2xl"></div>
                <div class="relative grid lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full text-sm mb-4">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            Live Demo - Kasir Web
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold mb-3">Coba Langsung Fitur Kasir</h2>
                        <p class="text-emerald-100 mb-6">
                            Rasakan langsung pengalaman menggunakan sistem kasir Apotek POS berbasis web. Transaksi cepat, kelola shift, dan cetak struk langsung dari browser!
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <a href="/pos/login" class="inline-flex items-center gap-2 bg-white text-emerald-700 px-6 py-3 rounded-xl font-semibold hover:bg-emerald-50 transition-colors shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Buka Kasir Web
                            </a>
                            <a href="#trial" class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-xl font-semibold hover:bg-white/30 transition-colors border border-white/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                Lihat Akun Demo
                            </a>
                        </div>
                    </div>
                    <div class="hidden lg:flex justify-center gap-4">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <div class="flex items-center gap-3 mb-3">
                                <svg class="w-5 h-5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Transaksi penjualan</span>
                            </div>
                            <div class="flex items-center gap-3 mb-3">
                                <svg class="w-5 h-5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Kelola shift kasir</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-emerald-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">Cetak struk & laporan</span>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                            <p class="text-xs text-emerald-200 mb-2">Akun Demo Kasir</p>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-200">Email:</span>
                                    <code class="bg-white/20 px-2 py-0.5 rounded text-xs font-mono">kasir@apotek.com</code>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-200">Pass:</span>
                                    <code class="bg-white/20 px-2 py-0.5 rounded text-xs font-mono">password</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Fitur Lengkap untuk Apotek Anda</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Semua yang Anda butuhkan untuk mengelola apotek dalam satu aplikasi yang terintegrasi
                </p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="w-14 h-14 bg-teal-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Kasir / POS</h3>
                    <p class="text-gray-600">Proses transaksi cepat dengan pencarian produk, kategori, dan keranjang belanja yang intuitif.</p>
                </div>
                <!-- Feature 2 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Manajemen Stok & Batch</h3>
                    <p class="text-gray-600">Kelola stok produk dengan sistem batch, tracking expired date, dan notifikasi stok rendah.</p>
                </div>
                <!-- Feature 3 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Data Pelanggan</h3>
                    <p class="text-gray-600">Simpan data pelanggan untuk riwayat transaksi dan program loyalitas apotek Anda.</p>
                </div>
                <!-- Feature 4 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Laporan & Analisis</h3>
                    <p class="text-gray-600">Dashboard ringkasan bisnis dengan laporan penjualan harian, mingguan, dan bulanan.</p>
                </div>
                <!-- Feature 5 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Cetak Struk Bluetooth</h3>
                    <p class="text-gray-600">Print struk langsung ke printer thermal Bluetooth untuk setiap transaksi.</p>
                </div>
                <!-- Feature 6 -->
                <div class="feature-card bg-white rounded-2xl p-8 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Shift & Multi User</h3>
                    <p class="text-gray-600">Sistem shift kasir dengan modal awal dan multiple user dengan role berbeda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Screenshots Section -->
    <section id="screenshots" class="py-20 lg:py-28 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Tampilan Aplikasi</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    UI modern dan responsif untuk Phone dan Tablet
                </p>
            </div>

            <!-- Phone Screenshots -->
            <div class="mb-16">
                <h3 class="text-2xl font-semibold text-gray-800 mb-8 flex items-center gap-3">
                    <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Phone View
                </h3>
                <div class="screenshot-carousel flex gap-6 overflow-x-auto pb-6 -mx-4 px-4">
                    @for ($i = 1; $i <= 13; $i++)
                    <div class="flex-shrink-0">
                        <div class="bg-gray-900 rounded-3xl p-1.5 shadow-xl">
                            <img src="/images/phone-{{ $i }}.jpeg" alt="Phone Screenshot {{ $i }}" class="rounded-2xl h-96 w-auto">
                        </div>
                    </div>
                    @endfor
                </div>
            </div>

            <!-- Tablet Screenshots -->
            <div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-8 flex items-center gap-3">
                    <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Tablet View
                </h3>
                <div class="screenshot-carousel flex gap-6 overflow-x-auto pb-6 -mx-4 px-4">
                    @for ($i = 1; $i <= 10; $i++)
                    <div class="flex-shrink-0">
                        <div class="bg-gray-900 rounded-3xl p-2 shadow-xl">
                            <img src="/images/tablet-{{ $i }}.jpeg" alt="Tablet Screenshot {{ $i }}" class="rounded-2xl h-80 w-auto">
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </section>

    <!-- Video Demo Section -->
    <section id="video" class="py-20 lg:py-28 bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 bg-red-500/20 text-red-400 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                    Video Demo
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Lihat Aplikasi Beraksi</h2>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Tonton video demo untuk melihat fitur lengkap Apotek POS dalam aksi nyata
                </p>
            </div>
            <div class="max-w-4xl mx-auto">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-black aspect-video">
                    <iframe
                        class="absolute inset-0 w-full h-full"
                        src="https://www.youtube.com/embed/oKMg_0gZWJc"
                        title="Apotek POS Demo Video"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="https://youtu.be/oKMg_0gZWJc" target="_blank" class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-700 transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                        Tonton di YouTube
                    </a>
                    <a href="#trial" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white px-6 py-3 rounded-xl font-semibold hover:bg-white/20 transition-colors border border-white/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Coba Live Demo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Trial Section -->
    <section id="trial" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Admin Demo Card -->
            <div class="bg-gradient-to-br from-teal-600 to-teal-800 rounded-3xl p-8 sm:p-12 lg:p-16 text-white text-center">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">Dashboard Admin</h2>
                <p class="text-lg text-teal-100 mb-8 max-w-2xl mx-auto">
                    Login ke dashboard admin untuk kelola produk, stok, kategori, supplier dan lihat laporan lengkap. Powered by Filament Admin Panel.
                </p>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 sm:p-8 max-w-md mx-auto mb-8">
                    <h3 class="text-xl font-semibold mb-4">Akun Demo Admin</h3>
                    <div class="space-y-3 text-left">
                        <div class="flex justify-between items-center">
                            <span class="text-teal-200">Email:</span>
                            <code class="bg-white/20 px-3 py-1 rounded text-sm">admin@apotek.com</code>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-teal-200">Password:</span>
                            <code class="bg-white/20 px-3 py-1 rounded text-sm">password</code>
                        </div>
                    </div>
                </div>
                <a href="/admin/login" class="inline-flex items-center gap-2 bg-white text-teal-700 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-teal-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Login Dashboard Admin
                </a>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 lg:py-28 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 bg-teal-100 text-teal-700 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    AFC Aplikatif Flutter Club - Batch 6
                </div>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Dapatkan Full Sourcecode</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Beli sourcecode lengkap dengan panduan instalasi. Cocok untuk belajar atau langsung deploy ke production.
                </p>
            </div>
            <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Card Harga Normal -->
                <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gray-500 text-white p-6 text-center">
                        <span class="text-sm font-medium uppercase tracking-wide">Harga Normal</span>
                    </div>
                    <div class="p-8">
                        <div class="text-center mb-8">
                            <span class="text-5xl font-bold text-gray-700">Rp 499K</span>
                            <span class="text-gray-500 ml-2">sekali bayar</span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Full sourcecode Flutter App (Phone & Tablet)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Full sourcecode Laravel Backend API</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Panduan instalasi lengkap</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Database SQL lengkap</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-600">Akses grup support</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-400 line-through">Tutorial step by step pembuatan</span>
                            </li>
                        </ul>
                        <div class="block w-full bg-gray-200 text-gray-500 text-center py-4 rounded-xl font-semibold text-lg cursor-not-allowed">
                            Setelah 5 Januari 2026
                        </div>
                    </div>
                </div>

                <!-- Card Harga Promo -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden border-2 border-teal-500 relative transform md:scale-105">
                    <div class="absolute -top-1 -right-1 bg-orange-500 text-white px-4 py-1 rounded-bl-xl rounded-tr-2xl text-sm font-bold animate-pulse">
                        PROMO
                    </div>
                    <div class="bg-teal-600 text-white p-6 text-center">
                        <span class="text-sm font-medium uppercase tracking-wide">Harga Promo</span>
                    </div>
                    <div class="p-8">
                        <div class="text-center mb-2">
                            <span class="text-5xl font-bold text-teal-600">Rp 179K</span>
                            <span class="text-gray-500 ml-2">sekali bayar</span>
                        </div>
                        <div class="text-center mb-8">
                            <span class="inline-flex items-center gap-2 bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Hemat Rp 320K - sampai 10 Jan 2026
                            </span>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Full sourcecode Flutter App (Phone & Tablet)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Full sourcecode Laravel Backend API</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Panduan instalasi lengkap</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Database SQL lengkap</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">Akses grup support</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-gray-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-400 line-through">Tutorial step by step pembuatan</span>
                            </li>
                        </ul>
                        <a href="https://jagoflutter.com/apotekapp" target="_blank" class="block w-full bg-teal-600 text-white text-center py-4 rounded-xl font-semibold text-lg hover:bg-teal-700 transition-colors">
                            Beli Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tech Stack Section -->
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Tech Stack</h2>
                <p class="text-lg text-gray-600">Dibangun dengan teknologi modern dan terpercaya</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" class="w-12 h-12" fill="#02569B">
                            <path d="M14.314 0L2.3 12 6 15.7 21.684.013h-7.357L14.314 0zm.014 11.072L7.857 17.53l6.47 6.47H21.7l-6.46-6.468 6.46-6.46h-7.37l-.002-.001z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Flutter</h3>
                    <p class="text-sm text-gray-500">Mobile App</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" class="w-12 h-12" fill="#FF2D20">
                            <path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012a.262.262 0 01-.06-.023L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034L5.044.05a.375.375 0 01.378 0L9.936 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.02.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.01.026-.023.041-.032l4.513-2.598a.375.375 0 01.378 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.011.02.018.04.024.06.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.514 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.094 3.622v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003-.002-.002c-.014-.01-.025-.021-.04-.031-.012-.01-.025-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.04-.01-.011-.021-.022-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.232.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.892 14.418l2.18-1.256V3.622l-1.577.91-2.182 1.255v10.35zm10.7-9.14l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L15.266 9.81l-1.578-.908v4.283l2.182 1.256 1.578.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.324 2.49-3.939 2.267z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Laravel</h3>
                    <p class="text-sm text-gray-500">Backend API</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg viewBox="0 0 128 128" class="w-12 h-12">
                            <!-- Dolphin -->
                            <path fill="#5d87a1" d="M116.948 97.807c-6.863-.187-12.104.452-16.585 2.341-1.273.537-3.305.552-3.513 2.147.7.733.807 1.83 1.355 2.731 1.047 1.729 2.82 4.047 4.406 5.271 1.729 1.337 3.478 2.771 5.356 3.906 3.344 2.014 7.082 3.165 10.281 5.183 1.88 1.186 3.75 2.686 5.572 4.012 0.904.658 1.51 1.678 2.634 2.147v-.244c-.605-.757-.762-1.793-1.355-2.633-0.838-0.838-1.678-1.576-2.535-2.437-2.471-3.271-5.546-6.147-8.869-8.477-2.647-1.858-8.576-4.37-9.68-7.419l-.195-.195c1.877-.2 4.079-.886 5.859-1.363 2.935-.794 5.567-.604 8.576-1.362 1.362-.378 2.731-.756 4.103-1.153v-.733c-1.534-1.573-2.634-3.664-4.299-5.086-4.336-3.71-9.062-7.42-13.984-10.47-2.579-1.601-5.764-2.64-8.479-4.011-0.914-0.462-2.511-.702-3.111-1.457-1.422-1.787-2.195-4.074-3.305-6.133-2.324-4.312-4.605-9.025-6.627-13.591-1.378-3.112-2.276-6.193-3.906-9.082-7.798-13.799-16.185-22.146-29.186-30.355-2.765-1.747-6.09-2.431-9.582-3.32-1.862-.065-3.726-.124-5.586-.196-1.133-.472-2.311-1.855-3.32-2.534-4.161-2.698-14.831-8.568-17.925-0.88-1.953 4.858 2.924 9.594 4.609 12.04 1.183 1.717 2.699 3.643 3.516 5.574 0.539 1.27 0.63 2.547 1.163 3.907 1.312 3.348 2.471 6.998 4.106 10.086 0.827 1.561 1.748 3.176 2.729 4.602 0.602 0.875 1.635 1.261 1.857 2.532-1.044 1.463-1.104 3.729-1.651 5.57-2.467 8.287-1.536 18.567 2.049 24.695 1.102 1.881 3.695 5.925 7.225 4.396 3.088-1.337 2.398-5.486 3.319-9.19 0.207-0.839 0.079-1.456 0.477-2.048v.194c0.948 1.899 1.897 3.787 2.833 5.679 2.101 3.386 5.829 6.915 8.918 9.279 1.601 1.226 2.861 3.343 4.898 4.103v-.243h-.194c-0.399-0.624-1.023-0.88-1.553-1.363-1.245-1.133-2.628-2.533-3.613-3.812-2.87-3.724-5.405-7.805-7.718-12.027-1.104-2.015-2.065-4.24-2.927-6.324-0.342-0.827-0.336-2.081-1.161-2.533-1.076 1.67-2.66 3.016-3.513 4.994-1.36 3.155-1.535 7.007-2.048 10.966-0.296.02-0.166 0-0.291.096-2.175-0.531-2.939-2.782-3.762-4.692-2.084-4.831-2.474-12.617-0.634-18.217 0.476-1.449 2.623-6.011 1.752-7.412-0.416-1.339-1.785-2.114-2.533-3.169-0.926-1.306-1.856-3.024-2.535-4.496-1.768-3.833-2.596-8.135-4.497-12.028-0.906-1.855-2.441-3.732-3.712-5.474-1.405-1.926-2.976-3.347-4.103-5.572-0.4-0.79-0.944-2.054-0.342-2.923 0.189-0.598 0.456-0.848 1.065-1.065 1.035-0.617 3.919 0.204 4.993 0.635 2.844 1.14 5.222 2.23 7.616 3.811 1.146 0.757 2.306 2.219 3.712 2.634h1.652c2.581 0.592 5.467 0.17 7.909 0.83 4.324 1.169 8.2 2.972 11.638 4.89 10.486 5.844 19.078 14.171 24.93 24.14 0.943 1.606 1.352 3.134 2.147 4.896 1.595 3.537 3.606 7.169 5.183 10.573 1.576 3.401 3.111 6.827 5.379 9.677 1.191 1.498 5.791 2.304 7.908 3.122 1.479 0.572 3.899 1.167 5.183 1.945 2.462 1.493 4.849 3.215 7.125 4.893 1.138 0.84 4.646 2.672 4.893 4.097z"/>
                            <!-- My text -->
                            <path fill="#f29111" d="M4.574 85.505c0.047 2.578.196 5.148.435 7.702 0.224 2.389 0.538 4.917 1.525 7.089 1.5 3.3 4.018 5.298 7.369 6.551 1.648 0.617 3.393 0.98 5.112 1.262 1.777 0.292 3.629 0.4 5.358 0.773 0.474 0.102 0.945 0.22 1.416 0.352v-4.205c-0.333-0.052-0.666-0.106-0.998-0.168-1.621-0.304-3.306-0.465-4.87-0.965-2.396-0.765-4.044-2.178-4.778-4.614-0.486-1.613-0.698-3.424-0.822-5.12-0.232-3.175-0.289-6.373-0.258-9.562 0.024-2.468 0.18-4.953 0.476-7.403 0.226-1.868 0.567-3.808 1.333-5.542 1.057-2.394 2.927-3.814 5.463-4.442 1.399-0.346 2.859-0.523 4.311-0.704 0.382-0.048 0.764-0.094 1.147-0.138v-4.206c-0.326 0.051-0.652 0.102-0.977 0.156-1.747 0.292-3.57 0.433-5.236 0.952-4.111 1.282-6.717 4.051-8.101 8.059-0.874 2.532-1.263 5.262-1.546 7.947-0.315 2.99-0.396 6.009-0.36 9.024z"/>
                            <path fill="#f29111" d="M52.726 61.626l-3.797 18.184c-0.465 2.214-0.936 4.337-1.199 6.582-0.07 0.6 0.188 1.411 0.617 1.851 0.922 0.944 2.071 1.68 3.203 2.399 0.385 0.244 0.996 0.197 1.499 0.19l0.088-0.002c2.36-0.069 4.725-0.107 7.089-0.128 0.215-0.002 0.43-0.003 0.645-0.003 3.041 0 6.058 0.068 8.814 0.167v-4.53c-2.298-0.099-4.76-0.161-7.341-0.161-0.195 0-0.39 0.001-0.584 0.002-2.31 0.02-4.605 0.056-6.874 0.107l2.989-14.358 0.081-0.003c1.35 3.951 2.771 7.884 4.313 11.752l3.702 0.127c1.348-4.049 2.666-8.115 3.79-12.238l0.09-0.006 2.834 14.514c2.6 0.105 5.168 0.244 7.649 0.422l-4.411-20.918-5.259-0.176c-1.211 4.053-2.47 8.085-3.916 12.054l-0.088 0.009c-1.387-4.002-2.729-8.021-3.937-12.094l-5.998 0.258z"/>
                            <!-- SQL text -->
                            <path fill="#5d87a1" d="M85.957 90.478c-0.453-0.024-0.905-0.047-1.357-0.07l-0.288 3.839c2.725 0.2 5.402 0.46 7.944 0.792v-4.216c-2.111-0.142-4.209-0.263-6.299-0.345z"/>
                            <path fill="#5d87a1" d="M99.098 91.513c-1.057-0.063-2.114-0.12-3.17-0.173l-0.294 4.166c0.816 0.073 1.634 0.152 2.449 0.235 2.335 0.238 4.728 0.486 7.015 0.873 0.476 0.08 0.913 0.364 1.332 0.605l0.117 0.067c0.081 0.047 0.299 0.181 0.299 0.478 0 0.333-0.246 0.486-0.326 0.532l-0.128 0.068c-0.558 0.278-1.15 0.456-1.728 0.615l-0.234 0.064c-0.904 0.24-1.833 0.38-2.735 0.515-1.47 0.22-3.003 0.353-4.56 0.398l-0.277 4.009c1.794-0.027 3.573-0.148 5.287-0.36 1.242-0.154 2.476-0.34 3.663-0.63 1.303-0.318 2.659-0.735 3.777-1.48 1.153-0.768 1.893-1.859 1.94-3.298 0.043-1.32-0.488-2.493-1.598-3.274-0.926-0.651-2.037-1.021-3.146-1.317-1.596-0.426-3.257-0.662-4.874-0.883l-0.367-0.049c-0.876-0.115-1.77-0.19-2.642-0.263l-0.302-0.025z"/>
                            <path fill="#5d87a1" d="M88.931 81.81c-0.034 2.058 0.015 4.123 0.156 6.17 0.072 1.044 0.172 2.094 0.362 3.124l4.244 0.219c-0.221-1.018-0.346-2.062-0.431-3.1-0.134-1.632-0.173-3.272-0.174-4.909l4.617 0.144c-0.002 1.476 0.036 2.953 0.129 4.424 0.06 0.948 0.148 1.901 0.296 2.841l4.146 0.233c-0.188-1.042-0.297-2.099-0.369-3.152-0.093-1.352-0.132-2.709-0.145-4.064-0.004-0.43-0.007-0.861-0.007-1.291 0-1.032 0.016-2.057 0.052-3.076l-4.19-0.094c-0.019 0.763-0.029 1.527-0.029 2.292 0 0.445 0.002 0.891 0.008 1.337l-4.573-0.129c0.007-1.054 0.029-2.107 0.068-3.158l-4.155-0.091c-0.004 0.827-0.005 1.653-0.005 2.48z"/>
                            <path fill="#5d87a1" d="M118.776 83.103c-0.02 1.576 0.007 3.157 0.099 4.728 0.057 0.977 0.14 1.96 0.297 2.927 0.169 1.038 0.421 2.13 0.997 3.027 0.759 1.181 1.942 1.821 3.292 2.128 0.98 0.223 1.998 0.329 2.998 0.404l0.312 0.021c0.417 0.026 0.834 0.05 1.252 0.072v-4.191l-0.297-0.018c-0.429-0.028-0.857-0.063-1.282-0.122-0.655-0.091-1.433-0.247-1.815-0.856-0.335-0.534-0.417-1.27-0.479-1.871-0.097-0.94-0.14-1.893-0.164-2.839-0.034-1.341-0.031-2.686-0.022-4.028l4.059 0.045v-4.055l-4.08-0.028c0.014-1.297 0.043-2.594 0.089-3.888l-4.147 0.563c-0.061 1.349-0.101 2.701-0.117 4.055l-2.911-0.016v3.909l1.918 0.023z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">MySQL</h3>
                    <p class="text-sm text-gray-500">Database</p>
                </div>
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <svg viewBox="0 0 24 24" class="w-12 h-12" fill="#06B6D4">
                            <path d="M12.001 4.8c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624C13.666 10.618 15.027 12 18.001 12c3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C16.337 6.182 14.976 4.8 12.001 4.8zm-6 7.2c-3.2 0-5.2 1.6-6 4.8 1.2-1.6 2.6-2.2 4.2-1.8.913.228 1.565.89 2.288 1.624 1.177 1.194 2.538 2.576 5.512 2.576 3.2 0 5.2-1.6 6-4.8-1.2 1.6-2.6 2.2-4.2 1.8-.913-.228-1.565-.89-2.288-1.624C10.337 13.382 8.976 12 6.001 12z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Tailwind CSS</h3>
                    <p class="text-sm text-gray-500">Styling</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 lg:py-28 bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-bold mb-6">Hubungi Kami</h2>
                    <p class="text-lg text-gray-300 mb-8">
                        Ada pertanyaan tentang Apotek POS atau ingin membeli sourcecode? Hubungi kami melalui WhatsApp atau ikuti update terbaru di media sosial.
                    </p>
                    <div class="space-y-4">
                        <a href="https://wa.me/6285640899224" target="_blank" class="flex items-center gap-4 bg-green-600 hover:bg-green-700 text-white px-6 py-4 rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            <span class="font-semibold">Chat WhatsApp</span>
                        </a>
                        <a href="https://www.instagram.com/codewithbahri" target="_blank" class="flex items-center gap-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-6 py-4 rounded-xl transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                            <span class="font-semibold">@codewithbahri</span>
                        </a>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold mb-6">Info Event</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-teal-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-white">AFC Aplikatif Flutter Club</h4>
                                <p class="text-gray-400">Batch 6 - Apotek POS System</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-white">Yang Didapat</h4>
                                <p class="text-gray-400">Sourcecode + Panduan Instalasi (tanpa tutorial step by step)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-950 text-gray-400 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="font-semibold text-white">Apotek POS</span>
                </div>
                <p class="text-sm">&copy; {{ date('Y') }} AFC Aplikatif Flutter Club &bull; <a href="https://jagoflutter.com" target="_blank" class="text-teal-400 hover:text-teal-300 transition-colors">jagoflutter.com</a></p>
            </div>
        </div>
    </footer>
</body>
</html>
