<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <div id="blog-container">
        <?php foreach ($blogs as $blog): ?><article class="post-item">
            <h2><?= htmlspecialchars($blog["title"] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
            <p><?= htmlspecialchars($blog["summary"] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <div class="content"><?= $blog["wysiwyg_content"] ?? '' ?></div>
            <a href="<?= htmlspecialchars($blog["url"] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="read-more" title="<?= htmlspecialchars($blog["title"] ?? '', ENT_QUOTES, 'UTF-8') ?>">Baca selengkapnya</a>
        </article><?php endforeach; ?>
        
    </div>
</body>
</html>
