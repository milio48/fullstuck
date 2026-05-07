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

function fst_route($method, $path, $callback, $middleware = []) {
    global $fst_routes, $fst_route_prefix, $fst_group_middleware, $fst_config;
    
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

    // Strict Mode: Detect Duplicates
    foreach ($fst_routes as $existing) {
        if ($existing[0] === $method && $existing[3] === $full_original_path) {
            fst_abort(500, "Duplicate route detected: [{$method}] {$full_original_path}. Each route must be unique.");
        }
    }

    if (!is_array($middleware)) $middleware = [$middleware];
    $combined_middleware = array_merge($fst_group_middleware ?? [], $middleware);

    $fst_routes[] = [$method, $final_pattern, $callback, $full_original_path, $combined_middleware];
}

function fst_get($path, $callback, $middleware = []) { fst_route('GET', $path, $callback, $middleware); }
function fst_post($path, $callback, $middleware = []) { fst_route('POST', $path, $callback, $middleware); }
function fst_put($path, $callback, $middleware = []) { fst_route('PUT', $path, $callback, $middleware); }
function fst_patch($path, $callback, $middleware = []) { fst_route('PATCH', $path, $callback, $middleware); }
function fst_delete($path, $callback, $middleware = []) { fst_route('DELETE', $path, $callback, $middleware); }
function fst_any($path, $callback, $middleware = []) { fst_route('ANY', $path, $callback, $middleware); }

function fst_group($prefix, $callback, $middleware = []) {
    global $fst_route_prefix, $fst_group_middleware;
    $parent_prefix = $fst_route_prefix;
    $parent_middleware = $fst_group_middleware ?? [];
    
    $fst_route_prefix = rtrim($parent_prefix, '/') . '/' . trim($prefix, '/');
    
    if (!is_array($middleware)) $middleware = [$middleware];
    $fst_group_middleware = array_merge($parent_middleware, $middleware);
    
    call_user_func($callback);
    
    $fst_route_prefix = $parent_prefix;
    $fst_group_middleware = $parent_middleware;
}

function _fst_get_request_paths() {
    global $fst_config;
    $request_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $base_path_config = $fst_config['routing']['base_path'] ?? '/';
    if ($base_path_config !== '/' && str_starts_with($request_uri_path, $base_path_config)) {
        $request_uri_path = substr($request_uri_path, strlen($base_path_config));
    }
    if (!str_starts_with($request_uri_path, '/')) $request_uri_path = '/' . $request_uri_path;
    $absolute_path = FST_ROOT_DIR . $request_uri_path;
    
    return [
        'uri_path' => $request_uri_path,
        'absolute_path' => $absolute_path
    ];
}

function _fst_is_protected_file($absolute_path) {
    // Protect core framework file and configuration file
    $normalized_abs_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolute_path);
    $normalized_file_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, FST_ROOT_DIR . '/fullstuck.php');
    $normalized_config_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, FST_CONFIG_FILE);

    return ($normalized_abs_path === $normalized_file_path || $normalized_abs_path === $normalized_config_path);
}

function _fst_serve_static_asset($request_uri_path, $absolute_path) {
    global $fst_config;
    $public_folders = $fst_config['routing']['public_folders'] ?? [];
    foreach ($public_folders as $folder) {
        $clean_folder = trim($folder, '/');
        if (str_starts_with(ltrim($request_uri_path, '/'), $clean_folder . '/')) {
            if (is_file($absolute_path)) {
                fst_serve_static_file($absolute_path); 
                die(); // Halt execution after serving static file
            }
            break; // Stop checking other public folders if prefix matches but file doesn't exist
        }
    }
    return false;
}

function _fst_match_static_routes() {
    global $fst_routes, $fst_route_found;
    $uri = fst_uri();
    $method = fst_method();
    
    foreach ($fst_routes as $route) {
        list($route_method, $pattern, $callback) = $route;
        if ($route_method !== 'ANY' && $route_method !== $method) continue;
        
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Remove the full match string
            
            // Execute Middleware
            $middleware_list = $route[4] ?? [];
            foreach ($middleware_list as $mw) {
                if (is_callable($mw)) {
                    $result = call_user_func($mw);
                    // If middleware returns false, halt the route callback execution
                    if ($result === false) {
                        $fst_route_found = true; 
                        return true; 
                    }
                }
            }

            call_user_func_array($callback, $matches);
            $fst_route_found = true; 
            return true; 
        }
    }
    return false;
}

function _fst_match_dynamic_routes($request_uri_path, $absolute_path) {
    global $fst_config, $fst_route_found;
    
    $routing_mode = $fst_config['routing']['mode'] ?? 'static';
    $allow_dynamic_fallback = $fst_config['routing']['static_config']['dynamic_fallback'] ?? false;

    if ($routing_mode === 'static' && !$allow_dynamic_fallback) {
        return false;
    }

    $dynamic_config = $fst_config['routing']['dynamic_config'] ?? [];
    $pages_dir = $dynamic_config['pages_dir'] ?? '';
    $allowed_exec_exts = $dynamic_config['whitelist_filetype'] ?? ['php'];
    $index_files = $dynamic_config['index_files'] ?? ['index.php', 'index.html'];
    $directory_listing = $dynamic_config['directory_listing'] ?? false;

    // Jika pages_dir dikonfigurasi, rebuild absolute_path agar mengarah ke subfolder tersebut
    if (!empty($pages_dir)) {
        $absolute_path = FST_ROOT_DIR . DIRECTORY_SEPARATOR . trim($pages_dir, '/\\') . $request_uri_path;
    }

    if (is_file($absolute_path)) {
        $ext = strtolower(pathinfo($absolute_path, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exec_exts)) { 
            fst_serve_dynamic_file($absolute_path); 
            $fst_route_found = true; 
            return true; 
        } else { 
            fst_serve_static_file($absolute_path); 
            $fst_route_found = true; 
            return true; 
        }
    }
    elseif (is_dir($absolute_path)) {
        if (str_ends_with($request_uri_path, '/')) {
            foreach ($index_files as $index_file) {
                $file_to_check = rtrim($absolute_path, '/') . '/' . $index_file;
                if (is_file($file_to_check)) { 
                    fst_serve_dynamic_file($file_to_check); 
                    $fst_route_found = true; 
                    return true; 
                }
            }
            if ($directory_listing) { 
                $relative_path_for_listing = trim($request_uri_path, '/'); 
                fst_show_directory_listing($absolute_path, $relative_path_for_listing); 
                $fst_route_found = true; 
                return true; 
            }
        } else { 
            fst_redirect($request_uri_path . '/', 301); 
            return true;
        }
    }
    elseif (!str_contains(basename($request_uri_path), '.')) {
        foreach ($allowed_exec_exts as $ext) {
            $file_to_check = $absolute_path . '.' . $ext;
            if (is_file($file_to_check)) { 
                fst_serve_dynamic_file($file_to_check); 
                $fst_route_found = true; 
                return true; 
            }
        }
    }
    
    return false;
}

function fst_run() {
    global $fst_route_found;
    
    ob_start();
    $handled = false;
    
    // 1. Ambil path dan URI yang sudah dibersihkan
    $req = _fst_get_request_paths(); 
    
    // 2. Keamanan: Cegah akses file krusial (fullstuck.php, config)
    if (_fst_is_protected_file($req['absolute_path'])) {
        fst_abort(404);
        $handled = true;
    }

    if (!$handled) {
        // 3. Prioritas #1: Serve Static Asset (gambar, css, js)
        if (_fst_serve_static_asset($req['uri_path'], $req['absolute_path'])) {
            $handled = true;
        }
    }
    
    if (!$handled) {
        // 4. Prioritas #2: Static Routing (Whitelist route & Admin)
        if (_fst_match_static_routes()) {
            $handled = true;
        }
    }
    
    if (!$handled) {
        // 5. Prioritas #3: Dynamic Routing (Fallback ke direktori)
        if (_fst_match_dynamic_routes($req['uri_path'], $req['absolute_path'])) {
            $handled = true;
        }
    }

    // 6. Jika semua gagal, berikan 404
    if (!$handled && !$fst_route_found) {
        fst_abort(404);
    }
    
    $output = ob_get_clean();

    // Eksekusi Clipping HTML jika ini permintaan SPA
    if (fst_is_spa()) {
        $target = fst_spa_target();
        $output = fst_extract_html_tag($output, $target); 
    } 
    // Jika bukan request SPA, tapi SPA mode aktif, injeksi Javascript-nya
    else if (fst_config('spa.enabled', false)) {
        $script_id = fst_config('spa.script_id', 'fst-spa-agent');
        $req_header = fst_config('spa.header_request', 'X-FST-Request');
        $target_header = fst_config('spa.header_target', 'X-FST-Target');
        $inject_id = $script_id ? 'id="'.$script_id.'" data-req-header="'.$req_header.'" data-target-header="'.$target_header.'"' : '';
        $script_tag = "<script {$inject_id}>\n" . (defined('FST_SPA_JS_CODE') ? FST_SPA_JS_CODE : '') . "\n</script>";
        $output = str_ireplace('</body>', $script_tag . '</body>', $output);
    }

    echo $output;
}

?>
