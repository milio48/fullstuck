# Getting Started

Panduan ini akan membantu Anda menginstal dan menjalankan project pertama dengan **FullStuck.php** dalam hitungan menit.

---

## 1. Download

Download file `fullstuck.php` dari repository dan taruh di folder project Anda.

```
my-project/
└── fullstuck.php
```

Hanya itu. Tidak ada folder `vendor/`, tidak ada `composer.json`.

---

## 2. Jalankan Server

### Opsi A: PHP Built-in Server (Lokal / Development)

```bash
cd my-project/
php -S localhost:8000 fullstuck.php
```

### Opsi B: Shared Hosting (Apache)

Upload `fullstuck.php` ke folder `public_html/` Anda. Framework akan otomatis membuat file `.htaccess` saat proses setup.

### Opsi C: Nginx

Tambahkan konfigurasi berikut di server block Anda:

```nginx
location / {
    try_files $uri $uri/ /fullstuck.php?$query_string;
}
```

---

## 3. Setup Wizard

Buka browser dan akses `http://localhost:8000`. Jika ini pertama kalinya, Anda akan disambut oleh **Setup Wizard** interaktif:

1. Wizard akan memeriksa versi PHP dan extension yang diperlukan.
2. Anda diminta memilih mode routing (`static` atau `dynamic`).
3. Anda diminta mengatur koneksi database (opsional — bisa pilih `none`).
4. Anda diminta membuat password untuk Developer Dashboard.
5. Klik **Install** — wizard akan membuat file `fullstuck.json` dan `.htaccess` secara otomatis.

Setelah selesai, Anda siap *coding*!

---

## 4. Project Pertama Anda

Buat file `router.php` di folder yang sama:

```
my-project/
├── fullstuck.php
├── fullstuck.json    ← dibuat otomatis oleh wizard
├── .htaccess         ← dibuat otomatis oleh wizard
└── router.php        ← buat sendiri
```

Isi `router.php` dengan kode berikut:

```php
<?php

// Halaman utama
fst_get('/', function() {
    echo '<h1>Selamat datang di FullStuck!</h1>';
    echo '<p>Framework Anda sudah berjalan.</p>';
    echo '<a href="/hello/dunia">Coba Dynamic Route</a>';
});

// Route dengan parameter
fst_get('/hello/{name:a}', function($name) {
    $safe_name = e($name); // XSS-safe output
    echo "<h1>Halo, {$safe_name}!</h1>";
    echo '<a href="/">Kembali</a>';
});

// API Endpoint
fst_get('/api/status', function() {
    fst_json([
        'status' => 'ok',
        'framework' => 'FullStuck.php',
        'php_version' => PHP_VERSION
    ]);
});
```

Buka `http://localhost:8000/` dan lihat hasilnya!

---

## 5. Developer Dashboard

Jika environment Anda di-set ke `development`, Anda bisa mengakses panel admin tersembunyi di:

```
http://localhost:8000/stuck
```

*(URL ini bisa dikustomisasi di `fullstuck.json`)*

Dashboard menyediakan:
- **System Monitor** — Cek konfigurasi, status database, dan extension PHP.
- **Config Editor** — Edit `fullstuck.json` langsung dari browser.
- **Route List** — Lihat semua rute yang terdaftar beserta pattern regex-nya.
- **Server Info** — Informasi lengkap PHP dan server.
- **Scan Project** — Analisis file project Anda untuk melihat fungsi `fst_*` apa saja yang digunakan.

---

## Langkah Selanjutnya

- 📖 [Routing](routing.md) — Pelajari static routing, dynamic routing, dan middleware.
- 🗄️ [Database](database.md) — Hubungkan ke SQLite atau MySQL.
- 🔐 [Security](security.md) — Lindungi form Anda dengan CSRF dan XSS escaping.
