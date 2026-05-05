<h1>About — Arsitektur SSR-to-SPA</h1>

<div class="card">
    <h3>🧠 Konsep Utama</h3>
    <p>Framework <code>FullStuck.php</code> mendeteksi header <code>X-FST-Request: true</code> untuk membedakan antara request normal (full page) dan request SPA (fragment only).</p>
</div>

<div class="card">
    <h3>⚡ Bagaimana Ini Bekerja?</h3>
    <ol style="color: var(--text-muted); padding-left: 1.5rem;">
        <li>User pertama kali mengakses → Server render <strong>halaman penuh</strong> (HTML + Layout + CSS + JS)</li>
        <li>User klik link → <code>fst.js</code> intercept, kirim <code>fetch()</code> dengan header khusus</li>
        <li>Server terima header → Bypass layout, kirim <strong>fragment HTML</strong> saja</li>
        <li><code>fst.js</code> swap DOM target, update URL via History API</li>
        <li>Navigasi Back/Forward → Ambil dari cache in-memory (zero-latency)</li>
    </ol>
</div>

<div class="card">
    <h3>📊 Perbandingan</h3>
    <table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">
        <tr style="border-bottom: 1px solid var(--border);">
            <th style="text-align: left; padding: 0.5rem; color: var(--text-muted);">Aspek</th>
            <th style="text-align: left; padding: 0.5rem; color: var(--text-muted);">Tradisional SSR</th>
            <th style="text-align: left; padding: 0.5rem; color: var(--text-muted);">FullStuck SPA</th>
        </tr>
        <tr style="border-bottom: 1px solid var(--border);">
            <td style="padding: 0.5rem;">Navigasi</td>
            <td style="padding: 0.5rem; color: var(--danger);">Full reload</td>
            <td style="padding: 0.5rem; color: var(--success);">Fragment swap</td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border);">
            <td style="padding: 0.5rem;">Payload</td>
            <td style="padding: 0.5rem; color: var(--danger);">Seluruh HTML</td>
            <td style="padding: 0.5rem; color: var(--success);">Konten saja</td>
        </tr>
        <tr style="border-bottom: 1px solid var(--border);">
            <td style="padding: 0.5rem;">JS Framework</td>
            <td style="padding: 0.5rem;">Tidak perlu</td>
            <td style="padding: 0.5rem; color: var(--success);">Tidak perlu (fst.js ~3KB)</td>
        </tr>
        <tr>
            <td style="padding: 0.5rem;">SEO</td>
            <td style="padding: 0.5rem; color: var(--success);">✅ Native</td>
            <td style="padding: 0.5rem; color: var(--success);">✅ Native (SSR first-load)</td>
        </tr>
    </table>
</div>

<div class="highlight-box">
    <p>💡 <strong>Zero-Config Promise:</strong> Developer hanya perlu menulis PHP prosedural. Framework yang menangani semuanya.</p>
</div>

<p class="timestamp">Rendered at: <?= date('Y-m-d H:i:s') ?> | Mode: <?= fst_is_fragment() ? 'SPA Fragment' : 'Full SSR' ?></p>
