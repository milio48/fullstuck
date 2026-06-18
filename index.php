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

// Aturan/Ruleset penyuntikkan ke DOM (Berbasis CSS Selector & Deklaratif murni)
$rules = [
    'texts' => [
        "title" => '$pageTitle'
    ],
    'loops' => [
        "#blog-container" => [
            'item'  => "article.post-item",
            'array' => '$blogs',
            'alias' => '$blog',
            
            // Teks dinamis pada masing-masing item
            'texts' => [
                "h2" => '$blog["title"]',
                "p"  => '$blog["summary"]'
            ],
            
            // Atribut dinamis
            'attributes' => [
                "a.read-more" => [
                    "href" => '$blog["url"]',
                    "title" => '$blog["title"]'
                ]
            ]
        ]
    ]
];

// Eksekusi fungsi satu pintu
render_template(__DIR__ . '/blog-list.html', $data, $rules);
