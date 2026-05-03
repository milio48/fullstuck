# Personal Blog System (SSR)

Proyek ini adalah demonstrasi pembuatan Website Tradisional yang dirender di server (*Server-Side Rendering*) menggunakan **FullStuck.php**.

## Fitur Framework yang Diuji
1. **Dynamic URL Routing**: Membaca parameter regex seperti `/post/{slug:s}`.
2. **View Extraction (`fst_view`)**: Meload HTML view dan memasukkan data (array extraction).
3. **HTTP 404 (`fst_abort`)**: Menangani halaman yang tidak ditemukan secara mulus.
4. **Query Builder API**: Menampilkan daftar artikel dari database.

## Cara Menjalankan

1. Buka terminal di direktori proyek ini.
2. Jalankan PHP Built-in Server:
   ```bash
   php -S localhost:8000 ../../fullstuck.php
   ```
3. Lakukan setup awal untuk memasukkan data blog ke database:
   Buka **URL:** `http://localhost:8000/setup`
4. Kembali ke halaman utama `http://localhost:8000/` untuk melihat hasilnya.
