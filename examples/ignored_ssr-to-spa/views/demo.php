<h1>🧪 Demo — Edge Case Testing</h1>

<p>Halaman ini menguji berbagai skenario edge case untuk memastikan stabilitas fitur SSR-to-SPA.</p>

<div class="card">
    <h3>Test 1: Render Mode Detection</h3>
    <p>Mode saat ini: <span class="badge <?= fst_is_fragment() ? 'badge-spa' : 'badge-ssr' ?>"><?= fst_is_fragment() ? 'SPA FRAGMENT' : 'FULL SSR' ?></span></p>
    <p>Header <code>X-FST-Request</code>: <code><?= $_SERVER['HTTP_X_FST_REQUEST'] ?? '(tidak ada)' ?></code></p>
    <p class="timestamp">Waktu render server: <?= date('H:i:s.u') ?></p>
</div>

<div class="card">
    <h3>Test 2: Link Types</h3>
    <p>Masing-masing link di bawah harus berperilaku berbeda:</p>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 0.5rem;">
            <a href="/" style="color: var(--accent);">🔗 Internal Link (SPA)</a>
            — Harus di-intercept oleh fst.js
        </li>
        <li style="margin-bottom: 0.5rem;">
            <a href="https://google.com" style="color: var(--accent);">🌐 External Link</a>
            — Harus diabaikan (navigasi biasa)
        </li>
        <li style="margin-bottom: 0.5rem;">
            <a href="/stuck" target="_blank" style="color: var(--accent);">📎 Target Blank</a>
            — Harus diabaikan (buka tab baru)
        </li>
        <li style="margin-bottom: 0.5rem;">
            <a href="#section-test" style="color: var(--accent);">⚓ Anchor Link</a>
            — Harus diabaikan (scroll saja)
        </li>
        <li style="margin-bottom: 0.5rem;">
            <a href="/" data-fst-bypass style="color: var(--warning);">⏭️ Bypass Link (data-fst-bypass)</a>
            — Harus diabaikan (opt-out)
        </li>
    </ul>
</div>

<!-- Partial Update Section -->
<div class="card">
    <h3>Test 3: Partial Update (Nested SPA)</h3>
    <p>Update hanya sebagian div tanpa mengganti seluruh halaman menggunakan <code>data-fst-target</code>.</p>
    
    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
        <a href="/demo?type=github" data-fst-target="#partial-box" class="btn">Update Tab GitHub</a>
        <a href="/demo?type=youtube" data-fst-target="#partial-box" class="btn">Update Tab YouTube</a>
    </div>

    <div id="partial-box" style="border: 2px dashed var(--accent); padding: 20px; border-radius: 8px; background: rgba(52, 152, 219, 0.05); min-height: 100px;">
        <?php 
            $type = $_GET['type'] ?? 'default';
            if ($type === 'github'): 
        ?>
            <h4>🐙 GitHub Content</h4>
            <p>Ini adalah konten yang di-load hanya ke dalam box ini saja.</p>
            <script>console.log('GitHub tab script executed!');</script>
        <?php elseif ($type === 'youtube'): ?>
            <h4>📺 YouTube Content</h4>
            <p>Konten video atau playlist bisa muncul di sini secara asinkron.</p>
            <script>console.log('YouTube tab script executed!');</script>
        <?php else: ?>
            <p style="color: #7f8c8d;">Klik tombol di atas untuk mengetes Partial Update.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3>Test 4: Dynamic PHP Content</h3>
    <p>Data dinamis yang berubah setiap request (verifikasi bahwa cache tidak menampilkan data stale):</p>
    <pre><?php
    echo "Random Number : " . rand(1000, 9999) . "\n";
    echo "PHP Version   : " . PHP_VERSION . "\n";
    echo "Memory Usage  : " . round(memory_get_usage() / 1024, 1) . " KB\n";
    echo "Request URI   : " . fst_uri() . "\n";
    echo "Fragment Mode : " . (fst_is_fragment() ? 'true' : 'false') . "\n";
    echo "Server Time   : " . date('Y-m-d H:i:s');
    ?></pre>
    <p style="font-size: 0.8rem; color: var(--text-muted);">⚠️ <em>Catatan: Setelah navigasi pertama, data di atas akan di-cache. Klik <button onclick="FST.clearCache('/demo'); window.location.reload();" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Clear Cache & Reload</button> untuk reset.</em></p>
</div>

<div class="card" id="section-test">
    <h3>Test 5: Anchor Target</h3>
    <p>Jika Anda mengklik ⚓ Anchor Link di atas, browser harus scroll ke sini tanpa SPA navigation.</p>
</div>

<div class="card">
    <h3>Test 6: Console Logs</h3>
    <p>Buka DevTools Console dan periksa log <code>[fst.js]</code> untuk memverifikasi:</p>
    <ul style="color: var(--text-muted); padding-left: 1.5rem;">
        <li>Init message saat page load</li>
        <li>Cache hit/miss saat navigasi</li>
        <li>Script execution di halaman Contact</li>
    </ul>
</div>

<script>
    console.log('[demo.php] Demo page script executed at', new Date().toISOString());
</script>
