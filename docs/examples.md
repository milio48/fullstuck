# Examples

Kumpulan contoh penggunaan FullStuck dari studi kasus nyata.

---

## 1. REST API — Task Manager

API CRUD lengkap dengan autentikasi API key dan validasi input.

### `fullstuck.json`

```json
{
  "environment": "development",
  "database": {
    "driver": "sqlite",
    "sqlite": { "database_path": "tasks.db" }
  },
  "routing": {
    "mode": "static",
    "static_config": { "routes_file": ["router.php"] }
  }
}
```

### `router.php`

```php
<?php
header('Content-Type: application/json');

// Setup database
fst_get('/setup', function() {
    fst_db('EXEC', "CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    fst_json(['message' => 'Database ready!']);
});

// Middleware autentikasi
function auth_check() {
    $key = fst_input('api_key');
    if ($key !== 'my-secret-key') {
        fst_json(['error' => 'Unauthorized'], 401);
        return false;
    }
}

// CRUD endpoints
fst_group('/api/v1', function() {

    fst_get('/tasks', function() {
        $tasks = fst_db_select('tasks', [], ['order_by' => 'id DESC']);
        fst_json(['data' => $tasks]);
    });

    fst_post('/tasks', function() {
        $val = fst_validate(fst_request(), ['title' => 'required|min:3']);
        if (!$val['valid']) fst_json(['errors' => $val['errors']], 400);

        fst_db_insert('tasks', ['title' => $val['data']['title']]);
        fst_json(['message' => 'Task created!'], 201);
    });

    fst_put('/tasks/{id:i}', function($id) {
        $val = fst_validate(fst_request(), ['title' => 'required|min:3']);
        if (!$val['valid']) fst_json(['errors' => $val['errors']], 400);

        fst_db_update('tasks', ['title' => $val['data']['title']], ['id' => $id]);
        fst_json(['message' => 'Task updated!']);
    });

    fst_delete('/tasks/{id:i}', function($id) {
        fst_db_delete('tasks', ['id' => $id]);
        fst_json(['message' => 'Task deleted!']);
    });

}, 'auth_check');
```

**Cara menjalankan:**
```bash
php -S localhost:8000 fullstuck.php
curl http://localhost:8000/api/v1/tasks?api_key=my-secret-key
```

---

## 2. Personal Blog — SSR dengan View

Blog sederhana menggunakan routing parameter dan template view.

### `router.php`

```php
<?php
fst_get('/', function() {
    $posts = fst_db_select('posts', [], [
        'order_by' => 'id DESC',
        'limit' => 10
    ]);
    fst_view('views/home.php', ['posts' => $posts]);
});

fst_get('/post/{slug:s}', function($slug) {
    $post = fst_db_select('posts', ['slug' => $slug], ['mode' => 'ROW']);
    if (!$post) fst_abort(404, 'Artikel tidak ditemukan.');
    fst_view('views/post.php', ['post' => $post]);
});
```

### `views/home.php`

```php
<h1>Blog Saya</h1>
<?php foreach ($posts as $post): ?>
    <article>
        <h2><a href="/post/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2>
        <time><?= e($post['created_at']) ?></time>
    </article>
<?php endforeach; ?>
```

---

## 3. Secure Drive — Upload, CSRF, Session

Sistem upload file dengan autentikasi dan proteksi CSRF.

### `router.php` (Bagian Upload)

```php
<?php
function require_login() {
    if (!fst_session_get('user_id')) {
        fst_flash_set('error', 'Silakan login terlebih dahulu.');
        fst_redirect('/login');
        return false;
    }
}

fst_post('/upload', function() {
    fst_csrf_check();

    $result = fst_upload('file', 'uploads/', [
        'allowed' => ['pdf', 'jpg', 'png', 'txt'],
        'max_size' => 2 * 1024 * 1024
    ]);

    if ($result['success']) {
        fst_flash_set('success', 'File berhasil diupload!');
    } else {
        fst_flash_set('error', $result['error']);
    }
    fst_redirect('/dashboard');
}, 'require_login');
```

---

## 4. Dynamic Wiki — Tanpa Router

Wiki yang sepenuhnya mengandalkan mode Dynamic Routing.

### `fullstuck.json`

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
dynamic-wiki/
├── fullstuck.json
└── pages/
    ├── index.php       → /
    ├── about.php       → /about
    └── wiki/
        └── hello.php   → /wiki/hello
```

### `pages/index.php`

```php
<!DOCTYPE html>
<html>
<body>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
        <a href="/wiki/hello">Wiki Hello</a>
    </nav>
    <h1>Dynamic Wiki</h1>
    <p>URL saat ini: <code><?= fst_uri() ?></code></p>
</body>
</html>
```

Tidak ada `router.php`. Framework otomatis memetakan URL ke file!

---

## Langkah Selanjutnya

- [Getting Started](getting-started.md) — Kembali ke panduan awal.
- [Routing](routing.md) — Pelajari routing lebih dalam.
