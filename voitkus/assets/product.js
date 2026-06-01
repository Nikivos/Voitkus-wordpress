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

  var cartQtyUpdateTimer = null;
  var cartUpdating = false;

  function destroySteppers(root) {
    var scope = root || document;

    scope.querySelectorAll('.quantity.qty-stepper').forEach(function (quantity) {
      quantity.querySelectorAll('.qty-stepper__btn').forEach(function (btn) {
        btn.remove();
      });
      quantity.classList.remove('qty-stepper');
    });
  }

  function initSteppers(root) {
    var scope = root || document;

    scope.querySelectorAll('.quantity').forEach(function (quantity) {
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
        var min = parseFloat(input.getAttribute('min') || '0');
        return Number.isFinite(min) ? min : 0;
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

        minus.disabled = cartUpdating || value <= min;
        plus.disabled = cartUpdating || (max !== null && value >= max);
      }

      function onCartQtyClick(delta) {
        if (cartUpdating) {
          return;
        }

        setValue(getValue() + delta * getStep());

        if (quantity.closest('form.woocommerce-cart-form')) {
          scheduleCartUpdate(quantity);
        }
      }

      minus.addEventListener('click', function () {
        onCartQtyClick(-1);
      });

      plus.addEventListener('click', function () {
        onCartQtyClick(1);
      });

      input.addEventListener('input', updateButtons);
      input.addEventListener('change', function () {
        if (!cartUpdating) {
          setValue(getValue());
        }
      });

      updateButtons();
    });
  }

  function scheduleCartUpdate(quantityEl) {
    if (typeof jQuery === 'undefined') {
      return;
    }

    clearTimeout(cartQtyUpdateTimer);
    cartQtyUpdateTimer = setTimeout(function () {
      var form = quantityEl.closest('form.woocommerce-cart-form');

      if (!form || cartUpdating) {
        return;
      }

      var $form = jQuery(form);
      var $btn = $form.find('[name="update_cart"]');

      $btn.prop('disabled', false);
      $btn.trigger('click');
    }, 200);
  }

  function refreshCartSteppers() {
    var form = document.querySelector('.woocommerce-cart-form');

    destroySteppers(form || document);
    initSteppers(form || document);
  }

  initSteppers();

  function isCartUpdatedNoticeText(text) {
    var lower = (text || '').toLowerCase();
    return (
      lower.indexOf('cart updated') !== -1 ||
      lower.indexOf('корзина обновлен') !== -1 ||
      (lower.indexOf('koszyk') !== -1 && (lower.indexOf('zaktualiz') !== -1 || lower.indexOf('odśwież') !== -1 || lower.indexOf('odswiez') !== -1))
    );
  }

  function clearCartUpdatedNotices() {
    var wrapper = document.querySelector('.cart-page .woocommerce-notices-wrapper') ||
      document.querySelector('.woocommerce-cart .woocommerce-notices-wrapper');

    if (!wrapper) {
      return;
    }

    wrapper.querySelectorAll('.woocommerce-message').forEach(function (el) {
      if (isCartUpdatedNoticeText(el.textContent)) {
        el.remove();
      }
    });

    if (!wrapper.textContent.trim()) {
      wrapper.innerHTML = '';
    }
  }

  function getWcAjaxUrl(endpoint) {
    var template = '';

    if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.wc_ajax_url) {
      template = wc_add_to_cart_params.wc_ajax_url;
    } else if (typeof wc_cart_fragments_params !== 'undefined' && wc_cart_fragments_params.wc_ajax_url) {
      template = wc_cart_fragments_params.wc_ajax_url;
    }

    if (template) {
      return template.toString().replace('%%endpoint%%', endpoint);
    }

    return window.location.origin + '/?wc-ajax=' + endpoint;
  }

  function parseJsonResponse(response) {
    if (typeof response === 'string') {
      try {
        return JSON.parse(response);
      } catch (error) {
        return null;
      }
    }

    return response;
  }

  function countFromFragmentHtml(html) {
    var wrap = document.createElement('div');
    wrap.innerHTML = html;
    var value = parseInt((wrap.textContent || '').trim(), 10);
    return Number.isFinite(value) ? value : null;
  }

  function setHeaderCartCount(count) {
    document.querySelectorAll('[data-voitkus-cart-count]').forEach(function (el) {
      if (count > 0) {
        el.textContent = String(count);
        el.style.display = '';
        el.removeAttribute('aria-hidden');
      } else {
        el.textContent = '0';
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
      }
    });
  }

  function syncCartHash(cartHash) {
    if (!cartHash || typeof wc_cart_fragments_params === 'undefined') {
      return;
    }

    wc_cart_fragments_params.cart_hash = cartHash;

    try {
      var storageKey = wc_cart_fragments_params.fragment_name;

      if (storageKey) {
        sessionStorage.setItem(storageKey + '_hash', cartHash);
      }
    } catch (error) {
      /* ignore */
    }
  }

  function applyCartFragments(fragments) {
    if (!fragments || typeof jQuery === 'undefined') {
      return false;
    }

    var count = null;
    var applied = false;

    jQuery.each(fragments, function (selector, html) {
      if (selector.indexOf('voitkus-cart-count') !== -1 || selector.indexOf('cart-count') !== -1) {
        var parsed = countFromFragmentHtml(html);

        if (parsed !== null) {
          count = parsed;
        }
      }

      var $targets = jQuery(selector);

      if ($targets.length) {
        $targets.replaceWith(html);
        applied = true;
      }
    });

    if (count !== null) {
      setHeaderCartCount(count);
      applied = true;
    }

    return applied;
  }

  function refreshCartFragments(done) {
    if (typeof jQuery === 'undefined') {
      return;
    }

    jQuery.ajax({
      type: 'POST',
      url: getWcAjaxUrl('get_refreshed_fragments'),
      dataType: 'json',
      success: function (data) {
        var payload = parseJsonResponse(data);

        if (payload && payload.fragments) {
          applyCartFragments(payload.fragments);

          if (payload.cart_hash) {
            syncCartHash(payload.cart_hash);
          }

          jQuery(document.body).trigger('wc_fragments_refreshed');
        }

        if (typeof done === 'function') {
          done(payload);
        }
      },
      error: function () {
        if (typeof done === 'function') {
          done(null);
        }
      }
    });
  }

  if (typeof jQuery !== 'undefined') {
    jQuery(document.body).on('wc_fragments_ajax_start', function () {
      cartUpdating = true;
    });

    jQuery(document.body).on('wc_fragments_ajax_complete wc_fragments_ajax_error', function () {
      cartUpdating = false;
    });

    jQuery(document.body).on('submit', 'form.woocommerce-cart-form', function () {
      cartUpdating = true;
    });

    jQuery(document.body).on('updated_cart_totals', function () {
      cartUpdating = false;
      refreshCartSteppers();
      clearCartUpdatedNotices();
    });

    jQuery(document.body).on('adding_to_cart', function (event, $button) {
      if ($button && $button.closest('.product-page__purchase').length) {
        $button.prop('disabled', true).css('opacity', '0.5');
      }
    });

    jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, $button) {
      if ($button && $button.closest('.product-page__purchase').length) {
        $button.prop('disabled', false).css('opacity', '1');
      }

      var applied = applyCartFragments(fragments);

      if (cartHash) {
        syncCartHash(cartHash);
      }

      if (!applied) {
        refreshCartFragments();
      }

      if ($button && $button.closest('.product-page__purchase').length) {
        showToast('Dodano do koszyka', false);
      }
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

    void toast.offsetWidth;
    toast.classList.add('is-visible');

    setTimeout(function () {
      toast.classList.remove('is-visible');
      setTimeout(function () {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 300);
    }, 4000);
  }
})();
