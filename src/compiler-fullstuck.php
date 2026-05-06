<?php
// compiler-fullstuck.php - FullStuck.php Builder

$src_dir = __DIR__;
$output_file = dirname(__DIR__) . '/fullstuck.php';

// Minify fst.js
$fst_js_path = $src_dir . '/assets/fst.js';
$fst_js_code = file_exists($fst_js_path) ? file_get_contents($fst_js_path) : '';
$fst_js_code = preg_replace('/\s+/', ' ', $fst_js_code); // Basic minification
$fst_js_code = addslashes(trim($fst_js_code));

// Urutan file sangat penting agar dependensi fungsi terpenuhi
$files = [
    'core.php',
    'database.php',
    'router.php',
    'http.php',
    'view.php',
    'utility.php',
    'install.php',
    'admin.php',
    'bootstrap.php'
];

$compiled_code = "define('FST_SPA_JS_CODE', '{$fst_js_code}');\n\n";

foreach ($files as $file) {
    $path = $src_dir . '/' . $file;
    if (!file_exists($path)) {
        die("Error: Source file {$file} missing.\n");
    }
    
    $content = file_get_contents($path);
    // Hapus tag pembuka dan penutup php
    $content = str_replace('<?php', '', $content);
    $content = str_replace('?>', '', $content);
    
    // Hapus komentar single-line dan multi-line secara hati-hati tapi pertahankan Line Break
    // Menghapus komentar block
    $content = preg_replace('!/\*.*?\*/!s', '', $content);
    // Menghapus komentar baris yang dimulai dengan // atau #
    $content = preg_replace('/^\s*(?:\/\/|#).*/m', '', $content);
    
    $compiled_code .= "\n// FILE: {$file}\n";
    $compiled_code .= trim($content) . "\n";
}

// Generate FIM Hash dari $compiled_code
$fim_hash = hash('sha256', $compiled_code);

// Bentuk Output Akhir dengan Sintaks Header
$output = "<?php\n";
$output .= "/**\n";
$output .= " * 🚀 FULLSTUCK.PHP - The Zero-Config, AI-Friendly Framework\n";
$output .= " * 🔗 Repository: https://github.com/milio48/fullstuck\n";
$output .= " * 📚 Raw Docs: https://raw.githubusercontent.com/milio48/fullstuck/refs/heads/main/docs/v0.1.0.md\n";
$output .= " * 💡 Version: 0.1.0 | FST_HASH: {$fim_hash}\n";
$output .= " */\n";
$output .= $compiled_code;

file_put_contents($output_file, $output);
echo "✅ Build complete! `fullstuck.php` has been successfully compiled from World 1 to World 2.\n";
