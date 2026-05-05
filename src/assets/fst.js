document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    if (!link || !link.href) return;
    if (link.target === '_blank' || link.hasAttribute('download')) return;
    if (link.hostname !== window.location.hostname) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey) return;
    
    e.preventDefault();
    fstNavigate(link.href);
});

window.addEventListener('popstate', function(e) {
    fstNavigate(window.location.href, false);
});

async function fstNavigate(url, pushState = true) {
    try {
        const reqHeader = document.querySelector('script#fst-spa-agent')?.getAttribute('data-req-header') || 'X-FST-Request';
        const targetHeader = document.querySelector('script#fst-spa-agent')?.getAttribute('data-target-header') || 'X-FST-Target';
        
        const headers = {};
        headers[reqHeader] = 'true';
        headers[targetHeader] = 'body'; // Default target
        
        const response = await fetch(url, {
            headers: headers
        });
        
        if (!response.ok) {
            window.location.href = url; // fallback
            return;
        }
        
        const html = await response.text();
        document.body.innerHTML = html;
        
        if (pushState) {
            window.history.pushState({}, '', url);
        }
        
        // Dispatch fst:load event for plugins/scripts to re-initialize
        document.dispatchEvent(new Event('fst:load'));
        
        // Re-execute scripts inside body
        const scripts = document.body.querySelectorAll('script');
        scripts.forEach(oldScript => {
            if (oldScript.id === 'fst-spa-agent') return;
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        
    } catch (err) {
        window.location.href = url; // fallback
    }
}

// Initial load event
document.dispatchEvent(new Event('fst:load'));
