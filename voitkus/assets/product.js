(function () {
  var galleries = document.querySelectorAll('[data-product-gallery]');

  galleries.forEach(function (gallery) {
    var main = gallery.querySelector('.product-page__gallery-img');
    var thumbs = gallery.querySelectorAll('.product-page__gallery-thumb');

    if (!main || !thumbs.length) {
      return;
    }

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var fullSrc = thumb.getAttribute('data-full-src');

        if (!fullSrc) {
          return;
        }

        main.setAttribute('src', fullSrc);
        thumbs.forEach(function (item) {
          item.classList.remove('is-active');
          item.setAttribute('aria-pressed', 'false');
        });
        thumb.classList.add('is-active');
        thumb.setAttribute('aria-pressed', 'true');
      });
    });
  });

  document.querySelectorAll('.product-page__purchase .quantity').forEach(function (quantity) {
    if (quantity.classList.contains('qty-stepper')) {
      return;
    }

    var input = quantity.querySelector('.qty');

    if (!input || quantity.querySelector('.qty-stepper__btn')) {
      return;
    }

    quantity.classList.add('qty-stepper');

    var minus = document.createElement('button');
    minus.type = 'button';
    minus.className = 'qty-stepper__btn qty-stepper__btn--minus';
    minus.setAttribute('aria-label', 'Zmniejsz ilość');
    minus.textContent = '−';

    var plus = document.createElement('button');
    plus.type = 'button';
    plus.className = 'qty-stepper__btn qty-stepper__btn--plus';
    plus.setAttribute('aria-label', 'Zwiększ ilość');
    plus.textContent = '+';

    quantity.insertBefore(minus, input);
    quantity.appendChild(plus);

    function getStep() {
      var step = parseFloat(input.getAttribute('step') || '1');
      return Number.isFinite(step) && step > 0 ? step : 1;
    }

    function getMin() {
      var min = parseFloat(input.getAttribute('min') || '1');
      return Number.isFinite(min) ? min : 1;
    }

    function getMax() {
      var max = parseFloat(input.getAttribute('max') || '');
      return Number.isFinite(max) && max > 0 ? max : null;
    }

    function getValue() {
      var value = parseFloat(input.value || String(getMin()));
      return Number.isFinite(value) ? value : getMin();
    }

    function setValue(nextValue) {
      var min = getMin();
      var max = getMax();
      var step = getStep();
      var value = Math.max(min, nextValue);

      if (max !== null) {
        value = Math.min(max, value);
      }

      if (step % 1 !== 0) {
        value = Math.round(value / step) * step;
      }

      input.value = String(value);
      input.dispatchEvent(new Event('change', { bubbles: true }));
      updateButtons();
    }

    function updateButtons() {
      var value = getValue();
      var min = getMin();
      var max = getMax();

      minus.disabled = value <= min;
      plus.disabled = max !== null && value >= max;
    }

    minus.addEventListener('click', function () {
      setValue(getValue() - getStep());
    });

    plus.addEventListener('click', function () {
      setValue(getValue() + getStep());
    });

    input.addEventListener('input', updateButtons);
    input.addEventListener('change', function () {
      setValue(getValue());
    });

    updateButtons();
  });
})();
