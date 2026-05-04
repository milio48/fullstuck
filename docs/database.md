# Database

FullStuck menyediakan wrapper PDO bawaan dan Query Builder ringan. Mendukung **SQLite** dan **MySQL** tanpa library tambahan.

---

## Konfigurasi

Atur koneksi database di `fullstuck.json`:

### SQLite

```json
{
  "database": {
    "driver": "sqlite",
    "sqlite": {
      "database_path": "database.db"
    }
  }
}
```

### MySQL

```json
{
  "database": {
    "driver": "mysql",
    "mysql": {
      "host": "localhost",
      "port": 3306,
      "dbname": "my_database",
      "username": "root",
      "password": "secret"
    }
  }
}
```

### Tanpa Database

```json
{
  "database": {
    "driver": "none"
  }
}
```

---

## Raw Query (`fst_db`)

Untuk query yang kompleks atau custom, gunakan `fst_db()` secara langsung:

```php
// Mode EXEC — Insert, Update, Delete (mengembalikan info eksekusi)
fst_db('EXEC', "CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)");
fst_db('EXEC', "INSERT INTO users (name, email) VALUES (?, ?)", ['Budi', 'budi@mail.com']);

// Mode ROW — Mengambil 1 baris
$user = fst_db('ROW', "SELECT * FROM users WHERE id = ?", [1]);

// Mode ALL — Mengambil semua baris
$users = fst_db('ALL', "SELECT * FROM users ORDER BY id DESC");

// Mode SCALAR — Mengambil 1 nilai saja
$count = fst_db('SCALAR', "SELECT COUNT(*) FROM users");
```

> **Penting:** Selalu gunakan *placeholder* `?` untuk parameter. Jangan pernah menyisipkan variabel langsung ke string SQL!

---

## Query Builder

Untuk operasi CRUD standar, gunakan Query Builder yang lebih ringkas. Semua fungsi ini menggunakan *Prepared Statements* secara otomatis.

### Insert

```php
fst_db_insert('users', [
    'name' => 'Budi',
    'email' => 'budi@mail.com'
]);
```

### Select

```php
// Ambil semua data
$users = fst_db_select('users');

// Ambil dengan kondisi
$admins = fst_db_select('users', ['role' => 'admin']);

// Ambil 1 baris saja
$user = fst_db_select('users', ['id' => 1], ['mode' => 'ROW']);

// Dengan opsi lengkap
$recent = fst_db_select('users', [], [
    'select'   => 'id, name',       // Kolom spesifik
    'order_by' => 'id DESC',        // Urutan
    'limit'    => 10,               // Batas jumlah
    'offset'   => 0                 // Mulai dari
]);
```

### Update

```php
fst_db_update('users', 
    ['name' => 'Budi Updated'],     // Data baru
    ['id' => 1]                     // Kondisi WHERE
);
```

### Delete

```php
fst_db_delete('users', ['id' => 1]);
```

> **Proteksi:** `fst_db_delete()` akan menolak operasi jika `$conditions` kosong, mencegah penghapusan seluruh tabel secara tidak sengaja.

---

## Contoh Penggunaan Lengkap

```php
// Setup tabel
fst_get('/setup', function() {
    fst_db('EXEC', "CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        status TEXT DEFAULT 'pending'
    )");
    fst_json(['message' => 'Database siap!']);
});

// CRUD API
fst_get('/api/tasks', function() {
    $tasks = fst_db_select('tasks', [], ['order_by' => 'id DESC']);
    fst_json(['data' => $tasks]);
});

fst_post('/api/tasks', function() {
    $val = fst_validate(fst_request(), [
        'title' => 'required|min:3'
    ]);
    if (!$val['valid']) fst_json(['errors' => $val['errors']], 400);

    fst_db_insert('tasks', ['title' => $val['data']['title']]);
    fst_json(['message' => 'Task dibuat!'], 201);
});
```

---

## Langkah Selanjutnya

- 📥 [Request & Response](request-response.md) — Pelajari validasi input dan format response.
- 🔐 [Security](security.md) — Lindungi operasi database Anda.
