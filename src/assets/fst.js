document.addEventListener('click', async function(e) {
    if (e.defaultPrevented) return;
    const link = e.target.closest('a');
    if (!link || !link.href || link.hasAttribute('data-no-spa') || link.classList.contains('no-spa') || link.target === '_blank' || link.hasAttribute('download') || link.hostname !== window.location.hostname || e.ctrlKey || e.metaKey || e.shiftKey) return;
    e.preventDefault();

    /* Ambil selector target dan opsi history */
    const reqHeader = document.querySelector('script#fst-spa-agent')?.getAttribute('data-req-header') || 'X-FST-Request';
    const targetHeader = document.querySelector('script#fst-spa-agent')?.getAttribute('data-target-header') || 'X-FST-Target';
    const targetSelector = link.getAttribute('data-fst-target') || 'body';
    const isHistoryOptOut = link.getAttribute('data-fst-history') === 'false';

    /* Tambahkan class 'fst-loading' ke elemen targetSelector */
    const targetElement = document.querySelector(targetSelector);
    if (targetElement) targetElement.classList.add('fst-loading');

    try {
        const headers = { [reqHeader]: 'true', [targetHeader]: targetSelector };
        const response = await fetch(link.href, { headers });
        if (!response.ok) { window.location.href = link.href; return; }
        const html = await response.text();

        if (!targetElement) throw new Error('Target not found');

        /* Dispatch event 'fst:unload' ke document */
        document.dispatchEvent(new Event('fst:unload'));

        /* Ganti innerHTML targetElement dengan html dari response */
        targetElement.innerHTML = html;

        /* Jika isHistoryOptOut false, jalankan history.pushState menyimpan stateObj: { fstHtml: html, fstTarget: targetSelector } */
        if (!isHistoryOptOut) {
            window.history.pushState({ fstHtml: html, fstTarget: targetSelector }, '', link.href);
        }

        /* Eksekusi ulang tag <script> (skip fst-spa-agent dan data-spa-ignore) */
        const scripts = targetElement.querySelectorAll('script');
        scripts.forEach(oldScript => {
            if (oldScript.id === 'fst-spa-agent' || oldScript.hasAttribute('data-spa-ignore')) return;
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        /* Dispatch event 'fst:load' ke document */
        document.dispatchEvent(new Event('fst:load'));
    } catch (err) {
        window.location.href = link.href;
    } finally {
        /* Hapus class 'fst-loading' dari elemen targetSelector */
        if (targetElement) targetElement.classList.remove('fst-loading');
    }
});

window.addEventListener('popstate', function(e) {
    /* Cek jika e.state && e.state.fstHtml && e.state.fstTarget tersedia */
    if (e.state && e.state.fstHtml && e.state.fstTarget) {
        const targetElement = document.querySelector(e.state.fstTarget);
        if (targetElement) {
            /* 1. Dispatch fst:unload */
            document.dispatchEvent(new Event('fst:unload'));
            /* 2. Isi innerHTML dengan e.state.fstHtml */
            targetElement.innerHTML = e.state.fstHtml;
            /* 3. Eksekusi ulang script (skip fst-spa-agent dan data-spa-ignore) */
            const scripts = targetElement.querySelectorAll('script');
            scripts.forEach(oldScript => {
                if (oldScript.id === 'fst-spa-agent' || oldScript.hasAttribute('data-spa-ignore')) return;
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
            /* 4. Dispatch fst:load */
            document.dispatchEvent(new Event('fst:load'));
        } else {
            window.location.reload();
        }
    } else {
        /* Fallback jika state tidak ada */
        window.location.reload();
    }
});

/* Initial load event */
document.dispatchEvent(new Event('fst:load'));
