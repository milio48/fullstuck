<?php
if (session_status() === PHP_SESSION_NONE) { 
    $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    session_set_cookie_params([
        'lifetime' => 0, 
        'path' => '/',
        'domain' => '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start(); 
}
define('FST_VERSION', '0.1.0');
define('FST_DOCS_URL', 'https://raw.githubusercontent.com/milio48/fullstuck/refs/heads/main/docs/v' . FST_VERSION . '.md');
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

function fst_app($key = null, $value = null) {
    static $state = [
        'config' => null,
        'pdo' => null,
        'routes' => [],
        'route_prefix' => '',
        'group_middleware' => [],
        'route_found' => false,
    ];

    if ($key === null) return $state;
    if ($value !== null) $state[$key] = $value;
    return $state[$key] ?? null;
}

function fst_is_safe_to_debug() {
    if (!fst_is_dev()) return false; // [PATCH] Mencegah leak stack-trace di production behind proxy
    $is_localhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']);
    $is_admin_logged_in = !empty($_SESSION['fst_admin_logged_in']);
    return $is_localhost || $is_admin_logged_in;
}

$config_content = @file_get_contents(FST_CONFIG_FILE);
$decoded_config = $config_content ? json_decode($config_content, true) : null;
if (fst_app('config') === null) fst_app('config', $decoded_config);
if (fst_app('routes') === null) fst_app('routes', []);
if (fst_app('route_prefix') === null) fst_app('route_prefix', '');
if (fst_app('route_found') === null) fst_app('route_found', false);

if ($decoded_config === null && file_exists(FST_CONFIG_FILE)) {
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
    while (ob_get_level() > 0) { ob_end_clean(); } // [PATCH] Bersihkan buffer HTML parsial
    http_response_code(500);
    
    if (!fst_is_dev() || !fst_is_safe_to_debug()) {
        // Mode Production atau akses tidak aman: Log pesan dan sembunyikan detail
        error_log($e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        if (function_exists('fst_abort')) { fst_abort(500, "Internal Server Error."); } 
        else { die("Internal Server Error."); }
    }
    
    // Mode Development: Tampilkan UI Cantik
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
    $fst_config = fst_app('config');
    return ($fst_config['environment'] ?? 'production') === 'development';
}

function fst_config($key = null, $default = null) {
    $fst_config = fst_app('config');
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

function fst_extract_html_fragment($html, $selector = 'body') {
    if (empty(trim($html))) return '';
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Mini-parser selector ke XPath
    $xpath_query = '//' . $selector; 
    // [PATCH] Mencegah XPath Injection dengan Strict Whitelist
    if (str_starts_with($selector, '#')) {
        $id = substr($selector, 1);
        $xpath_query = "//*[@id='{$id}']";
    } elseif (str_starts_with($selector, '.')) {
        $class = substr($selector, 1);
        $xpath_query = "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
    } else {
        $allowed_tags = ['body', 'main', 'header', 'footer', 'div', 'section', 'article', 'nav', 'aside', 'span', 'p', 'form', 'table'];
        if (in_array(strtolower($selector), $allowed_tags)) {
            $xpath_query = '//' . strtolower($selector);
        } else {
            return $html; // Fallback aman
        }
    }

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query($xpath_query);
    if ($nodes && $nodes->length > 0) {
        $inner_html = '';
        foreach ($nodes->item(0)->childNodes as $child) {
            $inner_html .= $dom->saveHTML($child);
        }
        return $inner_html;
    }
    return $html;
}


?>
