# Changelog

Semua perubahan yang mencolok (notable) pada proyek FullStuck.php akan didokumentasikan di file ini.
Format berdasarkan prinsip [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added
- **Arsitektur "Dua Dunia"**: Menambahkan alur pengembangan framework baru berbasis folder `src/`. Source code dipisah menjadi banyak sub-modul agar mudah di-maintain.
- **Compiler Script**: Menambahkan `src/compiler-fullstuck.php` untuk mem-*build* file modular kembali menjadi satu rilis utuh `fullstuck.php`.
- **System Map & Documentation**: Menambahkan folder `docs/` yang berisi `SYSTEM_MAP.md`, `ARCHITECTURE.md`, `DEVELOPMENT_FLOW.md`, `USING-FULLSTUCK.md`, dan perbaikan referensi API di `DOCUMENTATION.md`.

### Changed
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
