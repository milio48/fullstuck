# RESTful Task Manager (API-Only)

Proyek ini adalah demonstrasi pembuatan **REST API** (*Headless Backend*) menggunakan micro-framework **FullStuck.php**.

## Fitur Framework yang Diuji
1. **JSON Response (`fst_json`)**: Mengeluarkan response API standar.
2. **Request Validator (`fst_validate`)**: Memvalidasi request payload.
3. **Middleware API Key**: Memastikan hanya user berizin yang bisa mengakses endpoint.
4. **Group Routing (`fst_group`)**: Prefix endpoint terpusat di `/api/v1`.
5. **REST Methods**: Penggunaan `fst_get()`, `fst_post()`, `fst_put()`, `fst_delete()`.
6. **Query Builder API**: Operasi database CRUD sederhana via `fst_db_select`, `fst_db_insert`, `fst_db_update`, `fst_db_delete`.

## Cara Menjalankan

1. Buka terminal di direktori proyek ini.
2. Jalankan PHP Built-in Server dengan merujuk pada framework `fullstuck.php` di luar folder:
   ```bash
   php -S localhost:8000 ../../fullstuck.php
   ```
3. Lakukan instalasi database awal (akan meng-generate API Key demo):
   **URL:** `GET http://localhost:8000/setup`

## Dokumentasi Endpoint

Pastikan Anda menyertakan parameter `api_key` di setiap request (bisa lewat HTTP Header `X-API-KEY` atau *query string* `?api_key=XYZ`).

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/api/v1/me` | Mengambil data profil user. |
| GET | `/api/v1/tasks` | Mengambil daftar task milik user. Bisa difilter `?status=pending` |
| POST | `/api/v1/tasks` | Membuat task baru. Payload: `title` (Wajib, min 3 karakter). |
| PUT | `/api/v1/tasks/{id}` | Update status. Payload: `status` (Wajib bernilai `pending` atau `done`). |
| DELETE | `/api/v1/tasks/{id}` | Menghapus task milik user terkait. |
