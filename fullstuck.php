<?php
/**
 * 🚀 FULLSTUCK.PHP - The Zero-Config, AI-Friendly Framework
 * 🔗 Repository: https://github.com/milio48/fullstuck
 * 📚 Raw Docs: https://raw.githubusercontent.com/milio48/fullstuck/refs/heads/main/docs/v0.1.0.md
 * 💡 Version: 0.1.0 | FST_HASH: 4d0d242ebcdc7781b91e89370ab85edaccf75cb37e281bb2a86271e76370b285
 */
define('FST_SPA_JS_CODE', 'document.addEventListener(\'click\', function(e) { const link = e.target.closest(\'a\'); if (!link || !link.href) return; if (link.target === \'_blank\' || link.hasAttribute(\'download\')) return; if (link.hostname !== window.location.hostname) return; if (e.ctrlKey || e.metaKey || e.shiftKey) return; e.preventDefault(); fstNavigate(link.href); }); window.addEventListener(\'popstate\', function(e) { fstNavigate(window.location.href, false); }); async function fstNavigate(url, pushState = true) { try { const reqHeader = document.querySelector(\'script#fst-spa-agent\')?.getAttribute(\'data-req-header\') || \'X-FST-Request\'; const targetHeader = document.querySelector(\'script#fst-spa-agent\')?.getAttribute(\'data-target-header\') || \'X-FST-Target\'; const headers = {}; headers[reqHeader] = \'true\'; headers[targetHeader] = \'body\'; // Default target const response = await fetch(url, { headers: headers }); if (!response.ok) { window.location.href = url; // fallback return; } const html = await response.text(); document.body.innerHTML = html; if (pushState) { window.history.pushState({}, \'\', url); } // Dispatch fst:load event for plugins/scripts to re-initialize document.dispatchEvent(new Event(\'fst:load\')); // Re-execute scripts inside body const scripts = document.body.querySelectorAll(\'script\'); scripts.forEach(oldScript => { if (oldScript.id === \'fst-spa-agent\') return; const newScript = document.createElement(\'script\'); Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value)); newScript.appendChild(document.createTextNode(oldScript.innerHTML)); oldScript.parentNode.replaceChild(newScript, oldScript); }); } catch (err) { window.location.href = url; // fallback } } // Initial load event document.dispatchEvent(new Event(\'fst:load\'));');


// FILE: core.php
session_start();
if (!defined('FST_ROOT_DIR')) {
    $root = __DIR__;
    if (php_sapi_name() === 'cli-server') {
        $root = $_SERVER['DOCUMENT_ROOT'];
    } elseif (php_sapi_name() === 'cli') {
        $root = getcwd();
    }
    define('FST_ROOT_DIR', realpath($root) ?: $root);
}
define('FST_CONFIG_FILE', FST_ROOT_DIR . DIRECTORY_SEPARATOR . 'fullstuck.json');

if (!file_exists(FST_CONFIG_FILE)) {
    fst_handle_installation();
    die();
}

global $fst_config, $fst_pdo, $fst_routes, $fst_route_prefix, $fst_route_found;
$config_content = @file_get_contents(FST_CONFIG_FILE);
$fst_config = $config_content ? json_decode($config_content, true) : null;
$fst_routes = [];
$fst_route_prefix = '';
$fst_route_found = false;

if ($fst_config === null && file_exists(FST_CONFIG_FILE)) {
    if (function_exists('fst_abort')) fst_abort(500, "Failed to decode `fullstuck.json`. Check for syntax errors.");
    else die("Error: Failed to decode `fullstuck.json`. Check for syntax errors.");
}

if (fst_is_dev()) {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}
ini_set('display_errors', '0'); // Nonaktifkan display_errors asli agar digantikan oleh UI kita

function _fst_error_handler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) return false;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function _fst_exception_handler($e) {
    global $fst_config;
    http_response_code(500);
    
    if (!fst_is_dev()) {

        error_log($e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        if (function_exists('fst_abort')) { fst_abort(500, "Internal Server Error."); } 
        else { die("Internal Server Error."); }
    }

    $message = htmlspecialchars($e->getMessage());
    $file = htmlspecialchars($e->getFile());
    $line = $e->getLine();
    $trace = htmlspecialchars($e->getTraceAsString());
    
    $code_snippet = '';
    if (file_exists($e->getFile())) {
        $lines = file($e->getFile());
        $start = max(0, $line - 5);
        $end = min(count($lines), $line + 4);
        for ($i = $start; $i < $end; $i++) {
            $current_line = $i + 1;
            $line_content = htmlspecialchars($lines[$i]);
            $highlight = ($current_line === $line) ? 'background-color: rgba(220, 53, 69, 0.4); border-left: 3px solid #dc3545;' : 'border-left: 3px solid transparent;';
            $code_snippet .= "<div style='{$highlight} padding: 2px 5px;'><strong>" . str_pad($current_line, 4, ' ', STR_PAD_LEFT) . " |</strong> {$line_content}</div>";
        }
    }

    $class_name = get_class($e);

    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Exception: {$message}</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 20px; }
            .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 8px solid #dc3545; }
            h1 { color: #dc3545; margin-top: 0; font-size: 24px; word-break: break-all; line-height: 1.3;}
            .badge { display: inline-block; background: #dc3545; color: white; padding: 4px 10px; border-radius: 4px; font-size: 13px; font-weight: bold; margin-bottom: 15px; text-transform: uppercase;}
            .meta { background: #f1f3f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-family: monospace; font-size: 14px; border: 1px solid #e9ecef;}
            .meta strong { color: #555; display: inline-block; width: 60px;}
            .code-preview { background: #272822; color: #f8f8f2; padding: 15px 0; border-radius: 5px; overflow-x: auto; font-family: "Courier New", Courier, monospace; font-size: 14px; line-height: 1.5; margin-bottom: 20px;}
            .code-preview div { white-space: pre; }
            h3 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 30px; font-size: 18px;}
            pre.trace { background: #f1f3f5; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 13px; line-height: 1.6; border: 1px solid #e9ecef;}
        </style>
    </head>
    <body>
        <div class="container">
            <span class="badge">{$class_name}</span>
            <h1>{$message}</h1>
            <div class="meta">
                <strong>File:</strong> {$file}<br>
                <strong>Line:</strong> {$line}
            </div>
            
            <h3>Code Snippet</h3>
            <div class="code-preview">{$code_snippet}</div>
            
            <h3>Stack Trace</h3>
            <pre class="trace">{$trace}</pre>
        </div>
    </body>
    </html>
HTML;
    die();
}

function _fst_fatal_error_handler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
        _fst_exception_handler(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
    }
}

set_error_handler('_fst_error_handler');
set_exception_handler('_fst_exception_handler');
register_shutdown_function('_fst_fatal_error_handler');

function fst_is_dev() {
    global $fst_config;
    return ($fst_config['environment'] ?? 'production') === 'development';
}

function fst_config($key = null, $default = null) {
    global $fst_config;
    if ($key === null) return $fst_config;
    $keys = explode('.', $key);
    $val = $fst_config;
    foreach ($keys as $k) {
        if (is_array($val) && array_key_exists($k, $val)) {
            $val = $val[$k];
        } else {
            return $default;
        }
    }
    return $val;
}

function fst_is_spa(): bool {
    $header_name = fst_config('spa.header_request', 'X-FST-Request');
    $req_header = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
    return isset($_SERVER[$req_header]);
}

function fst_spa_target(): string {
    $header_name = fst_config('spa.header_target', 'X-FST-Target');
    $target_header = 'HTTP_' . str_replace('-', '_', strtoupper($header_name));
    return $_SERVER[$target_header] ?? 'body';
}

function fst_extract_html_tag($html, $tag = 'body') {
    if (empty(trim($html))) return '';
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $elements = $dom->getElementsByTagName($tag);
    if ($elements->length > 0) {
        $inner_html = '';
        foreach ($elements->item(0)->childNodes as $child) {
            $inner_html .= $dom->saveHTML($child);
        }
        return $inner_html;
    }
    return $html;
}

// FILE: database.php
try {
    if (!$fst_config) throw new Exception("Configuration not loaded.");

    $db_all_config = $fst_config['database'] ?? null;
    $driver = $db_all_config['driver'] ?? 'none';

    if ($driver !== 'none' && $db_all_config) {
        try {
            $db_config = $db_all_config[$driver];
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];

            switch ($driver) {
                case 'mysql':
                    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
                    $fst_pdo = new PDO($dsn, $db_config['username'], $db_config['password'], $options);
                    break;
                case 'sqlite':
                    $path = FST_ROOT_DIR . '/' . $db_config['database_path'];
                    $dsn = "sqlite:" . $path;
                    $fst_pdo = new PDO($dsn, null, null, $options);
                    break;
                default:
                    throw new Exception("Unsupported database driver '{$driver}' in fullstuck.json.");
            }
        } catch (Exception $e) {
            if (function_exists('fst_abort')) fst_abort(500, "Database Connection Failed: " . $e->getMessage());
            else die("FATAL ERROR: Database Connection Failed: " . $e->getMessage());
        }
    }
} catch (Exception $e) {
    if (function_exists('fst_abort')) fst_abort(500, "Database Connection Failed: " . $e->getMessage());
    else die("FATAL ERROR: Database Connection Failed: " . $e->getMessage());
}

function fst_db($mode, $sql, $params = []) {
    global $fst_pdo;

    if ($fst_pdo === null) {
        fst_abort(500, "Database function fst_db() called, but no database is configured or connected. Check 'fullstuck.json'.");
    }

    $stmt = $fst_pdo->prepare($sql);
    $stmt->execute($params);
    $normalizedSql = strtoupper(trim($sql));
    $isInsert = strpos($normalizedSql, 'INSERT') === 0;
    if (strtoupper($mode) === 'EXEC') {
        return ['affected_rows' => $stmt->rowCount(),'last_id' => $isInsert ? $fst_pdo->lastInsertId() : null,'query_type' => strtok($normalizedSql, ' '),'success' => true];
    }
    return match(strtoupper($mode)) { 'ROW' => $stmt->fetch(), 'SCALAR' => $stmt->fetchColumn(), 'ALL' => $stmt->fetchAll(), default => $stmt->fetchAll() };
}

function fst_db_select($table, $conditions = [], $options = []) {
    $columns = $options['select'] ?? '*';
    $sql = "SELECT {$columns} FROM `{$table}`";
    $params = [];
    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $k => $v) {
            $where[] = "`{$k}` = ?";
            $params[] = $v;
        }
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    if (isset($options['order_by'])) $sql .= " ORDER BY " . $options['order_by'];
    if (isset($options['limit'])) $sql .= " LIMIT " . (int)$options['limit'];
    if (isset($options['offset'])) $sql .= " OFFSET " . (int)$options['offset'];
    
    $mode = $options['mode'] ?? 'ALL';
    return fst_db($mode, $sql, $params);
}

function fst_db_insert($table, $data) {
    if (empty($data)) return false;
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($data), '?');
    $sql = "INSERT INTO `{$table}` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $placeholders) . ")";
    return fst_db('EXEC', $sql, array_values($data));
}

function fst_db_update($table, $data, $conditions = []) {
    if (empty($data)) return false;
    $set = [];
    $params = [];
    foreach ($data as $k => $v) {
        $set[] = "`{$k}` = ?";
        $params[] = $v;
    }
    $sql = "UPDATE `{$table}` SET " . implode(", ", $set);
    
    if (!empty($conditions)) {
        $where = [];
        foreach ($conditions as $k => $v) {
            $where[] = "`{$k}` = ?";
            $params[] = $v;
        }
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    return fst_db('EXEC', $sql, $params);
}

function fst_db_delete($table, $conditions) {
    if (empty($conditions)) return false; // Prevent accidental full table delete
    $where = [];
    $params = [];
    foreach ($conditions as $k => $v) {
        $where[] = "`{$k}` = ?";
        $params[] = $v;
    }
    $sql = "DELETE FROM `{$table}` WHERE " . implode(" AND ", $where);
    return fst_db('EXEC', $sql, $params);
}

// FILE: router.php
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

            $middleware_list = $route[4] ?? [];
            foreach ($middleware_list as $mw) {
                if (is_callable($mw)) {
                    $result = call_user_func($mw);

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

    $req = _fst_get_request_paths(); 

    if (_fst_is_protected_file($req['absolute_path'])) {
        fst_abort(404);
        $handled = true;
    }

    if (!$handled) {

        if (_fst_serve_static_asset($req['uri_path'], $req['absolute_path'])) {
            $handled = true;
        }
    }
    
    if (!$handled) {

        if (_fst_match_static_routes()) {
            $handled = true;
        }
    }
    
    if (!$handled) {

        if (_fst_match_dynamic_routes($req['uri_path'], $req['absolute_path'])) {
            $handled = true;
        }
    }

    if (!$handled && !$fst_route_found) {
        fst_abort(404);
    }
    
    $output = ob_get_clean();

    if (fst_is_spa()) {
        $target = fst_spa_target();
        $output = fst_extract_html_tag($output, $target); 
    } 

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

// FILE: http.php
function fst_uri() {
    global $fst_config;
    $base_path = $fst_config['routing']['base_path'] ?? '/';
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    if ($base_path !== '/' && str_starts_with($uri, $base_path)) {
        $uri = substr($uri, strlen($base_path));
    }
    if (!str_starts_with($uri, '/')) $uri = '/' . $uri;
    if ($uri !== '/' && str_ends_with($uri, '/')) $uri = rtrim($uri, '/');
    return $uri ?: '/';
}
function fst_method() { return $_SERVER['REQUEST_METHOD']; }
function _fst_parsed_body() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = array_merge($_GET, $_POST);
    if (empty($_POST)) {
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $cache = array_merge($cache, $json);
            }
        }
    }
    return $cache;
}
function fst_input($key, $default = null) { $data = _fst_parsed_body(); return $data[$key] ?? $default; }
function fst_request() { return _fst_parsed_body(); }
function fst_file($key) { return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK ? $_FILES[$key] : null; }
function fst_escape($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
function e($str) { return fst_escape($str); }

function fst_json($data, $status = 200) { fst_status_code($status); header('Content-Type: application/json'); echo json_encode($data); die(); }
function fst_text($string, $status = 200) { fst_status_code($status); header('Content-Type: text/plain'); echo $string; die(); }
function fst_redirect($url, $code = 302) {
    global $fst_config;
    $base_path = $fst_config['routing']['base_path'] ?? '/';
    if (!preg_match('/^(http:\/\/|https:\/\/|\/\/)/', $url)) {
        $url = rtrim($base_path, '/') . '/' . ltrim($url, '/');
    }
    if (fst_is_spa()) {
        header("X-FST-Redirect: " . $url);
        die();
    }
    header("Location: " . $url, true, $code);
    die();
}
function fst_status_code($code) { http_response_code($code); }

function fst_session_set($key, $value) { $_SESSION[$key] = $value; }
function fst_session_get($key, $default = null) { return $_SESSION[$key] ?? $default; }
function fst_session_forget($key) { unset($_SESSION[$key]); }
function fst_flash_set($key, $message) { $_SESSION['_flash'][$key] = $message; }
function fst_flash_has($key) { return isset($_SESSION['_flash'][$key]); }
function fst_flash_get($key, $default = null) { $message = $_SESSION['_flash'][$key] ?? $default; unset($_SESSION['_flash'][$key]); return $message; }

function fst_csrf_token() { if (empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token'] = bin2hex(random_bytes(32)); return $_SESSION['_csrf_token']; }
function fst_csrf_field() { return '<input type="hidden" name="_token" value="' . fst_csrf_token() . '">'; }
function fst_csrf_check() { $submitted_token = $_POST['_token'] ?? $_GET['_token'] ?? null; if (!$submitted_token || !hash_equals(fst_csrf_token(), $submitted_token)) fst_abort(403, 'Invalid CSRF token.'); }

function fst_upload($key, $folder, $options = []) {
    $file = fst_file($key);
    if (!$file) return ['success' => false, 'error' => 'No file uploaded or upload error.', 'path' => null];
    $max_size_kb = $options['max_size'] ?? 2048;
    $allowed_types = $options['allowed_types'] ?? [];
    if ($file['size'] > $max_size_kb * 1024) return ['success' => false, 'error' => "File is too large (max {$max_size_kb} KB).", 'path' => null];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowed_types) && !in_array($ext, $allowed_types)) return ['success' => false, 'error' => "Invalid file type. Allowed: " . implode(', ', $allowed_types), 'path' => null];
    $safe_basename = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($file['name'], ".".$ext));
    $filename = $safe_basename . '-' . uniqid() . '.' . $ext;
    
    $destination_folder = FST_ROOT_DIR . '/' . trim($folder, '/');
    $destination_path = $destination_folder . '/' . $filename;
    $public_path = trim($folder, '/') . '/' . $filename;
    if (!is_dir($destination_folder) && !mkdir($destination_folder, 0755, true)) return ['success' => false, 'error' => "Failed to create upload directory.", 'path' => null];
    if (move_uploaded_file($file['tmp_name'], $destination_path)) return ['success' => true, 'path' => $public_path, 'error' => null];
    else return ['success' => false, 'error' => 'Failed to move uploaded file.', 'path' => null];
}

// FILE: view.php
function fst_view($path, $data = []) { extract($data); require FST_ROOT_DIR . '/' . $path; }
function fst_partial($path, $data = []) { fst_view($path, $data); }

function fst_serve_static_file($file_path) {
    global $fst_config;
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    $mime_types = $fst_config['mime_types'] ?? [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png',
        'svg'  => 'image/svg+xml',
        'html' => 'text/html',
        'txt'  => 'text/plain'
    ];
    
    $content_type = $mime_types[$ext] ?? 'application/octet-stream';
    
    header('Content-Type: ' . $content_type);
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: public, max-age=31536000');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    
    readfile($file_path);
}

function fst_serve_dynamic_file($file_path) { 
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION)); 
    if ($ext === 'php') { 
        require $file_path; 
    } else { 
        fst_serve_static_file($file_path); 
    } 
}

function fst_show_directory_listing($dir_path, $uri_prefix) { 
    echo "<h1>Index of /" . htmlspecialchars(trim($uri_prefix, '/')) . "</h1><hr><ul>"; 
    if ($uri_prefix) { 
        echo "<li><a href='../'>../ (Parent Directory)</a></li>"; 
    } 
    $files = scandir($dir_path); 
    if ($files === false) { 
        echo "<li>Cannot read directory contents.</li>"; 
    } else { 
        natcasesort($files); 
        foreach ($files as $file) { 
            if ($file === '.' || $file === '..') continue; 
            $relative_uri = '/' . trim($uri_prefix, '/') . ($uri_prefix ? '/' : '') . $file; 
            $is_dir = is_dir($dir_path . '/' . $file); 
            $link_text = $file . ($is_dir ? '/' : ''); 
            echo "<li><a href='" . htmlspecialchars($relative_uri) . "'>" . htmlspecialchars($link_text) . "</a></li>"; 
        } 
    } 
    echo "</ul><hr>"; 
}

// FILE: utility.php
function fst_dump(...$vars) {
    global $fst_config;
    if (!fst_is_dev()) {
        return;
    }
    echo '<pre style="background-color: #1a1a1a; color: #f0f0f0; padding: 15px; border: 1px solid #444; margin: 10px; border-radius: 5px; text-align: left; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.5;">';
    foreach ($vars as $var) { var_dump($var); }
    echo '</pre>';
}
function fst_dd(...$vars) { fst_dump(...$vars); die(); }

function _fst_strlen($str) {
    return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);
}

function fst_validate($data, $rules) {
    $errors = [];
    $sanitized = [];

    foreach ($rules as $field => $rule_string) {
        $value = $data[$field] ?? null;
        $rules_array = is_array($rule_string) ? $rule_string : explode('|', $rule_string);
        
        $field_valid = true;

        foreach ($rules_array as $rule) {
            $params = [];
            if (str_contains($rule, ':')) {
                list($rule_name, $param_str) = explode(':', $rule, 2);
                $params = explode(',', $param_str);
            } else {
                $rule_name = $rule;
            }

            if ($rule_name !== 'required' && ($value === null || trim((string)$value) === '')) {
                continue;
            }

            if ($rule_name === 'required') {
                if ($value === null || trim((string)$value) === '') {
                    $errors[$field][] = "Bidang '{$field}' wajib diisi.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "Bidang '{$field}' harus berupa email yang valid.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'min') {
                $min = (int)($params[0] ?? 0);
                if (_fst_strlen((string)$value) < $min) {
                    $errors[$field][] = "Bidang '{$field}' minimal {$min} karakter.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'max') {
                $max = (int)($params[0] ?? 0);
                if (_fst_strlen((string)$value) > $max) {
                    $errors[$field][] = "Bidang '{$field}' maksimal {$max} karakter.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'numeric') {
                if (!is_numeric($value)) {
                    $errors[$field][] = "Bidang '{$field}' harus berupa angka.";
                    $field_valid = false;
                }
            } elseif ($rule_name === 'in') {
                if (!in_array($value, $params)) {
                    $errors[$field][] = "Bidang '{$field}' harus salah satu dari: " . implode(', ', $params) . ".";
                    $field_valid = false;
                }
            }
        }
        
        if ($value !== null) {
            $sanitized[$field] = is_string($value) ? trim($value) : $value;
        }
    }

    return [
        'valid' => count($errors) === 0,
        'errors' => $errors,
        'data' => $sanitized
    ];
}

// FILE: install.php
function fst_handle_installation() {
    $error_message = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $driver = $_POST['driver'] ?? 'sqlite';
            $server_type = $_POST['server_type'] ?? 'apache_litespeed';
            
            if ($driver !== 'none') {
                if ($driver === 'mysql') { $dsn = "mysql:host={$_POST['db_host']};dbname={$_POST['db_name']};charset=utf8mb4"; new PDO($dsn, $_POST['db_user'], $_POST['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]); }
                else { $path = FST_ROOT_DIR . '/' . $_POST['db_path']; $dir = dirname($path); if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new Exception("Failed to create folder '{$dir}'. Check permissions."); new PDO("sqlite:" . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); }
            }
            
            $config_data = [
                "environment" => "development", "admin" => ["page_url" => $_POST['admin_url'] ?? '/stuck',"password" => password_hash($_POST['admin_pass'], PASSWORD_DEFAULT)],
                "database" => ["driver" => $driver,"mysql" => ["host" => $_POST['db_host'] ?? 'localhost',"dbname" => $_POST['db_name'] ?? '',"username" => $_POST['db_user'] ?? 'root',"password" => $_POST['db_pass'] ?? ''],"sqlite" => ["database_path" => $_POST['db_path'] ?? 'database.sqlite']],
                "routing" => ["mode" => $_POST['routing_mode'] ?? 'static',"base_path" => "/","public_folders" => ["assets", "uploads", "storage/public"],"error_handlers" => ["404" => "views/errors/404.php","403" => "Sorry, you do not have permission.","405" => "Method not allowed.","500" => "views/errors/500.php"],"static_config" => ["routes_file" => ["router.php"],"dynamic_fallback" => false],"dynamic_config" => ["whitelist_filetype" => ["php", "html"],"index_files" => ["index.php", "index.html"],"directory_listing" => false],"regex_shortcuts" => ["i"=>"([0-9]+)","a"=>"([a-zA-Z0-9]+)","s"=>"([a-zA-Z0-9\\-]+)","h"=>"([a-fA-F0-9]+)","any"=>"([^/]+)"]],
                "mime_types" => ["css"=>"text/css","js"=>"application/javascript","jpg"=>"image/jpeg","jpeg"=>"image/jpeg","png"=>"image/png","gif"=>"image/gif","svg"=>"image/svg+xml","woff"=>"font/woff","woff2"=>"font/woff2","ttf"=>"font/ttf","eot"=>"application/vnd.ms-fontobject","html"=>"text/html","htm"=>"text/html","txt"=>"text/plain","json"=>"application/json","pdf"=>"application/pdf"]
            ];
            
            if (file_put_contents(FST_CONFIG_FILE, json_encode($config_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) throw new Exception("Failed to write `fullstuck.json`. Check folder permissions.");
            
            $htaccess_content = null;
            if ($server_type === 'apache_litespeed') {
                $htaccess_code = implode("\n", [
                    '# 1. Nonaktifkan fitur "Index of" dan "MultiViews"',
                    'Options -Indexes -MultiViews',
                    '',
                    '<IfModule mod_rewrite.c>',
                    '    RewriteEngine On',
                    '    RewriteBase /',
                    '    ',
                    '    # 2. Aturan "Rakus" (Kirim SEMUA ke fullstuck.php)',
                    '    RewriteRule ^(.*)$ fullstuck.php [L]',
                    '</IfModule>'
                ]);
                if (file_put_contents(FST_ROOT_DIR . '/.htaccess', $htaccess_code) === false) $htaccess_content = $htaccess_code;
            }
            echo fst_show_install_success($htaccess_content); return;
        } catch (Exception $e) { $error_message = "ERROR: " . $e->getMessage(); }
    }
    echo fst_show_install_form($error_message);
}
function fst_render_status_row($label, $success, $note = '', $optional = false) { if ($success) $status = '<span style="color:green;">✔ OK</span>'; else if ($optional) $status = '<span style="color:orange;">⚠ Optional</span>'; else $status = '<span style="color:red;">❌ Failed</span>'; return "<tr><td>{$label}</td><td>{$status}</td><td>" . htmlspecialchars($note) . "</td></tr>"; }
function fst_show_install_success($htaccess_content) { $htaccess_html = ''; if ($htaccess_content) { $htaccess_safe = htmlspecialchars($htaccess_content); $htaccess_html = <<<HTML
    <p style="color:red; font-weight:bold;">ACTION REQUIRED:</p>
    <p>Failed to write the <code>.htaccess</code> file automatically (likely a folder permission issue). Please create a <code>.htaccess</code> file in the same folder as <code>fullstuck.php</code> and paste in the following code:</p>
    <pre class="code">{$htaccess_safe}</pre>
HTML;
} else { $htaccess_html = '<p style="color:green;">The <code>.htaccess</code> file (if needed) has also been created automatically.</p>'; }
$html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Installation Complete</title>
<style>body{font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; line-height: 1.6;} .code{background: #f4f4f4; padding: 15px; border-radius: 4px; border: 1px solid #ddd; overflow-x: auto;} a {display:inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px;}</style>
</head><body><h1>🚀 Installation Successful!</h1><p>The <code>fullstuck.json</code> file has been successfully created.</p>{$htaccess_html}<p>Your framework is now ready to use.</p><a href="./">Start Using Framework</a></body></html>
HTML;
return $html;
}

function fst_show_install_form($error_message) { $checks = ['php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),'dir_writable' => is_writable(FST_ROOT_DIR),'pdo_loaded' => extension_loaded('pdo'),'pdo_mysql' => extension_loaded('pdo_mysql'),'pdo_sqlite' => extension_loaded('pdo_sqlite'),'server_soft' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown']; $detected_server = 'other'; if (stripos($checks['server_soft'], 'Apache') !== false || stripos($checks['server_soft'], 'Litespeed') !== false) $detected_server = 'apache_litespeed'; elseif (stripos($checks['server_soft'], 'Development Server') !== false) $detected_server = 'php_s'; elseif (stripos($checks['server_soft'], 'nginx') !== false) $detected_server = 'nginx'; $status_rows = ''; $status_rows .= fst_render_status_row('PHP Version (>= 8.0)', $checks['php_version'], 'Your version: ' . PHP_VERSION); $status_rows .= fst_render_status_row('Directory Writable', $checks['dir_writable'], FST_ROOT_DIR); $status_rows .= fst_render_status_row('PDO Extension', $checks['pdo_loaded'], 'Required for database'); $status_rows .= fst_render_status_row('PDO MySQL Driver', $checks['pdo_mysql'], '', !$checks['pdo_sqlite']); $status_rows .= fst_render_status_row('PDO SQLite Driver', $checks['pdo_sqlite'], '', !$checks['pdo_mysql']); $status_rows .= fst_render_status_row('Web Server Info', true, $checks['server_soft'], true); $error_html = $error_message ? "<div class='error'>" . htmlspecialchars($error_message) . "</div>" : ''; $opt_apache = ($detected_server === 'apache_litespeed') ? 'selected' : ''; $opt_nginx = ($detected_server === 'nginx') ? 'selected' : ''; $opt_php_s = ($detected_server === 'php_s') ? 'selected' : ''; $opt_other = ($detected_server === 'other') ? 'selected' : ''; $opt_sqlite = 'selected'; $opt_mysql = ''; if (!$checks['pdo_sqlite'] && $checks['pdo_mysql']) { $opt_mysql = 'selected'; $opt_sqlite = ''; } $root_dir_safe = htmlspecialchars(FST_ROOT_DIR);
$html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>FullStuck.php Installation</title>
<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; line-height: 1.6; } h1, h2 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; } table { width: 100%; border-collapse: collapse; margin-bottom: 20px; } th, td { text-align: left; padding: 8px; border-bottom: 1px solid #f0f0f0; } tr:nth-child(even) { background-color: #f9f9f9; } .form-group { margin-bottom: 15px; } label { display: block; font-weight: bold; margin-bottom: 5px; } input[type="text"], input[type="password"], select { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; } button { background: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; } button:hover { background: #0056b3; } .error { background: #ffe0e0; border: 1px solid #ffb0b0; color: #d00; padding: 15px; border-radius: 4px; margin-bottom: 20px; } .note { font-size: 0.9em; color: #555; } code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }</style>
</head><body><h1>🚀 Welcome to FullStuck.php</h1><p>The <code>fullstuck.json</code> configuration file was not found. Please complete the installation steps below to get started.</p>{$error_html}<h2>🛠️ Server Compatibility Check</h2><table><thead><tr><th>Requirement</th><th>Status</th><th>Notes</th></tr></thead><tbody>{$status_rows}</tbody></table><h2>⚙️ Configuration</h2><form method="POST" id="install-form"><div class="form-group"><label>Web Server Type</label><select name="server_type"><option value="apache_litespeed" {$opt_apache}>Apache / Litespeed (.htaccess will be created automatically)</option><option value="nginx" {$opt_nginx}>Nginx (Instructions will be shown later)</option><option value="php_s" {$opt_php_s}>PHP -S (No .htaccess needed)</option><option value="other" {$opt_other}>Other (Manual configuration)</option></select></div><div class="form-group"><label>Database Driver</label><select name="driver" id="driver-select"><option value="sqlite" {$opt_sqlite}>SQLite</option><option value="mysql" {$opt_mysql}>MySQL</option><option value="none">No Database (Setup Later)</option></select></div><div id="mysql-fields"><div class="form-group"><label for="db_host">Database Host</label><input type="text" name="db_host" id="db_host" value="localhost"></div><div class="form-group"><label for="db_name">Database Name</label><input type="text" name="db_name" id="db_name" value="fullstuck_db"></div><div class="form-group"><label for="db_user">Database Username</label><input type="text" name="db_user" id="db_user" value="root"></div><div class="form-group"><label for="db_pass">Database Password</label><input type="password" name="db_pass" id="db_pass"></div></div><div id="sqlite-fields"><div class="form-group"><label for="db_path">SQLite File Path</label><input type="text" name="db_path" id="db_path" value="database.sqlite"><p class="note">Default: <code>database.sqlite</code>. Path is relative to <code>{$root_dir_safe}</code>. The folder will be created if it doesn't exist.</p></div></div><div class="form-group"><label>Routing Mode</label><select name="routing_mode"><option value="static" selected>Static (Whitelist Mode / routes.php) - Recommended</option><option value="dynamic">Dynamic (File System Mode / Apache-like)</option></select><p class="note">Static is more secure and structured. Dynamic is faster for initial setup.</p></div><div class="form-group"><label for="admin_url">Admin Dashboard URL</label><input type="text" name="admin_url" id="admin_url" value="/stuck" required><p class="note">The secret URL to access the admin panel in development mode.</p></div><div class="form-group"><label for="admin_pass">Admin Dashboard Password</label><input type="password" name="admin_pass" id="admin_pass" required><p class="note">Will be hashed. Used for the admin API in development mode.</p></div><button type="submit">Install FullStuck.php</button></form>
<script>
    const driverSelect = document.getElementById('driver-select');
    const mysqlFields = document.getElementById('mysql-fields');
    const sqliteFields = document.getElementById('sqlite-fields');
    function toggleFields() {
        if (driverSelect.value === 'mysql') {
            mysqlFields.style.display = 'block';
            sqliteFields.style.display = 'none';
        } else if (driverSelect.value === 'sqlite') {
            mysqlFields.style.display = 'none';
            sqliteFields.style.display = 'block';
        } else { // Ini adalah kasus 'none'
            mysqlFields.style.display = 'none';
            sqliteFields.style.display = 'none';
        }
    }
    driverSelect.addEventListener('change', toggleFields);
    toggleFields();
</script>
</body></html>
HTML;
return $html;
}

// FILE: admin.php
if (fst_is_dev()) {
    $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';

    fst_get($admin_base . '/login', 'fst_admin_show_login');
    fst_post($admin_base . '/login', 'fst_admin_do_login');
    fst_get($admin_base . '/logout', 'fst_admin_do_logout');

    fst_get($admin_base, 'fst_admin_show_monitor');

    fst_get($admin_base . '/config', 'fst_admin_show_config');
    fst_post($admin_base . '/config/save', 'fst_admin_save_config');

    fst_get($admin_base . '/routes', 'fst_admin_show_routes');
    
    fst_get($admin_base . '/server-info', 'fst_admin_show_server_info');

    fst_get($admin_base . '/scan', 'fst_admin_show_scan_page');
    fst_post($admin_base . '/scan/run', 'fst_admin_run_scan');

    fst_get($admin_base . '/integrity', 'fst_admin_show_integrity');
    fst_get($admin_base . '/plugins', 'fst_admin_show_plugins');
    fst_post($admin_base . '/plugins/install', 'fst_admin_install_plugin');
    fst_post($admin_base . '/plugins/toggle', 'fst_admin_toggle_plugin');
    fst_post($admin_base . '/plugins/uninstall', 'fst_admin_uninstall_plugin');
}


if (fst_is_dev()) {

    function fst_admin_check_auth() {
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        if (empty($_SESSION['fst_admin_logged_in'])) {
            fst_flash_set('error_message', 'Please login to access the admin area.');
            fst_redirect($admin_base . '/login');
        }
    }

    function fst_admin_show_login() {
        header('Content-Type: text/html; charset=UTF-8');
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        $error = fst_flash_get('error_message');
        $error_html = $error ? "<p style='color:red;'>{$error}</p>" : '';
        $csrf = fst_csrf_field();

        $html = <<<HTML
<!DOCTYPE html><html lang="en"><head><title>Admin Login</title><style> body{font-family:sans-serif; max-width:400px; margin:50px auto; padding:20px; border:1px solid #ccc;} input{width:100%; padding:8px; margin-bottom:10px;} button{padding:10px 15px;}</style></head>
<body><h1>Admin Login</h1>{$error_html}
<form method="POST" action="{$admin_base}/login">{$csrf}
<label for="password">Password:</label><input type="password" name="password" id="password" required><button type="submit">Login</button></form></body></html>
HTML;
        echo $html;
    }

    function fst_admin_do_login() {
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        fst_csrf_check();

        $password = $_POST['password'] ?? '';
        $hashed_password = $fst_config['admin']['password'] ?? '';

        if (password_verify($password, $hashed_password)) {
            $_SESSION['fst_admin_logged_in'] = true;
            fst_redirect($admin_base);
        } else {
            fst_flash_set('error_message', 'Invalid password.');
            fst_redirect($admin_base . '/login');
        }
    }

    function fst_admin_do_logout() {
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        unset($_SESSION['fst_admin_logged_in']);
        fst_flash_set('success_message', 'You have been logged out.');
        fst_redirect($admin_base . '/login');
    }
    
    function fst_admin_render_page($title, $content) {
         header('Content-Type: text/html; charset=UTF-8');
         global $fst_config;
         $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
         $success_msg = fst_flash_get('success_message');
         $error_msg = fst_flash_get('error_message');
         $info_html = '';
         if ($success_msg) $info_html .= "<p style='color:green;'>{$success_msg}</p>";
         if ($error_msg) $info_html .= "<p style='color:red;'>{$error_msg}</p>";
         
         $html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>{$title} - Admin</title>
<style>
    body { font-family: sans-serif; margin: 0; }
    .container { max-width: 900px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; }
    nav { background: #333; padding: 10px; margin-bottom: 20px; }
    nav a { color: white; margin-right: 15px; text-decoration: none; }
    nav a:hover { text-decoration: underline; }
    h1, h2 { border-bottom: 1px solid #eee; padding-bottom: 5px; }
    pre { background: #f4f4f4; padding: 10px; border: 1px solid #ccc; overflow-x: auto; }
    textarea { width: 100%; min-height: 400px; box-sizing: border-box; font-family: monospace; }
    button { padding: 10px 15px; background: #007bff; color: white; border: none; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px;}
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left;}
    th { background-color: #f2f2f2;}

    
    .alert-warning {
        background-color: #fffbe6;
        border: 1px solid #ffe58f;
        border-left-width: 5px;
        border-left-color: #ffa940;
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        color: #ad8601;
        font-family: monospace;
        font-size: 1.1em;
    }
    .alert-warning strong {
        color: #d46b08;
    }
</style>
</head><body>
<nav>
    <a href="{$admin_base}">Monitor</a>
    <a href="{$admin_base}/config">Config Editor</a>
    <a href="{$admin_base}/routes">Route List</a>
    <a href="{$admin_base}/server-info">Server Info</a>
    <a href="{$admin_base}/scan">Scan Project</a>
    <a href="{$admin_base}/integrity">Integrity</a>
    <a href="{$admin_base}/plugins">Plugins</a>
    <a href="{$admin_base}/logout" style="float:right;">Logout</a>
</nav>
<div class="container">
    <h1>{$title}</h1>
    {$info_html}
    {$content}
</div>
</body></html>
HTML;
         echo $html;
    }

    function fst_admin_show_monitor() {
        fst_admin_check_auth();
        global $fst_config, $fst_pdo;
        
        $dev_warning_html = ''; // Variabel baru untuk warning khusus
        $warnings = [];
        $errors = [];

        $current_env = $fst_config['environment'] ?? 'production';

        if ($current_env === 'development') {

            $dev_warning_html = '<div class="alert-warning"><strong>WARNING:</strong> Environment is set to \'development\'. Make sure to change it to \'production\' before going live!</div>';
        } elseif ($current_env !== 'production') {


            $warnings[] = "Environment is set to '{$current_env}'. This is not a 'production' build.";
        }


        if ($fst_config['routing']['mode'] === 'static') {
            $route_files = (array)($fst_config['routing']['static_config']['routes_file'] ?? []);
            foreach ($route_files as $file) {
                if (!file_exists(FST_ROOT_DIR . '/' . $file)) {
                    $errors[] = "Static route file not found: <code>{$file}</code>";
                }
            }
        }
        
        $public_folders = $fst_config['routing']['public_folders'] ?? [];
        foreach ($public_folders as $folder) {
            if (!is_dir(FST_ROOT_DIR . '/' . $folder)) {
                $warnings[] = "Public folder not found (will be ignored): <code>{$folder}</code>";
            }
        }

        $error_handlers = $fst_config['routing']['error_handlers'] ?? [];
        foreach ($error_handlers as $code => $handler) {
            if (preg_match('/\.php$|\.html$/', $handler) && !file_exists(FST_ROOT_DIR . '/' . $handler)) {
                $warnings[] = "Error handler file for code {$code} not found: <code>{$handler}</code> (Fallback will be used)";
            }
        }

        $db_status = '';
        $db_driver = $fst_config['database']['driver'] ?? 'none';
        
        if ($db_driver === 'none') {
            $db_status = '<span style="color:orange;">⚠ Not Configured</span>';
        } elseif ($fst_pdo) { // Cek jika $fst_pdo berhasil diinisialisasi
            try {
                $stmt = $fst_pdo->query("SELECT 1");
                $stmt->fetch();
                $db_status = '<span style="color:green;">✔ OK</span> (Driver: ' . $db_driver . ')';
            } catch (Exception $e) {
                $db_status = '<span style="color:red;">❌ FAILED</span>: ' . $e->getMessage();
                $errors[] = "Database connection test failed: " . $e->getMessage();
            }
        } else {

            $db_status = '<span style="color:red;">❌ FAILED</span> (Connection failed during boot)';
            $errors[] = "Database connection failed during boot. Check 'fullstuck.json' or server logs.";
        }

        $content = "<h2>Configuration Status</h2>";

        $content .= $dev_warning_html; 

        $content .= "<p><strong>Environment:</strong> " . htmlspecialchars($current_env) . "</p>";
        $content .= "<p><strong>Routing Mode:</strong> " . htmlspecialchars($fst_config['routing']['mode']) . "</p>";
        $content .= "<p><strong>Database Status:</strong> {$db_status}</p>";

        $ext_checks = [
            ['name' => 'mbstring', 'level' => 'recommended', 'note' => 'Digunakan untuk penghitungan panjang string multibyte (validasi). Tanpa ini, framework fallback ke strlen().'],
            ['name' => 'fileinfo', 'level' => 'recommended', 'note' => 'Meningkatkan deteksi MIME type saat upload file.'],
            ['name' => 'json', 'level' => 'required', 'note' => 'Diperlukan untuk parsing fullstuck.json dan fst_json().'],
            ['name' => 'pdo', 'level' => 'required', 'note' => 'Diperlukan untuk koneksi database.'],
            ['name' => 'session', 'level' => 'required', 'note' => 'Diperlukan untuk session, flash message, dan CSRF.'],
        ];
        $ext_html = "<h2>PHP Extension Check</h2><table><thead><tr><th>Extension</th><th>Status</th><th>Level</th><th>Keterangan</th></tr></thead><tbody>";
        foreach ($ext_checks as $ext) {
            $loaded = extension_loaded($ext['name']);
            $status_icon = $loaded ? '<span style="color:green;">✔ Loaded</span>' : '<span style="color:orange;">✗ Not Loaded</span>';
            $level_label = $ext['level'] === 'required' ? '<b>Required</b>' : 'Recommended';
            if (!$loaded && $ext['level'] === 'recommended') {
                $warnings[] = "Extension <code>{$ext['name']}</code> tidak aktif. {$ext['note']}";
            } elseif (!$loaded && $ext['level'] === 'required') {
                $errors[] = "Extension <code>{$ext['name']}</code> (REQUIRED) tidak aktif! {$ext['note']}";
            }
            $ext_html .= "<tr><td><code>{$ext['name']}</code></td><td>{$status_icon}</td><td>{$level_label}</td><td>{$ext['note']}</td></tr>";
        }
        $ext_html .= "</tbody></table>";

        if (!empty($errors)) {
            $content .= "<h2><span style='color:red;'>Errors Found!</span></h2><ul>";
            foreach($errors as $err) { $content .= "<li>{$err}</li>"; }
            $content .= "</ul>";
        }
        if (!empty($warnings)) {
            $content .= "<h2><span style='color:orange;'>Warnings</span></h2><ul>";
            foreach($warnings as $warn) { $content .= "<li>{$warn}</li>"; }
            $content .= "</ul>";
        }

        $content .= $ext_html;

        fst_admin_render_page('System Monitor', $content);
    }

    function fst_admin_show_config() {
        fst_admin_check_auth();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        $csrf = fst_csrf_field();
        
        $config_content = file_get_contents(FST_CONFIG_FILE);
        
        $content = <<<HTML
<p>Edit the raw JSON configuration below. Be careful with syntax!</p>
<form action="{$admin_base}/config/save" method="POST">
    {$csrf}
    <textarea name="config_content" spellcheck="false">{$config_content}</textarea>
    <br><br>
    <button type="submit">Save Configuration</button>
</form>
HTML;
        fst_admin_render_page('Configuration Editor', $content);
    }

    function fst_admin_save_config() {
        fst_admin_check_auth();
        fst_csrf_check();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';

        $new_content = $_POST['config_content'] ?? '';

        $decoded = json_decode($new_content);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            fst_flash_set('error_message', 'Invalid JSON syntax! Changes not saved. Error: ' . json_last_error_msg());
        } else {
            if (file_put_contents(FST_CONFIG_FILE, $new_content) !== false) {
                 fst_flash_set('success_message', 'Configuration saved successfully!');
            } else {
                 fst_flash_set('error_message', 'Failed to write configuration file! Check permissions.');
            }
        }
        fst_redirect($admin_base . '/config');
    }
    
     function fst_admin_show_routes() {
        fst_admin_check_auth();
        global $fst_routes, $fst_config, $fst_route_prefix;
        
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $base_path = $fst_config['routing']['base_path'] ?? '/';
        $base_url = rtrim($scheme . "://" . $host . $base_path, '/');
        
        $content = "<p>List of registered routes (from static files or admin routes).</p>";
        $content .= "<table><thead><tr><th>Method</th><th>Original Path</th><th>Pattern (Regex)</th><th>Example URL (GET only)</th></tr></thead><tbody>";
        
        if (empty($fst_routes)) {
             $content .= "<tr><td colspan='4'>No routes registered yet.</td></tr>";
        } else {
            foreach ($fst_routes as $route) {
                 list($method, $pattern, $callback, $original_path) = array_pad($route, 4, null);
                 
                 if ($original_path === null) {
                      $original_path = preg_replace(['/#\^|\\\$#/', '/\(\[\^\/]\+\)/', '/\(\[0-9]\+\)/', '/\(\[a-zA-Z0-9\\-]+)/'], ['', '{param}', '{id}', '{slug}'], str_replace('\/', '/', $pattern));
                 }

                 $link = '-';
                 if ($method === 'GET' || $method === 'ANY') {
                      $test_url_path = preg_replace('/\{[^}]+\??\}/', 'test', $original_path);
                      $test_url = $base_url . $test_url_path;
                      $link = "<a href='{$test_url}' target='_blank' title='Click to test (opens in new tab)'>" . htmlspecialchars($original_path) . "</a>";
                 } else {
                      $link = htmlspecialchars($original_path);
                 }

                 $content .= "<tr><td>{$method}</td><td><code>" . htmlspecialchars($original_path) . "</code></td><td><code>" . htmlspecialchars($pattern) . "</code></td><td>{$link}</td></tr>";
            }
        }
        $content .= "</tbody></table>";
        
        fst_admin_render_page('Registered Routes', $content);
    }
     
     function fst_get_server_info() { return [ 'PHP Version' => PHP_VERSION, 'System' => php_uname(), 'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A', 'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A', 'FullStuck Root' => FST_ROOT_DIR, 'SAPI' => php_sapi_name(), 'PDO Loaded' => extension_loaded('pdo') ? 'Yes' : 'No', 'PDO MySQL' => extension_loaded('pdo_mysql') ? 'Yes' : 'No', 'PDO SQLite' => extension_loaded('pdo_sqlite') ? 'Yes' : 'No', 'mbstring' => extension_loaded('mbstring') ? 'Yes' : 'No (fallback to strlen)', 'json' => extension_loaded('json') ? 'Yes' : 'No', 'session' => extension_loaded('session') ? 'Yes' : 'No', 'fileinfo' => extension_loaded('fileinfo') ? 'Yes' : 'No (upload mime detection limited)', ]; }
     
     function fst_admin_show_server_info() {
         fst_admin_check_auth();
         $server_info = fst_get_server_info();
         
         $content = "<table><thead><tr><th>Parameter</th><th>Value</th></tr></thead><tbody>";
         foreach ($server_info as $key => $value) {
             $content .= "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
         }
         $content .= "</tbody></table>";
         
         $content .= "<h2>PHP Info (Raw)</h2>";
         $content .= "<details><summary>Click to expand/collapse</summary><div style='width:100%; height: 400px; overflow:auto; border:1px solid #ccc;'>";
         ob_start();
         phpinfo();
         $phpinfo = ob_get_clean();
         if (preg_match('/<body.*(.*)<\/body>/is', $phpinfo, $matches)) {
             $content .= $matches[1];
         } else {
             $content .= "Could not parse phpinfo().";
         }
         $content .= "</div></details>";
         
         fst_admin_render_page('Server Information', $content);
     }

    function fst_admin_show_scan_page() {
        fst_admin_check_auth();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        $csrf = fst_csrf_field();
        
        $scan_results_html = '';
        $scan_data = fst_flash_get('scan_results');

        if ($scan_data !== null) {
            $file_count = count($scan_data);
            $scan_results_html .= "<h2>Scan Results ({$file_count} PHP files analyzed):</h2>";
            if (empty($scan_data)) {
                 $scan_results_html .= "<p>No PHP files found or scanned.</p>";
            } else {
                 $scan_results_html .= "<table border='1' style='width:100%; border-collapse: collapse;'><thead><tr><th>File Path</th><th>Function Groups & Functions Found</th></tr></thead><tbody>";
                 ksort($scan_data);
                 
                 foreach ($scan_data as $file => $groups) {
                     $scan_results_html .= "<tr><td><code>" . htmlspecialchars($file) . "</code></td><td>";
                     if(empty($groups)){
                         $scan_results_html .= "<i>(No fst_ usage found)</i>";
                     } else {
                         $group_details = [];
                         foreach($groups as $group_name => $functions) {
                             $group_details[] = "<strong>" . htmlspecialchars($group_name) . ":</strong> " . implode(', ', array_map('htmlspecialchars', $functions));
                         }
                         $scan_results_html .= implode('<br>', $group_details);
                     }
                     $scan_results_html .= "</td></tr>";
                 }
                 $scan_results_html .= "</tbody></table>";
            }
        } else {
             $scan_results_html .= "<p>Click 'Start Scan' to analyze project files.</p>";
        }

        $content = <<<HTML
<p>Click the button below to scan your project directory (<code>{$_SERVER['DOCUMENT_ROOT']}</code>) for usage of <code>fst_</code> functions in <code>.php</code> files.</p>
<p><strong>Warning:</strong> This might take a while on large projects. Folders like <code>vendor</code> and <code>node_modules</code> are automatically skipped.</p>

<form action="{$admin_base}/scan/run" method="POST">
    {$csrf}
    <button type="submit">Start Scan</button>
</form>

{$scan_results_html}
HTML;
        fst_admin_render_page('Scan Project for fst_ Usage', $content);
    }

    function fst_admin_run_scan() {
        fst_admin_check_auth();
        fst_csrf_check();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';

        $function_groups = [
            'Core' => ['fst_abort', 'fst_run', 'fst_is_dev', 'fst_config', 'fst_extract_html_tag'],
            'Database' => ['fst_db', 'fst_db_select', 'fst_db_insert', 'fst_db_update', 'fst_db_delete'],
            'Views' => [
                'fst_view',
                'fst_partial',
                'fst_serve_static_file',
                'fst_serve_dynamic_file',
                'fst_show_directory_listing'
            ],
            'Request' => ['fst_uri', 'fst_method', 'fst_input', 'fst_request', 'fst_file', 'fst_is_spa', 'fst_spa_target'],
            'Routing' => ['fst_route', 'fst_get', 'fst_post', 'fst_put', 'fst_patch', 'fst_delete', 'fst_any', 'fst_group'],
            'Response' => ['fst_json', 'fst_text', 'fst_redirect', 'fst_status_code'],
            'Session' => ['fst_session_set', 'fst_session_get', 'fst_session_forget', 'fst_flash_set', 'fst_flash_has', 'fst_flash_get'],
            'Security' => ['fst_csrf_token', 'fst_csrf_field', 'fst_csrf_check', 'fst_escape', 'e'],
            'Upload' => ['fst_upload'],
            'Validation' => ['fst_validate'],
            'Debug' => ['fst_dump', 'fst_dd'],
            'Installation' => ['fst_handle_installation', 'fst_render_status_row', 'fst_show_install_success', 'fst_show_install_form'],
            'Admin' => [
                'fst_admin_check_auth', 'fst_admin_show_login', 'fst_admin_do_login',
                'fst_admin_do_logout', 'fst_admin_render_page', 'fst_admin_show_monitor',
                'fst_admin_show_config', 'fst_admin_save_config', 'fst_admin_show_routes',
                'fst_get_server_info', 'fst_admin_show_server_info', 'fst_admin_show_scan_page',
                'fst_admin_run_scan', 'fst_check_integrity', 'fst_admin_show_integrity', 'fst_admin_show_plugins',
                'fst_admin_install_plugin', 'fst_admin_toggle_plugin', 'fst_admin_uninstall_plugin'
            ]
        ];

        $results = [];
        $php_files = [];

        $scan_dir = function ($dir) use (&$scan_dir, &$php_files) {
            $items = @scandir($dir);
            if ($items === false) return;

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                    if ($item === 'vendor' || $item === 'node_modules' || $item === '.git') continue;
                    $scan_dir($path);
                } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                    $php_files[] = $path;
                }
            }
        };

        $scan_dir(FST_ROOT_DIR);

        foreach ($php_files as $file_path) {
            $content = @file_get_contents($file_path);
            if ($content === false) continue;

            $found_functions = [];
            if (preg_match_all('/\b(fst_\w+)\s*\(/', $content, $matches)) {
                $found_functions = array_unique($matches[1]);
                sort($found_functions);
            }
            
            $relative_path = str_replace(FST_ROOT_DIR . '/', '', $file_path);
            $results[$relative_path] = [];

            foreach($found_functions as $func_name) {
                $group_found = false;
                foreach ($function_groups as $group_name => $group_funcs) {
                    if (in_array($func_name, $group_funcs)) {
                        $results[$relative_path][$group_name][] = $func_name;
                        $group_found = true;
                        break;
                    }
                }
                if (!$group_found) {
                    $results[$relative_path]['Unknown'][] = $func_name;
                }
            }
             if (isset($results[$relative_path])) {
                 ksort($results[$relative_path]);
             }
        }
        
        fst_flash_set('scan_results', $results);
        fst_redirect($admin_base . '/scan');
    }

    function fst_check_integrity() {
        $file_path = FST_ROOT_DIR . '/fullstuck.php';
        if (!file_exists($file_path)) return false;
        
        $content = file_get_contents($file_path);
        if (!preg_match('/FST_HASH:\s*([a-f0-9]{64})/', $content, $matches)) return false;
        
        $declared_hash = $matches[1];
        $parts = explode(" */\n", $content, 2);
        if (count($parts) !== 2) return false;
        
        $actual_hash = hash('sha256', $parts[1]);
        return [
            'valid' => hash_equals($declared_hash, $actual_hash),
            'declared' => $declared_hash,
            'actual' => $actual_hash
        ];
    }

    function fst_admin_show_integrity() {
        fst_admin_check_auth();
        $integrity = fst_check_integrity();
        
        $remote_url = "https://raw.githubusercontent.com/milio48/fullstuck/main/version.json";
        $remote_info = "<i>Not checked</i>";

        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $remote_json = @file_get_contents($remote_url, false, $ctx);
        if ($remote_json) {
            $remote_data = json_decode($remote_json, true);
            if ($remote_data && isset($remote_data['hash'])) {
                if ($integrity && $integrity['declared'] === $remote_data['hash']) {
                    $remote_info = "<span style='color:green;'>✔ Match with official GitHub registry (v{$remote_data['version']})</span>";
                } else {
                    $remote_info = "<span style='color:red;'>❌ Mismatch with official GitHub registry!</span>";
                }
            }
        } else {
            $remote_info = "<span style='color:orange;'>Failed to connect to GitHub</span>";
        }
        
        $html = "<h2>File Integrity Monitoring (FIM)</h2>";
        if (!$integrity) {
            $html .= "<div class='alert-warning'>Cannot perform integrity check. <code>fullstuck.php</code> not found or malformed header.</div>";
        } else {
            if ($integrity['valid']) {
                $html .= "<div style='color:green; font-size:1.2em; margin-bottom:10px;'>✔ Local Integrity OK: The core file has not been tampered with.</div>";
            } else {
                $html .= "<div style='color:red; font-size:1.2em; font-weight:bold; margin-bottom:10px;'>❌ Local Integrity FAILED: The core file has been modified!</div>";
            }
            $html .= "<ul>";
            $html .= "<li><strong>Declared Hash (Line 1):</strong> <code>{$integrity['declared']}</code></li>";
            $html .= "<li><strong>Actual Content Hash:</strong> <code>{$integrity['actual']}</code></li>";
            $html .= "<li><strong>Remote Verification:</strong> {$remote_info}</li>";
            $html .= "</ul>";
        }
        
        fst_admin_render_page('Integrity Check', $html);
    }

    function fst_admin_show_plugins() {
        fst_admin_check_auth();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';
        $csrf = fst_csrf_field();

        $plugin_dir = FST_ROOT_DIR . '/fst-plugins';
        $local_plugins = [];
        if (is_dir($plugin_dir)) {
            $files = scandir($plugin_dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $is_disabled = str_ends_with($file, '.disabled');
                if (str_ends_with($file, '.php') || $is_disabled) {
                    $local_plugins[] = [
                        'filename' => $file,
                        'name' => str_replace(['.php', '.disabled'], '', $file),
                        'active' => !$is_disabled
                    ];
                }
            }
        }

        $html = "<h2>Installed Plugins</h2>";
        if (empty($local_plugins)) {
            $html .= "<p>No plugins installed yet.</p>";
        } else {
            $html .= "<table><thead><tr><th>Plugin Name</th><th>Status</th><th>Actions</th></tr></thead><tbody>";
            foreach ($local_plugins as $p) {
                $status = $p['active'] ? '<span style="color:green;">✔ Active</span>' : '<span style="color:gray;">○ Inactive</span>';
                $toggle_text = $p['active'] ? 'Disable' : 'Enable';
                $toggle_style = $p['active'] ? 'background:#6c757d;' : 'background:#28a745;';
                
                $html .= "<tr>";
                $html .= "<td><strong>" . htmlspecialchars($p['name']) . "</strong><br><small style='color:#666;'>" . htmlspecialchars($p['filename']) . "</small></td>";
                $html .= "<td>{$status}</td>";
                $html .= "<td>
                    <form action='{$admin_base}/plugins/toggle' method='POST' style='display:inline;'>
                        {$csrf}
                        <input type='hidden' name='filename' value='" . htmlspecialchars($p['filename']) . "'>
                        <button type='submit' style='{$toggle_style}'>{$toggle_text}</button>
                    </form>
                    <form action='{$admin_base}/plugins/uninstall' method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to uninstall this plugin?\")'>
                        {$csrf}
                        <input type='hidden' name='filename' value='" . htmlspecialchars($p['filename']) . "'>
                        <button type='submit' style='background:#dc3545;'>Uninstall</button>
                    </form>
                </td>";
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        }

        $remote_store_url = "https://raw.githubusercontent.com/milio48/fullstuck/main/store.json";
        $local_store_file = FST_ROOT_DIR . '/store.json';
        $store_plugins = [];
        $is_remote = false;

        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $remote_json = @file_get_contents($remote_store_url, false, $ctx);
        if ($remote_json) {
            $store_plugins = json_decode($remote_json, true) ?: [];
            $is_remote = true;
        } elseif (file_exists($local_store_file)) {
            $store_plugins = json_decode(file_get_contents($local_store_file), true) ?: [];
        }
        
        $source_label = $is_remote ? "<span style='color:green;'>GitHub Store</span>" : "<span style='color:orange;'>Local Registry</span>";

        $html .= "<br><hr><h2>Plugin Store <small style='font-size:14px; font-weight:normal;'>({$source_label})</small></h2>";
        
        if (empty($store_plugins)) {
            $html .= "<p>No plugins found in store.</p>";
        } else {
            $html .= "<table><thead><tr><th>Plugin Name</th><th>Description</th><th>Action</th></tr></thead><tbody>";
            foreach ($store_plugins as $plugin) {
                $id = $plugin['id'] ?? '';
                $is_installed = false;
                foreach ($local_plugins as $lp) {
                    if ($lp['name'] === $id) { $is_installed = true; break; }
                }
                
                $btn_text = $is_installed ? 'Re-install' : 'Install';
                $btn_style = $is_installed ? 'background:#6c757d;' : 'background:#28a745;';
                
                $html .= "<tr>";
                $html .= "<td><strong>" . htmlspecialchars($plugin['name'] ?? 'Unknown') . "</strong><br><small style='color:#666;'>ID: " . htmlspecialchars($id) . "</small></td>";
                $html .= "<td>" . htmlspecialchars($plugin['description'] ?? '') . "</td>";
                $html .= "<td>
                    <form action='{$admin_base}/plugins/install' method='POST' style='display:inline;'>
                        {$csrf}
                        <input type='hidden' name='id' value='" . htmlspecialchars($id) . "'>
                        <input type='hidden' name='url' value='" . htmlspecialchars($plugin['url'] ?? '') . "'>
                        <button type='submit' style='{$btn_style}'>{$btn_text}</button>
                    </form>
                </td>";
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        }
        
        fst_admin_render_page('Plugin Manager', $html);
    }

    function fst_admin_toggle_plugin() {
        fst_admin_check_auth();
        fst_csrf_check();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';

        $filename = basename($_POST['filename'] ?? '');
        $plugin_dir = FST_ROOT_DIR . '/fst-plugins';
        $path = $plugin_dir . '/' . $filename;

        if (!empty($filename) && file_exists($path)) {
            if (str_ends_with($filename, '.disabled')) {
                $new_path = str_replace('.disabled', '', $path);
                rename($path, $new_path);
                fst_flash_set('success_message', 'Plugin enabled.');
            } else {
                $new_path = $path . '.disabled';
                rename($path, $new_path);
                fst_flash_set('success_message', 'Plugin disabled.');
            }
        }
        fst_redirect($admin_base . '/plugins');
    }

    function fst_admin_uninstall_plugin() {
        fst_admin_check_auth();
        fst_csrf_check();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';

        $filename = basename($_POST['filename'] ?? '');
        $plugin_dir = FST_ROOT_DIR . '/fst-plugins';
        $path = $plugin_dir . '/' . $filename;

        if (!empty($filename) && file_exists($path)) {
            unlink($path);
            fst_flash_set('success_message', 'Plugin uninstalled successfully.');
        }
        fst_redirect($admin_base . '/plugins');
    }

    function fst_admin_install_plugin() {
        fst_admin_check_auth();
        fst_csrf_check();
        global $fst_config;
        $admin_base = $fst_config['admin']['page_url'] ?? '/stuck';

        $id = $_POST['id'] ?? '';
        $url = $_POST['url'] ?? '';

        if (empty($id) || empty($url)) {
            fst_flash_set('error_message', 'Invalid plugin data.');
            fst_redirect($admin_base . '/plugins');
        }

        $plugin_dir = FST_ROOT_DIR . '/fst-plugins';
        if (!is_dir($plugin_dir)) {
            if (!mkdir($plugin_dir, 0755, true)) {
                fst_flash_set('error_message', 'Failed to create fst-plugins directory.');
                fst_redirect($admin_base . '/plugins');
            }
        }

        $ctx = stream_context_create(['http' => ['timeout' => 10]]);
        $content = @file_get_contents($url, false, $ctx);

        if ($content === false) {
            fst_flash_set('error_message', 'Failed to download plugin from: ' . htmlspecialchars($url));
        } else {
            $filename = $plugin_dir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $id) . '.php';
            if (file_put_contents($filename, $content) !== false) {
                fst_flash_set('success_message', 'Plugin <strong>' . htmlspecialchars($id) . '</strong> installed successfully!');
            } else {
                fst_flash_set('error_message', 'Failed to save plugin file. Check permissions.');
            }
        }

        fst_redirect($admin_base . '/plugins');
    }

}

// FILE: bootstrap.php
if (isset($fst_config['routing']['mode']) && $fst_config['routing']['mode'] === 'static') {
    $routes_files = (array) ($fst_config['routing']['static_config']['routes_file'] ?? []);
    foreach ($routes_files as $file) {
        if (file_exists(FST_ROOT_DIR . '/' . $file)) {
            require FST_ROOT_DIR . '/' . $file;
        } elseif (!fst_is_dev()) {
            fst_abort(500, "Configuration Error: Routes file not found at '{$file}'");
        }
    }
}

$plugin_dir = FST_ROOT_DIR . '/fst-plugins';
if (is_dir($plugin_dir)) {
    foreach (glob($plugin_dir . '/*.php') as $plugin) {
        require_once $plugin;
    }
}

fst_run();
