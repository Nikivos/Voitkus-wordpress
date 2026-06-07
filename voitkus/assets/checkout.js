(function ($) {
  'use strict';

  var resetConfirmMsg = 'Wyczyścić adres, metodę dostawy i wybrany paczkomat?';

  function $panels() {
    return $('[data-voitkus-shipping-panel]');
  }

  function markSelectedShipping() {
    $('.woocommerce-shipping-methods input.shipping_method[type="radio"]').each(function () {
      $(this).closest('.voitkus-shipping-method-item, li').toggleClass('is-selected', this.checked);
    });
    $('.woocommerce-shipping-methods .is-single-method').addClass('is-selected');
  }

  function syncInpostChosenPoint() {
    $panels().each(function () {
      var $panel = $(this);
      var $out = $panel.find('[data-voitkus-inpost-selected]');
      if (!$out.length) {
        return;
      }

      var $chosen = $panel
        .find('.voitkus-shipping-method-plugins .inpost-chosen-point, .inpost-chosen-point, .easypack-selected-point')
        .filter(function () {
          return ($(this).text() || '').trim() !== '';
        })
        .first();

      if ($chosen.length) {
        $out.text(($chosen.text() || '').trim()).prop('hidden', false);
      }
    });
  }

  function scrollToAddressForm($panel) {
    var $step = $panel.find('#voitkus-shipping-address-step');
    if (!$step.length) {
      return false;
    }

    $step.addClass('is-open');
    $step.find('.shipping-calculator-form, #shipping-calculator-form').addClass('is-open').show();

    if ($step[0] && $step[0].scrollIntoView) {
      $step[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    window.setTimeout(function () {
      $step.find('input, select').filter(':visible').first().trigger('focus');
    }, 300);

    return true;
  }

  function highlightInpostButtons() {
    var fallbackLabel = 'Wybierz paczkomat';

    $('.voitkus-shipping-method-plugins').find('button, a.button').each(function () {
      var $el = $(this);

      if ($el.closest('.voitkus-shipping-tools, .shipping-calculator-form').length) {
        return;
      }

      var text = ($el.text() || '').trim();
      var isInpost =
        /paczkomat|punkt|inpost|wybierz|parcel/i.test(text) ||
        /inpost|paczkomat|easypack/i.test($el.attr('class') || '') ||
        $el.closest('[class*="inpost"], [class*="easypack"]').length > 0;

      if (!isInpost && text === '') {
        return;
      }

      $el.addClass('voitkus-inpost-native-btn');

      if (text === '') {
        if (!$el.attr('aria-label')) {
          $el.attr('aria-label', fallbackLabel);
        }
        if (!$el.find('img, svg').length) {
          $el.text(fallbackLabel);
        }
      }
    });
  }

  function bindShippingTools() {
    $(document.body).on('click', '[data-voitkus-scroll-address]', function (event) {
      event.preventDefault();
      var $panel = $(this).closest('[data-voitkus-shipping-panel]');
      if (!scrollToAddressForm($panel)) {
        window.alert('Formularz adresu jest niedostępny. Włącz kalkulator wysyłki w WooCommerce → Ustawienia → Wysyłka.');
      }
    });

    $(document.body).on('click', '.voitkus-shipping-panel .shipping-calculator-button', function (event) {
      event.preventDefault();
      scrollToAddressForm($(this).closest('[data-voitkus-shipping-panel]'));
    });

    $(document.body).on('click', '.voitkus-shipping-tools__edit-address', function (event) {
      event.preventDefault();
      var $postcode = $('#billing_postcode_field input, #shipping_postcode_field input, #billing_postcode, #shipping_postcode').filter(':visible').first();
      var $target = $postcode.length ? $postcode.closest('.form-row') : $('#customer_details').first();

      var $shipToggle = $('#ship-to-different-address-checkbox');
      if ($shipToggle.length && !$shipToggle.prop('checked')) {
        $shipToggle.prop('checked', true).trigger('change');
      }

      if ($target.length && $target[0].scrollIntoView) {
        $target[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      ($postcode.length ? $postcode : $target.find('input, select').filter(':visible').first()).trigger('focus');
    });

    $(document.body).on('click', '[data-voitkus-reset-shipping]', function (event) {
      if (!window.confirm(resetConfirmMsg)) {
        event.preventDefault();
        return;
      }

      var $form = $(this).closest('form.voitkus-shipping-reset-form');
      if ($form.length) {
        event.preventDefault();
        $form.trigger('submit');
      }
    });
  }

  function bindShippingCardSelect() {
    $(document.body).on('click', '.voitkus-shipping-method-item', function (event) {
      if ($(event.target).closest('button, a, input, label, .voitkus-shipping-method-plugins').length) {
        return;
      }

      var $radio = $(this).find('input.shipping_method[type="radio"]');
      if ($radio.length && !$radio.prop('checked')) {
        $radio.prop('checked', true).trigger('change');
      }
    });

    $(document.body).on('change', '.woocommerce-shipping-methods input.shipping_method', function () {
      markSelectedShipping();
      syncInpostChosenPoint();
      if ($('.woocommerce-cart').length) {
        $(document.body).trigger('wc_update_cart');
      }
    });
  }

  function bindInvoiceToggle() {
    var $scope = $('.checkout-page');
    var $checkbox = $('#billing_voitkus_invoice');

    if (!$checkbox.length || !$scope.length) {
      return;
    }

    function syncInvoiceFields() {
      var active = $checkbox.prop('checked');
      $scope.toggleClass('is-voitkus-invoice-requested', active);
      $('#billing_company_field, #billing_voitkus_nip_field').toggle(active);
      $('#billing_company, #billing_voitkus_nip').prop('required', active);
    }

    $checkbox.off('change.voitkusInvoice').on('change.voitkusInvoice', syncInvoiceFields);
    syncInvoiceFields();
  }

  function refreshShippingUi() {
    markSelectedShipping();
    highlightInpostButtons();
    syncInpostChosenPoint();

    $panels().filter('.is-cart-context').each(function () {
      var $panel = $(this);
      $panel.find('#voitkus-shipping-address-step').addClass('is-open');
      $panel.find('.shipping-calculator-form, #shipping-calculator-form').addClass('is-open').show();
    });
  }

  $(function () {
    bindShippingTools();
    bindShippingCardSelect();
    bindInvoiceToggle();
    refreshShippingUi();
  });

  $(document.body).on('updated_cart_totals updated_checkout wc_fragments_refreshed', function () {
    refreshShippingUi();
    bindInvoiceToggle();
  });
})(jQuery);
