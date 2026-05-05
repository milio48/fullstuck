<?php
// helpers.php for examples/ignored_ssr-to-spa
function fst_spa_view($view_path, $data = []) {
    ob_start();
    fst_view($view_path, $data);
    $content = ob_get_clean();
    fst_view('layouts/master.php', ['content' => $content]);
}
