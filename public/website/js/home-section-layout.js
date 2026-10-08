(() => {
    const sections = [...document.querySelectorAll('[data-home-lazy-section]')];
    if (!sections.length) return;

    const reveal = (section) => section.classList.add('is-layout-visible');

    if (!matchMedia('(max-width: 767px)').matches || !('IntersectionObserver' in window)) {
        sections.forEach(reveal);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            reveal(entry.target);
            observer.unobserve(entry.target);
        });
    }, { rootMargin: '160px 0px', threshold: 0 });

    sections.forEach((section) => observer.observe(section));
})();
