<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body { font-family: system-ui; max-width: 600px; margin: 40px auto; padding: 20px; line-height: 1.8; background-color: #fafafa; color: #333; }
        .article-content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0;}
        a { color: #3498db; text-decoration: none; display: inline-block; margin-bottom: 20px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <a href="/">&larr; Kembali ke Beranda</a>
    
    <div class="article-content">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        <small style="color: #7f8c8d;">Ditulis pada: <?= $post['created_at'] ?></small>
        <hr>
        <p>
            <!-- Simulasi render body HTML -->
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </p>
    </div>
</body>
</html>
