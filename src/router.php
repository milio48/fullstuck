<?php
function fst_abort($code, $message = '') {
    global $fst_config;
    http_response_code($code);
    $handler_path = $fst_config['routing']['error_handlers'][$code] ?? null;
    if ($handler_path) {
        if (preg_match('/\.php$|\.html$/', $handler_path)) {
            if (file_exists(FST_ROOT_DIR . '/' . $handler_path)) {
                if (function_exists('fst_view')) fst_view($handler_path, ['error_code' => $code, 'error_message' => $message]);
                else require FST_ROOT_DIR . '/' . $handler_path;
                die();
            }
        } else {
            echo htmlspecialchars($handler_path); die();
        }
    }
    $default_titles = [404 => 'Not Found', 403 => 'Forbidden', 405 => 'Method Not Allowed', 500 => 'Internal Server Error'];
    $title = $default_titles[$code] ?? 'Error';
    $message_safe = htmlspecialchars($message);
    $html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Error {$code}</title>
<style>body{font-family: sans-serif; text-align: center; padding-top: 50px;}</style>
</head><body><h1>Error {$code}: {$title}</h1><p>{$message_safe}</p></body></html>
HTML;
    echo $html; die();
}

function fst_route($method, $path, $callback) {
    global $fst_routes, $fst_route_prefix, $fst_config;
    
    $full_original_path = $fst_route_prefix . $path;
    if ($full_original_path !== '/' && str_ends_with($full_original_path, '/')) {
        $full_original_path = rtrim($full_original_path, '/');
    }
     if (!str_starts_with($full_original_path, '/')) {
        $full_original_path = '/' . $full_original_path;
    }

    $path_for_regex = $full_original_path;

    $shortcuts = $fst_config['routing']['regex_shortcuts'] ?? ['i'=>'([0-9]+)','a'=>'([a-zA-Z0-9]+)','s'=>'([a-zA-Z0-9\-]+)','any'=>'([^/]+)'];
    $default_regex = $shortcuts['any'] ?? '([^/]+)';

    $final_pattern = preg_replace_callback(
        '/\{([a-zA-Z0-9_]+)(?::([a-z]))?\}/',
        function ($matches) use ($shortcuts, $default_regex) {
             $type = $matches[2] ?? 'any';
             $regex = $shortcuts[$type] ?? $default_regex;
             return str_starts_with($regex, '(') ? $regex : '(' . $regex . ')';
        },
        $path_for_regex);
    
    $final_pattern = preg_replace_callback(
        '/\{([a-zA-Z0-9_]+)(?::([a-z]))?\}(\?)/',
        function ($matches) use ($shortcuts, $default_regex) {
             $type = $matches[2] ?? 'any';
             $regex = $shortcuts[$type] ?? $default_regex;
             $regex = str_starts_with($regex, '(') ? $regex : '(' . $regex . ')';
             return "(?:/" . $regex . ")?";
        },
        $final_pattern);

    $final_pattern = '#^' . str_replace('/', '\/', $final_pattern) . '$#';

    $fst_routes[] = [$method, $final_pattern, $callback, $full_original_path];
}

function fst_get($path, $callback) { fst_route('GET', $path, $callback); }
function fst_post($path, $callback) { fst_route('POST', $path, $callback); }
function fst_put($path, $callback) { fst_route('PUT', $path, $callback); }
function fst_patch($path, $callback) { fst_route('PATCH', $path, $callback); }
function fst_delete($path, $callback) { fst_route('DELETE', $path, $callback); }
function fst_any($path, $callback) { fst_route('ANY', $path, $callback); }
function fst_group($prefix, $callback) {
    global $fst_route_prefix;
    $parent_prefix = $fst_route_prefix;
    $fst_route_prefix = rtrim($parent_prefix, '/') . '/' . trim($prefix, '/');
    call_user_func($callback);
    $fst_route_prefix = $parent_prefix;
}

function fst_run() {
    global $fst_routes, $fst_route_found, $fst_config;
    
    $uri = fst_uri();
    $method = fst_method();
    
    $request_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $base_path_config = $fst_config['routing']['base_path'] ?? '/';
    if ($base_path_config !== '/' && str_starts_with($request_uri_path, $base_path_config)) {
        $request_uri_path = substr($request_uri_path, strlen($base_path_config));
    }
    if (!str_starts_with($request_uri_path, '/')) $request_uri_path = '/' . $request_uri_path;

    $absolute_path = FST_ROOT_DIR . $request_uri_path;

    $normalized_abs_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolute_path);
    $normalized_file_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, __FILE__);
    $normalized_config_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, FST_CONFIG_FILE);

    if ($normalized_abs_path === $normalized_file_path || $normalized_abs_path === $normalized_config_path) {
        fst_abort(404);
        return;
    }

    // Prioritas #1: Cek Aset Statis (public_folders)
    $public_folders = $fst_config['routing']['public_folders'] ?? [];
    foreach ($public_folders as $folder) {
        $clean_folder = trim($folder, '/');
        if (str_starts_with(ltrim($request_uri_path, '/'), $clean_folder . '/')) {
            if (is_file($absolute_path)) {
                fst_serve_static_file($absolute_path); die();
            }
            break;
        }
    }
    
    // Prioritas #2: Cek Rute Terdaftar (Admin, API, dll) SELALU
    foreach ($fst_routes as $route) {
        list($route_method, $pattern, $callback) = $route;
        if ($route_method !== 'ANY' && $route_method !== $method) continue;
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            call_user_func_array($callback, $matches);
            $fst_route_found = true; return; 
        }
    }
    
    $routing_mode = $fst_config['routing']['mode'] ?? 'static';
    $allow_dynamic_fallback = $fst_config['routing']['static_config']['dynamic_fallback'] ?? false;

    if ($routing_mode === 'static' && !$allow_dynamic_fallback) {
        fst_abort(404);
        return;
    }

    // Prioritas #3: Cek Filesystem
    if ($routing_mode === 'dynamic' || ($routing_mode === 'static' && $allow_dynamic_fallback)) {
        $dynamic_config = $fst_config['routing']['dynamic_config'] ?? [];
        $allowed_exec_exts = $dynamic_config['whitelist_filetype'] ?? ['php'];
        $index_files = $dynamic_config['index_files'] ?? ['index.php', 'index.html'];
        $directory_listing = $dynamic_config['directory_listing'] ?? false;

        if (is_file($absolute_path)) {
            $ext = strtolower(pathinfo($absolute_path, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_exec_exts)) { fst_serve_dynamic_file($absolute_path); $fst_route_found = true; return; }
            else { fst_serve_static_file($absolute_path); $fst_route_found = true; return; }
        }
        elseif (is_dir($absolute_path)) {
            if (str_ends_with($request_uri_path, '/')) {
                foreach ($index_files as $index_file) {
                    $file_to_check = rtrim($absolute_path, '/') . '/' . $index_file;
                    if (is_file($file_to_check)) { fst_serve_dynamic_file($file_to_check); $fst_route_found = true; return; }
                }
                if ($directory_listing) { $relative_path_for_listing = trim($request_uri_path, '/'); fst_show_directory_listing($absolute_path, $relative_path_for_listing); $fst_route_found = true; return; }
            } else { fst_redirect($request_uri_path . '/', 301); }
        }
        elseif (!str_contains(basename($request_uri_path), '.')) {
            foreach ($allowed_exec_exts as $ext) {
                $file_to_check = $absolute_path . '.' . $ext;
                if (is_file($file_to_check)) { fst_serve_dynamic_file($file_to_check); $fst_route_found = true; return; }
            }
        }
    }

    if (!$fst_route_found) fst_abort(404);
}
?>
