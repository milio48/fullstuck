<?php
/**
 * Plugin Name: Hello FullStuck
 * Description: Plugin perkenalan untuk mendemonstrasikan sistem Auto-Discovery.
 * Version: 1.0.0
 */

// Mendaftarkan route baru melalui plugin
fst_get('/hello-world', function() {
    $style = "
        <style>
            body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f0f2f5; }
            .card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #007bff; }
            h1 { color: #333; margin-bottom: 10px; }
            p { color: #666; }
            .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; transition: 0.3s; }
            .btn:hover { background: #0056b3; }
        </style>
    ";

    echo $style;
    echo "
        <div class='card'>
            <h1>🚀 Hello World!</h1>
            <p>Plugin <strong>Hello FullStuck</strong> berhasil dimuat secara otomatis.</p>
            <p><small>Lokasi File: <code>fst-plugins/fst-hello-world.php</code></small></p>
            <a href='" . (fst_config('admin.page_url') ?: '/stuck') . "' class='btn'>Kembali ke Dashboard</a>
        </div>
    ";
});
