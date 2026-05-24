<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - {{ config('app.name', 'SIT LPK') }}</title>
    <meta name="description" content="Kebijakan privasi lpksgemilangputrabangsa.com tentang pengumpulan, penggunaan, dan perlindungan data pengguna.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "hsl(var(--primary-foreground))",
                        },
                        muted: {
                            DEFAULT: "hsl(var(--muted))",
                            foreground: "hsl(var(--muted-foreground))",
                        },
                        card: {
                            DEFAULT: "hsl(var(--card))",
                            foreground: "hsl(var(--card-foreground))",
                        },
                    },
                    borderRadius: {
                        lg: "var(--radius)",
                        md: "calc(var(--radius) - 2px)",
                        sm: "calc(var(--radius) - 4px)",
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            :root {
                --background: 0 0% 100%;
                --foreground: 222.2 84% 4.9%;
                --card: 0 0% 100%;
                --card-foreground: 222.2 84% 4.9%;
                --primary: 221.2 83.2% 53.3%;
                --primary-foreground: 210 40% 98%;
                --muted: 210 40% 96.1%;
                --muted-foreground: 215.4 16.3% 46.9%;
                --border: 214.3 31.8% 91.4%;
                --input: 214.3 31.8% 91.4%;
                --ring: 221.2 83.2% 53.3%;
                --radius: 0.75rem;
            }
        }
    </style>
</head>
<body class="font-sans antialiased text-foreground bg-background selection:bg-primary/10 selection:text-primary">
    <nav class="sticky top-0 z-50 border-b border-border bg-background/90 backdrop-blur">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-xl font-bold tracking-tight">
                <span class="text-primary">SIT</span>LPK
            </a>
            <a href="{{ url('/') }}" class="inline-flex h-10 items-center justify-center rounded-full border border-input bg-background px-5 text-sm font-semibold text-foreground shadow-sm transition-all hover:bg-muted">
                Kembali ke Beranda
            </a>
        </div>
    </nav>

    <main class="relative overflow-hidden">
        <div class="absolute left-1/2 top-20 -z-10 h-[460px] w-[460px] -translate-x-1/2 rounded-full bg-orange-500/20 blur-[120px]"></div>

        <section class="mx-auto max-w-4xl px-6 py-16 lg:py-24">
            <div class="mb-10 text-center">
                <div class="mb-5 inline-flex items-center rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-sm font-medium text-primary">
                    Kebijakan Privasi
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl">Privacy Policy</h1>
                <p class="mt-5 text-lg leading-8 text-muted-foreground">
                    Selamat datang di website kami, <strong class="font-semibold text-foreground">lpksgemilangputrabangsa.com</strong>. Privasi pengunjung adalah hal yang sangat penting bagi kami.
                </p>
            </div>

            <article class="rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-10">
                <div class="space-y-9 text-base leading-8 text-muted-foreground">
                    <p>
                        Halaman Privacy Policy ini menjelaskan bagaimana informasi pengguna dikumpulkan, digunakan, dan dilindungi saat mengakses website ini. Dengan menggunakan website ini, Anda dianggap telah menyetujui kebijakan privasi yang berlaku.
                    </p>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">1. Informasi yang Kami Kumpulkan</h2>
                        <p>Kami dapat mengumpulkan beberapa informasi dari pengunjung, seperti:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Nama</li>
                            <li>Alamat email</li>
                            <li>Nomor telepon</li>
                            <li>Informasi perangkat dan browser</li>
                            <li>Alamat IP</li>
                            <li>Data aktivitas saat mengakses website</li>
                        </ul>
                        <p class="mt-3">Informasi tersebut dapat dikumpulkan melalui:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Formulir kontak</li>
                            <li>Form pendaftaran</li>
                            <li>Cookies</li>
                            <li>Layanan analitik pihak ketiga</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">2. Penggunaan Informasi</h2>
                        <p>Informasi yang dikumpulkan digunakan untuk:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Memberikan layanan dan informasi kepada pengguna</li>
                            <li>Meningkatkan kualitas website dan layanan</li>
                            <li>Menanggapi pertanyaan atau permintaan pengguna</li>
                            <li>Mengirimkan informasi promosi atau update terbaru</li>
                            <li>Keperluan analisis dan pengembangan website</li>
                        </ul>
                        <p class="mt-3">Kami tidak menjual, menyewakan, atau membagikan data pribadi pengguna kepada pihak lain tanpa izin, kecuali diwajibkan oleh hukum.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">3. Cookies</h2>
                        <p>Website ini dapat menggunakan cookies untuk meningkatkan pengalaman pengguna. Cookies membantu kami memahami bagaimana pengunjung menggunakan website sehingga kami dapat meningkatkan layanan.</p>
                        <p class="mt-3">Pengguna dapat memilih untuk menonaktifkan cookies melalui pengaturan browser masing-masing.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">4. Keamanan Data</h2>
                        <p>Kami berusaha menjaga keamanan informasi pengguna dengan langkah-langkah teknis dan administratif yang wajar untuk mencegah akses, penggunaan, atau pengungkapan data tanpa izin.</p>
                        <p class="mt-3">Namun, perlu dipahami bahwa tidak ada metode transmisi data melalui internet yang sepenuhnya aman.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">5. Tautan ke Website Lain</h2>
                        <p>Website kami mungkin mengandung tautan menuju website pihak ketiga. Kami tidak bertanggung jawab atas kebijakan privasi maupun isi dari website tersebut.</p>
                        <p class="mt-3">Pengguna disarankan untuk membaca kebijakan privasi masing-masing website yang dikunjungi.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">6. Hak Pengguna</h2>
                        <p>Pengguna memiliki hak untuk:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Meminta akses terhadap data pribadi</li>
                            <li>Memperbaiki data yang tidak akurat</li>
                            <li>Meminta penghapusan data tertentu</li>
                            <li>Menolak penggunaan data untuk tujuan pemasaran</li>
                        </ul>
                        <p class="mt-3">Permintaan dapat diajukan melalui kontak yang tersedia di website.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">7. Perubahan Kebijakan Privasi</h2>
                        <p>Kami dapat memperbarui Privacy Policy ini sewaktu-waktu tanpa pemberitahuan terlebih dahulu. Perubahan akan berlaku segera setelah dipublikasikan di halaman ini.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">8. Kontak</h2>
                        <p>Jika Anda memiliki pertanyaan mengenai Privacy Policy ini, silakan hubungi kami melalui halaman kontak yang tersedia di website.</p>
                    </section>
                </div>
            </article>
        </section>
    </main>

    <footer class="border-t border-border bg-background py-10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 text-center md:flex-row md:text-left">
            <p class="text-sm text-muted-foreground">&copy; 2026 — Semua Hak Dilindungi</p>
            <div class="flex gap-6">
                <a href="{{ route('privacy-policy') }}" class="text-sm font-medium text-primary">Privacy Policy</a>
                <a href="{{ url('/') }}" class="text-sm text-muted-foreground hover:text-foreground">Beranda</a>
            </div>
        </div>
    </footer>
</body>
</html>
