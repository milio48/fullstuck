# Secure Document Drive (Portal Upload)

Proyek ini adalah demonstrasi pembuatan portal unggah dokumen (seperti Google Drive mini) yang sangat aman menggunakan **FullStuck.php**.

## Fitur Framework yang Diuji
1. **Keamanan Cross-Site Request Forgery (CSRF)**: Menggunakan `fst_csrf_field()` di Form dan `fst_csrf_check()` di backend.
2. **Session & Flash Messages**: Penggunaan `fst_session_set()`, `fst_session_get()`, dan `fst_flash_get()` untuk manajemen login sederhana dan pesan notifikasi (seperti alert "Login Sukses" atau "Upload Gagal").
3. **Middleware Autentikasi**: Memblokir akses ke `/drive` bagi yang belum login menggunakan parameter Middleware pada `fst_group`.
4. **File Upload Utility**: Menggunakan `fst_upload()` untuk menangani penyimpanan file dengan filter ekstensi & *size* yang super ringkas.
5. **Static Asset Serving**: Menampilkan file CSS dari folder `/public` dan membolehkan download dari folder `/uploads` secara langsung.

## Cara Menjalankan

1. Buka terminal di direktori proyek ini.
2. Pastikan Anda punya koneksi internet (agar CSS berjalan normal, namun tidak wajib).
3. Jalankan server:
   ```bash
   php -S localhost:8000 ../../fullstuck.php
   ```
4. Buka di Browser: `http://localhost:8000/`
   *(Password Login default: **rahasia**)*
