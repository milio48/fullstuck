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
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

function fst_is_dev() {
    global $fst_config;
    return ($fst_config['environment'] ?? 'production') === 'development';
}
?>
