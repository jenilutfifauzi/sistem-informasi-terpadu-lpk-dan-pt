<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms & Services - {{ config('app.name', 'SIT LPK') }}</title>
    <meta name="description" content="Terms & Services LPKS Gemilang Putra Bangsa tentang penggunaan website, layanan, data pengguna, biaya, dan ketentuan kerja sama.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                    Syarat dan Ketentuan
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-foreground sm:text-5xl">Terms &amp; Services</h1>
                <p class="mt-5 text-lg leading-8 text-muted-foreground">
                    Selamat datang di <strong class="font-semibold text-foreground">lpksgemilangputrabangsa.com</strong>. Dengan mengakses dan menggunakan website ini, Anda dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan berikut.
                </p>
            </div>

            <article class="rounded-3xl border border-border bg-card p-6 shadow-sm sm:p-10">
                <div class="space-y-9 text-base leading-8 text-muted-foreground">
                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">1. Tentang Layanan</h2>
                        <p>LPKS Gemilang Putra Bangsa merupakan perusahaan jasa penyaluran dan pelatihan tenaga kerja yang membantu mempertemukan pengguna jasa dengan tenaga kerja sesuai kebutuhan dan ketentuan yang berlaku.</p>
                        <p class="mt-3">Layanan kami dapat mencakup:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Rekrutmen tenaga kerja</li>
                            <li>Pelatihan dan pembekalan kerja</li>
                            <li>Penyaluran tenaga kerja</li>
                            <li>Konsultasi kebutuhan tenaga kerja</li>
                            <li>Pendampingan administrasi penempatan kerja</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">2. Penggunaan Website</h2>
                        <p>Pengguna website wajib:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Menggunakan website secara wajar dan tidak melanggar hukum</li>
                            <li>Tidak menyebarkan informasi palsu, spam, atau konten merugikan</li>
                            <li>Tidak mencoba merusak sistem website atau mengambil data tanpa izin</li>
                        </ul>
                        <p class="mt-3">Segala aktivitas yang melanggar hukum dapat ditindak sesuai peraturan yang berlaku di Indonesia.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">3. Informasi dan Data Pengguna</h2>
                        <p>Setiap data yang diberikan pengguna melalui formulir, WhatsApp, email, atau media lainnya akan dijaga kerahasiaannya dan hanya digunakan untuk kebutuhan layanan perusahaan.</p>
                        <p class="mt-3">Kami tidak memperjualbelikan data pribadi pengguna kepada pihak lain tanpa izin pengguna, kecuali diwajibkan oleh hukum.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">4. Proses Penyaluran Tenaga Kerja</h2>
                        <p>LPKS Gemilang Putra Bangsa berupaya melakukan seleksi dan pelatihan tenaga kerja secara profesional. Namun:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Penempatan kerja tetap mempertimbangkan kecocokan kebutuhan pengguna jasa</li>
                            <li>Keputusan penerimaan akhir berada pada pihak pengguna jasa atau perusahaan</li>
                            <li>Kandidat wajib memberikan data dan dokumen yang benar</li>
                        </ul>
                        <p class="mt-3">Apabila ditemukan data palsu atau manipulasi dokumen, perusahaan berhak membatalkan proses kerja sama.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">5. Pembayaran dan Biaya Layanan</h2>
                        <p>Biaya layanan, administrasi, atau pelatihan akan diinformasikan secara transparan kepada pengguna sebelum proses berjalan.</p>
                        <p class="mt-3">Segala transaksi dilakukan berdasarkan kesepakatan kedua belah pihak.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">6. Hak Kekayaan Intelektual</h2>
                        <p>Seluruh isi website seperti logo, desain, teks, foto, video, dan materi pelatihan merupakan milik LPKS Gemilang Putra Bangsa dan tidak boleh digunakan kembali tanpa izin tertulis.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">7. Batas Tanggung Jawab</h2>
                        <p>Kami berusaha memberikan layanan terbaik, namun tidak bertanggung jawab atas:</p>
                        <ul class="mt-3 list-disc space-y-1 pl-6">
                            <li>Gangguan teknis website</li>
                            <li>Kerugian akibat penggunaan website di luar ketentuan</li>
                            <li>Kesalahan data yang diberikan pengguna</li>
                            <li>Perselisihan di luar ruang lingkup kerja sama resmi perusahaan</li>
                        </ul>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">8. Perubahan Ketentuan</h2>
                        <p>LPKS Gemilang Putra Bangsa berhak mengubah Terms &amp; Services kapan saja tanpa pemberitahuan sebelumnya. Versi terbaru akan selalu tersedia di website resmi perusahaan.</p>
                    </section>

                    <section>
                        <h2 class="mb-3 text-2xl font-bold tracking-tight text-foreground">9. Kontak</h2>
                        <p>Apabila ada pertanyaan terkait Terms &amp; Services ini, silakan hubungi kami melalui Website Resmi LPKS Gemilang Putra Bangsa.</p>
                        <p class="mt-3">Email dan nomor kontak resmi dapat dilihat pada halaman kontak website perusahaan.</p>
                    </section>
                </div>
            </article>
        </section>
    </main>

    <footer class="border-t border-border bg-background py-10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 text-center md:flex-row md:text-left">
            <p class="text-sm text-muted-foreground">&copy; 2026 — Semua Hak Dilindungi</p>
            <div class="flex gap-6">
                <a href="{{ route('privacy-policy') }}" class="text-sm text-muted-foreground hover:text-foreground">Privacy Policy</a>
                <a href="{{ route('terms-of-service') }}" class="text-sm font-medium text-primary">Terms of Service</a>
                <a href="{{ url('/') }}" class="text-sm text-muted-foreground hover:text-foreground">Beranda</a>
            </div>
        </div>
    </footer>
</body>
</html>
