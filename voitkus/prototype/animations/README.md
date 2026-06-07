# Voitkus — animations prototype

Standalone demo for three interactions before theme integration.

## Open

```bash
open "voitkus/prototype/animations/index.html"
```

Or drag `index.html` into a browser.

## Demos

1. **Scroll reveal** — `data-reveal`, `data-reveal-stagger` (homepage sections)
2. **Taste bars** — `data-taste-animate` + `data-fill` on PDP profile
3. **Add to cart** — `data-atc` + cart badge bump

## Integrated in theme

Same motion rules live in production:

- `assets/motion.js` — homepage reveal + PDP taste bars
- `assets/product.js` — add-to-cart fly + cart bump
- `style.css` — motion styles (`translate3d` only, no layout thrash)

Only `transform` + `opacity`. Respects `prefers-reduced-motion`. Disabled on cart/checkout.
