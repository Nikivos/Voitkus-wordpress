(function () {
  'use strict';

  var reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

  function prefersReducedMotion() {
    return reducedMotionQuery.matches;
  }

  function markVisible(elements) {
    elements.forEach(function (el) {
      el.classList.add('is-visible');
    });
  }

  function observeReveal() {
    var revealEls = document.querySelectorAll('[data-voitkus-reveal]');
    var staggerEls = document.querySelectorAll('[data-voitkus-reveal-stagger]');

    if (prefersReducedMotion()) {
      markVisible(revealEls);
      markVisible(staggerEls);
      return;
    }

    if (!('IntersectionObserver' in window)) {
      markVisible(revealEls);
      markVisible(staggerEls);
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -6% 0px',
      }
    );

    revealEls.forEach(function (el) {
      observer.observe(el);
    });

    staggerEls.forEach(function (el) {
      observer.observe(el);
    });
  }

  function bindHomeRevealTargets() {
    if (!document.body.classList.contains('home')) {
      return;
    }

    var sections = [
      { root: '.profiles', targets: ['.profiles__header', '.profiles__grid'] },
      { root: '.why', targets: ['.why__header', '.why__grid'] },
      { root: '.wine', targets: ['.wine__intro', '.wine__panel'] },
      { root: '.lots', targets: ['.lots__header', '.lots__grid'] },
      { root: '.grind', targets: ['.grind__layout'] },
      { root: '.founder', targets: ['.founder__layout'] },
      { root: '.reviews', targets: ['.reviews__disclosure'] },
    ];

    var staggerSelectors = '.profiles__grid, .why__grid, .lots__grid, .reviews__grid, .founder__layout, .grind__layout';

    sections.forEach(function (config) {
      var section = document.querySelector(config.root);

      if (!section) {
        return;
      }

      config.targets.forEach(function (selector) {
        var target = section.querySelector(selector);

        if (!target) {
          return;
        }

        if (target.matches(staggerSelectors)) {
          target.setAttribute('data-voitkus-reveal-stagger', '60');
          return;
        }

        target.setAttribute('data-voitkus-reveal', '');
      });
    });
  }

  function animateTasteBlock(block) {
    if (!block || block.classList.contains('is-taste-animated')) {
      return;
    }

    block.querySelectorAll('.product-taste__fill[data-fill]').forEach(function (fill) {
      var value = fill.getAttribute('data-fill') || '0';
      fill.style.setProperty('--fill', value);
    });

    if (prefersReducedMotion()) {
      block.classList.add('is-taste-animated');
      return;
    }

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        block.classList.add('is-taste-animated');
      });
    });
  }

  function observeTasteBars() {
    var block = document.querySelector('.product-taste[data-voitkus-taste]');

    if (!block) {
      return;
    }

    block.querySelectorAll('.product-taste__fill[data-fill]').forEach(function (fill) {
      fill.style.setProperty('--fill', fill.getAttribute('data-fill') || '0');
    });

    if (prefersReducedMotion()) {
      block.classList.add('is-taste-animated');
      return;
    }

    if (!('IntersectionObserver' in window)) {
      animateTasteBlock(block);
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          animateTasteBlock(entry.target);
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.2 }
    );

    observer.observe(block);
  }

  document.documentElement.classList.add('voitkus-motion-ready');
  bindHomeRevealTargets();

  requestAnimationFrame(function () {
    observeReveal();
    observeTasteBars();
  });
})();
