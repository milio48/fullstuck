<?php

// Middleware Cek Login
function require_login() {
    if (!fst_session_get('logged_in')) {
        fst_flash_set('error', 'Anda harus login terlebih dahulu.');
        fst_redirect('/');
        return false;
    }
}

// Route Login Page
fst_get('/', function() {
    // Jika sudah login, lempar ke dashboard
    if (fst_session_get('logged_in')) fst_redirect('/drive');
    
    fst_view('views/login.php', [
        'error' => fst_flash_get('error'),
        'success' => fst_flash_get('success')
    ]);
});

// Proses Auth
fst_post('/auth', function() {
    fst_csrf_check(); // Keamanan ganda dengan CSRF Token
    
    $password = fst_input('password');
    if ($password === 'rahasia') { // Hardcode password untuk demo
        fst_session_set('logged_in', true);
        fst_redirect('/drive');
    } else {
        fst_flash_set('error', 'Password salah! (Hint: ketik "rahasia")');
        fst_redirect('/');
    }
});

// Area Private/Aman dengan Grup dan Middleware
fst_group('/drive', function() {
    
    fst_get('/', function() {
        // Ambil list file di folder uploads
        $files = [];
        if (is_dir('uploads')) {
            $scan = scandir('uploads');
            foreach($scan as $file) {
                if ($file !== '.' && $file !== '..') {
                    $files[] = $file;
                }
            }
        }
        
        fst_view('views/dashboard.php', [
            'files' => $files,
            'success' => fst_flash_get('success'),
            'error' => fst_flash_get('error')
        ]);
    });
    
    fst_post('/upload', function() {
        fst_csrf_check();
        
        if (!is_dir('uploads')) mkdir('uploads');
        
        // Fungsi helper bawaan fst_upload
        $result = fst_upload('dokumen', 'uploads', [
            'max_size' => 5 * 1024 * 1024, // 5MB
            'allowed_ext' => ['jpg', 'png', 'pdf', 'txt']
        ]);
        
        if ($result) {
            fst_flash_set('success', 'File berhasil diunggah! Lokasi: ' . $result);
        } else {
            fst_flash_set('error', 'Gagal unggah. Pastikan format file benar dan di bawah 5MB.');
        }
        
        fst_redirect('/drive');
    });
    
    fst_post('/logout', function() {
        fst_csrf_check();
        session_destroy();
        fst_redirect('/');
    });
    
}, 'require_login');
