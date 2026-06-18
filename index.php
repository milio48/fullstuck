<?php
require 'compiler.php';

$compiler = new DomCompiler(__DIR__ . '/blog-list.html');

$compiler
    ->setText("//title", '$pageTitle')
    ->setLoop("//div[@id='blog-container']", ".//article[contains(@class, 'post-item')]", '$blogs', '$blog', function($item) {
        $item->setText(".//h2", '$blog["title"]');
        $item->setText(".//p", '$blog["summary"]');
    })
    ->render([
        'pageTitle' => 'Eksperimen DOM Templating',
        'blogs' => [
            ['title' => 'Vibe Coding', 'summary' => 'Menyenangkan...'],
            ['title' => 'Single File', 'summary' => 'Cepat dan ringan...']
        ]
    ]);
