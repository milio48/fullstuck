# FullStuck.php v0.2.5 - Dokumentasi Resmi

**FullStuck.php** adalah sebuah micro-framework PHP *single-file* (minim dependensi) yang dirancang untuk mempercepat pembuatan project web, baik API maupun full-stack (view based). Framework ini mendukung routing statis bergaya Express/Laravel, maupun routing dinamis bergaya Apache/PHP native.

## 1. Alur Eksekusi (Flowchart & Lifecycle)

Ketika sebuah request masuk (biasanya diteruskan melalui `.htaccess` ke `fullstuck.php`), inilah urutan eksekusi framework:

1. **Bootstrapping & Check Install**: 
   - Framework memulai `session_start()`.
   - Mendeteksi `FST_ROOT_DIR` secara dinamis: jika dijalankan via `php -S` (cli-server), menggunakan `$_SERVER['DOCUMENT_ROOT']`; jika via CLI, menggunakan `getcwd()`; jika di web server biasa (Apache/Nginx), fallback ke `__DIR__`. Pengguna juga bisa men-*define* `FST_ROOT_DIR` sebelum *include* untuk kontrol penuh.
   - Mengecek apakah file `fullstuck.json` ada di `FST_ROOT_DIR`. Jika **TIDAK**, halaman instalasi GUI akan muncul (`fst_handle_installation()`) untuk men-generate config dan `.htaccess`.
2. **Load Configuration**: 
   - Membaca dan men-decode file `fullstuck.json` ke dalam variabel global `$fst_config`.
   - Mengatur `error_reporting` berdasarkan status *environment* (development/production).
3. **Database Connection**: 
   - Mengecek konfigurasi database (MySQL, SQLite, atau None).
   - Membuka koneksi menggunakan PDO dan menyimpannya di global variabel `$fst_pdo`.
4. **Fungsi Helpers (FST Core)**:
   - Mendeklarasikan seluruh fungsi *helper* berawalan `fst_` seperti `fst_route()`, `fst_db()`, `fst_view()`, dll.
5. **Admin Panel (Dev Mode)**:
   - Jika environment diatur ke `development`, framework mendaftarkan rute internal untuk Admin Panel (default: `/stuck`). Panel ini berisi fitur *Monitoring*, *Config Editor*, *Route Viewer*, dan *Code Scanner*.
6. **Include Routes (Static Mode)**:
   - Jika konfigurasi `routing.mode` di-set ke `static`, framework akan melakukan *require* pada file routing yang telah didefinisikan (contoh: `router.php`).
7. **Dispatcher / Eksekusi Request (`fst_run()`)**:
   - **Prioritas 1 (Aset Statik):** Mengecek apakah request mengarah ke folder publik (`public_folders` seperti `/assets`, `/uploads`). Jika file ada, langsung disajikan beserta Mime-Type yang sesuai.
   - **Prioritas 2 (Rute Terdaftar):** Mencocokkan method dan URI ke regex yang sudah didaftarkan lewat `fst_get`, `fst_post`, dll. Jika cocok, eksekusi callback-nya.
   - **Prioritas 3 (Routing Dinamis/Filesystem):** Jika diaktifkan, mengecek apakah file PHP atau HTML dengan nama path tersebut ada di dalam direktori. Framework bertindak layaknya server Apache tradisional.
   - **Fallback:** Jika tidak ada satupun yang cocok, lempar ke `fst_abort(404)`.

---

## 2. Fitur & Fungsi Helper (API Reference)

### Routing
- `fst_get($path, $callback, $middleware = [])`, `fst_post()`, `fst_put()`, `fst_patch()`, `fst_delete()`, `fst_any()`: Mendaftarkan rute. Mendukung parameter regex seperti `{id:i}`, `{slug:s}`, `{name:a}`. Parameter ketiga `middleware` bersifat opsional (mendukung *string callback* atau array). Jika middleware me-*return* `false`, eksekusi *callback* utama akan dihentikan.
- `fst_group($prefix, $callback, $middleware = [])`: Mengelompokkan rute dengan prefix tertentu (contoh: `/api/v1`). Mendukung pelekatan *middleware* yang akan berlaku untuk semua rute di dalam grup tersebut.

### Request & HTTP
- `fst_uri()`: Mengembalikan URI request saat ini (sudah dikurangi base path).
- `fst_method()`: Mengembalikan HTTP Method (`GET`, `POST`, dll).
- `fst_input($key, $default)`: Mengambil nilai dari request data (gabungan `$_GET`, `$_POST`, dan JSON body jika ada).
- `fst_request()`: Mengembalikan seluruh data request. Otomatis mendeteksi dan mem-parse JSON body dari `php://input` jika `$_POST` kosong (berguna untuk `PUT`, `PATCH`, dan `POST` dengan `Content-Type: application/json`). Data di-cache secara internal agar aman dipanggil berkali-kali.
- `fst_file($key)`: Mengambil file aman dari `$_FILES`.

### Response & View
- `fst_view($path, $data)`: Meload file view/template dengan mengekstrak data array menjadi variabel (contoh `fst_view('home.php', ['title' => 'Home'])`).
- `fst_partial($path, $data)`: Alias untuk `fst_view`, biasanya digunakan di dalam view untuk meload komponen.
- `fst_json($data, $status)`: Mengembalikan response JSON dan `die()`.
- `fst_text($string, $status)`: Mengembalikan response Text biasa.
- `fst_redirect($url, $code)`: Redirect HTTP dan `die()`.
- `fst_abort($code, $message)`: Melemparkan error HTTP (404, 403, 500) dan merender handler khusus (jika disetel di config) atau default error page.

### Database
- `fst_db($mode, $sql, $params = [])`: Fungsi utilitas DB via PDO. 
  - `mode` bisa bernilai: `'EXEC'` (untuk Insert/Update/Delete, mengembalikan array info eksekusi), `'ROW'` (1 baris data), `'ALL'` (banyak baris), `'SCALAR'` (1 kolom saja).
- `fst_db_select($table, $conditions = [], $options = [])`: Query Builder untuk SELECT. Parameter `$options` mendukung `'select'`, `'order_by'`, `'limit'`, `'offset'`, `'mode'`.
- `fst_db_insert($table, $data)`: Query Builder untuk INSERT. Array associative `$data` berupa kolom => nilai.
- `fst_db_update($table, $data, $conditions = [])`: Query Builder untuk UPDATE. 
- `fst_db_delete($table, $conditions)`: Query Builder untuk DELETE. Jika array `$conditions` kosong, fungsi akan menolak operasi (demi keamanan dari delete massal tanpa Where).

### Security
- `fst_csrf_token()`: Menghasilkan (atau mengambil) token CSRF sesi aktif.
- `fst_csrf_field()`: Menghasilkan tag HTML `<input type="hidden">` dengan token CSRF.
- `fst_csrf_check()`: Memvalidasi token CSRF (biasanya dipanggil di dalam rute POST). Jika gagal, otomatis `fst_abort(403)`.
- `fst_escape($str)`: Membersihkan string dari karakter berbahaya untuk mencegah XSS. Menggunakan `htmlspecialchars()` dengan `ENT_QUOTES` dan `UTF-8`.
- `e($str)`: Alias pendek untuk `fst_escape()`. Contoh penggunaan di View: `<?= e($user['name']) ?>`.
- `fst_upload($key, $folder, $options)`: Helper mempermudah proses upload file lengkap dengan filter ekstensi dan size.

### Utility & Debugging
- `fst_validate($data, $rules)`: Memvalidasi array data (misal `$_POST`) terhadap aturan. Contoh aturan: `'required|email|min:5|max:20|numeric|in:a,b,c'`. Mengembalikan array `['valid' => bool, 'errors' => array, 'data' => array]`.
- `fst_session_set`, `fst_session_get`, `fst_flash_set`, `fst_flash_get`: Manajemen Session dan Flash messages.
- `fst_dump(...)`, `fst_dd(...)`: Fungsi debugging mirip *dump and die* di Laravel (Hanya berjalan di environment `development`).

---

## 3. Konfigurasi (`fullstuck.json`)
Sistem diatur sepenuhnya melalui `fullstuck.json` tanpa perlu define `.env`.
Contoh parameter krusial:
- `environment`: `development` atau `production`.
- `routing.mode`: `static` (Wajib define route) atau `dynamic` (Auto baca file PHP di root folder layaknya PHP native).
- `admin.page_url`: Akses masuk dashboard developer.
