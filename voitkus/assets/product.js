(function () {
  function initProductGalleries() {
    var galleries = document.querySelectorAll('[data-product-gallery]');

    galleries.forEach(function (gallery) {
      if (gallery.dataset.voitkusGalleryReady === '1') {
        return;
      }

      var main = gallery.querySelector('.product-page__gallery-img');
      var stage = gallery.querySelector('.product-page__gallery-stage');
      var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.product-page__gallery-thumb'));
      var prevBtn = gallery.querySelector('.product-page__gallery-nav--prev');
      var nextBtn = gallery.querySelector('.product-page__gallery-nav--next');
      var counter = gallery.querySelector('.product-page__gallery-counter');

      if (!main || !thumbs.length) {
        return;
      }

      var slides = thumbs
        .map(function (thumb) {
          return thumb.getAttribute('data-full-src') || '';
        })
        .filter(function (src) {
          return src !== '';
        });

      if (!slides.length) {
        return;
      }

      gallery.dataset.voitkusGalleryReady = '1';

      var index = 0;
      var touchStartX = 0;
      var touchStartY = 0;
      var touchTracking = false;

      function prefersReducedMotionLocal() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      }

      function setSlide(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;
        main.setAttribute('src', slides[index]);

        thumbs.forEach(function (thumb, thumbIndex) {
          var isActive = thumbIndex === index;
          thumb.classList.toggle('is-active', isActive);
          thumb.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        if (counter) {
          counter.textContent = (index + 1) + ' / ' + slides.length;
        }

        var activeThumb = thumbs[index];

        if (activeThumb && typeof activeThumb.scrollIntoView === 'function') {
          activeThumb.scrollIntoView({
            behavior: prefersReducedMotionLocal() ? 'auto' : 'smooth',
            block: 'nearest',
            inline: 'nearest',
          });
        }
      }

      function step(delta) {
        setSlide(index + delta);
      }

      thumbs.forEach(function (thumb, thumbIndex) {
        thumb.addEventListener('click', function () {
          setSlide(thumbIndex);
        });
      });

      if (prevBtn) {
        prevBtn.addEventListener('click', function () {
          step(-1);
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function () {
          step(1);
        });
      }

      if (stage) {
        stage.addEventListener(
          'touchstart',
          function (event) {
            if (!event.touches || event.touches.length !== 1) {
              return;
            }

            touchStartX = event.touches[0].clientX;
            touchStartY = event.touches[0].clientY;
            touchTracking = true;
          },
          { passive: true }
        );

        stage.addEventListener(
          'touchend',
          function (event) {
            if (!touchTracking || !event.changedTouches || !event.changedTouches.length) {
              return;
            }

            touchTracking = false;

            var deltaX = event.changedTouches[0].clientX - touchStartX;
            var deltaY = event.changedTouches[0].clientY - touchStartY;

            if (Math.abs(deltaX) < 40 || Math.abs(deltaX) < Math.abs(deltaY)) {
              return;
            }

            step(deltaX < 0 ? 1 : -1);
          },
          { passive: true }
        );
      }

      gallery.setAttribute('tabindex', '0');

      gallery.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft') {
          event.preventDefault();
          step(-1);
        }

        if (event.key === 'ArrowRight') {
          event.preventDefault();
          step(1);
        }
      });
    });
  }

  initProductGalleries();

  var cartQtyUpdateTimer = null;
  var cartUpdating = false;
  var lastAtcButton = null;

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

  function isAddedToCartNoticeText(text) {
    var lower = (text || '').toLowerCase();
    return (
      lower.indexOf('added to your cart') !== -1 ||
      lower.indexOf('dodany do koszyka') !== -1 ||
      lower.indexOf('dodana do koszyka') !== -1 ||
      lower.indexOf('dodane do koszyka') !== -1 ||
      lower.indexOf('dodano do koszyka') !== -1 ||
      lower.indexOf('został dodany do koszyka') !== -1 ||
      lower.indexOf('zobacz koszyk') !== -1 ||
      lower.indexOf('view cart') !== -1
    );
  }

  function clearStaleCartNotices() {
    var wrappers = document.querySelectorAll(
      '.cart-page .woocommerce-notices-wrapper, ' +
      '.woocommerce-cart .woocommerce-notices-wrapper, ' +
      '.product-page .woocommerce-notices-wrapper, ' +
      'body.single-product .woocommerce-notices-wrapper'
    );

    if (!wrappers.length) {
      document.querySelectorAll('.product-page .woocommerce-message, .product-page .woocommerce-info').forEach(function (el) {
        var text = el.textContent || '';

        if (
          isCartUpdatedNoticeText(text) ||
          isAddedToCartNoticeText(text) ||
          isVariationRequiredNoticeText(text)
        ) {
          el.remove();
        }
      });

      return;
    }

    wrappers.forEach(function (wrapper) {
      wrapper.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error').forEach(function (el) {
        var text = el.textContent || '';

        if (
          isCartUpdatedNoticeText(text) ||
          isAddedToCartNoticeText(text) ||
          isVariationRequiredNoticeText(text)
        ) {
          el.remove();
        }
      });

      if (!wrapper.textContent.trim()) {
        wrapper.innerHTML = '';
      }
    });
  }

  function clearCartUpdatedNotices() {
    clearStaleCartNotices();
  }

  function getWcAjaxUrl(endpoint) {
    var template = '';

    if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.wc_ajax_url) {
      template = wc_add_to_cart_params.wc_ajax_url;
    } else if (typeof wc_cart_fragments_params !== 'undefined' && wc_cart_fragments_params.wc_ajax_url) {
      template = wc_cart_fragments_params.wc_ajax_url;
    } else if (typeof voitkusCart !== 'undefined' && voitkusCart.ajaxUrl && endpoint === 'add_to_cart') {
      return String(voitkusCart.ajaxUrl);
    }

    if (template) {
      return template.toString().replace('%%endpoint%%', endpoint);
    }

    return window.location.origin + '/?wc-ajax=' + endpoint;
  }

  function isVariationRequiredNoticeText(text) {
    var lower = (text || '').toLowerCase();

    return (
      lower.indexOf('prosimy wybra') !== -1 ||
      lower.indexOf('proszę wybra') !== -1 ||
      lower.indexOf('prosze wybra') !== -1 ||
      lower.indexOf('wybierz opcje') !== -1 ||
      lower.indexOf('wybrać opcje') !== -1 ||
      lower.indexOf('wybrac opcje') !== -1 ||
      lower.indexOf('please choose product options') !== -1 ||
      lower.indexOf('choose product options') !== -1 ||
      lower.indexOf('przechodząc do produktu') !== -1 ||
      lower.indexOf('przechodzac do produktu') !== -1 ||
      (lower.indexOf('przed dodaniem') !== -1 && lower.indexOf('opcje') !== -1) ||
      (lower.indexOf('przechodz') !== -1 && lower.indexOf('opcje') !== -1)
    );
  }

  function isMielenieAttributeName(name) {
    if (!name) {
      return false;
    }

    var normalized = name.replace(/^attribute_/, '').toLowerCase();

    return normalized === 'mielenie' || normalized === 'pa_mielenie' || normalized === 'pa-mielenie';
  }

  function ensureHiddenMielenieSelected($form) {
    if (!$form || !$form.length) {
      return;
    }

    $form.find('.variations select').each(function () {
      var $select = jQuery(this);
      var name = $select.attr('name') || '';

      if (!isMielenieAttributeName(name) || $select.val()) {
        return;
      }

      var $option = $select.find('option[value!=""]').first();

      if ($option.length) {
        $select.val($option.val());
      }
    });
  }

  function ensureSingleOptionAttributesSelected($form) {
    if (!$form || !$form.length) {
      return;
    }

    $form.find('.variations select').each(function () {
      var $select = jQuery(this);

      if ($select.val()) {
        return;
      }

      var $options = $select.find('option[value!=""]');

      if ($options.length === 1) {
        $select.val($options.first().val());
      }
    });
  }

  function getPurchasableVariations($form) {
    return getProductVariations($form).filter(function (variation) {
      return variation
        && variation.variation_id
        && variation.is_in_stock !== false
        && variation.is_purchasable !== false;
    });
  }

  function hasPendingVariationChoice($form) {
    if (!$form || !$form.length) {
      return false;
    }

    var pending = false;

    $form.find('.variations select').each(function () {
      var $select = jQuery(this);
      var name = $select.attr('name') || '';

      if (isMielenieAttributeName(name)) {
        return;
      }

      if ($select.find('option[value!=""]').length > 1 && !$select.val()) {
        pending = true;
      }
    });

    return pending;
  }

  function findMatchingVariation(variations, selected) {
    var match = null;

    variations.forEach(function (variation) {
      if (match || !variation || !variation.variation_id) {
        return;
      }

      if (variation.is_in_stock === false || variation.is_purchasable === false) {
        return;
      }

      if (!variationAttributesMatch(variation.attributes || {}, selected)) {
        return;
      }

      match = variation;
    });

    return match;
  }

  function getVariationRequiredMessage($form) {
    if ($form && $form.find('.variations select[name="attribute_pa_waga"], .variations select[name="attribute_waga"]').length) {
      return 'Wybierz wagę przed dodaniem do koszyka.';
    }

    return 'Wybierz opcje produktu przed dodaniem do koszyka.';
  }

  function showVariationRequiredToast($form) {
    showToast(getVariationRequiredMessage($form), true);
  }

  function getProductVariations($form) {
    var raw = $form.data('product_variations');

    return Array.isArray(raw) ? raw : [];
  }

  function collectFormAttributes($form) {
    var attributes = {};

    $form.find('.variations select').each(function () {
      var name = jQuery(this).attr('name');

      if (name) {
        attributes[name] = jQuery(this).val() || '';
      }
    });

    return attributes;
  }

  function variationAttributesMatch(definition, selected) {
    var key;

    for (key in definition) {
      if (!Object.prototype.hasOwnProperty.call(definition, key)) {
        continue;
      }

      var expected = definition[key];

      if (expected === '' || expected === undefined) {
        continue;
      }

      if ((selected[key] || '') !== '' && selected[key] !== expected) {
        return false;
      }
    }

    for (key in selected) {
      if (!Object.prototype.hasOwnProperty.call(selected, key)) {
        continue;
      }

      if (!selected[key]) {
        continue;
      }

      if (
        definition[key] !== undefined
        && definition[key] !== ''
        && definition[key] !== selected[key]
      ) {
        return false;
      }
    }

    return true;
  }

  function syncVariationFields($form, variation) {
    if (!variation || !variation.variation_id) {
      return;
    }

    $form.find('input.variation_id, input[name="variation_id"]').val(String(variation.variation_id));

    Object.keys(variation.attributes || {}).forEach(function (key) {
      var value = variation.attributes[key];

      if (!value) {
        return;
      }

      var $select = $form.find('.variations select[name="' + key + '"]');

      if ($select.length && $select.val() !== value) {
        $select.val(value);
      }
    });
  }

  function resolveVariationFromForm($form) {
    ensureHiddenMielenieSelected($form);
    ensureSingleOptionAttributesSelected($form);

    var parsedId = parseInt($form.find('input.variation_id, input[name="variation_id"]').val(), 10);

    if (Number.isFinite(parsedId) && parsedId > 0) {
      return parsedId;
    }

    var variations = getProductVariations($form);

    if (!variations.length) {
      return 0;
    }

    var purchasable = getPurchasableVariations($form);

    if (purchasable.length === 1) {
      syncVariationFields($form, purchasable[0]);
      return parseInt(purchasable[0].variation_id, 10);
    }

    var selected = collectFormAttributes($form);
    var match = findMatchingVariation(variations, selected);

    if (match) {
      syncVariationFields($form, match);
      return parseInt(match.variation_id, 10);
    }

    if (hasPendingVariationChoice($form)) {
      return 0;
    }

    return 0;
  }

  function hasSelectedVariation($form) {
    if (!$form || !$form.length || !$form.hasClass('variations_form')) {
      return true;
    }

    return resolveVariationFromForm($form) > 0;
  }

  function shouldAjaxAddFromForm($form) {
    if (!$form || !$form.length) {
      return false;
    }

    if ($form.hasClass('variations_form')) {
      var variationId = $form.find('input.variation_id, input[name="variation_id"]').val();

      return Boolean(variationId && variationId !== '0');
    }

    return true;
  }

  function handleProductAddToCartClick(event) {
    if (typeof jQuery === 'undefined') {
      return;
    }

    var target = event.target;

    if (!(target instanceof Element)) {
      return;
    }

    var buttonEl = target.closest('.product-page__purchase .single_add_to_cart_button');

    if (!buttonEl) {
      return;
    }

    var $button = jQuery(buttonEl);
    var $form = $button.closest('form.cart');

    if (!$form.length) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    if (!hasSelectedVariation($form)) {
      showVariationRequiredToast($form);
      return;
    }

    voitkusAjaxAddToCart($form, $button);
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

  function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  function getVisibleCartBadge() {
    var isMobile = window.matchMedia('(max-width: 900px)').matches;
    var selector = isMobile ? '[data-voitkus-cart-count="mobile"]' : '[data-voitkus-cart-count="desktop"]';

    return document.querySelector(selector);
  }

  function getVisibleCartControl() {
    var isMobile = window.matchMedia('(max-width: 900px)').matches;

    return document.querySelector(isMobile ? '.header-icon-btn--cart' : '.header-action--cart');
  }

  function bumpCartUi() {
    if (prefersReducedMotion()) {
      return;
    }

    var control = getVisibleCartControl();
    var badge = getVisibleCartBadge();

    if (control) {
      control.classList.remove('is-cart-bump');
      requestAnimationFrame(function () {
        control.classList.add('is-cart-bump');
      });
      window.setTimeout(function () {
        control.classList.remove('is-cart-bump');
      }, 480);
    }

    if (badge) {
      badge.classList.remove('is-cart-pop');
      requestAnimationFrame(function () {
        badge.classList.add('is-cart-pop');
      });
      window.setTimeout(function () {
        badge.classList.remove('is-cart-pop');
      }, 420);
    }
  }

  function getFlyTargetRect() {
    var control = getVisibleCartControl();

    if (!control) {
      return null;
    }

    var badge = getVisibleCartBadge();

    if (badge) {
      var badgeStyle = window.getComputedStyle(badge);
      var badgeRect = badge.getBoundingClientRect();

      if (
        badgeStyle.display !== 'none'
        && badgeStyle.visibility !== 'hidden'
        && badgeRect.width > 0
        && badgeRect.height > 0
      ) {
        return badgeRect;
      }
    }

    return control.getBoundingClientRect();
  }

  function getAddQuantity(fromEl) {
    var qty = 1;

    if (fromEl instanceof HTMLElement) {
      var buttonQty = parseInt(fromEl.getAttribute('data-quantity') || '1', 10);

      if (Number.isFinite(buttonQty) && buttonQty > 0) {
        qty = buttonQty;
      }

      if (typeof jQuery !== 'undefined') {
        var $form = jQuery(fromEl).closest('form.cart');

        if ($form.length) {
          var formQty = parseInt($form.find('input.qty').val() || String(qty), 10);

          if (Number.isFinite(formQty) && formQty > 0) {
            qty = formQty;
          }
        }
      }
    }

    return qty;
  }

  function flyToCart(fromEl) {
    if (prefersReducedMotion() || !fromEl) {
      return;
    }

    var targetRect = getFlyTargetRect();

    if (!targetRect) {
      return;
    }

    var fromRect = fromEl.getBoundingClientRect();
    var startX = fromRect.left + fromRect.width / 2;
    var startY = fromRect.top + fromRect.height / 2;
    var endX = targetRect.left + targetRect.width / 2;
    var endY = targetRect.top + targetRect.height / 2;
    var qty = getAddQuantity(fromEl);
    var fly = document.createElement('span');

    fly.className = 'voitkus-atc-fly';
    fly.textContent = '+' + String(qty);
    fly.setAttribute('aria-hidden', 'true');

    fly.style.transform = 'translate3d(' + startX + 'px, ' + startY + 'px, 0) translate(-50%, -50%) scale(1)';
    document.body.appendChild(fly);

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        fly.style.transform = 'translate3d(' + endX + 'px, ' + endY + 'px, 0) translate(-50%, -50%) scale(0.72)';
        fly.style.opacity = '0';
      });
    });

    function cleanup() {
      fly.removeEventListener('transitionend', cleanup);

      if (fly.parentNode) {
        fly.parentNode.removeChild(fly);
      }
    }

    fly.addEventListener('transitionend', cleanup);
    window.setTimeout(cleanup, 900);
  }

  function normalizeAtcButton(button) {
    if (!button) {
      return null;
    }

    if (button.jquery) {
      return button;
    }

    if (button instanceof HTMLElement) {
      return jQuery(button);
    }

    return null;
  }

  function rememberAtcButton($button) {
    var normalized = normalizeAtcButton($button);

    if (normalized && normalized.length) {
      lastAtcButton = normalized;
    }
  }

  function resolveAtcButton(button) {
    var resolved = normalizeAtcButton(button);

    if (resolved && resolved.length) {
      return resolved;
    }

    return lastAtcButton && lastAtcButton.length ? lastAtcButton : null;
  }

  function resolveProductIdFromForm($form) {
    if (!$form || !$form.length) {
      return 0;
    }

    var fromHiddenProduct = parseInt($form.find('input[name="product_id"]').val(), 10);

    if (Number.isFinite(fromHiddenProduct) && fromHiddenProduct > 0) {
      return fromHiddenProduct;
    }

    var fromData = parseInt($form.data('product_id'), 10);

    if (Number.isFinite(fromData) && fromData > 0) {
      return fromData;
    }

    var fromAddToCart = parseInt($form.find('[name="add-to-cart"]').first().val(), 10);

    if (Number.isFinite(fromAddToCart) && fromAddToCart > 0) {
      return fromAddToCart;
    }

    var article = $form.closest('article[id^="product-"]');

    if (article.length) {
      var fromArticle = parseInt(String(article.attr('id') || '').replace(/^product-/, ''), 10);

      if (Number.isFinite(fromArticle) && fromArticle > 0) {
        return fromArticle;
      }
    }

    return 0;
  }

  function buildAddToCartData($form) {
    ensureHiddenMielenieSelected($form);

    var variationId = 0;

    if ($form.hasClass('variations_form')) {
      variationId = resolveVariationFromForm($form);

      if (variationId <= 0) {
        return null;
      }
    }

    var payload = {};
    var fields = $form.serializeArray();

    fields.forEach(function (field) {
      if (field.name.indexOf('attribute_') === 0 && !field.value) {
        return;
      }

      payload[field.name] = field.value;
    });

    if (variationId > 0) {
      // Custom attribute "waga" on prod: parent+variation_id POST fails WC validation.
      // Adding by variation ID matches native WC shortcut and works reliably.
      payload.product_id = String(variationId);
      delete payload.variation_id;

      Object.keys(payload).forEach(function (key) {
        if (key.indexOf('attribute_') === 0) {
          delete payload[key];
        }
      });
    } else {
      var productId = resolveProductIdFromForm($form);

      if (productId > 0) {
        payload.product_id = String(productId);
      } else if (payload['add-to-cart']) {
        payload.product_id = payload['add-to-cart'];
      }
    }

    if (!payload.quantity) {
      var qty = $form.find('input.qty').val();
      payload.quantity = qty || 1;
    }

    return payload;
  }

  function voitkusAjaxAddToCart($form, $button) {
    if (typeof jQuery === 'undefined') {
      return false;
    }

    var data = buildAddToCartData($form);

    if (!data || !data.product_id) {
      setAddToCartLoading($button, false);

      if ($form.hasClass('variations_form')) {
        showVariationRequiredToast($form);
      } else {
        showToast('Nie udało się dodać do koszyka', true);
      }

      return false;
    }

    rememberAtcButton($button);
    setAddToCartLoading($button, true);
    jQuery(document.body).trigger('adding_to_cart', [$button]);

    jQuery.ajax({
      type: 'POST',
      url: getWcAjaxUrl('add_to_cart'),
      data: data,
      dataType: 'json',
      success: function (response) {
        var payload = parseJsonResponse(response);

        if (!payload) {
          setAddToCartLoading($button, false);
          showToast('Nie udało się dodać do koszyka', true);
          return;
        }

        if (payload.error) {
          setAddToCartLoading($button, false);
          showToast('Nie udało się dodać do koszyka', true);
          return;
        }

        jQuery(document.body).trigger('added_to_cart', [payload.fragments, payload.cart_hash, $button]);
      },
      error: function () {
        setAddToCartLoading($button, false);
        showToast('Nie udało się dodać do koszyka', true);
      },
    });

    return true;
  }

  function handleAddedToCart(fragments, cartHash, button) {
    var $button = resolveAtcButton(button);

    if ($button) {
      setAddToCartLoading($button, false);
    }

    var applied = applyCartFragments(fragments);

    if (cartHash) {
      syncCartHash(cartHash);
    }

    if (!applied) {
      refreshCartFragments(function () {
        if ($button && getVoitkusAddToCartRoot($button)) {
          runAddToCartFeedback($button[0]);
        }
      });
      return;
    }

    if ($button && getVoitkusAddToCartRoot($button)) {
      runAddToCartFeedback($button[0]);
    }

    clearStaleCartNotices();
  }

  function getVoitkusAddToCartRoot($button) {
    if (!$button || !$button.length) {
      return null;
    }

    var $purchase = $button.closest('.product-page__purchase');

    if ($purchase.length) {
      return $purchase;
    }

    if ($button.closest('.lot-card').length) {
      return $button.closest('.lot-card');
    }

    return null;
  }

  function setAddToCartLoading($button, isLoading) {
    if (!$button || !$button.length) {
      return;
    }

    $button.prop('disabled', isLoading);
    $button.toggleClass('is-loading', isLoading);

    if ($button.hasClass('single_add_to_cart_button')) {
      $button.css('opacity', isLoading ? '0.5' : '1');
    }
  }

  function pulseAddToCartButton(buttonEl) {
    if (!buttonEl || prefersReducedMotion()) {
      return;
    }

    buttonEl.classList.add('is-added');

    window.setTimeout(function () {
      buttonEl.classList.remove('is-added');
    }, 1100);
  }

  function runAddToCartFeedback(buttonEl) {
    if (!buttonEl) {
      return;
    }

    requestAnimationFrame(function () {
      pulseAddToCartButton(buttonEl);
      flyToCart(buttonEl);
      bumpCartUi();
      showToast('Dodano do koszyka', false);
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

  function isNoticeFragmentSelector(selector) {
    var lower = (selector || '').toLowerCase();

    return lower.indexOf('woocommerce-notices') !== -1 || lower.indexOf('woocommerce-message') !== -1;
  }

  function applyCartFragments(fragments) {
    if (!fragments || typeof jQuery === 'undefined') {
      return false;
    }

    var count = null;
    var applied = false;

    jQuery.each(fragments, function (selector, html) {
      if (isNoticeFragmentSelector(selector)) {
        return;
      }
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

  document.addEventListener('click', handleProductAddToCartClick, true);

  document.addEventListener('submit', function (event) {
    var form = event.target;

    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    if (!form.closest('.product-page__purchase') || !form.classList.contains('cart')) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    if (typeof jQuery === 'undefined') {
      return;
    }

    var $form = jQuery(form);
    var $button = $form.find('.single_add_to_cart_button');

    if (!hasSelectedVariation($form)) {
      showVariationRequiredToast($form);
      return;
    }

    if (!$button.length) {
      return;
    }

    voitkusAjaxAddToCart($form, $button);
  }, true);

  if (typeof jQuery !== 'undefined') {
    jQuery(function ($) {
      $('.product-page__purchase form.variations_form').each(function () {
        var $form = $(this);

        ensureHiddenMielenieSelected($form);
        ensureSingleOptionAttributesSelected($form);
        resolveVariationFromForm($form);

        $form.on('woocommerce_variation_has_changed', function () {
          ensureHiddenMielenieSelected($form);

          var variationId = parseInt($form.find('input.variation_id, input[name="variation_id"]').val(), 10);

          if (!Number.isFinite(variationId) || variationId <= 0) {
            resolveVariationFromForm($form);
          }
        });

        $form.find('.variations select[name="attribute_pa_waga"], .variations select[name="attribute_waga"]').on('change', function () {
          ensureHiddenMielenieSelected($form);
        });

        $form.find('.variations select').each(function () {
          if ($(this).val()) {
            $(this).trigger('change');
          }
        });
      });
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
      rememberAtcButton($button);

      if (!getVoitkusAddToCartRoot($button)) {
        return;
      }

      setAddToCartLoading($button, true);
    });

    jQuery(document.body).on('added_to_cart', function (event, fragments, cartHash, $button) {
      handleAddedToCart(fragments, cartHash, $button);
    });

    jQuery(document.body).on('wc_add_to_cart_error', function (event, $button) {
      if (getVoitkusAddToCartRoot($button)) {
        setAddToCartLoading($button, false);
      }
    });

    jQuery(document).on('click', '.lot-card__atc, .product-page__purchase .single_add_to_cart_button', function () {
      rememberAtcButton(jQuery(this));
    });
  }

  if (document.querySelector('.cart-page, .woocommerce-cart, .product-page')) {
    clearStaleCartNotices();
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

  function showToast(message, isError) {
    var toast = document.createElement('div');
    toast.className = 'voitkus-toast' + (isError ? ' voitkus-toast--error' : '');

    var icon = isError
      ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>'
      : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>';

    toast.innerHTML = icon + ' <span>' + message + '</span>';

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
