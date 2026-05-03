<?php
// compiler-fullstuck.php - FullStuck.php Builder

$src_dir = __DIR__;
$output_file = dirname(__DIR__) . '/fullstuck.php';

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

$output = "<?php\n// fullstuck.php v0.2.6 (Compiled from /src)\n";

foreach ($files as $file) {
    $path = $src_dir . '/' . $file;
    if (!file_exists($path)) {
        die("Error: Source file {$file} missing.\n");
    }
    
    $content = file_get_contents($path);
    // Hapus tag pembuka dan penutup php
    $content = str_replace('<?php', '', $content);
    $content = str_replace('?>', '', $content);
    
    $output .= "\n// ==========================================\n";
    $output .= "// FILE: {$file}\n";
    $output .= "// ==========================================\n";
    $output .= trim($content) . "\n";
}

file_put_contents($output_file, $output);
echo "✅ Build complete! `fullstuck.php` has been successfully compiled from World 1 to World 2.\n";
