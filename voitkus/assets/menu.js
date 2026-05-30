(function () {
  const header = document.querySelector('[data-component="site-header"]');
  if (!header) {
    return;
  }

  const toggle = document.querySelector('.mobile-menu-toggle');
  const closeBtn = document.querySelector('.site-nav__close');
  const nav = document.querySelector('#site-nav');
  const backdrop = document.querySelector('.mobile-nav-backdrop');

  if (!toggle || !nav || !backdrop) {
    return;
  }

  const openLabel = toggle.getAttribute('data-label-open') || 'Open menu';
  const closeLabel = toggle.getAttribute('data-label-close') || 'Close menu';
  const mobileQuery = window.matchMedia('(max-width: 900px)');

  function isMobile() {
    return mobileQuery.matches;
  }

  function syncClosedState() {
    if (!isMobile()) {
      nav.removeAttribute('aria-hidden');
      backdrop.setAttribute('aria-hidden', 'true');
      return;
    }

    if (!document.body.classList.contains('mobile-nav-open')) {
      nav.setAttribute('aria-hidden', 'true');
      backdrop.setAttribute('aria-hidden', 'true');
    }
  }

  function setOpen(isOpen) {
    if (!isMobile()) {
      return;
    }

    document.body.classList.toggle('mobile-nav-open', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    toggle.setAttribute('aria-label', isOpen ? closeLabel : openLabel);
    nav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

    if (isOpen) {
      closeBtn?.focus();
    } else {
      toggle.focus();
    }
  }

  function toggleMenu() {
    setOpen(!document.body.classList.contains('mobile-nav-open'));
  }

  toggle.addEventListener('click', toggleMenu);
  closeBtn?.addEventListener('click', function () {
    setOpen(false);
  });
  backdrop.addEventListener('click', function () {
    setOpen(false);
  });

  nav.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      setOpen(false);
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && document.body.classList.contains('mobile-nav-open')) {
      setOpen(false);
    }
  });

  mobileQuery.addEventListener('change', function () {
    setOpen(false);
    syncClosedState();
  });

  syncClosedState();
})();
