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
- **`CHANGELOG.md`**: Rekam setiap penambahan fitur, perubahan penting, atau fungsi yang dihapus di bawah section `[Unreleased]` agar histori versi terlihat jelas.
- **`TODO.md`**: Ubah status pekerjaan yang sudah beres menjadi *checked* (`- [x]`). Jika Anda mendeteksi bug atau ide baru, tambahkan ke dalam list.
- **`docs/vX.X.X.md`**: (Ganti X dengan versi aktif) Apabila Anda membuat fungsi pembantu (*helper*) baru (misal: `fst_sesuatu()`), Anda WAJIB menambahkan deskripsi dan cara panggilannya di file dokumentasi versi terbaru agar Asisten AI dapat mempelajarinya.
- **`version.json`**: File registry publik untuk mencatat versi dan hash `fullstuck.php` terbaru.
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
| 3 | `docs/vX.X.X.md` | Tambahkan deskripsi fungsi baru di bagian API Reference versi terbaru. |
| 4 | `CHANGELOG.md` | Catat di bawah section `[Unreleased]`. |
| 5 | `php src/compiler-fullstuck.php` | Compile ulang agar `fullstuck.php` di root sinkron. |
| 6 | `version.json` | Update **hash** di file ini dengan nilai `FST_HASH` terbaru dari header `fullstuck.php`. Ini penting untuk fitur Remote Integrity Check. |

*(Note untuk AI Agent: Sebelum Anda memberikan summary final ke user, pastikan file `fullstuck.php` di root selalu ikut ter-update akibat proses build, agar sinkron dengan perubahan pada `src/`).*

## 6. Aturan Pembuatan Plugin (Store)
Untuk berkontribusi atau membuat plugin resmi yang dapat diinstal melalui Admin Dashboard, ikuti aturan berikut:

1. **Lokasi File**: File plugin wajib diletakkan di dalam folder `store/` pada root repositori ini.
2. **Konvensi Penamaan**: Nama file harus menggunakan prefix **`fst-`** diikuti dengan ID unik plugin, dan diakhiri dengan ekstensi `.php`.
   - Format: `fst-{id}.php`
   - Contoh: `fst-hello-world.php` (ID: `hello-world`)
3. **Unique ID**: ID plugin harus unik dan hanya boleh mengandung karakter alphanumeric, dash (`-`), dan underscore (`_`). ID ini akan menjadi penentu URL pengunduhan.
4. **Registrasi Store**: Setelah membuat file plugin, Anda **WAJIB** mendaftarkan plugin tersebut ke dalam file `store.json` di root repositori dengan format:
   ```json
   {
     "id": "id-plugin-anda",
     "name": "Nama Plugin",
     "description": "Deskripsi singkat fungsi plugin."
   }
   ```
5. **Keamanan**: Gunakan helper bawaan framework (seperti `fst_get`, `fst_post`, `fst_app`, dll) daripada memanggil variabel superglobal PHP secara langsung untuk menjaga keamanan dan portabilitas plugin.

### 7. Pembuatan Plugin (Manual)

* **Aturan Penamaan:** File wajib berada di `fst-plugins/` dan mengikuti format `fst-{id}.php` (contoh: `fst-hello-world.php`).
* **Registrasi:** Wajib memanggil `fst_register_plugin('{id}', ...)` dengan ID yang konsisten dengan nama file.
* **Routing & Sub-halaman:** Karena menggunakan rute tunggal `/stuck/p/{id}`, gunakan variabel `$_GET['action']` atau `fst_input('action')` untuk membuat sub-halaman (misal: halaman settings).
* **Keamanan Form:** Ingatkan pengembang untuk menggunakan `fst_method() === 'POST'`, `fst_csrf_check()`, `fst_csrf_field()`, dan `fst_redirect()` saat memproses form.

**Contoh kode utuh:**

```php
<?php
// File: fst-plugins/fst-hello-world.php

fst_register_plugin('hello-world', [
    'name' => 'Hello World',
    'menu_label' => 'Hello UI',
    'admin_route' => function() {
        $method = fst_method();
        $action = fst_input('action', 'index');

        if ($method === 'POST') {
            fst_csrf_check(); // WAJIB untuk form
            $nama = fst_input('nama');
            fst_flash_set('success_message', 'Tersimpan: ' . fst_escape($nama));
            fst_redirect('/stuck/p/hello-world');
        }

        if ($action === 'index') {
            $msg = fst_flash_get('success_message');
            if ($msg) echo "<p style='color:green;'>{\$msg}</p>";
            
            echo "<h2>Pengaturan Hello</h2>";
            echo '<form method="POST" action="/stuck/p/hello-world">
                    ' . fst_csrf_field() . '
                    <input type="text" name="nama" placeholder="Ketik nama...">
                    <button type="submit">Simpan</button>
                  </form>';
        }
    }
]);
```
