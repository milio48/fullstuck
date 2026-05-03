<?php
function fst_dump(...$vars) {
    global $fst_config;
    if (!fst_is_dev()) {
        return;
    }
    echo '<pre style="background-color: #1a1a1a; color: #f0f0f0; padding: 15px; border: 1px solid #444; margin: 10px; border-radius: 5px; text-align: left; overflow-x: auto; font-family: monospace; font-size: 13px; line-height: 1.5;">';
    foreach ($vars as $var) { var_dump($var); }
    echo '</pre>';
}
function fst_dd(...$vars) { fst_dump(...$vars); die(); }
?>
