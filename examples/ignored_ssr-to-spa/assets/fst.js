/**
 * fst.js — FullStuck Frontend Agent
 * 
 * Agen frontend super ringan yang mengubah SSR app menjadi SPA-like experience.
 * Fitur:
 * - Intercept semua link navigasi internal
 * - Fetch fragment HTML via X-FST-Request header
 * - DOM Swapping ke #app-content
 * - In-Memory Cache untuk navigasi zero-latency
 * - History API (Back/Forward browser tetap bekerja)
 * - Re-execute <script> tags dalam fragment
 * - Progress bar visual saat loading
 * - Fallback ke hard-reload jika error
 */
const FST = {
    config: {
        targetSelector: '#app-content',
        headerName: 'X-FST-Request',
        progressSelector: '#fst-progress',
        renderModeSelector: '#render-mode',
        transitionDuration: 200 // ms, harus sinkron dengan CSS
    },

    cache: {},
    isNavigating: false,

    init() {
        // Delegasi event untuk semua link
        document.addEventListener('click', this.handleLinkClick.bind(this));
        window.addEventListener('popstate', this.handlePopState.bind(this));

        // Simpan halaman pertama ke cache
        const target = document.querySelector(this.config.targetSelector);
        if (target) {
            this.cache[window.location.href] = target.innerHTML;
        }

        console.log('[fst.js] Agent initialized. Target:', this.config.targetSelector);
    },

    /**
     * Intercept klik pada <a> tags.
     * Abaikan: link eksternal, target=_blank, anchor #, download, mailto.
     */
    async handleLinkClick(e) {
        const link = e.target.closest('a');
        if (!link || !link.href) return;

        // Skip: external, target=_blank, download, mailto/tel, anchor-only, bypass
        if (link.origin !== window.location.origin ||
            link.getAttribute('target') === '_blank' ||
            link.hasAttribute('download') ||
            link.href.startsWith('mailto:') || link.href.startsWith('tel:') ||
            link.getAttribute('href')?.startsWith('#') ||
            link.hasAttribute('data-fst-bypass')) return;

        if (this.isNavigating) return;

        const url = new URL(link.href);
        const currentUrl = new URL(window.location.href);

        if (url.origin === currentUrl.origin &&
            url.pathname === currentUrl.pathname &&
            url.search === currentUrl.search) {
            return;
        }

        e.preventDefault();
        const destination = link.href;
        const targetSelector = link.getAttribute('data-fst-target') || this.config.targetSelector;

        // Simpan state sebelum pindah (hanya jika targetnya adalah main content)
        if (targetSelector === this.config.targetSelector) {
            const currentTarget = document.querySelector(this.config.targetSelector);
            if (currentTarget) this.cache[window.location.href] = currentTarget.innerHTML;
        }

        await this.navigate(destination, targetSelector);
        history.pushState({ fst: true, target: targetSelector }, '', destination);
    },

    /**
     * Handle browser Back/Forward navigation.
     */
    async handlePopState(e) {
        const targetSelector = (e.state && e.state.target) ? e.state.target : this.config.targetSelector;
        await this.navigate(window.location.href, targetSelector);
    },

    /**
     * Core navigation: Fetch fragment dan swap DOM.
     */
    async navigate(url, targetSelector = null) {
        if (this.isNavigating) return;
        this.isNavigating = true;

        targetSelector = targetSelector || this.config.targetSelector;

        try {
            this.showProgress();

            const targetEl = document.querySelector(targetSelector);
            if (!targetEl) throw new Error(`Target element ${targetSelector} not found`);

            // Visual feedback: Fade out
            targetEl.style.opacity = '0.5';
            targetEl.style.transition = `opacity ${this.config.transitionDuration}ms`;

            // 1. Cek Cache (Hanya untuk main content)
            if (targetSelector === this.config.targetSelector && this.cache[url]) {
                this.swapDOM(this.cache[url], url, targetSelector);
                this.hideProgress();
                this.updateRenderMode('SPA (Cached)');
                this.isNavigating = false;
                return;
            }

            // 2. Fetch HTML Fragment
            const response = await fetch(url, {
                headers: {
                    [this.config.headerName]: 'true',
                    'X-FST-Target': targetSelector
                }
            });

            if (!response.ok) throw new Error(`Server returned ${response.status}`);

            let htmlFragment = await response.text();

            // Jika targetnya bukan default (#app-content), kita mungkin perlu 
            // mengekstrak elemen tersebut dari hasil fetch jika server mengirim halaman utuh.
            if (targetSelector !== this.config.targetSelector) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlFragment, 'text/html');
                const partial = doc.querySelector(targetSelector);
                if (partial) {
                    htmlFragment = partial.innerHTML;
                }
            }

            if (targetSelector === this.config.targetSelector) {
                this.cache[url] = htmlFragment;
            }

            // 3. Swap DOM
            this.swapDOM(htmlFragment, url, targetSelector);
            this.hideProgress();
            this.updateRenderMode(targetSelector === this.config.targetSelector ? 'SPA Fragment' : `SPA Partial (${targetSelector})`);
            this.isNavigating = false;

        } catch (error) {
            console.error('[fst.js] Navigation error:', error);
            this.hideProgress();
            this.isNavigating = false;
            window.location.href = url;
        }
    },

    /**
     * Replace konten target dengan fragment HTML baru.
     */
    swapDOM(htmlString, url = null, targetSelector = null) {
        const selector = targetSelector || this.config.targetSelector;
        const targetEl = document.querySelector(selector);
        if (!targetEl) return;

        targetEl.innerHTML = htmlString;
        targetEl.style.opacity = '1';

        // Animasi masuk
        targetEl.classList.remove('fst-exit');
        targetEl.classList.add('fst-enter');
        setTimeout(() => targetEl.classList.remove('fst-enter'), this.config.transitionDuration);

        // Re-execute scripts
        this.executeScripts(targetEl);

        // Handle scroll behavior (Hanya jika ganti halaman utama)
        if (selector === this.config.targetSelector) {
            let hash = '';
            if (url) {
                try { hash = new URL(url).hash; } catch (e) { hash = url.split('#')[1] ? '#' + url.split('#')[1] : ''; }
            } else {
                hash = window.location.hash;
            }

            if (hash) {
                const el = document.querySelector(hash);
                if (el) { el.scrollIntoView({ behavior: 'smooth' }); return; }
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    },

    /**
     * Re-execute <script> tags yang ter-inject via innerHTML.
     * innerHTML tidak mengeksekusi script secara default.
     */
    executeScripts(element) {
        const scripts = element.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            // Copy semua atribut
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            // Copy inline content
            if (oldScript.innerHTML) {
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            }
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    },

    // ─── UI Helpers ──────────────────────────────

    showProgress() {
        const bar = document.querySelector(this.config.progressSelector);
        if (bar) {
            bar.classList.add('active');
        }
        document.body.classList.add('fst-loading');
    },

    hideProgress() {
        const bar = document.querySelector(this.config.progressSelector);
        if (bar) {
            bar.classList.remove('active');
            bar.classList.add('done');
            setTimeout(() => bar.classList.remove('done'), 300);
        }
        document.body.classList.remove('fst-loading');
    },

    updateRenderMode(mode) {
        const el = document.querySelector(this.config.renderModeSelector);
        if (el) el.textContent = mode;
    },

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    /**
     * Public API: Clear cache.
     * Berguna untuk form submission atau data mutation.
     */
    clearCache(url = null) {
        if (url) {
            delete this.cache[url];
        } else {
            this.cache = {};
        }
        console.log('[fst.js] Cache cleared:', url || 'all');
    }
};

// Auto-init saat DOM ready
document.addEventListener('DOMContentLoaded', () => FST.init());
