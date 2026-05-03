# FullStuck.php TODO & Refactoring Plan

Berikut adalah daftar rencana perbaikan dan pengembangan (refactoring) untuk `fullstuck.php`. Framework ini memiliki visi meminimalisir dependensi, namun ada beberapa aspek yang bisa dioptimalkan agar lebih elegan dan aman.

## 1. Arsitektur "Dua Dunia" & Refactoring
- [x] **Pemisahan Mode Development (Modular) vs Release (Single-File)**: 
  Selesai! Menggunakan folder `src/` dan `compiler-fullstuck.php`.
- [x] **Pemisahan Admin Panel**: 
  Selesai! Dipindahkan ke `src/admin.php`.
- [x] **Perampingan fungsi `fst_run()`**:
  Fungsi dispatch router telah dipisah ke dalam private helpers seperti `_fst_serve_static_asset()`, `_fst_match_static_routes()`, dan `_fst_match_dynamic_routes()` sehingga mudah dibaca.

## 2. Fitur Baru (Tambahan)
- [x] **Middleware System**:
  Selesai! Telah ditambahkan sebagai parameter opsional ketiga/keempat pada `fst_get()`, `fst_group()`, dll. Jika middleware me-*return* `false`, proses *routing* akan terhenti.
- [ ] **Request Validator**:
  Fungsi validasi sederhana bawaan `fst_validate($rules)` untuk menyaring input `$_POST/$_GET` (misal: `required`, `email`, `min:5`).
- [ ] **Basic Query Builder**:
  Meskipun `fst_db()` sudah praktis, bisa ditingkatkan dengan fungsi Query builder super ringan, contoh: `fst_db_insert('users', ['name'=>'Budi'])` atau `fst_db_select('users', ['id' => 1])`.

## 3. Peningkatan Keamanan & Handling Error
- [ ] **Exception / Error Handler**:
  Tangkap semua *throw exception* dan *fatal error* di level atas menggunakan `set_exception_handler` dan `set_error_handler`, lalu arahkan ke tampilan UI error page yang informatif mirip *Whoops* (di mode dev) atau log ke file (di mode prod).
- [ ] **Strict Typing & Sanitization**:
  Tambahkan pembersihan default (XSS prevention helper) misalnya `fst_escape()` untuk mencetak data HTML aman di view.

## 4. UI/UX Dashboard
- [ ] Poles UI Dashboard instalasi dan Monitor agar terlihat lebih modern.
- [ ] Tambahkan tombol *Clear Cache* (jika kelak mengimplementasikan cache router).

---
## Prioritas Eksekusi
1. Rancang UI Error Handling yang informatif (menangkap semua Fatal Error / Exception).
2. Fungsi Request Validator (`fst_validate`).
