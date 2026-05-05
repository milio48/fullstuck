<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSR-to-SPA Demo — FullStuck.php</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <nav class="main-nav">
        <div class="nav-brand">🚀 FullStuck SPA</div>
        <div class="nav-links">
            <a href="/">Home</a>
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <a href="/demo">Demo</a>
            <a href="/stuck" target="_blank">Admin ↗</a>
        </div>
    </nav>

    <div class="fst-progress-bar" id="fst-progress"></div>

    <!-- ↓ INI ADALAH TARGET DOM UNTUK SPA SWAPPING ↓ -->
    <main id="app-content">
        <?php echo $content; ?>
    </main>

    <footer class="main-footer">
        <p>FullStuck.php SSR-to-SPA Experiment &bull; Zero Config SPA Activated</p>
        <p class="footer-meta">
            Rendered: <?= date('H:i:s') ?> |
            Mode: <span id="render-mode"><?= fst_is_spa() ? 'SPA Fragment' : 'Full SSR' ?></span>
        </p>
    </footer>
</body>
</html>
