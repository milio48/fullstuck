<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Secure Drive</title>
    <style>
        body { font-family: sans-serif; background: #eceff1; padding: 30px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .alert { background: #e74c3c; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;}
        .alert-success { background: #2ecc71; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;}
        .file-list { border-top: 1px solid #eee; margin-top: 20px; padding-top: 20px; }
        .file-list ul { list-style: none; padding: 0; }
        .file-list li { background: #f9f9f9; margin-bottom: 5px; padding: 10px; display: flex; justify-content: space-between; border-radius: 4px;}
        .btn-upload { background: #27ae60; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
        .btn-logout { background: #e74c3c; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; float: right; }
    </style>
</head>
<body>
    <div class="container">
        <form action="/drive/logout" method="POST" style="display:inline;">
            <?= fst_csrf_field() ?>
            <button type="submit" class="btn-logout">Logout</button>
        </form>
        
        <h2>Dashboard Anda 📁</h2>
        
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div style="background: #f1f4f6; padding: 15px; border-radius: 6px;">
            <h4>Upload File Baru</h4>
            <form action="/drive/upload" method="POST" enctype="multipart/form-data">
                <?= fst_csrf_field() ?>
                <input type="file" name="dokumen" required>
                <button type="submit" class="btn-upload">Unggah Sekarang</button>
            </form>
        </div>

        <div class="file-list">
            <h3>Daftar File (<?= count($files) ?>)</h3>
            <?php if (empty($files)): ?>
                <p style="color: #7f8c8d;">Belum ada file di direktori uploads/.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <!-- karena uploads didaftarkan di public_folders, kita bisa akses URL langsung -->
                        <li>
                            <span>📄 <?= htmlspecialchars($file) ?></span>
                            <a href="/uploads/<?= urlencode($file) ?>" target="_blank">Buka</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
