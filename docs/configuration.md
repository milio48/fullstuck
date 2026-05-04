# Configuration

Referensi lengkap parameter `fullstuck.json`.

---

## Struktur Dasar

```json
{
  "environment": "development",
  "database": { ... },
  "admin": { ... },
  "routing": { ... }
}
```

---

## `environment`

| Nilai | Keterangan |
|-------|------------|
| `"development"` | Error detail ditampilkan. Admin Dashboard aktif. `fst_dump`/`fst_dd` berfungsi. |
| `"production"` | Error disembunyikan (hanya dicatat di log server). Admin Dashboard nonaktif. |

---

## `database`

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
      "dbname": "nama_database",
      "username": "root",
      "password": "password_anda"
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

## `admin`

```json
{
  "admin": {
    "password": "$2y$12$...",
    "page_url": "/stuck"
  }
}
```

| Parameter | Keterangan |
|-----------|------------|
| `password` | Hash bcrypt dari password admin. Dibuat otomatis oleh Setup Wizard. |
| `page_url` | URL untuk mengakses Developer Dashboard. Default: `/stuck`. |

---

## `routing`

### Mode Static (Default)

```json
{
  "routing": {
    "mode": "static",
    "base_path": "/",
    "static_config": {
      "routes_file": ["router.php"],
      "dynamic_fallback": false
    },
    "public_folders": ["assets", "uploads"],
    "error_handlers": {
      "404": "views/404.php"
    }
  }
}
```

### Mode Dynamic

```json
{
  "routing": {
    "mode": "dynamic",
    "dynamic_config": {
      "pages_dir": "pages",
      "index_file": "index.php",
      "whitelist_filetype": ["php"],
      "directory_listing": false
    }
  }
}
```

### Hybrid (Static + Dynamic Fallback)

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

---

## Parameter Routing Lengkap

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `routing.mode` | string | `"static"` | Mode routing: `static` atau `dynamic`. |
| `routing.base_path` | string | `"/"` | Base path jika app diinstall di subfolder (misal: `/myapp`). |
| `routing.static_config.routes_file` | array | `["router.php"]` | File routing yang di-include saat boot. |
| `routing.static_config.dynamic_fallback` | bool | `false` | Aktifkan fallback ke dynamic routing jika static tidak match. |
| `routing.dynamic_config.pages_dir` | string | `""` | Folder tempat file PHP untuk dynamic routing. Kosong = root project. |
| `routing.dynamic_config.index_file` | string | `"index.php"` | File default saat mengakses direktori. |
| `routing.dynamic_config.whitelist_filetype` | array | `["php"]` | Ekstensi file yang boleh dieksekusi secara dinamis. |
| `routing.dynamic_config.directory_listing` | bool | `false` | Tampilkan daftar file jika tidak ada index. |
| `routing.public_folders` | array | `[]` | Folder yang bisa diakses langsung (CSS, JS, gambar). |
| `routing.error_handlers` | object | `{}` | Custom error page per kode HTTP. |
| `routing.regex_shortcuts` | object | (built-in) | Custom regex shortcuts untuk URL parameter. |

---

## Custom Error Pages

```json
{
  "routing": {
    "error_handlers": {
      "404": "views/errors/404.php",
      "403": "views/errors/403.php",
      "500": "Terjadi kesalahan internal."
    }
  }
}
```

Jika nilainya berupa path file `.php` atau `.html`, framework akan me-render file tersebut. Jika berupa string biasa, teks tersebut langsung ditampilkan.

---

## Langkah Selanjutnya

- [Examples](examples.md) — Lihat contoh project lengkap.
- [Getting Started](getting-started.md) — Kembali ke panduan awal.
