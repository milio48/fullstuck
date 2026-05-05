# Laporan Eksperimen: SSR-to-SPA (Zero-Config SPA) di FullStuck.php
**Status:** Stabil (Proof of Concept - Siap Integrasi)
**Target:** FullStuck.php Core (Dunia 1)

## 1. Ringkasan Eksekutif
Eksperimen ini membuktikan bahwa FullStuck.php dapat memiliki kemampuan SPA modern (seperti navigasi tanpa reload, transisi CSS, partial update/nested views) **tanpa memerlukan setup frontend yang rumit (React/Vue/Webpack)**.

Pendekatan ini mirip dengan filosofi **HTMX** atau **Unpoly**, di mana HTML tetap menjadi *single source of truth*, namun navigasi di-hijack oleh client-side agent super ringan (`fst.js`) untuk memberikan *user experience* sekelas SPA.

---

## 2. Implementasi di Project Level (Dunia 2)
Berikut adalah mekanisme yang saat ini berjalan stabil di dalam folder `ssr-to-spa`, yang menyimulasikan fungsionalitas tanpa menyentuh core framework.

### A. Context-Aware Rendering (`helpers.php`)
Server harus tahu apakah request datang dari browser secara langsung (butuh layout penuh) atau datang dari `fst.js` (hanya butuh fragmen).

```php
function fst_is_fragment(): bool {
    return isset($_SERVER['HTTP_X_FST_REQUEST']) && $_SERVER['HTTP_X_FST_REQUEST'] === 'true';
}

function fst_spa_view(string $view_path, array $data = [], ?string $layout = 'layouts/master.php'): void {
    extract($data);
    ob_start();
    require FST_ROOT_DIR . '/' . $view_path;
    $view_content = ob_get_clean();

    if (fst_is_fragment() || $layout === null) {
        echo $view_content; // SPA Mode: Kirim fragmen HTML saja
    } else {
        $content = $view_content;
        require FST_ROOT_DIR . '/' . $layout; // SSR Mode: Render layout penuh
    }
}
```

### B. Frontend Orchestrator (`fst.js`)
File Vanilla JS ringan (< 5KB) yang meng-intercept link dan memanipulasi DOM.

**1. Link Interception & History API:**
```javascript
async handleLinkClick(e) {
    const link = e.target.closest('a');
    if (!link || /* skip eksternal, anchor sama, target=_blank, bypass */) return;

    e.preventDefault();
    const destination = link.href;
    const targetSelector = link.getAttribute('data-fst-target') || '#app-content';

    await this.navigate(destination, targetSelector);
    history.pushState({ fst: true, target: targetSelector }, '', destination);
}
```

**2. Fetch Fragment & Targeted Swapping:**
```javascript
const response = await fetch(url, {
    headers: { 
        'X-FST-Request': 'true',
        'X-FST-Target': targetSelector // Memberitahu server target spesifik (opsional)
    }
});

let htmlFragment = await response.text();

// Mengekstrak bagian tertentu jika diminta (Nested SPA)
if (targetSelector !== '#app-content') {
    const doc = new DOMParser().parseFromString(htmlFragment, 'text/html');
    const partial = doc.querySelector(targetSelector);
    if (partial) htmlFragment = partial.innerHTML;
}

// Eksekusi ulang script yang ada di dalam fragmen HTML baru
this.executeScripts(targetEl);
```

---

## 3. Usulan Integrasi Core (Dunia 1)
Agar fitur ini menjadi "built-in" secara elegan, berikut adalah usulan perombakan (refactoring) pada `fullstuck.php`.

### A. Upgrade `fst_view()` Native
`fst_view()` harus dimodifikasi untuk mendukung Layout Pattern bawaan dan mendeteksi request SPA.
*Catatan: Parameter ke-3 (`$layout`) ditambahkan untuk fleksibilitas.*

```php
function fst_view(string $view, array $data = [], ?string $layout = 'layouts/master.php') {
    $view_path = FST_ROOT_DIR . '/' . $view;
    if (!file_exists($view_path)) {
        fst_error(500, "View file not found: $view");
    }

    extract($data);
    ob_start();
    require $view_path;
    $content = ob_get_clean();

    // Deteksi SPA mode dari headers
    $is_spa_request = isset($_SERVER['HTTP_X_FST_REQUEST']) && $_SERVER['HTTP_X_FST_REQUEST'] === 'true';

    if ($is_spa_request || $layout === null) {
        echo $content;
    } else {
        $layout_path = FST_ROOT_DIR . '/' . $layout;
        if (file_exists($layout_path)) {
            require $layout_path;
        } else {
            echo $content; // Fallback jika layout tidak ada
        }
    }
}
```

### B. SPA-Aware Redirects (`fst_redirect`)
Saat terjadi submit form atau verifikasi login, server sering melakukan redirect. Pada SPA, mengirim header HTTP `Location` biasa akan membuat `fst.js` kebingungan atau fetch API mengikuti redirect secara membabi buta.
Solusinya adalah merespons dengan header khusus (`X-FST-Redirect`).

```php
function fst_redirect($url) {
    if (isset($_SERVER['HTTP_X_FST_REQUEST']) && $_SERVER['HTTP_X_FST_REQUEST'] === 'true') {
        // Beritahu agen JS bahwa server ingin berpindah halaman
        header("X-FST-Redirect: $url");
        exit;
    }
    // SSR Redirect konvensional
    header("Location: $url");
    exit;
}
```
*Catatan: Pada `fst.js`, kita perlu menambahkan logika untuk membaca header `X-FST-Redirect` pada response, lalu secara otomatis memanggil `this.navigate(redirectUrl)`.*

### C. Implementasi Virtual Output Buffer (Server-Side Target Clipping)
Jika fitur partial update (`data-fst-target`) sering digunakan, merender seluruh halaman dan memotongnya di sisi JS (seperti sekarang) agak boros CPU. Core bisa melakukan pemotongan ini (clipping) secara otomatis jika ada header `X-FST-Target`.

Di dalam siklus utama `fst_run()`:
```php
function fst_run() {
    ob_start(); // Mulai tangkap SEMUA output dari eksekusi framework
    
    // ... proses routing, eksekusi file / view ...

    $output = ob_get_clean(); // Ambil hasil akhirnya

    // Cek apakah ini partial request dari agen SPA
    $target_selector = $_SERVER['HTTP_X_FST_TARGET'] ?? null;
    
    if ($target_selector && $target_selector !== '#app-content') {
        // Parsing $output menggunakan DOMDocument untuk mengambil ID spesifik
        $target_id = ltrim($target_selector, '#');
        
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $element = $dom->getElementById($target_id);
        if ($element) {
            echo $dom->saveHTML($element);
            return; // Hanya kirim bagian ini
        }
    }

    echo $output; // Kirim full jika tidak ada target spesifik
}
```

### D. Konfigurasi `fullstuck.json` & Auto-Injection `fst.js`
Agar benar-benar Zero-Config, developer cukup mengaktifkannya di `fullstuck.json`.

```json
"routing": {
    "spa_mode": true
}
```

Jika `true`, fungsi `fst_run()` (setelah output buffer) harus menambahkan tag `<script>` agen `fst.js` (bisa di-minify ke dalam string konstan di `fullstuck.php` agar tidak ada dependensi eksternal) sesaat sebelum tag `</body>`.

```php
if ($fst_config['routing']['spa_mode'] ?? false) {
    // Injeksi otomatis agen JS
    $fst_js_code = "<script>/* Minified fst.js content here */</script>";
    $output = str_replace('</body>', $fst_js_code . '</body>', $output);
}
```

## Kesimpulan
Pendekatan ini akan menjadikan FullStuck.php framework tercepat untuk membangun aplikasi web reaktif tanpa kurva pembelajaran frontend baru. Modifikasi core sangat sedikit namun memberikan dampak UX yang eksponensial.
