// URL handling fallback for extensionless URLs
(function() {
    'use strict';
    
    // If current URL has .html and is not index.html, redirect to extensionless version
    if (window.location.pathname.includes('.html') && !window.location.pathname.endsWith('index.html')) {
        const newUrl = window.location.href.replace('.html', '');
        window.history.replaceState(null, '', newUrl);
    }
    
    // Handle link clicks to ensure extensionless navigation
    document.addEventListener('DOMContentLoaded', function() {
        // Update all internal links
        const links = document.querySelectorAll('a[href*=".html"]');
        links.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.includes('.html') && !href.startsWith('http')) {
                link.setAttribute('href', href.replace('.html', ''));
            }
        });
    });
})();
