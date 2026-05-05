<?php
// Test Query Builder

fst_get('/setup', function() {
    fst_db('EXEC', "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT)");
    fst_text("Tabel users berhasil dibuat.");
});

fst_get('/insert', function() {
    $res = fst_db_insert('users', [
        'name' => 'Budi ' . rand(1, 100),
        'email' => 'budi' . rand(1, 100) . '@test.com'
    ]);
    fst_dump("Insert Sukses", $res);
});

fst_get('/select', function() {
    $users = fst_db_select('users', [], ['order_by' => 'id DESC']);
    fst_dump("Daftar Users", $users);
});

fst_get('/select/{id:i}', function($id) {
    $user = fst_db_select('users', ['id' => $id], ['mode' => 'ROW']);
    fst_dump("User ID $id", $user);
});

fst_get('/update/{id:i}', function($id) {
    $res = fst_db_update('users', ['name' => 'Updated Budi'], ['id' => $id]);
    fst_dump("Update Sukses", $res);
});

fst_get('/delete/{id:i}', function($id) {
    $res = fst_db_delete('users', ['id' => $id]);
    fst_dump("Delete Sukses", $res);
});
