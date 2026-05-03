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
