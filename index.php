<?php
require 'compiler.php';

// Data simulasi (bisa berasal dari Database)
$data = [
    'pageTitle' => 'Eksperimen DOM Templating Deklaratif (Procedural)',
    'blogs' => [
        [
            'title' => 'Vibe Coding', 
            'summary' => 'Sangat menyenangkan ketika tidak ada OOP yang rumit...',
            'url' => 'https://example.com/vibe-coding'
        ],
        [
            'title' => 'Procedural PHP', 
            'summary' => 'Lebih fungsional, bersih, ringan dan elegan.',
            'url' => 'https://example.com/procedural-php'
        ]
    ]
];

// Aturan/Ruleset penyuntikkan ke DOM (Berbasis DSL)
$rules = [
    // Teks langsung (CSS Selector -> String)
    "title" => '$pageTitle',
    
    // Looping menggunakan directive '@foreach' di dalam scope block (CSS Selector -> Array)
    "article.post-item" => [
        "@foreach" => '$blogs as $blog',
        
        // CSS Selector (Child scope) -> String
        "h2" => '$blog["title"]',
        "p"  => '$blog["summary"]',
        
        "a.read-more" => [
            // Attribute scope (dibungkus `[...]`)
            "[href]" => '$blog["url"]',
            "[title]" => '$blog["title"]'
        ]
    ]
];

// Eksekusi fungsi satu pintu
render_template(__DIR__ . '/blog-list.html', $data, $rules);
