# Routing

FullStuck mendukung dua mode routing yang bisa dipilih sesuai kebutuhan project Anda.

---

## Mode Static (Default)

Anda mendaftarkan rute secara eksplisit di file `router.php`, mirip seperti Laravel atau Express.js.

### Rute Dasar

```php
fst_get('/about', function() {
    echo '<h1>About Page</h1>';
});

fst_post('/contact', function() {
    $name = fst_input('name');
    fst_json(['message' => "Terima kasih, {$name}!"]);
});
```

### Semua HTTP Method yang Didukung

```php
fst_get($path, $callback);      // GET
fst_post($path, $callback);     // POST
fst_put($path, $callback);      // PUT
fst_patch($path, $callback);    // PATCH
fst_delete($path, $callback);   // DELETE
fst_any($path, $callback);      // Semua method
```

### Parameter Dinamis (URL Parameters)

Gunakan kurung kurawal `{}` untuk menangkap segmen URL:

```php
// {id:i} = Integer (angka saja)
fst_get('/user/{id:i}', function($id) {
    fst_json(['user_id' => $id]);
});

// {slug:s} = Slug (huruf, angka, strip)
fst_get('/post/{slug:s}', function($slug) {
    echo "Menampilkan artikel: {$slug}";
});

// {name:a} = Alfanumerik (huruf dan angka)
fst_get('/hello/{name:a}', function($name) {
    echo "Halo, " . e($name);
});

// {file:any} = Apapun (kecuali /)
fst_get('/download/{file:any}', function($file) {
    echo "Download file: " . e($file);
});
```

| Shortcut | Regex | Contoh Match |
|----------|-------|-------------|
| `{id:i}` | `([0-9]+)` | `123`, `42` |
| `{slug:s}` | `([a-zA-Z0-9\-]+)` | `hello-world`, `post-1` |
| `{name:a}` | `([a-zA-Z0-9]+)` | `john`, `user42` |
| `{file:any}` | `([^/]+)` | `foto.jpg`, `data_2024` |

---

## Group Routing

Kelompokkan rute dengan prefix yang sama menggunakan `fst_group()`:

```php
fst_group('/api/v1', function() {
    fst_get('/users', function() {
        fst_json(['data' => 'list users']);
    });

    fst_post('/users', function() {
        fst_json(['data' => 'create user']);
    });

    fst_get('/users/{id:i}', function($id) {
        fst_json(['data' => "user {$id}"]);
    });
});
// Menghasilkan: GET /api/v1/users, POST /api/v1/users, GET /api/v1/users/123
```

---

## Middleware

Middleware adalah fungsi yang dieksekusi **sebelum** callback rute. Jika middleware me-return `false`, callback utama tidak akan dijalankan.

### Middleware pada Rute Tunggal

```php
function require_auth() {
    if (!fst_session_get('logged_in')) {
        fst_json(['error' => 'Unauthorized'], 401);
        return false;
    }
}

fst_get('/dashboard', function() {
    echo '<h1>Dashboard</h1>';
}, 'require_auth');
```

### Middleware pada Group

```php
fst_group('/admin', function() {
    fst_get('/stats', function() { /* ... */ });
    fst_get('/users', function() { /* ... */ });
}, 'require_auth');
```

### Multiple Middleware

```php
fst_get('/secret', function() {
    echo 'Top Secret!';
}, ['require_auth', 'require_admin']);
```

---

## Mode Dynamic

Mode ini bekerja seperti server Apache/PHP tradisional — framework otomatis memetakan URL ke file PHP di dalam folder yang Anda tentukan.

### Konfigurasi di `fullstuck.json`

```json
{
  "routing": {
    "mode": "dynamic",
    "dynamic_config": {
      "pages_dir": "pages",
      "index_file": "index.php"
    }
  }
}
```

### Struktur Folder

```
my-project/
├── fullstuck.php
├── fullstuck.json
└── pages/
    ├── index.php       → http://localhost:8000/
    ├── about.php       → http://localhost:8000/about
    └── wiki/
        └── hello.php   → http://localhost:8000/wiki/hello
```

URL dipetakan secara otomatis ke file di dalam `pages_dir`. Tidak perlu mendaftarkan route sama sekali!

> **Catatan:** Di dalam file dynamic, Anda tetap bisa menggunakan semua fungsi `fst_*` seperti `fst_uri()`, `fst_db()`, `e()`, dan lainnya.

---

## Hybrid Mode (Static + Dynamic Fallback)

Anda bisa menggunakan static routing sebagai prioritas utama, dengan dynamic routing sebagai fallback:

```json
{
  "routing": {
    "mode": "static",
    "static_config": {
      "routes_file": ["router.php"],
      "dynamic_fallback": true
    },
    "dynamic_config": {
      "pages_dir": "pages"
    }
  }
}
```

Dalam mode ini, framework akan:
1. Mencari kecocokan di rute static terlebih dahulu.
2. Jika tidak ada yang cocok, fallback ke pencarian file di `pages/`.

---

## Langkah Selanjutnya

- 🗄️ [Database](database.md) — Hubungkan rute Anda ke database.
- 📥 [Request & Response](request-response.md) — Pelajari cara menangkap input dan mengirim response.
