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

  function initSteppers() {
    document.querySelectorAll('.quantity').forEach(function (quantity) {
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
        
        // Form trigger for WooCommerce cart update
        var cartUpdate = quantity.closest('form.woocommerce-cart-form');
        if (cartUpdate && typeof jQuery !== 'undefined') {
          jQuery('[name="update_cart"]').prop('disabled', false);
          jQuery('[name="update_cart"]').trigger('click');
        }
      });

      plus.addEventListener('click', function () {
        setValue(getValue() + getStep());

        // Form trigger for WooCommerce cart update
        var cartUpdate = quantity.closest('form.woocommerce-cart-form');
        if (cartUpdate && typeof jQuery !== 'undefined') {
          jQuery('[name="update_cart"]').prop('disabled', false);
          jQuery('[name="update_cart"]').trigger('click');
        }
      });

      input.addEventListener('input', updateButtons);
      input.addEventListener('change', function () {
        setValue(getValue());
      });

      updateButtons();
    });
  }

  initSteppers();

  if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('updated_cart_totals updated_checkout', function() {
      initSteppers();
    });

    // AJAX Add to Cart for Single Product
    jQuery('.product-page__purchase form.cart').on('submit', function (e) {
      var $form = jQuery(this);
      
      if ($form.closest('.product-page__purchase').length === 0) {
        return;
      }

      e.preventDefault();

      var $btn = $form.find('.single_add_to_cart_button');
      var data = new FormData($form[0]);
      
      // Ensure add-to-cart value is included
      data.append($btn.attr('name') || 'add-to-cart', $btn.val());

      $btn.prop('disabled', true).css('opacity', '0.5');

      fetch(window.location.href, {
        method: 'POST',
        body: data,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(function(res) { return res.text(); })
      .then(function(html) {
        $btn.prop('disabled', false).css('opacity', '1');

        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var error = doc.querySelector('.woocommerce-error');

        if (error) {
          showToast(error.textContent.trim(), true);
          return;
        }
        
        // Trigger WooCommerce fragment refresh to update header cart count
        jQuery(document.body).trigger('wc_fragment_refresh');

        showToast('Dodano do koszyka', false);
      })
      .catch(function() {
        $form.off('submit').submit();
      });
    });
  }

  function showToast(message, isError) {
    var toast = document.createElement('div');
    toast.className = 'voitkus-toast' + (isError ? ' voitkus-toast--error' : '');
    
    var icon = isError 
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>'
      : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>';

    var cartBtn = document.querySelector('.header-action--cart') || document.querySelector('.header-icon-btn--cart');
    var cartUrl = cartBtn ? cartBtn.getAttribute('href') : '/koszyk/';

    var actionHtml = isError 
      ? '' 
      : ' <a href="' + cartUrl + '" class="voitkus-toast__link">Zobacz</a>';

    toast.innerHTML = icon + ' <span>' + message + '</span>' + actionHtml;
    
    document.body.appendChild(toast);

    // Force reflow
    void toast.offsetWidth;
    toast.classList.add('is-visible');

    setTimeout(function() {
      toast.classList.remove('is-visible');
      setTimeout(function() {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 4000);
  }
})();
