<?php

function auth_admin() {
    if (!isset($_GET['token']) || $_GET['token'] !== 'secret') {
        fst_text("Akses Ditolak: Token salah", 403);
        return false; // Hentikan eksekusi callback rute
    }
}

function auth_user() {
    if (!isset($_GET['user'])) {
        fst_text("Akses Ditolak: Parameter user kosong", 403);
        return false;
    }
}

// Rute Publik (Tanpa Middleware)
fst_get('/', function() {
    fst_text("Halaman Publik (Sukses)");
});

// Middleware Tunggal
fst_get('/admin', function() {
    fst_text("Halaman Admin Panel (Sukses)");
}, 'auth_admin');

// Middleware Grup
fst_group('/user', function() {
    fst_get('/profil', function() {
        fst_text("Halaman Profil " . htmlspecialchars($_GET['user']) . " (Sukses)");
    });
}, 'auth_user');

// Multiple Middleware (Array)
fst_get('/super', function() {
    fst_text("Halaman Super Admin (Sukses)");
}, ['auth_user', 'auth_admin']);
