# Voitkus Coffee — WordPress + WooCommerce

## Структура

```
brand/       — стратегия, бренд, SKU (документация)
prototype/   — HTML/CSS прототип (смотреть в браузере)
voitkus/     — WordPress-тема → заливать на Hostinger
```

## Прототип (локально)

```bash
cd prototype && python3 dev_server.py
```

→ http://127.0.0.1:5500

## Тема на Hostinger (ручной перенос)

Папку **`voitkus/`** целиком копируешь в:

```
public_html/wp-content/themes/voitkus/
```

После заливки: **Wygląd → Motywy** → активируй Voitkus.

Подробнее: [voitkus/README.md](voitkus/README.md)

## Meta поля товара

См. [brand/12-mvp-products.md](brand/12-mvp-products.md). В админке — блок **Lot Report (Voitkus)** на странице товара.

## Дедлайн

10.06.2026 — [brand/ROADMAP.md](brand/ROADMAP.md)
