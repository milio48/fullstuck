<?php
function fst_handle_installation() {
    $error_message = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $driver = $_POST['driver'] ?? 'sqlite';
            $server_type = $_POST['server_type'] ?? 'apache_litespeed';
            
            if ($driver !== 'none') {
                if ($driver === 'mysql') { $dsn = "mysql:host={$_POST['db_host']};dbname={$_POST['db_name']};charset=utf8mb4"; new PDO($dsn, $_POST['db_user'], $_POST['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]); }
                elseif ($driver === 'pgsql') { $port = $_POST['db_port'] ?: '5432'; $dsn = "pgsql:host={$_POST['db_host']};port={$port};dbname={$_POST['db_name']}"; new PDO($dsn, $_POST['db_user'], $_POST['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]); }
                else { $path = FST_ROOT_DIR . '/' . $_POST['db_path']; $dir = dirname($path); if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new Exception("Failed to create folder '{$dir}'. Check permissions."); new PDO("sqlite:" . $path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); }
            }
            
            $config_data = [
                "environment" => "development", "admin" => ["page_url" => $_POST['admin_url'] ?? '/stuck',"password" => password_hash($_POST['admin_pass'], PASSWORD_DEFAULT)],
                "database" => [
                    "driver" => $driver,
                    "mysql" => ["host" => $_POST['db_host'] ?? 'localhost',"dbname" => $_POST['db_name'] ?? '',"username" => $_POST['db_user'] ?? 'root',"password" => $_POST['db_pass'] ?? ''],
                    "sqlite" => ["database_path" => $_POST['db_path'] ?? 'database.sqlite'],
                    "pgsql" => ["host" => $_POST['db_host'] ?? 'localhost', "port" => $_POST['db_port'] ?? '5432', "dbname" => $_POST['db_name'] ?? '', "username" => $_POST['db_user'] ?? 'postgres', "password" => $_POST['db_pass'] ?? '']
                ],
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

            // Download Documentation for AI if requested
            if (isset($_POST['download_docs']) && $_POST['download_docs'] === '1') {
                $docs_content = @file_get_contents(FST_DOCS_URL);
                if ($docs_content) {
                    $docs_filename = 'fullstuck_v' . FST_VERSION . '.md';
                    @file_put_contents(FST_ROOT_DIR . '/' . $docs_filename, $docs_content);
                }
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

function fst_show_install_form($error_message) { $checks = ['php_version' => version_compare(PHP_VERSION, '8.0.0', '>='),'dir_writable' => is_writable(FST_ROOT_DIR),'pdo_loaded' => extension_loaded('pdo'),'pdo_mysql' => extension_loaded('pdo_mysql'),'pdo_sqlite' => extension_loaded('pdo_sqlite'),'pdo_pgsql' => extension_loaded('pdo_pgsql'),'server_soft' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown']; $detected_server = 'other'; if (stripos($checks['server_soft'], 'Apache') !== false || stripos($checks['server_soft'], 'Litespeed') !== false) $detected_server = 'apache_litespeed'; elseif (stripos($checks['server_soft'], 'Development Server') !== false) $detected_server = 'php_s'; elseif (stripos($checks['server_soft'], 'nginx') !== false) $detected_server = 'nginx'; $status_rows = ''; $status_rows .= fst_render_status_row('PHP Version (>= 8.0)', $checks['php_version'], 'Your version: ' . PHP_VERSION); $status_rows .= fst_render_status_row('Directory Writable', $checks['dir_writable'], FST_ROOT_DIR); $status_rows .= fst_render_status_row('PDO Extension', $checks['pdo_loaded'], 'Required for database'); $status_rows .= fst_render_status_row('PDO MySQL Driver', $checks['pdo_mysql'], '', !$checks['pdo_sqlite'] && !$checks['pdo_pgsql']); $status_rows .= fst_render_status_row('PDO SQLite Driver', $checks['pdo_sqlite'], '', !$checks['pdo_mysql'] && !$checks['pdo_pgsql']); $status_rows .= fst_render_status_row('PDO PostgreSQL Driver', $checks['pdo_pgsql'], '', !$checks['pdo_mysql'] && !$checks['pdo_sqlite']); $status_rows .= fst_render_status_row('Web Server Info', true, $checks['server_soft'], true); $error_html = $error_message ? "<div class='error'>" . htmlspecialchars($error_message) . "</div>" : ''; $opt_apache = ($detected_server === 'apache_litespeed') ? 'selected' : ''; $opt_nginx = ($detected_server === 'nginx') ? 'selected' : ''; $opt_php_s = ($detected_server === 'php_s') ? 'selected' : ''; $opt_other = ($detected_server === 'other') ? 'selected' : ''; $opt_sqlite = 'selected'; $opt_mysql = ''; $opt_pgsql = ''; if (!$checks['pdo_sqlite']) { if ($checks['pdo_mysql']) $opt_mysql = 'selected'; elseif ($checks['pdo_pgsql']) $opt_pgsql = 'selected'; $opt_sqlite = ''; } $root_dir_safe = htmlspecialchars(FST_ROOT_DIR);
$html = <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>FullStuck.php Installation</title>
<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; line-height: 1.6; } h1, h2 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; } table { width: 100%; border-collapse: collapse; margin-bottom: 20px; } th, td { text-align: left; padding: 8px; border-bottom: 1px solid #f0f0f0; } tr:nth-child(even) { background-color: #f9f9f9; } .form-group { margin-bottom: 15px; } label { display: block; font-weight: bold; margin-bottom: 5px; } input[type="text"], input[type="password"], select { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; } button { background: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; } button:hover { background: #0056b3; } .error { background: #ffe0e0; border: 1px solid #ffb0b0; color: #d00; padding: 15px; border-radius: 4px; margin-bottom: 20px; } .note { font-size: 0.9em; color: #555; } code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }</style>
</head><body><h1>🚀 Welcome to FullStuck.php</h1><p>The <code>fullstuck.json</code> configuration file was not found. Please complete the installation steps below to get started.</p>{$error_html}<h2>🛠️ Server Compatibility Check</h2><table><thead><tr><th>Requirement</th><th>Status</th><th>Notes</th></tr></thead><tbody>{$status_rows}</tbody></table><h2>⚙️ Configuration</h2><form method="POST" id="install-form"><div class="form-group"><label>Web Server Type</label><select name="server_type"><option value="apache_litespeed" {$opt_apache}>Apache / Litespeed (.htaccess will be created automatically)</option><option value="nginx" {$opt_nginx}>Nginx (Instructions will be shown later)</option><option value="php_s" {$opt_php_s}>PHP -S (No .htaccess needed)</option><option value="other" {$opt_other}>Other (Manual configuration)</option></select></div><div class="form-group"><label>Database Driver</label><select name="driver" id="driver-select"><option value="sqlite" {$opt_sqlite}>SQLite</option><option value="mysql" {$opt_mysql}>MySQL</option><option value="pgsql" {$opt_pgsql}>PostgreSQL</option><option value="none">No Database (Setup Later)</option></select></div><div id="mysql-fields"><div class="form-group"><label for="db_host">Database Host</label><input type="text" name="db_host" id="db_host" value="localhost"></div><div id="port-field" class="form-group"><label for="db_port">Database Port</label><input type="text" name="db_port" id="db_port" placeholder="e.g. 3306 or 5432"></div><div class="form-group"><label for="db_name">Database Name</label><input type="text" name="db_name" id="db_name" value="fullstuck_db"></div><div class="form-group"><label for="db_user">Database Username</label><input type="text" name="db_user" id="db_user" value="root"></div><div class="form-group"><label for="db_pass">Database Password</label><input type="password" name="db_pass" id="db_pass"></div></div><div id="sqlite-fields"><div class="form-group"><label for="db_path">SQLite File Path</label><input type="text" name="db_path" id="db_path" value="database.sqlite"><p class="note">Default: <code>database.sqlite</code>. Path is relative to <code>{$root_dir_safe}</code>. The folder will be created if it doesn't exist.</p></div></div><div class="form-group"><label>Routing Mode</label><select name="routing_mode"><option value="static" selected>Static (Whitelist Mode / routes.php) - Recommended</option><option value="dynamic">Dynamic (File System Mode / Apache-like)</option></select><p class="note">Static is more secure and structured. Dynamic is faster for initial setup.</p></div><div class="form-group"><label for="admin_url">Admin Dashboard URL</label><input type="text" name="admin_url" id="admin_url" value="/stuck" required><p class="note">The secret URL to access the admin panel in development mode.</p></div><div class="form-group"><label for="admin_pass">Admin Dashboard Password</label><input type="password" name="admin_pass" id="admin_pass" required><p class="note">Will be hashed. Used for the admin API in development mode.</p></div><div class="form-group"><label style="display:flex; align-items:center; cursor:pointer;"><input type="checkbox" name="download_docs" value="1" style="width:auto; margin-right:10px;" checked> Download documentation for AI (<code>fullstuck_v<?= FST_VERSION ?>.md</code>)</label><p class="note">Helps AI agents (like ChatGPT/Claude) understand the framework context better.</p></div><button type="submit">Install FullStuck.php</button></form>
<script>
    const driverSelect = document.getElementById('driver-select');
    const mysqlFields = document.getElementById('mysql-fields');
    const sqliteFields = document.getElementById('sqlite-fields');
    const portField = document.getElementById('port-field');
    const dbUser = document.getElementById('db_user');

    function toggleFields() {
        if (driverSelect.value === 'mysql' || driverSelect.value === 'pgsql') {
            mysqlFields.style.display = 'block';
            sqliteFields.style.display = 'none';
            portField.style.display = (driverSelect.value === 'pgsql') ? 'block' : 'none';
            if (driverSelect.value === 'pgsql' && dbUser.value === 'root') dbUser.value = 'postgres';
            if (driverSelect.value === 'mysql' && dbUser.value === 'postgres') dbUser.value = 'root';
        } else if (driverSelect.value === 'sqlite') {
            mysqlFields.style.display = 'none';
            sqliteFields.style.display = 'block';
        } else {
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
?>
