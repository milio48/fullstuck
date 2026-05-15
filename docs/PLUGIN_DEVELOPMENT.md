# 🔌 Panduan Pembuatan Plugin FullStuck

Sistem plugin di FullStuck dirancang agar sangat minimalis. Anda hanya butuh 1 file PHP untuk menambahkan antarmuka Admin (Backend) dan routing aplikasi (Frontend).

## 1. Aturan Dasar

* **Lokasi File:** Seluruh file plugin lokal disimpan di folder `fst-plugins/` di root proyek.
* **Konvensi Penamaan:** File wajib menggunakan prefix `fst-` diikuti ID plugin (contoh: `fst-analytics.php`).
* **Keamanan Mutlak:**
  * Gunakan helper bawaan (`fst_input()`, `fst_get()`, `fst_post()`) alih-alih `$_GET` atau `$_POST` langsung.
  * Anda **WAJIB** memanggil `fst_csrf_check()` saat menangani form POST di admin.
  * Gunakan `fst_escape()` saat mencetak output variabel ke HTML.

## 2. Struktur Kode Plugin (Boilerplate)

Berikut adalah kerangka dasar (boilerplate) untuk membuat plugin. Plugin akan otomatis terdeteksi jika memiliki file dengan format nama yang benar.

```php
<?php
/**
 * Plugin Name: My Awesome Plugin
 * Description: Deskripsi singkat plugin saya.
 * Version: 1.0.0
 */

// ==========================================
// 1. FRONTEND: Daftarkan Route Aplikasi
// ==========================================
// Plugin dapat menyisipkan route baru ke dalam aplikasi utama.
fst_get('/api/awesome-data', function() {
    return fst_json(['status' => 'success', 'data' => 'Hello from plugin!']);
});


// ==========================================
// 2. BACKEND: Daftarkan Antarmuka Admin
// ==========================================
fst_register_plugin('my-awesome-plugin', [
    'name' => 'Awesome Analytics', // Nama panjang
    'menu_label' => 'Analytics', // Teks pendek untuk Sidebar Admin
    'admin_route' => function() {
        // Logika Admin Dashboard Anda ada di sini
        $method = fst_method();
        $action = fst_input('action', 'index');
        $admin_base = fst_config('admin.page_url', '/stuck');

        // Penanganan Simpan Data (POST)
        if ($method === 'POST') {
            fst_csrf_check(); // WAJIB
            $api_key = fst_input('api_key');
            
            // Simpan ke database atau config...
            fst_flash_set('success_message', 'Pengaturan tersimpan!');
            fst_redirect($admin_base . '/p/my-awesome-plugin');
        }

        // Tampilan Halaman Pengaturan (GET)
        if ($action === 'index') {
            echo "<h2>Pengaturan Awesome Plugin</h2>";
            echo '<form method="POST" action="' . $admin_base . '/p/my-awesome-plugin">
                    ' . fst_csrf_field() . '
                    <label>API Key:</label>
                    <input type="text" name="api_key" placeholder="Enter key...">
                    <br><br>
                    <button type="submit">Simpan</button>
                  </form>';
        }
    }
]);

```

## 3. Mendaftarkan ke Marketplace (Opsional)

Jika plugin Anda bermanfaat untuk publik dan ingin bisa diinstal via fitur "One-Click Install" di Admin Dashboard:

1. Simpan file plugin Anda ke folder `store/` di repositori GitHub Anda.
2. Daftarkan informasi plugin Anda ke dalam file `store.json` di root repositori.
