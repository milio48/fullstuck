# Changelog

Semua perubahan yang mencolok (notable) pada proyek FullStuck.php akan didokumentasikan di file ini.
Format berdasarkan prinsip [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added
- **JSON Body Auto-Parsing**: `fst_request()` dan `fst_input()` kini otomatis mendeteksi dan mem-parse `application/json` body dari `php://input`. Didukung oleh fungsi internal `_fst_parsed_body()` dengan *static cache* agar stream hanya dibaca sekali per request. Menghilangkan kebutuhan boilerplate `json_decode(file_get_contents('php://input'))` di sisi project.
- **Extension Health Check di Admin Dashboard**: Halaman Monitor kini menampilkan tabel pengecekan extension PHP (`mbstring`, `fileinfo`, `json`, `pdo`, `session`) dengan status *Required* atau *Recommended*. Extension yang tidak aktif otomatis masuk ke daftar Warnings/Errors. Halaman Server Info juga diperkaya dengan informasi SAPI dan status extension.
- **Basic Query Builder**: Menambahkan fungsi utilitas ringan `fst_db_select`, `fst_db_insert`, `fst_db_update`, dan `fst_db_delete` untuk mempermudah operasi CRUD tanpa harus menulis sintaks SQL secara manual berulang-ulang, lengkap dengan implementasi *Prepared Statements* otomatis di balik layar.
- **Request Validator (`fst_validate`)**: Fungsi utility untuk memvalidasi dan membersihkan data array seperti `$_POST` atau `$_GET`. Mendukung rule seperti `required`, `email`, `min:X`, `max:X`, `numeric`, dan `in:X,Y,Z`. Mengembalikan data yang sudah disanitasi dengan `trim()`.
- **Beautiful Error Handling UI**: Menambahkan UI yang informatif dan bergaya *Whoops* saat terjadi Exception atau Fatal Error di environment `development`. UI menampilkan *class name*, pesan error, letak file, dan *code snippet* (preview kode di sekitarnya). Saat di `production`, detail error akan disembunyikan dan hanya dicatat di server log untuk keamanan.
- **Middleware System**: Menambahkan dukungan *middleware* (mendukung fungsi tunggal atau *array*) melalui parameter opsional pada helper routing seperti `fst_get()`, `fst_post()`, dan `fst_group()`. Eksekusi routing akan terhenti jika middleware me-return `false`.
- **Arsitektur "Dua Dunia"**: Menambahkan alur pengembangan framework baru berbasis folder `src/`. Source code dipisah menjadi banyak sub-modul agar mudah di-maintain.
- **Compiler Script**: Menambahkan `src/compiler-fullstuck.php` untuk mem-*build* file modular kembali menjadi satu rilis utuh `fullstuck.php`.
- **System Map & Documentation**: Menambahkan folder `docs/` yang berisi `SYSTEM_MAP.md`, `ARCHITECTURE.md`, `DEVELOPMENT_FLOW.md`, `USING-FULLSTUCK.md`, dan perbaikan referensi API di `DOCUMENTATION.md`.

### Changed
- **Fix Admin Header Pollution**: Fungsi `fst_admin_show_login()` dan `fst_admin_render_page()` kini secara eksplisit menetapkan `Content-Type: text/html; charset=UTF-8`. Ini mencegah *header pollution* dari project-level `header()` calls (contoh: project API yang menetapkan `application/json` secara global) bocor ke halaman HTML internal framework.
- **Graceful Fallback `mbstring`**: Fungsi `fst_validate` kini menggunakan helper internal `_fst_strlen()` yang secara otomatis fallback ke `strlen()` jika extension `mbstring` tidak tersedia. Sesuai filosofi Zero-Dependency — framework tetap berjalan di environment PHP minimalis.
- **Deteksi Root Project Dinamis (`FST_ROOT_DIR`)**: Konstanta `FST_ROOT_DIR` kini ditentukan secara dinamis berdasarkan SAPI environment (`cli-server` → `$_SERVER['DOCUMENT_ROOT']`, `cli` → `getcwd()`, web server → `__DIR__`). Ini memperbaiki bug kritis di mana framework tidak bisa menemukan `fullstuck.json` saat dipanggil dari luar direktori project (contoh: `php -S localhost:8000 ../../fullstuck.php`). Pengguna juga dapat men-*define* `FST_ROOT_DIR` sendiri sebelum *include* untuk skenario lanjutan.
- **Refactor `fst_run()`**: Kode pemrosesan rute yang panjang telah dipecah menjadi kumpulan *private helper* (`_fst_get_request_paths`, `_fst_serve_static_asset`, `_fst_match_static_routes`, `_fst_match_dynamic_routes`) untuk meningkatkan keterbacaan (*readability*) dan kemudahan *maintenance*.
- Refactor pemisahan komponen `fullstuck.php` yang tadinya menjadi 1 file raksasa ke sub-modul: `core.php`, `database.php`, `router.php`, `http.php`, `view.php`, `utility.php`, `install.php`, `admin.php`, dan `bootstrap.php`.
- Logika fungsi-fungsi admin / *dashboard* telah dipisahkan secara rahasia ke dalam `src/admin.php` di dalam Dunia 1.

### Removed
- **PSR-4 Autoloader**: Wacana penambahan autoloader dibatalkan karena dinilai melenceng dari gaya pengembangan minimalis-prosedural (*Zero-Dependency*) milik FullStuck.

## [0.2.6] - *Versi Dasar (Legacy)*
- Rilis arsitektur awal FullStuck.php sebagai *micro-framework single-file* 900+ baris.
- *Hybrid Routing* berjalan stabil.
- Koneksi *Database* PDO Helper (`fst_db()`) untuk SQLite & MySQL.
- *Flash Messages*, *Session Management*, dan *CSRF Protection* bawaan.
- GUI Instalasi *wizard* interaktif untuk `fullstuck.json`.
