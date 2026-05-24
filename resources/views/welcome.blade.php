<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'LPK Registration') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-foreground bg-background selection:bg-primary/10 selection:text-primary">

    <!-- Navigation (Simple) -->
    <nav class="absolute top-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-xl tracking-tight">
                <span class="text-primary">SIT</span>LPK
            </div>
            
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    <div class="hidden sm:flex gap-4">
                        @auth
                            <a href="{{ url('/admin') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Log in</a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 overflow-hidden">
        <!-- Warm Orange Glow Center -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-orange-500/30 blur-[120px] pointer-events-none -z-10"></div>
        <div class="mx-auto max-w-7xl px-6 lg:px-8 relative">
            <div class="mx-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-2 lg:items-center">
                <div class="lg:pr-8 lg:pt-4">
                    <div class="mb-6 inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-sm font-medium text-primary">
                        <span class="flex h-2 w-2 rounded-full bg-primary mr-2"></span>
                        Resmi, Aman & Terpercaya
                    </div>
                    <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-6xl lg:text-5xl xl:text-6xl">
                        Raih <span class="text-primary">Gaji Tinggi</span> & Masa Depan Cerah di Luar Negeri
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-muted-foreground">
                        Jadilah pahlawan devisa dengan penghasilan fantastis. Kami siap membimbing Anda dari nol hingga terbang. Proses cepat, biaya transparan, dan resmi terdaftar di pemerintahan.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4">
                        <a href="#pendaftaran" class="inline-flex h-12 items-center justify-center rounded-full bg-primary px-8 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary transition-all">
                            Daftar Sekarang
                        </a>
                        <a href="#tentang" class="inline-flex h-12 items-center justify-center rounded-full border border-input bg-background px-8 text-sm font-semibold text-foreground shadow-sm hover:bg-accent hover:text-accent-foreground transition-all">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="relative mt-8 sm:mt-0 lg:mt-0">
                    <div class="relative rounded-2xl bg-muted/50 dark:bg-muted/20 border border-border p-2 shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1593526613712-7b4b9a707330?q=80&w=2070&auto=format&fit=crop" alt="LPK Training" class="aspect-[4/3] w-full rounded-xl bg-gray-50 object-cover shadow-inner ring-1 ring-gray-900/10">
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute -top-12 -right-12 -z-10 h-[300px] w-[300px] rounded-full bg-primary/20 blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-12 -left-12 -z-10 h-[300px] w-[300px] rounded-full bg-secondary/40 blur-3xl opacity-50"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us / Trust Section -->
    <section id="tentang" class="py-24 bg-background">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Kenapa Memilih Kami?</h2>
                <p class="mt-4 text-lg text-muted-foreground">
                    Keunggulan kami sebagai mitra terpercaya untuk kesuksesan karir internasional Anda.
                </p>
            </div>
            <div class="mx-auto grid max-w-2xl grid-cols-1 gap-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                <!-- Benefit 1 -->
                <div class="flex flex-col bg-card p-8 rounded-2xl border border-border hover:shadow-lg transition-all">
                    <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-foreground">
                        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-primary/10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z" />
                            </svg>
                        </div>
                        Resmi & Legal
                    </dt>
                    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-muted-foreground">
                        <p class="flex-auto">Terdaftar resmi di Disnaker dan instansi terkait. Kami menjamin legalitas dokumen dan perlindungan hukum penuh bagi setiap calon tenaga kerja.</p>
                    </dd>
                </div>
                <!-- Benefit 2 -->
                <div class="flex flex-col bg-card p-8 rounded-2xl border border-border hover:shadow-lg transition-all">
                    <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-foreground">
                        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-primary/10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Gaji Tinggi & Transparan
                    </dt>
                    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-muted-foreground">
                        <p class="flex-auto">Akses ke pekerjaan dengan standar gaji internasional. Semua biaya dan potongan dijelaskan secara transparan di awal tanpa ada yang ditutupi.</p>
                    </dd>
                </div>
                <!-- Benefit 3 -->
                <div class="flex flex-col bg-card p-8 rounded-2xl border border-border hover:shadow-lg transition-all">
                    <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-foreground">
                        <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-primary/10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-primary">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                            </svg>
                        </div>
                        Pelatihan Profesional
                    </dt>
                    <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-muted-foreground">
                        <p class="flex-auto">Fasilitas pelatihan bahasa dan keterampilan yang lengkap (BLK) untuk memastikan Anda siap kerja dan bersaing di lingkungan internasional.</p>
                    </dd>
                </div>
            </div>
        </div>
    </section>

    <!-- Country Grid Section -->
    <section id="pendaftaran" class="py-24 bg-muted/30 relative">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-16">
                <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">Pilih Negara Tujuan</h2>
                <p class="mt-4 text-lg text-muted-foreground">
                    Pilih negara tujuan karir Anda dan isi formulir pendaftaran yang tersedia.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $countries = [
                        [
                            'name' => 'HONGKONG',
                            'desc' => 'Peluang karir di pusat keuangan Asia.',
                            'color' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
                            'flag' => 'https://flagcdn.com/w80/hk.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'MALAYSIA',
                            'desc' => 'Tetangga serumpun dengan budaya yang dekat.',
                            'color' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                            'flag' => 'https://flagcdn.com/w80/my.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'TAIWAN',
                            'desc' => 'Industri maju dengan standar gaji yang tinggi.',
                            'color' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400',
                            'flag' => 'https://flagcdn.com/w80/tw.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'BULGARIA',
                            'desc' => 'Gerbang menuju peluang di Eropa Timur.',
                            'color' => 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
                            'flag' => 'https://flagcdn.com/w80/bg.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'SLOVAKIA',
                            'desc' => 'Negara Eropa Tengah dengan industri berkembang.',
                            'color' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                            'flag' => 'https://flagcdn.com/w80/sk.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'HUNGARIA',
                            'desc' => 'Pusat budaya dan ekonomi di jantung Eropa.',
                            'color' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400',
                            'flag' => 'https://flagcdn.com/w80/hu.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'ROMANIA',
                            'desc' => 'Ekonomi yang tumbuh pesat di Eropa Tenggara.',
                            'color' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                            'flag' => 'https://flagcdn.com/w80/ro.png',
                            'link' => '#'
                        ],
                        [
                            'name' => 'POLANDIA',
                            'desc' => 'Salah satu ekonomi terkuat di Eropa Tengah.',
                            'color' => 'bg-violet-50 text-violet-600 dark:bg-violet-900/20 dark:text-violet-400',
                            'flag' => 'https://flagcdn.com/w80/pl.png',
                            'link' => '#'
                        ],
                    ];
                @endphp

                @foreach($countries as $country)
                    <div class="group relative flex flex-col items-center justify-between rounded-2xl border border-border bg-card p-8 text-center shadow-sm transition-all hover:shadow-md hover:-translate-y-1 hover:border-primary/50">
                        <div class="w-full flex-1 flex flex-col items-center">
                            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-muted/50 overflow-hidden shadow-sm transition-transform group-hover:scale-110">
                                <img src="{{ $country['flag'] }}" alt="Flag of {{ $country['name'] }}" class="h-full w-full object-cover">
                            </div>
                            <h3 class="text-lg font-bold tracking-tight text-foreground">{{ $country['name'] }}</h3>
                            <p class="mt-2 text-sm text-muted-foreground">{{ $country['desc'] }}</p>
                        </div>
                        
                        <a href="{{ $country['link'] }}" target="_blank" class="mt-6 w-full inline-flex h-10 items-center justify-center rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                            Form Pendaftaran
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
            </div>
        </div>
    </section>

    <!-- SEO CTA Section -->
    <section class="py-24 bg-primary/5 border-t border-border">
        <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl text-pretty">
                Jangan Tunda Kesuksesan Anda.<br>Kuota Terbatas!
            </h2>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground leading-relaxed">
                Ribuan orang telah berhasil mengubah nasib mereka melalui <strong>Lowongan Kerja Luar Negeri</strong> bersama kami. Sebagai <strong>Penyalur Tenaga Kerja Resmi</strong>, kami memastikan keberangkatan Anda aman dan terjamin. Ambil langkah pertama menuju kemandirian finansial hari ini.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#pendaftaran" class="w-full sm:w-auto rounded-full bg-primary px-8 py-3.5 text-base font-semibold text-white shadow-xl hover:bg-primary/90 hover:scale-105 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary animate-pulse">
                    Daftar Sekarang Juga
                </a>
                <a href="#tentang" class="text-sm font-semibold leading-6 text-foreground hover:text-primary transition-colors">
                    Konsultasi Gratis <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-background border-t border-border py-12">
        <div class="mx-auto max-w-7xl px-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <p class="text-sm text-muted-foreground">
                &copy; {{ date('Y') }} SIT LPK. All rights reserved.
            </p>
            <div class="flex gap-6">
                <a href="{{ route('privacy-policy') }}" class="text-sm text-muted-foreground hover:text-foreground">Privacy Policy</a>
                <a href="#" class="text-sm text-muted-foreground hover:text-foreground">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>
</html>

