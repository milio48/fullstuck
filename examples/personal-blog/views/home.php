<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body { font-family: system-ui; max-width: 600px; margin: 40px auto; padding: 20px; line-height: 1.6; background-color: #fafafa; color: #333; }
        .post-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1 { color: #2c3e50; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>📝 <?= htmlspecialchars($title) ?></h1>
    <p>Dibangun dengan ❤️ menggunakan <b>FullStuck.php</b></p>
    <hr>
    
    <?php if (empty($posts)): ?>
        <p>Belum ada artikel. Klik <a href="/setup">Setup Data</a> untuk mengisi artikel demo.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post-card">
                <h2><a href="/post/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                <small style="color: #7f8c8d;"><?= $post['created_at'] ?></small>
                <p><?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
