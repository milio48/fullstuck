<?php
// Test Error Handling UI

fst_get('/', function() {
    fst_text("Halaman Utama. Silakan akses /error atau /fatal");
});

fst_get('/error', function() {
    // Memanggil fungsi yang tidak ada untuk memicu Throwable/Error
    fungsi_yang_tidak_pernah_ada();
});

fst_get('/fatal', function() {
    // Memaksa throw exception
    throw new Exception("Ini adalah simulasi Exception dengan UI yang cantik!", 400);
});
