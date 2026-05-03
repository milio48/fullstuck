<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Secure Drive</title>
    <style>
        body { font-family: sans-serif; background: #eceff1; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #2980b9; color: #fff; border: none; padding: 10px; width: 100%; border-radius: 4px; cursor: pointer; }
        .alert { background: #e74c3c; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;}
        .alert-success { background: #2ecc71; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Secure Drive 🔒</h2>
        
        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="/auth" method="POST">
            <!-- Wajib cetak CSRF Token -->
            <?= fst_csrf_field() ?>
            <input type="password" name="password" placeholder="Masukkan Password" required>
            <button type="submit">Masuk</button>
        </form>
        <p style="font-size: 12px; color: #7f8c8d; margin-top: 15px;">*Hint password: <b>rahasia</b></p>
    </div>
</body>
</html>
