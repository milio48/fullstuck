<?php
// helpers.php for examples/ignored_ssr-to-spa

function fst_is_fragment(): bool {
    return isset($_SERVER['HTTP_X_FST_REQUEST']) && $_SERVER['HTTP_X_FST_REQUEST'] === 'true';
}

function fst_spa_view($view_path, $data = []) {
    ob_start();
    fst_view($view_path, $data);
    $content = ob_get_clean();
    fst_view('layouts/master.php', ['content' => $content]);
}
