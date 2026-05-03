<?php
if (isset($fst_config['routing']['mode']) && $fst_config['routing']['mode'] === 'static') {
    $routes_files = (array) ($fst_config['routing']['static_config']['routes_file'] ?? []);
    foreach ($routes_files as $file) {
        if (file_exists(FST_ROOT_DIR . '/' . $file)) require FST_ROOT_DIR . '/' . $file;
        else fst_abort(500, "Configuration Error: Routes file not found at '{$file}'");
    }
}

fst_run();
?>
