# FullStuck.php

Sebuah cita-cita framework untuk semua project yang mengedepankan filosofi *single-file* (minim dependensi), mudah dideploy, dan tidak membingungkan.

## Fitur Utama
- **Zero Config Setup**: Drop file `fullstuck.php` di webserver, buka di browser, instalasi via GUI akan membuat `.htaccess` dan `fullstuck.json`.
- **Hybrid Routing**: Mendukung `static` route (seperti Laravel/Express) dan `dynamic` route (seperti PHP tradisional yang otomatis membaca path folder).
- **Built-in Developer Dashboard**: Memantau konfigurasi, routes, dan scanning error proyek di mode development.
- **Database Ready**: Wrapper bawaan `fst_db()` menggunakan PDO, support SQLite & MySQL.

## Memulai (Pengguna Framework)
1. Taruh `fullstuck.php` di dalam web root.
2. Akses dari browser Anda.
3. Ikuti setup wizard.

## Pengembangan (Framework Developers & AI Agents)
Project ini menggunakan arsitektur **Dua Dunia**. Kami mengembangkannya secara modular di folder `src/`, lalu meng-compile-nya menjadi 1 file rilis.
- 🤖 **Bagi AI Agents / Contributor Baru:** Harap membaca [docs-dev/SYSTEM_MAP.md](docs-dev/SYSTEM_MAP.md) terlebih dahulu untuk memuat (*load*) konteks sistem proyek ini secara aman.
- **Dokumentasi API:** [docs-dev/DOCUMENTATION.md](docs-dev/DOCUMENTATION.md)
- **Arsitektur Build:** [docs-dev/ARCHITECTURE.md](docs-dev/ARCHITECTURE.md)
