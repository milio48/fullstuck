<?php

// Route Instalasi Data (Demo)
fst_get('/setup', function() {
    fst_db('EXEC', "CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT UNIQUE, title TEXT, content TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
    
    // Hapus data lama agar bersih
    fst_db('EXEC', "DELETE FROM posts");
    
    // Insert 2 Artikel
    fst_db_insert('posts', [
        'slug' => 'hello-world',
        'title' => 'Selamat Datang di FullStuck',
        'content' => 'Ini adalah blog post pertama yang dirender menggunakan framework ini. Mudah sekali bukan?'
    ]);
    
    fst_db_insert('posts', [
        'slug' => 'keunggulan-fullstuck',
        'title' => 'Mengapa Memilih Micro-Framework?',
        'content' => 'Dalam pembuatan aplikasi, tidak selamanya kita butuh tools raksasa. Terkadang kecepatan dan kesederhanaan adalah kunci. FullStuck menjawab kebutuhan itu.'
    ]);
    
    fst_redirect('/');
});

// Halaman Beranda Blog
fst_get('/', function() {
    $posts = fst_db_select('posts', [], ['order_by' => 'created_at DESC']);
    
    fst_view('views/home.php', [
        'title' => 'Beranda Blog Saya',
        'posts' => $posts
    ]);
});

// Halaman Detail Artikel
fst_get('/post/{slug:s}', function($slug) {
    // Ambil artikel berdasarkan slug
    $post = fst_db_select('posts', ['slug' => $slug], ['mode' => 'ROW']);
    
    // Jika tidak ada, tampilkan error 404 cantik
    if (!$post) {
        fst_abort(404, "Artikel tidak ditemukan.");
    }
    
    fst_view('views/post.php', [
        'title' => $post['title'],
        'post'  => $post
    ]);
});
