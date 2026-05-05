<h1>Contact Us</h1>

<p>Halaman ini menguji apakah <code>&lt;script&gt;</code> tags di dalam fragment bisa dieksekusi ulang setelah DOM swap.</p>

<div class="card">
    <h3>📝 Test Form (No Backend)</h3>
    <form id="contact-form" onsubmit="return false;">
        <label for="name">Nama</label>
        <input type="text" id="name" placeholder="Ketik nama Anda...">
        
        <label for="email">Email</label>
        <input type="email" id="email" placeholder="user@example.com">
        
        <label for="message">Pesan</label>
        <textarea id="message" rows="4" placeholder="Tulis sesuatu..." style="resize: vertical;"></textarea>
        
        <button type="button" onclick="handleSubmit()">Kirim Pesan</button>
    </form>
    <div id="form-result" style="margin-top: 1rem;"></div>
</div>

<div class="card">
    <h3>🔍 Script Execution Test</h3>
    <p>Jika teks di bawah ini muncul, berarti <code>executeScripts()</code> di fst.js bekerja:</p>
    <div id="script-test" style="padding: 0.75rem; background: rgba(52, 211, 153, 0.1); border: 1px solid rgba(52, 211, 153, 0.3); border-radius: 8px; margin-top: 0.5rem;">
        ⏳ Menunggu eksekusi script...
    </div>
</div>

<!-- Script yang harus dieksekusi ulang saat fragment swap -->
<script>
    console.log('[contact.php] Inline script executed!');
    
    const testEl = document.getElementById('script-test');
    if (testEl) {
        testEl.innerHTML = '✅ <strong>Script berhasil dieksekusi!</strong> Timestamp: ' + new Date().toLocaleTimeString();
        testEl.style.background = 'rgba(52, 211, 153, 0.15)';
    }

    function handleSubmit() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const msg = document.getElementById('message').value;
        const result = document.getElementById('form-result');

        if (!name || !email) {
            result.innerHTML = '<span style="color: var(--danger);">❌ Nama dan email wajib diisi!</span>';
            return;
        }

        result.innerHTML = '<span class="badge badge-success">✅ Pesan dari ' + name + ' (' + email + ') berhasil ditangkap oleh JS!</span>';
        
        // Clear cache untuk halaman ini karena ada "perubahan data"
        if (typeof FST !== 'undefined') {
            FST.clearCache(window.location.href);
        }
    }
</script>

<p class="timestamp" style="margin-top: 1.5rem;">Rendered at: <?= date('Y-m-d H:i:s') ?> | Mode: <?= fst_is_fragment() ? 'SPA Fragment' : 'Full SSR' ?></p>
