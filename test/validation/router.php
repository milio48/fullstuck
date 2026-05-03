<?php
// Test fst_validate UI

fst_get('/', function() {
    $html = '
    <h2>Test Validation</h2>
    <form action="/submit" method="POST">
        Nama (req, min:3): <input type="text" name="nama"><br>
        Email (req, email): <input type="text" name="email"><br>
        Umur (numeric): <input type="text" name="umur"><br>
        Role (in:admin,user): <input type="text" name="role"><br>
        <button type="submit">Submit</button>
    </form>
    ';
    fst_text($html);
});

fst_post('/submit', function() {
    $data = fst_request(); // Merge $_GET & $_POST
    $rules = [
        'nama' => 'required|min:3',
        'email' => 'required|email',
        'umur' => 'numeric',
        'role' => 'in:admin,user'
    ];
    
    $result = fst_validate($data, $rules);
    
    if (!$result['valid']) {
        fst_dump("Validasi Gagal!", $result['errors']);
    } else {
        fst_dump("Validasi Sukses!", $result['data']);
    }
});
