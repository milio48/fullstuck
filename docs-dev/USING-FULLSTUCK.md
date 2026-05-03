# Panduan Penggunaan (Dunia 2)

Dokumen ini ditujukan untuk **End-User** (Developer Aplikasi) yang menggunakan `fullstuck.php` untuk membangun website atau API. Di Dunia 2, Anda hanya akan berinteraksi dengan **satu file rilis utuh** yaitu `fullstuck.php`.

## 1. Alur Setup Pertama Kali (The Wizard)

Keunikan FullStuck adalah Anda tidak perlu mengkonfigurasi file konfigurasi (seperti `.env`) secara manual.
1. Download / letakkan file `fullstuck.php` ke folder root project Anda.
2. Jalankan server (lihat bagian *Skenario Menjalankan Server* di bawah).
3. Buka URL project Anda di browser (contoh: `http://localhost:8000/` atau domain Anda).
4. Framework akan mendeteksi ketiadaan file `fullstuck.json` dan secara otomatis menampilkan **GUI Setup Wizard**.
5. Isi konfigurasi di layar: jenis database (SQLite/MySQL), *credentials*, password admin, tipe server, dan mode routing.
6. Klik Install. Framework akan otomatis meng-generate file `fullstuck.json` dan file `.htaccess` (jika menggunakan server Apache/Litespeed).
7. Selesai! Framework siap digunakan. Anda bisa mengakses URL `/stuck` (atau sesuai konfigurasi yang Anda atur tadi) untuk login ke **Dashboard Admin / Monitor**.

## 2. Skenario Menjalankan Server

Secara arsitektur, FullStuck bertindak sebagai *Front Controller*. Artinya, **semua request HTTP yang masuk (kecuali akses file gambar/CSS/JS statik yang sah) harus dialihkan ke `fullstuck.php`**.

### Skenario A: Shared Hosting / cPanel (Apache & LiteSpeed)
Sangat ramah bagi pemula dan pengguna *Shared Hosting*.
1. Upload `fullstuck.php` ke folder `public_html` (atau sub-folder).
2. Akses domain Anda, jalankan instalasi *wizard*.
3. Framework akan berusaha keras membuat file `.htaccess` secara otomatis. Jika gagal karena *permission* folder, wizard akan menyajikan kodenya untuk Anda *copy-paste* secara manual.
   Isi `.htaccess` biasanya seperti ini:
   ```apache
   Options -Indexes -MultiViews
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /
       # Aturan "Rakus" mengoper SEMUA request ke fullstuck.php
       RewriteRule ^(.*)$ fullstuck.php [L]
   </IfModule>
   ```

### Skenario B: Local Development (PHP Built-in Server)
Skenario paling ringan untuk pengembangan (tanpa perlu menginstall XAMPP/Laragon).
1. Buka Terminal / Command Prompt di folder tempat `fullstuck.php` berada.
2. Jalankan perintah:
   ```bash
   php -S localhost:8000 fullstuck.php
   ```
3. *Penjelasan*: Menyebut nama file `fullstuck.php` di akhir perintah akan memaksa PHP menjadikannya sebagai *Router Script*. Seluruh request otomatis masuk ke framework tanpa perlu konfigurasi `.htaccess`. Buka `http://localhost:8000` di browser.

### Skenario C: FrankenPHP / Caddy / Nginx
Bagi yang menggunakan Docker atau web server berkinerja tinggi, Anda harus mengatur *fallback* di level konfigurasi server.
- **Nginx (`nginx.conf`)**:
  Arahkan block `location /` menggunakan `try_files`.
  ```nginx
  location / {
      try_files $uri $uri/ /fullstuck.php?$query_string;
  }
  ```
- **FrankenPHP / Caddy (`Caddyfile`)**:
  Gunakan blok instruksi rewrite.
  ```caddy
  localhost {
      root * public/
      php_server
      try_files {path} {path}/ /fullstuck.php
  }
  ```

## 3. Pilihan Mode Routing

Setelah server menyala, Anda bisa mendesain alur URL melalui `fullstuck.json` (bisa diubah via Admin Dashboard).

### Mode Statik (Direkomendasikan)
Gaya pengembangan modern ala Laravel / Express.js. Lebih aman karena menggunakan sistem *Whitelist*.
- Buat file baru bernama `router.php`.
- Definisikan rute Anda:
  ```php
  <?php
  fst_get('/', function() {
      fst_text("Hello World!");
  });

  // Contoh regex: hanya menerima Angka (i)
  fst_get('/users/{id:i}', function($id) {
      fst_json(['user_id' => $id, 'status' => 'active']);
  });
  ```

### Mode Dinamis (Legacy Style)
Gaya pengembangan klasik ala Native PHP. Framework akan bertindak layaknya server *Apache* dan membaca file/folder berdasarkan URL.
- Misal ada request `GET /api/user`.
- Framework akan otomatis mencari file `api/user.php` atau `api/user/index.php` lalu mengeksekusinya.
- Sangat cocok untuk migrasi project lama (*legacy code*) agar bisa menumpang fitur keamanan & *database helper* `fst_` tanpa mengubah struktur folder sama sekali.
