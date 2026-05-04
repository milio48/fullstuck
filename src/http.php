<?php
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
?>
