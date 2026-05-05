# Arsitektur "Dua Dunia" FullStuck.php

Sesuai dengan filosofi framework ini, FullStuck.php didesain agar sangat mudah digunakan sebagai sebuah *single-file framework* (hanya butuh menaruh 1 file `fullstuck.php` ke root server). Namun, hal ini bisa membuat pengembangan framework itu sendiri menjadi sangat sulit apabila semuanya tertumpuk di dalam satu file raksasa.

Oleh karena itu, framework ini menggunakan konsep **Dua Dunia**:

## 1. Dunia 1 (Pengembangan / Framework Development)
Di dunia ini, pengembangan source code framework dilakukan secara modular agar mudah dibaca, di-*maintain*, dan dikembangkan (*Developer Experience* yang baik). Kode dipecah berdasarkan tanggung jawab fungsinya dan diletakkan di dalam folder `src/`.

Berikut adalah struktur file modular di dalam folder `src/`:
- `core.php` : Berisi inisialisasi awal, manajemen *error reporting*, konstanta environment, dan sistem baca file JSON konfigurasi.
- `database.php` : Wrapper koneksi `PDO` (mendukung driver SQLite dan MySQL) beserta helper utama query `fst_db()`.
- `router.php` : Jantung dari framework. Berisi logika registrasi rute, dispatch / handler request (termasuk static/dynamic file handler fallback), dan handler error/abort.
- `http.php` : Modul yang menangani URI request, method, payload HTTP (`$_GET`/`$_POST`), CSRF Protection, mekanisme Flash & Session, File Upload, dan output Response (JSON/Text/Redirect).
- `view.php` : Menangani file output rendering (HTML interface). Memuat rendering templating (`fst_view`, `fst_partial`) dan manajemen sajian file aset statik.
- `utility.php` : Modul helper / utility yang berisi fungsi bantu penunjang, seperti fungsi *dump and die* (`fst_dump`, `fst_dd`).
- `install.php` : Tampilan Antarmuka Grafis (GUI Wizard) untuk memandu proses instalasi pertama kali apabila `fullstuck.json` belum tercipta, lengkap dengan validasi lingkungan/server.
- `admin.php` : Dashboard Developer bawaan. Meliputi logika login admin terproteksi, monitoring server status, konfigurasi editor berbasis web, pemantau route, dan source code scanner (mendata letak pemanggilan helper `fst_`).
- `bootstrap.php` : Titik akhir (penutup). Menyatukan instruksi untuk *load* rute-rute tambahan pengguna dan akhirnya mengeksekusi `fst_run()` untuk menjalankan engine.

## 2. Dunia 2 (Rilis / Penggunaan oleh End-User)
Di dunia ini, *End-User* (pengguna framework) hanya membutuhkan satu file rilis saja yang efisien untuk di-hosting.
Untuk menyatukan seluruh kode dari Dunia 1 menjadi Dunia 2, digunakan sebuah **Compiler Script**.

### Compiler (`src/compiler-fullstuck.php`)
Ini adalah script *build tool* sederhana yang mengotomatisasi penggabungan. 
- Script ini akan membaca semua potongan file `.php` yang ada di dalam `src/` secara berurutan agar dependensi fungsinya tidak rusak.
- Tag parser akan menghapus semua sisa tag pembuka `<?php` dan tag penutup `?>` pada setiap potongan kode.
- Hasil gabungan akan disatukan kembali ke dalam satu file rilis baru `fullstuck.php` murni yang langsung siap di-commit atau dirilis ke web root pengguna.

**Cara Build:**
Jalankan perintah ini di terminal / command prompt setiap kali selesai melakukan modifikasi pada kode di dalam `src/`:
```bash
php src/compiler-fullstuck.php
```
Maka `fullstuck.php` akan otomatis terbarui dari *source* terkini.
# System Map: FullStuck.php

*Dokumen ini adalah titik awal (entry point) bagi Developer maupun AI Agent untuk memahami konteks keseluruhan repositori FullStuck.php dengan cepat dan aman.*

## 1. Filosofi & Arsitektur Utama
- **Arsitektur "Dua Dunia"**: FullStuck dibangun dengan sistem modular di dalam `src/`, namun digunakan oleh *end-user* sebagai satu file utuh (`fullstuck.php`). Baca panduannya di [ARCHITECTURE.md](ARCHITECTURE.md).
- **Desain Core**: Ini adalah *micro-framework* tanpa dependensi pihak ketiga (no composer needed). Fitur unggulannya adalah *Hybrid Routing* (statis bergaya modern & dinamis bergaya PHP lawas). Baca referensi API-nya di [DOCUMENTATION.md](DOCUMENTATION.md).
- **Roadmap Pengembangan**: Lihat daftar pekerjaan selanjutnya di [TODO.md](TODO.md).

## 2. Struktur Direktori (File Tree)

```text
/ (Project Root)
├── fullstuck.php               # [DUNIA 2] Framework rilis single-file. (JANGAN DIEDIT MANUAL!)
├── fullstuck.json              # File konfigurasi yang otomatis terbuat saat GUI Setup Wizard berjalan.
├── README.md                   # Pengenalan publik repository.
├── docs-dev/                   # Direktori Dokumentasi & Konteks (Anda ada di sini)
│   ├── SYSTEM_MAP.md           # Peta sistem untuk AI / Developer baru.
│   ├── ARCHITECTURE.md         # Aturan main arsitektur "Dua Dunia" & compiler.
│   ├── DEVELOPMENT_FLOW.md     # SOP / Aturan pengembangan Dunia 1 (Git, Test, & Report).
│   ├── CHANGELOG.md            # Catatan riwayat versi, penambahan fitur, dan perbaikan.
│   ├── USING-FULLSTUCK.md      # Panduan setup & eksekusi framework untuk pengguna.
│   ├── DOCUMENTATION.md        # Penjelasan flow dan daftar lengkap fungsi helper fst_*.
│   └── TODO.md                 # Rencana refactoring dan fitur baru.
├── docs/                       # Dokumentasi publik/end-user (Source untuk GitHub Pages).
└── src/                        # [DUNIA 1] Source code framework modular (EDIT KODE DI SINI)
    ├── compiler-fullstuck.php  # Script builder (Satukan src/ menjadi fullstuck.php)
    ├── core.php                # Konstanta dasar, inisialisasi & error reporting
    ├── database.php            # Koneksi PDO & wrapper fungsi fst_db()
    ├── router.php              # Logika sistem rute, dispatcher utama, & fst_abort()
    ├── http.php                # Modul Request/Response, Session, CSRF, & Upload
    ├── view.php                # Rendering view HTML & asset statik
    ├── utility.php             # Fungsi penunjang debugging (fst_dump, fst_dd)
    ├── install.php             # Antarmuka (GUI) untuk Setup Wizard awal
    ├── admin.php               # Dashboard tersembunyi untuk developer (stuck panel)
    └── bootstrap.php           # Script eksekusi penutup yang memanggil fst_run()
```

## 3. Aturan Modifikasi Kode (Standard Operating Procedure)
Bagi AI Agent atau Developer yang hendak mengubah / menambah fitur, **WAJIB** mengikuti SOP ini:
1. **Pahami Lokasi**: Jangan pernah mengubah baris kode langsung di `fullstuck.php` yang ada di root direktori.
2. **Ubah di `src/`**: Lakukan perubahan fungsi, class, atau logika pada file yang paling relevan di dalam folder `src/`.
3. **Compile Code**: Setelah selesai merubah `src/`, jalankan perintah berikut untuk menghasilkan file `fullstuck.php` yang baru:
   ```bash
   php src/compiler-fullstuck.php
   ```
4. **Testing**: Lakukan uji coba terhadap output `fullstuck.php`.

