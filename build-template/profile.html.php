<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: sans-serif; background: #eef2f5; margin: 0; padding: 40px 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #4a90e2; text-decoration: none; font-weight: bold; }
        .back-link:hover { text-decoration: underline; }
        .profile-card { background: #fff; max-width: 600px; margin: 0 auto; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        .profile-header { background: linear-gradient(135deg, #4a90e2, #0056b3); color: #fff; text-align: center; padding: 40px 20px; }
        .avatar { width: 120px; height: 120px; border-radius: 50%; border: 4px solid #fff; object-fit: cover; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .user-fullname { margin: 0 0 5px 0; font-size: 24px; }
        .user-role { margin: 0; opacity: 0.8; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .profile-body { padding: 30px; }
        h3 { color: #333; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .social-links { list-style: none; padding: 0; display: flex; gap: 10px; margin-bottom: 30px; }
        .social-links li a { display: block; padding: 8px 16px; background: #f4f6f8; text-decoration: none; border-radius: 20px; color: #555; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .social-links li a:hover { background: #4a90e2; color: #fff; }
        .bio-content { line-height: 1.6; color: #555; font-size: 15px; }
        .bio-content strong { color: #333; }
        .bio-content em { color: #4a90e2; }
    </style>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto;">
        <a href="?page=dashboard" class="back-link">&larr; Kembali ke Dashboard</a>
    </div>

    <div class="profile-card">
        <div class="profile-header">
            <!-- Atribut src dan alt akan diubah -->
            <img src="<?= htmlspecialchars($user["avatar_url"] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($user["name"] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="avatar">
            <h1 class="user-fullname"><?= htmlspecialchars($user["name"] ?? '', ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="user-role"><?= htmlspecialchars($user["role"] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="profile-body">
            <h3>Social Media</h3>
            <!-- Container loop social media -->
            <ul class="social-links">
                <?php foreach ($user["socials"] as $soc): ?><li class="social-item"><a href="<?= htmlspecialchars($soc["url"] ?? '', ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?= htmlspecialchars($soc["platform"] ?? '', ENT_QUOTES, 'UTF-8') ?></a></li><?php endforeach; ?>
                
                
            </ul>

            <h3>Tentang Saya</h3>
            <!-- Container injeksi raw HTML (WYSIWYG bypass) -->
            <div class="bio-content"><?= $user["bio_html"] ?? '' ?></div>
        </div>
    </div>
</body>
</html>
