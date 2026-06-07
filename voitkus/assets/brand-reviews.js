(function () {
  'use strict';

  var disclosure = document.querySelector('[data-reviews-disclosure]');

  if (disclosure) {
    var hasItems = disclosure.getAttribute('data-has-items') === '1';
    var desktopMq = window.matchMedia('(min-width: 901px)');

    function syncDisclosure() {
      if (hasItems && desktopMq.matches) {
        disclosure.open = true;
      }
    }

    syncDisclosure();

    if (typeof desktopMq.addEventListener === 'function') {
      desktopMq.addEventListener('change', syncDisclosure);
    } else if (typeof desktopMq.addListener === 'function') {
      desktopMq.addListener(syncDisclosure);
    }
  }

  document.querySelectorAll('[data-rating-value]').forEach(function (valueEl) {
    var starsWrap = valueEl.closest('.product-reviews__rating');

    if (!starsWrap) {
      return;
    }

    var inputs = starsWrap.querySelectorAll('.product-reviews__rating-input');

    function syncRatingValue() {
      var selected = starsWrap.querySelector('.product-reviews__rating-input:checked');
      valueEl.textContent = selected ? selected.value + '/5' : '';
    }

    inputs.forEach(function (input) {
      input.addEventListener('change', syncRatingValue);
    });

    syncRatingValue();
  });
})();
