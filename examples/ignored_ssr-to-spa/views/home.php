<h1>Welcome to FullStuck SPA</h1>

<div class="highlight-box">
    <p>✨ Ini adalah demo <strong>SSR-to-SPA</strong> — navigasi antar halaman <em>tanpa full-reload</em>, tapi kode PHP tetap prosedural biasa. Zero JS framework.</p>
</div>

<p>Klik link di navigasi atas untuk melihat SPA transition. Perhatikan:</p>
<ul style="color: var(--text-muted); margin-bottom: 1.5rem; padding-left: 1.5rem;">
    <li>URL berubah (History API) ✅</li>
    <li>Back/Forward browser tetap bekerja ✅</li>
    <li>Halaman pertama dimuat penuh (SSR), navigasi berikutnya hanya fragmen (SPA) ✅</li>
    <li>Progress bar muncul saat loading ✅</li>
    <li>Transisi fade smooth ✅</li>
</ul>

<h2>Quick Links</h2>
<div class="link-grid">
    <a href="/about" class="link-card">
        <div class="icon">📖</div>
        <div class="title">About</div>
        <div class="desc">Penjelasan arsitektur</div>
    </a>
    <a href="/contact" class="link-card">
        <div class="icon">✉️</div>
        <div class="title">Contact</div>
        <div class="desc">Form dengan script test</div>
    </a>
    <a href="/demo" class="link-card">
        <div class="icon">🧪</div>
        <div class="title">Demo</div>
        <div class="desc">Test edge cases</div>
    </a>
    <a href="/stuck" class="link-card" target="_blank">
        <div class="icon">⚙️</div>
        <div class="title">Admin Panel</div>
        <div class="desc">FullStuck Dashboard</div>
    </a>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3>📡 Server Info</h3>
    <p class="timestamp">Rendered at: <?= date('Y-m-d H:i:s') ?></p>
    <p>Mode: <span class="badge badge-ssr"><?= fst_is_fragment() ? 'SPA FRAGMENT' : 'FULL SSR' ?></span></p>
    <p>URI: <code><?= fst_uri() ?></code></p>
</div>
