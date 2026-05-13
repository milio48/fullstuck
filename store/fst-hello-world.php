<?php
/**
 * Plugin Name: Hello FullStuck
 * Description: Plugin perkenalan untuk mendemonstrasikan sistem Auto-Discovery dan Admin Interface.
 * Version: 1.1.0
 */

// Mendaftarkan route baru melalui plugin (Frontend)
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

// Mendaftarkan antarmuka admin (Backend)
fst_register_plugin('hello-world', [
    'name' => 'Hello FullStuck',
    'menu_label' => 'Hello World',
    'admin_route' => function() {
        $method = fst_method();
        $action = fst_input('action', 'index');
        
        // Contoh Penanganan Form (Aman)
        if ($method === 'POST') {
            fst_csrf_check(); // Proteksi Mutlak CSRF
            $nama = fst_input('nama');
            fst_flash_set('success_message', 'Pengaturan tersimpan untuk: ' . fst_escape($nama));
            fst_redirect(fst_config('admin.page_url', '/stuck') . '/p/hello-world');
        }

        if ($action === 'index') {
            echo "<h2>Pengaturan Hello FullStuck</h2>";
            echo "<p>Ini adalah halaman pengaturan khusus untuk plugin Hello World.</p>";
            echo "<p>Coba kunjungi frontend route yang didaftarkan plugin ini: <a href='/hello-world' target='_blank'>/hello-world</a></p>";
            
            echo '<form method="POST" action="' . fst_config('admin.page_url', '/stuck') . '/p/hello-world" style="background:#f9f9f9; padding:20px; border:1px solid #ddd; max-width:400px; margin-top:20px;">
                    ' . fst_csrf_field() . '
                    <label style="display:block; margin-bottom:10px; font-weight:bold;">Nama Anda:</label>
                    <input type="text" name="nama" placeholder="Ketik nama Anda di sini..." style="width:100%; padding:10px; margin-bottom:15px; box-sizing:border-box; border:1px solid #ccc; border-radius:4px;">
                    <button type="submit" style="background:#007bff; color:white; border:none; padding:10px 15px; border-radius:4px; cursor:pointer;">Simpan Pengaturan</button>
                  </form>';
        }
    }
]);
