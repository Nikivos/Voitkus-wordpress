(function () {
  'use strict';

  var config = window.voitkusCookieConsent || {};
  var storageKey = config.storageKey || 'voitkus_cookie_consent_v1';

  function readConsent() {
    try {
      var raw = window.localStorage.getItem(storageKey);
      if (!raw) {
        return null;
      }
      return JSON.parse(raw);
    } catch (error) {
      return null;
    }
  }

  function writeConsent(consent) {
    var payload = {
      necessary: true,
      analytics: !!consent.analytics,
      marketing: !!consent.marketing,
      version: 1,
      updated: Date.now(),
    };

    try {
      window.localStorage.setItem(storageKey, JSON.stringify(payload));
    } catch (error) {
      /* ignore quota errors */
    }

    document.dispatchEvent(
      new CustomEvent('voitkus:cookie-consent', {
        detail: payload,
      })
    );

    return payload;
  }

  function loadGoogleAnalytics(measurementId) {
    if (!measurementId || window.voitkusGaLoaded) {
      return;
    }

    window.voitkusGaLoaded = true;
    window.dataLayer = window.dataLayer || [];

    window.gtag = function gtag() {
      window.dataLayer.push(arguments);
    };

    window.gtag('js', new Date());
    window.gtag('config', measurementId, { anonymize_ip: true });

    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(measurementId);
    document.head.appendChild(script);
  }

  function loadMetaPixel(pixelId) {
    if (!pixelId || window.voitkusMetaLoaded) {
      return;
    }

    window.voitkusMetaLoaded = true;

    !(function (f, b, e, v, n, t, s) {
      if (f.fbq) {
        return;
      }
      n = f.fbq = function fbq() {
        n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
      };
      if (!f._fbq) {
        f._fbq = n;
      }
      n.push = n;
      n.loaded = true;
      n.version = '2.0';
      n.queue = [];
      t = b.createElement(e);
      t.async = true;
      t.src = v;
      s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s);
    })(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

    window.fbq('init', pixelId);
    window.fbq('track', 'PageView');
  }

  function applyOptionalScripts(consent) {
    if (!consent) {
      return;
    }

    if (consent.analytics && config.analyticsId) {
      loadGoogleAnalytics(config.analyticsId);
    }

    if (consent.marketing && config.marketingId) {
      loadMetaPixel(config.marketingId);
    }
  }

  function hideBanner(banner) {
    banner.hidden = true;
    banner.classList.remove('is-open');
    document.documentElement.classList.remove('voitkus-cookie-banner-open');
  }

  function showBanner(banner) {
    banner.hidden = false;
    banner.classList.add('is-open');
    document.documentElement.classList.add('voitkus-cookie-banner-open');
  }

  function bindBanner(banner) {
    var settingsPanel = banner.querySelector('#voitkus-cookie-settings');
    var analyticsInput = banner.querySelector('#voitkus-cookie-analytics');
    var marketingInput = banner.querySelector('#voitkus-cookie-marketing');
    var settingsBtn = banner.querySelector('[data-voitkus-cookie-action="settings"]');
    var saveBtn = banner.querySelector('[data-voitkus-cookie-action="save"]');
    var acceptBtn = banner.querySelector('[data-voitkus-cookie-action="accept"]');
    var rejectBtn = banner.querySelector('[data-voitkus-cookie-action="reject"]');

    function setSettingsOpen(isOpen) {
      if (!settingsPanel || !saveBtn || !acceptBtn) {
        return;
      }

      settingsPanel.hidden = !isOpen;
      saveBtn.hidden = !isOpen;
      acceptBtn.hidden = isOpen;
    }

    function persist(consent) {
      var saved = writeConsent(consent);
      applyOptionalScripts(saved);
      hideBanner(banner);
      setSettingsOpen(false);
    }

    if (settingsBtn) {
      settingsBtn.addEventListener('click', function () {
        setSettingsOpen(settingsPanel && settingsPanel.hidden);
      });
    }

    if (acceptBtn) {
      acceptBtn.addEventListener('click', function () {
        persist({ analytics: true, marketing: true });
      });
    }

    if (rejectBtn) {
      rejectBtn.addEventListener('click', function () {
        persist({ analytics: false, marketing: false });
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        persist({
          analytics: analyticsInput ? analyticsInput.checked : false,
          marketing: marketingInput ? marketingInput.checked : false,
        });
      });
    }

    document.addEventListener('click', function (event) {
      var trigger = event.target.closest('[data-voitkus-cookie-preferences]');
      if (!trigger) {
        return;
      }

      event.preventDefault();
      var existing = readConsent();
      if (analyticsInput) {
        analyticsInput.checked = !!(existing && existing.analytics);
      }
      if (marketingInput) {
        marketingInput.checked = !!(existing && existing.marketing);
      }
      setSettingsOpen(true);
      showBanner(banner);
    });
  }

  function init() {
    var banner = document.getElementById('voitkus-cookie-banner');
    if (!banner) {
      return;
    }

    bindBanner(banner);

    var existing = readConsent();
    if (existing) {
      applyOptionalScripts(existing);
      return;
    }

    showBanner(banner);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
