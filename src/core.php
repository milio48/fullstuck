<?php
session_start();
define('FST_ROOT_DIR', __DIR__);
define('FST_CONFIG_FILE', FST_ROOT_DIR . '/fullstuck.json');

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
        // Mode Production: Log pesan dan sembunyikan detail
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
    global $fst_config;
    return ($fst_config['environment'] ?? 'production') === 'development';
}
?>
