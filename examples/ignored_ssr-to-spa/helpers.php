<?php
/**
 * SSR-to-SPA Helper Functions (Dunia 2 - Project Level)
 * 
 * Implementasi proposal "Zero-Config SPA" dari sisi project,
 * tanpa memodifikasi fullstuck.php (Dunia 1).
 * 
 * Mekanisme:
 * 1. fst_is_fragment() — Deteksi apakah request dari fst.js via X-FST-Request header
 * 2. fst_spa_view()    — Context-aware rendering: kirim fragment (SPA) atau full layout (SSR)
 * 3. fst_layout()      — Render layout master, menginject $content dan fst.js secara otomatis
 */

/**
 * Deteksi apakah request ini berasal dari agen fst.js (SPA navigation).
 * Mengecek keberadaan header X-FST-Request: true.
 */
function fst_is_fragment(): bool {
    return isset($_SERVER['HTTP_X_FST_REQUEST']) && $_SERVER['HTTP_X_FST_REQUEST'] === 'true';
}

/**
 * Ambil ID target yang diminta oleh frontend (untuk Partial Update).
 */
function fst_get_target(): ?string {
    return $_SERVER['HTTP_X_FST_TARGET'] ?? null;
}

/**
 * Context-Aware View Renderer.
 * 
 * Jika request dari fst.js (fragment mode): kirim HTML konten saja.
 * Jika request normal (full page load): bungkus konten dalam layout master.
 * 
 * @param string $view_path  Path relatif ke file view (dari project root)
 * @param array  $data       Data yang akan di-extract ke view
 * @param string|null $layout Path ke layout file. Null = tanpa layout (mirip API)
 */
function fst_spa_view(string $view_path, array $data = [], ?string $layout = 'layouts/master.php'): void {
    // Buffer konten view
    extract($data);
    ob_start();
    require FST_ROOT_DIR . '/' . $view_path;
    $view_content = ob_get_clean();

    // Evaluasi output: Fragment (SPA) atau Full Page (SSR)?
    if (fst_is_fragment() || $layout === null) {
        // SPA Mode: Kirim fragmen HTML saja, tanpa layout
        echo $view_content;
    } else {
        // SSR Normal Mode: Bungkus dalam layout
        $content = $view_content;
        require FST_ROOT_DIR . '/' . $layout;
    }
}
