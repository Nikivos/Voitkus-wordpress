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
    if (prefersReducedMotion()) {
      markVisible(document.querySelectorAll('[data-reveal], [data-reveal-stagger]'));
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

    document.querySelectorAll('[data-reveal]').forEach(function (el) {
      observer.observe(el);
    });

    document.querySelectorAll('[data-reveal-stagger]').forEach(function (el) {
      observer.observe(el);
    });
  }

  function animateTaste(block) {
    if (!block || block.classList.contains('is-animated')) {
      return;
    }

    block.querySelectorAll('[data-fill]').forEach(function (fill) {
      fill.style.setProperty('--fill', fill.getAttribute('data-fill') || '0');
    });

    if (prefersReducedMotion()) {
      block.classList.add('is-animated');
      return;
    }

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        block.classList.add('is-animated');
      });
    });
  }

  function setupTasteBars() {
    var block = document.querySelector('[data-taste-animate]');

    if (!block) {
      return null;
    }

    if (prefersReducedMotion()) {
      animateTaste(block);
      return function () {
        block.classList.remove('is-animated');
        animateTaste(block);
      };
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateTaste(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.2 }
    );

    observer.observe(block);

    return function () {
      block.classList.remove('is-animated');
      animateTaste(block);
    };
  }

  function getCartBadge() {
    return document.getElementById('proto-cart-count');
  }

  function flyToCart(fromEl, toEl) {
    if (prefersReducedMotion() || !fromEl || !toEl) {
      return;
    }

    var fromRect = fromEl.getBoundingClientRect();
    var toRect = toEl.getBoundingClientRect();
    var startX = fromRect.left + fromRect.width / 2;
    var startY = fromRect.top + fromRect.height / 2;
    var deltaX = toRect.left + toRect.width / 2 - startX;
    var deltaY = toRect.top + toRect.height / 2 - startY;
    var dot = document.createElement('span');

    dot.className = 'proto-atc-fly';
    dot.style.left = startX + 'px';
    dot.style.top = startY + 'px';
    document.body.appendChild(dot);

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        dot.style.transform = 'translate3d(' + deltaX + 'px, ' + deltaY + 'px, 0) scale(0.35)';
        dot.style.opacity = '0';
      });
    });

    function cleanup() {
      dot.removeEventListener('transitionend', cleanup);

      if (dot.parentNode) {
        dot.parentNode.removeChild(dot);
      }
    }

    dot.addEventListener('transitionend', cleanup);
    window.setTimeout(cleanup, 900);
  }

  function setupAddToCart() {
    var button = document.querySelector('[data-atc]');
    var cart = document.getElementById('proto-cart');
    var countEl = getCartBadge();
    var resetBtn = document.getElementById('reset-cart');
    var count = 0;
    var locked = false;

    if (!button || !cart || !countEl) {
      return;
    }

    function bumpCart() {
      cart.classList.remove('is-bump');
      countEl.classList.remove('is-pop');

      requestAnimationFrame(function () {
        cart.classList.add('is-bump');
        countEl.classList.add('is-pop');
      });

      window.setTimeout(function () {
        cart.classList.remove('is-bump');
        countEl.classList.remove('is-pop');
      }, 480);
    }

    button.addEventListener('click', function () {
      if (locked) {
        return;
      }

      locked = true;
      button.classList.remove('is-added');
      flyToCart(button, countEl);

      window.setTimeout(function () {
        button.classList.add('is-added');
        count = count + 1;
        countEl.textContent = String(count);
        bumpCart();
      }, prefersReducedMotion() ? 0 : 280);

      window.setTimeout(function () {
        button.classList.remove('is-added');
        locked = false;
      }, 1200);
    });

    resetBtn?.addEventListener('click', function () {
      count = 0;
      countEl.textContent = '0';
      button.classList.remove('is-added');
      locked = false;
    });
  }

  var replayTaste = setupTasteBars();
  observeReveal();
  setupAddToCart();

  document.getElementById('replay-taste')?.addEventListener('click', function () {
    if (typeof replayTaste === 'function') {
      replayTaste();
    }
  });
})();
