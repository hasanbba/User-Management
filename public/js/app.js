(() => {
    const body = document.body;
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeTargets = document.querySelectorAll('[data-sidebar-close]');

    if (!toggle) return;

    const closeSidebar = () => {
        body.classList.remove('sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isOpen = body.classList.toggle('sidebar-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    closeTargets.forEach((target) => target.addEventListener('click', closeSidebar));
    document.querySelectorAll('#appSidebar a').forEach((link) => link.addEventListener('click', closeSidebar));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });
})();
