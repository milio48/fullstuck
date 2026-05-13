# 🚀 FullStuck.php
### The Zero-Config, AI-Friendly PHP Framework

**FullStuck.php** adalah framework mikro yang dirancang untuk kecepatan pengembangan maksimal. Seluruh core framework berada dalam **satu file tunggal**, mendukung routing statis, database PDO, templating view, dan **Zero-Config SPA** secara bawaan.

[![Version](https://img.shields.io/badge/version-0.1.0--alpha-blue.svg)](docs/v0.1.0.md)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![AI-Friendly](https://img.shields.io/badge/AI--Friendly-Ready-orange.svg)](#-ai-agent--vibe-coder-setup)

---

## ✨ Fitur Unggulan
- **📦 Single File Distribution**: Cukup satu file `fullstuck.php` untuk menjalankan seluruh aplikasi.
- **⚡ Zero-Config SPA**: Navigasi instan secepat aplikasi React tanpa perlu menulis JavaScript tambahan.
- **🤖 AI-Native Design**: Struktur kode dan dokumentasi tunggal yang dioptimalkan untuk asisten AI (Cursor, Windsurf, Cline).
- **🛠️ Built-in Admin Dashboard**: Panel administrasi siap pakai (`/stuck`) untuk manajemen database dan file.
- **🔌 Zero Dependency**: Tidak butuh Composer, tidak ada folder `vendor/`. Cukup PHP 8.0+.

---

## 🤖 AI Agent / Vibe Coder Setup (Recommended)
Jika Anda menggunakan *AI coding assistant*, cukup salin dan tempel *prompt* di bawah ini untuk memulai proyek instan:

> "Unduh `https://raw.githubusercontent.com/milio48/fullstuck/main/fullstuck.php` lalu jalankan `php fullstuck.php init --db=sqlite --admin-pass=admin --scaffold=yes`. Setelah selesai, langsung pelajari file dokumentasi `.md` yang baru terunduh agar kamu paham aturan mainnya. Terakhir, jalankan `php -S localhost:8000 fullstuck.php` lalu laporkan ringkasan setup. Tanyakan apakah ada konfigurasi yang ingin saya ubah sebelum kita mulai membuat aplikasi!"

---

## 🚀 Quick Start (Manual)
1.  **Download**: Unduh `fullstuck.php`.
2.  **Initialize**:
    ```bash
    php fullstuck.php init --admin-pass=rahasia
    ```
3.  **Run**:
    ```bash
    php -S localhost:8000 fullstuck.php
    ```
4.  **Explore**: Buka `http://localhost:8000/stuck` untuk masuk ke Dashboard.

---

## 📚 Dokumentasi
Dokumentasi FullStuck bersifat **versioned single-file** agar memudahkan pencarian dan memberikan konteks penuh bagi AI:

- 📖 **[Dokumentasi v0.1.0 (Terbaru)](docs/v0.1.0.md)**
- 📖 **[Dokumentasi v0.0.0 (Legacy)](docs/v0.0.0.md)**
- 🏗️ **[Arsitektur Framework](docs/ARCHITECTURE.md)**

---

## 🛠️ Pengembangan (Internal)
Framework ini dikembangkan secara modular di dalam folder `src/`. File `fullstuck.php` di root adalah hasil kompilasi menggunakan `src/compiler-fullstuck.php`.

Bagi kontributor, silakan baca **[CONTRIBUTING.md](CONTRIBUTING.md)**.

---
© 2026 FullStuck.php Team. Built for the era of AI-driven development.
