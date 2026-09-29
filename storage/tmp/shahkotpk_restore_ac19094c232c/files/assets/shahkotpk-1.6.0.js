document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;

  // Mobile menu
  const toggle = document.querySelector('[data-mobile-menu-toggle]');
  const drawer = document.querySelector('[data-mobile-drawer]');
  if (toggle && drawer) {
    const closeEls = drawer.querySelectorAll('[data-mobile-close]');
    const setOpen = (open) => {
      drawer.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      body.style.overflow = open ? 'hidden' : '';
    };
    toggle.addEventListener('click', () => setOpen(true));
    closeEls.forEach(el => el.addEventListener('click', () => setOpen(false)));
    drawer.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') setOpen(false);
    });
  }

  // Slider
  document.querySelectorAll('[data-portal-slider]').forEach(slider => {
    const slides = Array.from(slider.querySelectorAll('.portal-slide'));
    const dots = Array.from(slider.querySelectorAll('.portal-slider-dot'));
    const prev = slider.querySelector('[data-slider-prev]');
    const next = slider.querySelector('[data-slider-next]');
    const intervalMs = Math.max(2500, Math.min(12000, Number(slider.dataset.interval || 5500)));
    if (!slides.length) return;

    let index = Math.max(0, slides.findIndex(s => s.classList.contains('is-active')));
    let timer = null;
    let startX = null;

    const show = (i) => {
      index = (i + slides.length) % slides.length;
      slides.forEach((slide, idx) => slide.classList.toggle('is-active', idx === index));
      dots.forEach((dot, idx) => dot.classList.toggle('is-active', idx === index));
    };

    const stop = () => { if (timer) clearInterval(timer); timer = null; };
    const start = () => {
      stop();
      if (slides.length > 1) timer = setInterval(() => show(index + 1), intervalMs);
    };

    prev && prev.addEventListener('click', () => { show(index - 1); start(); });
    next && next.addEventListener('click', () => { show(index + 1); start(); });
    dots.forEach((dot, idx) => dot.addEventListener('click', () => { show(idx); start(); }));

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('touchstart', e => { startX = e.changedTouches[0].clientX; }, {passive:true});
    slider.addEventListener('touchend', e => {
      if (startX === null) return;
      const diff = e.changedTouches[0].clientX - startX;
      if (Math.abs(diff) > 45) show(index + (diff < 0 ? 1 : -1));
      startX = null;
      start();
    }, {passive:true});

    show(index);
    start();
  });

  // Subtle reveal animation
  if (body.dataset.animations === '1' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const targets = document.querySelectorAll('.section-head, .info-card, .quick-service-card, .business-card, .spotlight-card, .gallery-card, .content-copy, .content-image, .cta-block, .custom-card');
    targets.forEach(el => el.classList.add('reveal-ready'));

    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, {threshold: 0.10, rootMargin: '0px 0px -35px 0px'});

    targets.forEach(el => observer.observe(el));
  }
});