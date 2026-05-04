# FullStuck.php

**Micro-framework PHP single-file untuk membangun web app dan REST API dengan cepat.**

Tidak perlu Composer. Tidak perlu konfigurasi rumit. Cukup **1 file**, buka di browser, dan mulai *coding*.

---

## Kenapa FullStuck?

- ☁️ **Zero Dependency** — Tidak butuh Composer, npm, atau library eksternal apapun.
- 📦 **Single File** — Seluruh framework dikemas dalam satu file `fullstuck.php` (~1000 baris).
- ⚡ **Instant Setup** — Drop file ke server, buka di browser, selesai. Setup Wizard otomatis membuat konfigurasi.
- 🔀 **Hybrid Routing** — Pilih mode *Static* (gaya Laravel/Express) atau *Dynamic* (gaya PHP tradisional). Atau gabungkan keduanya.
- 🛡️ **Built-in Security** — CSRF protection, XSS escaping, dan input validation sudah tersedia tanpa plugin tambahan.
- 🗄️ **Database Ready** — PDO wrapper bawaan + Query Builder ringan untuk SQLite & MySQL.
- 🔧 **Developer Dashboard** — Panel admin tersembunyi untuk monitoring konfigurasi, route, dan scanning project (hanya di mode development).

---

## Quick Start

```bash
# 1. Download fullstuck.php dan taruh di folder project Anda
# 2. Jalankan PHP Built-in Server
php -S localhost:8000 fullstuck.php

# 3. Buka browser ke http://localhost:8000
# 4. Ikuti Setup Wizard — selesai!
```

Setelah setup, buat file `router.php` dan mulai tulis rute pertama Anda:

```php
<?php
fst_get('/', function() {
    fst_text('Hello, FullStuck!');
});

fst_get('/api/users', function() {
    $users = fst_db_select('users');
    fst_json(['data' => $users]);
});
```

---

## Dokumentasi

| Halaman | Keterangan |
|---------|------------|
| [Getting Started](getting-started.md) | Instalasi, setup, dan project pertama Anda. |
| [Routing](routing.md) | Static routing, dynamic routing, group, dan middleware. |
| [Database](database.md) | Koneksi database, raw query, dan Query Builder. |
| [Request & Response](request-response.md) | Mengambil input, mengirim JSON, view rendering, redirect. |
| [Security](security.md) | CSRF, XSS escaping, file upload, dan session management. |
| [Configuration](configuration.md) | Referensi lengkap parameter `fullstuck.json`. |
| [Examples](examples.md) | Kumpulan contoh kode dari studi kasus nyata. |

---

## Persyaratan Sistem

- PHP 8.0 atau lebih baru.
- Extension `pdo`, `json`, dan `session` (umumnya sudah aktif secara default).
- Extension `mbstring` dan `fileinfo` disarankan (framework tetap berjalan tanpanya).

---

## Lisensi

FullStuck.php adalah proyek *open-source*.
