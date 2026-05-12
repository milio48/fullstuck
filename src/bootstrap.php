<?php
$fst_config = fst_app('config');
$routes_files = (array) ($fst_config['routing']['routes_file'] ?? ['router.php']);
foreach ($routes_files as $file) {
    if (file_exists(FST_ROOT_DIR . '/' . $file)) {
        require FST_ROOT_DIR . '/' . $file;
    } elseif (!fst_is_dev()) {
        fst_abort(500, "Configuration Error: Routes file not found at '{$file}'");
    }
}

// Auto-Discovery Plugins
$plugin_dir = FST_ROOT_DIR . '/fst-plugins';
if (is_dir($plugin_dir)) {
    foreach (glob($plugin_dir . '/*.php') as $plugin) {
        require_once $plugin;
    }
}

if (php_sapi_name() !== 'cli') {
    fst_run();
}
?>

