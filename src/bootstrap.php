<?php
$fst_config = fst_app('config');

// Deteksi miskonfigurasi web server (Routing Leakage)
if (php_sapi_name() !== 'cli' && strpos($_SERVER['REQUEST_URI'], 'fullstuck.php') !== false) {
    http_response_code(500);
    die('
        <div style="font-family: system-ui, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ff4444; border-radius: 8px; background: #fff1f1; color: #333;">
            <h2 style="color: #d32f2f; margin-top: 0;">🚨 Routing Misconfigured!</h2>
            <p>Framework mendeteksi <code>fullstuck.php</code> di dalam URL. Ini menandakan URL Rewriting di web server Anda belum aktif.</p>
            <p><strong>Solusi:</strong> Pastikan Anda menggunakan web server yang mendukung single-entry routing (Apache dengan .htaccess, Nginx, atau FrankenPHP). Silakan baca dokumentasi FullStuck bagian Deployment.</p>
        </div>
    ');
}

// 1. Load Plugins Terlebih Dahulu
$plugin_dir = FST_ROOT_DIR . '/fst-plugins';
if (is_dir($plugin_dir)) {
    foreach (glob($plugin_dir . '/fst-*.php') as $plugin) {
        require_once $plugin;
    }
}

// 2. Load Route Files
$routes_files = (array) ($fst_config['routing']['routes_file'] ?? ['router.php']);
foreach ($routes_files as $file) {
    if (file_exists(FST_ROOT_DIR . '/' . $file)) {
        require FST_ROOT_DIR . '/' . $file;
    } elseif (!fst_is_dev()) {
        fst_abort(500, "Configuration Error: Routes file not found at '{$file}'");
    }
}

// 3. Eksekusi Framework (Jika bukan CLI)
if (php_sapi_name() !== 'cli') {
    fst_run();
}
