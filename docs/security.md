# Security

Panduan fitur keamanan bawaan FullStuck: CSRF, XSS, session, dan file upload.

---

## CSRF Protection

Lindungi form dari serangan Cross-Site Request Forgery.

### Di dalam Form HTML

```php
<form method="POST" action="/update">
    <?= fst_csrf_field() ?>
    <input type="text" name="name">
    <button type="submit">Simpan</button>
</form>
```

### Di dalam Route POST

```php
fst_post('/update', function() {
    fst_csrf_check(); // Otomatis abort(403) jika token tidak valid

    // Proses data yang aman...
    $name = fst_input('name');
});
```

### Fungsi CSRF

| Fungsi | Keterangan |
|--------|------------|
| `fst_csrf_token()` | Menghasilkan/mengambil token CSRF sesi aktif. |
| `fst_csrf_field()` | Menghasilkan `<input type="hidden">` dengan token. |
| `fst_csrf_check()` | Memvalidasi token. Gagal = `abort(403)`. |

---

## XSS Protection

### `fst_escape($str)` / `e($str)`

Bersihkan output dari karakter berbahaya sebelum ditampilkan di HTML:

```php
<!-- BAHAYA: Rentan XSS -->
<p><?= $user['name'] ?></p>

<!-- AMAN: Output di-escape -->
<p><?= e($user['name']) ?></p>
```

`e()` adalah alias pendek dari `fst_escape()`. Keduanya menggunakan `htmlspecialchars()` dengan `ENT_QUOTES` dan `UTF-8`.

> **Aturan Emas:** Selalu gunakan `e()` saat menampilkan variabel di dalam HTML!

---

## Session Management

### Set & Get

```php
fst_session_set('user_id', 42);
$user_id = fst_session_get('user_id');
fst_session_forget('user_id');
```

### Flash Messages (Tampil 1x Saja)

```php
// Set flash message (biasanya sebelum redirect)
fst_flash_set('success', 'Data berhasil disimpan!');
fst_redirect('/dashboard');

// Baca flash message (otomatis terhapus setelah dibaca)
$msg = fst_flash_get('success');
if ($msg) echo "<p class='alert'>" . e($msg) . "</p>";
```

| Fungsi | Keterangan |
|--------|------------|
| `fst_session_set($key, $value)` | Simpan data ke session. |
| `fst_session_get($key, $default)` | Ambil data dari session. |
| `fst_session_forget($key)` | Hapus data dari session. |
| `fst_flash_set($key, $value)` | Simpan flash message. |
| `fst_flash_get($key)` | Ambil & hapus flash message. |
| `fst_flash_has($key)` | Cek apakah flash message ada. |

---

## File Upload (`fst_upload`)

Upload file dengan validasi ekstensi dan ukuran:

```php
fst_post('/upload', function() {
    fst_csrf_check();

    $result = fst_upload('document', 'uploads/', [
        'allowed' => ['pdf', 'docx', 'txt'],
        'max_size' => 5 * 1024 * 1024  // 5 MB
    ]);

    if ($result['success']) {
        fst_json(['file' => $result['filename']]);
    } else {
        fst_json(['error' => $result['error']], 400);
    }
});
```

### Form Upload

```php
<form method="POST" action="/upload" enctype="multipart/form-data">
    <?= fst_csrf_field() ?>
    <input type="file" name="document">
    <button type="submit">Upload</button>
</form>
```

---

## Contoh: Login Sederhana

```php
fst_post('/login', function() {
    fst_csrf_check();

    $email = fst_input('email');
    $password = fst_input('password');

    $user = fst_db_select('users', ['email' => $email], ['mode' => 'ROW']);

    if ($user && password_verify($password, $user['password'])) {
        fst_session_set('user_id', $user['id']);
        fst_flash_set('success', 'Login berhasil!');
        fst_redirect('/dashboard');
    } else {
        fst_flash_set('error', 'Email atau password salah.');
        fst_redirect('/login');
    }
});
```

---

## Langkah Selanjutnya

- [Configuration](configuration.md) — Konfigurasi error handler dan opsi lanjutan.
- [Examples](examples.md) — Contoh project lengkap.
