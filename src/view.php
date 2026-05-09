<?php
function fst_view($path, $data = []) { extract($data); require FST_ROOT_DIR . '/' . $path; }
function fst_partial($path, $data = []) { fst_view($path, $data); }

function fst_serve_static_file($file_path) {
    $fst_config = fst_app('config');
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
?>
