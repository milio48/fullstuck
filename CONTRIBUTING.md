# Standard Operating Procedure (SOP) Pengembangan - Dunia 1

Dokumen ini adalah **panduan mutlak** bagi Developer dan **AI Agent** yang bertugas mengembangkan *source code* framework `FullStuck.php` (pekerjaan di Dunia 1).

## 1. Alur Kerja (Workflow)
Setiap kali menerima instruksi untuk menambah fitur atau memperbaiki *bug*, ikuti urutan langkah kerja ini:
1. **Analisis Konteks**: Baca `docs-dev/SYSTEM_MAP.md` dan pahami modul mana di dalam `src/` yang paling tepat untuk diubah.
2. **Modifikasi Kode**: **DILARANG KERAS** memodifikasi file `fullstuck.php` di root. Lakukan perubahan hanya pada file-file di dalam `src/`.
3. **Kompilasi Otomatis**: Satukan kembali seluruh *source code* ke dalam file rilis menggunakan script compiler:
   ```bash
   php src/compiler-fullstuck.php
   ```
4. **Validasi (Testing)**: Validasi bahwa fitur berjalan dengan baik di "Dunia 2" menggunakan folder `test/` (Lihat aturan poin 3).
5. **Pencatatan**: Lakukan pembaruan dokumen laporan.

## 2. Aturan Dokumentasi dan Pelaporan (Reporting)
Setiap perubahan fungsional atau arsitektural **WAJIB** didokumentasikan agar *memory/context* tetap sinkron:
- **`docs-dev/CHANGELOG.md`**: Rekam setiap penambahan fitur, perubahan penting, atau fungsi yang dihapus di bawah section `[Unreleased]` agar histori versi terlihat jelas.
- **`TODO.md`**: Ubah status pekerjaan yang sudah beres menjadi *checked* (`- [x]`). Jika Anda mendeteksi bug atau ide baru, tambahkan ke dalam list.
- **`docs-dev/DOCUMENTATION.md`**: Apabila Anda membuat fungsi pembantu (*helper*) baru (misal: `fst_sesuatu()`), Anda WAJIB menambahkan deskripsi dan cara panggilannya di file ini.
- **`docs-dev/ARCHITECTURE.md` atau `SYSTEM_MAP.md`**: Apabila Anda menambah file sistem baru atau merubah arsitektur *flow*.

## 3. Aturan Pengujian Fitur (Folder `test/`)
Saat Anda menambahkan fitur baru (atau saat AI diminta memberi contoh), Anda wajib mensimulasikan lingkungan "Dunia 2" pengguna asli di dalam folder `test/`.

**Bagaimana cara kerjanya jika `fullstuck.php` ada di root?**
Anda tidak perlu menduplikasi file `fullstuck.php` ke dalam folder test. File tersebut tetap berdiam di root project. Anda cukup menjalankan *PHP Server* dari dalam sub-folder uji coba dengan me-referensi (*pointing*) ke file di root.

**SOP Eksekusi Test:**
1. Buat folder test spesifik. Contoh: `test/contoh-middleware/`
2. Di dalam folder test tersebut, buat konfigurasi simulasi (misal: buat `router.php`, `fullstuck.json`, atau views jika dibutuhkan).
3. Buka terminal lalu masuk ke folder test tersebut:
   ```bash
   cd test/contoh-middleware/
   ```
4. Jalankan server simulasi, dengan mengarahkan *router script* PHP ke file `fullstuck.php` milik root direktori:
   ```bash
   php -S localhost:8000 ../../fullstuck.php
   ```
5. Buka `http://localhost:8000` di browser. Framework kini berjalan dengan *working directory* di dalam folder test, tetapi menggunakan engine terbaru yang ada di root!

## 4. Aturan Git Commit
Pesan commit harus rapi, ringkas, dan mengikuti standar *Conventional Commits*:
- `feat: [nama fitur]` - Penambahan fungsionalitas / *helper* baru.
- `fix: [nama bug]` - Perbaikan *error* / logika yang salah.
- `docs: [penjelasan]` - Update pada folder `docs/` atau `README.md`.
- `refactor: [penjelasan]` - Merombak kode di `src/` tanpa mengubah fitur *end-user*.
- `test: [penjelasan]` - Penambahan/uji coba kasus pada folder `test/`.
- `build: [penjelasan]` - Perubahan pada `src/compiler-fullstuck.php`.

## 5. Checklist Wajib Saat Menambah Fungsi `fst_*` Baru
Setiap kali menambah fungsi baru ke framework (contoh: `fst_db_select`, `fst_validate`, dll), Anda **WAJIB** melakukan update pada lokasi-lokasi berikut agar seluruh ekosistem tetap sinkron:

| # | Lokasi File | Yang Diupdate |
|---|-------------|---------------|
| 1 | `src/*.php` | Implementasi fungsi baru. |
| 2 | `src/admin.php` → `$function_groups` | **Daftarkan** nama fungsi ke grup yang sesuai di dalam array `$function_groups` pada fungsi `fst_admin_run_scan()`. Jika tidak, fungsi akan muncul sebagai **Unknown** saat user menjalankan fitur Scan Project di Admin Dashboard. |
| 3 | `docs/v0.1.0.md` | Tambahkan deskripsi fungsi baru di bagian API Reference. |
| 4 | `CHANGELOG.md` | Catat di bawah section `[Unreleased]`. |
| 5 | `php src/compiler-fullstuck.php` | Compile ulang agar `fullstuck.php` di root sinkron. |

*(Note untuk AI Agent: Sebelum Anda memberikan summary final ke user, pastikan file `fullstuck.php` di root selalu ikut ter-update akibat proses build, agar sinkron dengan perubahan pada `src/`).*
