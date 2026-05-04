# Request & Response

Panduan lengkap cara menangkap input dari klien dan mengirim response.

---

## Mengambil Input

### `fst_input($key, $default)`

```php
$name = fst_input('name');
$page = fst_input('page', 1);    // default = 1
```

### `fst_request()`

Mengambil seluruh data request sebagai array. Otomatis mendeteksi JSON body.

```php
$data = fst_request();
```

### `fst_file($key)`

```php
$file = fst_file('avatar');
if ($file) echo $file['name'];
```

### Info Request

```php
$uri    = fst_uri();      // "/api/users"
$method = fst_method();   // "GET"
```

---

## Validasi Input

```php
$val = fst_validate(fst_request(), [
    'name'  => 'required|min:3|max:50',
    'email' => 'required|email',
    'age'   => 'numeric',
    'role'  => 'in:admin,user,editor'
]);

if (!$val['valid']) fst_json(['errors' => $val['errors']], 400);
$name = $val['data']['name']; // Data sudah di-trim
```

| Rule | Keterangan |
|------|------------|
| `required` | Tidak boleh kosong. |
| `email` | Format email valid. |
| `min:N` | Minimal N karakter. |
| `max:N` | Maksimal N karakter. |
| `numeric` | Harus angka. |
| `in:a,b,c` | Harus salah satu dari daftar. |

---

## Mengirim Response

### JSON

```php
fst_json(['status' => 'ok']);
fst_json(['error' => 'Not found'], 404);
```

### Teks Biasa

```php
fst_text('Hello World');
```

### Redirect

```php
fst_redirect('/login');
fst_redirect('/new-url', 301);
```

### View / Template (SSR)

```php
fst_view('views/home.php', [
    'title' => 'Blog Saya',
    'posts' => $posts
]);
```

Di dalam view, variabel langsung tersedia:

```php
<h1><?= e($title) ?></h1>
<?php foreach ($posts as $post): ?>
    <h2><?= e($post['title']) ?></h2>
<?php endforeach; ?>
```

### Error Page

```php
fst_abort(404, 'Halaman tidak ditemukan');
```

---

## Langkah Selanjutnya

- [Security](security.md) — CSRF, XSS escaping, dan file upload.
- [Configuration](configuration.md) — Kustomisasi konfigurasi lanjutan.
