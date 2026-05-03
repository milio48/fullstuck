<?php
// Konfigurasi Header untuk merespon JSON by default
header('Content-Type: application/json');

// Middleware: Autentikasi API Key
function auth_api() {
    // Ambil API Key dari URL parameter (?api_key=XYZ) atau Header
    $api_key = fst_input('api_key') ?? ($_SERVER['HTTP_X_API_KEY'] ?? '');
    
    if (empty($api_key)) {
        fst_json(['error' => 'API Key wajib disertakan'], 401);
        return false;
    }
    
    // Cari user berdasarkan api_key
    $user = fst_db_select('users', ['api_key' => $api_key], ['mode' => 'ROW']);
    if (!$user) {
        fst_json(['error' => 'API Key tidak valid'], 403);
        return false;
    }
    
    // Simpan user ke variabel global agar bisa dipakai di endpoint lain
    global $current_user;
    $current_user = $user;
}

// Route Setup Database (Hanya untuk keperluan demo awal)
fst_get('/setup', function() {
    fst_db('EXEC', "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, api_key TEXT UNIQUE, name TEXT)");
    fst_db('EXEC', "CREATE TABLE IF NOT EXISTS tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT, status TEXT DEFAULT 'pending')");
    
    // Insert dummy user
    $apiKey = bin2hex(random_bytes(8)); // Hasilkan string 16 karakter
    fst_db_insert('users', ['api_key' => $apiKey, 'name' => 'Demo User']);
    
    fst_json([
        'message' => 'Database siap!', 
        'demo_api_key' => $apiKey,
        'info' => 'Gunakan param ?api_key='.$apiKey.' pada URL untuk tes endpoint lain.'
    ]);
});

// Grouping Route API v1 dengan Middleware auth_api
fst_group('/api/v1', function() {
    
    // 1. Dapatkan Profil User
    fst_get('/me', function() {
        global $current_user;
        fst_json(['data' => $current_user]);
    });
    
    // 2. Dapatkan Daftar Task
    fst_get('/tasks', function() {
        global $current_user;
        $status = fst_input('status'); // Optional filter
        $conditions = ['user_id' => $current_user['id']];
        if ($status) $conditions['status'] = $status;
        
        $tasks = fst_db_select('tasks', $conditions, ['order_by' => 'id DESC']);
        fst_json(['data' => $tasks]);
    });
    
    // 3. Tambah Task Baru (Validasi Input)
    fst_post('/tasks', function() {
        global $current_user;
        $req = fst_request();
        
        $val = fst_validate($req, [
            'title' => 'required|min:3'
        ]);
        
        if (!$val['valid']) {
            fst_json(['error' => 'Validasi gagal', 'details' => $val['errors']], 400);
        }
        
        fst_db_insert('tasks', [
            'user_id' => $current_user['id'],
            'title' => $val['data']['title'],
            'status' => 'pending'
        ]);
        
        fst_json(['message' => 'Task berhasil ditambahkan'], 201);
    });
    
    // 4. Update Status Task
    fst_put('/tasks/{id:i}', function($id) {
        global $current_user;
        
        // Pastikan task ini milik user tersebut (mencegah IDOR)
        $task = fst_db_select('tasks', ['id' => $id, 'user_id' => $current_user['id']], ['mode' => 'ROW']);
        if (!$task) fst_json(['error' => 'Task tidak ditemukan atau bukan milik Anda'], 404);
        
        $req = fst_request();
        // Karena PUT mungkin payload-nya dikirim via body mentah (x-www-form-urlencoded atau JSON), 
        // pastikan ambil data mentah juga jika fst_request kosong. Tapi karena `fst_validate` butuh array:
        if (empty($req)) {
            $req = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $val = fst_validate($req, [
            'status' => 'required|in:pending,done'
        ]);
        
        if (!$val['valid']) fst_json(['error' => 'Validasi gagal', 'details' => $val['errors']], 400);
        
        fst_db_update('tasks', ['status' => $val['data']['status']], ['id' => $id]);
        fst_json(['message' => 'Task berhasil diperbarui']);
    });
    
    // 5. Hapus Task
    fst_delete('/tasks/{id:i}', function($id) {
        global $current_user;
        
        $task = fst_db_select('tasks', ['id' => $id, 'user_id' => $current_user['id']], ['mode' => 'ROW']);
        if (!$task) fst_json(['error' => 'Task tidak ditemukan atau bukan milik Anda'], 404);
        
        fst_db_delete('tasks', ['id' => $id]);
        fst_json(['message' => 'Task berhasil dihapus']);
    });

}, 'auth_api');
