# 12. MVP — карточки товаров (WooCommerce)

> Черновик для заливки. Цвета → [10-packaging-system.md](./10-packaging-system.md).  
> **Цвет = профиль вкуса**, не уникальный ID лота — два SKU могут делить ощущение/цвет.  
> **Статус:** v0.2 — подтвердить цены, ноты и данные после cupping.

## Сводка MVP

| SKU | Slug | Цвет | Тег | 250g | 1kg |
|-----|------|------|-----|------|-----|
| 1 | `ethiopia-sidama-natural` | Orange `#FF6A00` | funky | 62 PLN | 220 PLN |
| 2 | `colombia-huila-washed` | Yellow `#FFE600` | everyday | 49 PLN | 175 PLN |
| 3 | `brazil-cerrado-espresso` | Peach `#FFCBA8` | espresso | 52 PLN | 185 PLN |
| 4 | `guatemala-huehuetenango` | Yellow `#FFE600` | everyday | 51 PLN | 180 PLN |
| 5 | `rwanda-nyamasheke` | Magenta `#FF2D9A` | funky | 65 PLN | 230 PLN |

*Lime `#B8F500` — резерв под следующий лот (напр. Ethiopia Guji).*

---

## SKU 1 — Ethiopia Sidama Natural

| Поле | Значение |
|------|----------|
| **Название PL** | Etiopia Sidama Natural |
| **Название EN** | Ethiopia Sidama Natural |
| **accent_color** | `#FF6A00` |
| **Тег** | funky |
| **Страна / регион** | Ethiopia · Sidama |
| **OBRÓBKA** | Naturalna |
| **SELEKCJA** | 74158 |
| **ROAST** | Filter |
| **WYSOKOŚĆ** | 2050 m n.p.m. |
| **NUTKI** | Herbata / Cytrusy / Kwiaty |
| **Краткое описание** | Jasny, kwiatowy lot z Etiopii — czysta filiżanka z nutą herbaty i cytrusów. |
| **Brew** | V60 1:16, 93°C, 2:45–3:15 |
| **Помол** | whole, v60, aeropress, french_press |

---

## SKU 2 — Colombia Huila Washed

| Поле | Значение |
|------|----------|
| **Название PL** | Kolumbia Huila Washed |
| **accent_color** | `#FFE600` |
| **Тег** | everyday |
| **Страна / регион** | Colombia · Huila |
| **OBRÓBKA** | Myta |
| **SELEKCJA** | Castillo / Caturra |
| **ROAST** | Filter |
| **WYSOKOŚĆ** | 1650 m n.p.m. |
| **NUTKI** | Czekolada / Orzech / Czerwone owoce |
| **Краткое описание** | Zbalansowana, codzienna filiżanka — słodycz i orzech z lekką owocową kwasowością. |
| **Brew** | V60 1:15, 92°C, 2:30–3:00 |
| **Помол** | whole, v60, aeropress, moka |

---

## SKU 3 — Brazil Cerrado Espresso

| Поле | Значение |
|------|----------|
| **Название PL** | Brazylia Cerrado Espresso |
| **accent_color** | `#FFCBA8` |
| **Тег** | espresso |
| **Страна / регион** | Brazil · Cerrado |
| **OBRÓBKA** | Myta |
| **SELEKCJA** | Mundo Novo / Catuai |
| **ROAST** | Espresso |
| **WYSOKOŚĆ** | 1100 m n.p.m. |
| **NUTKI** | Czekolada / Karmel / Orzech |
| **Краткое описание** | Słodkie espresso z pełnym body — stabilne w koszu, dobre z mlekiem i bez. |
| **Brew** | 18 g / 36 g, 25–30 s, 93°C |
| **Помол** | whole, espresso, moka |

---

## SKU 4 — Guatemala Huehuetenango

| Поле | Значение |
|------|----------|
| **Название PL** | Gwatemala Huehuetenango |
| **accent_color** | `#FFE600` |
| **Тег** | everyday |
| **Страна / регион** | Guatemala · Huehuetenango |
| **OBRÓBKA** | Myta |
| **SELEKCJA** | Bourbon |
| **ROAST** | Omni |
| **WYSOKOŚĆ** | 1800 m n.p.m. |
| **NUTKI** | Czekolada mleczna / Jabłko / Karmel |
| **Краткое описание** | Przyjazny omni-roast — rano filtr, po południu espresso. |
| **Brew** | V60 1:15 lub espresso 1:2 |
| **Помол** | whole, v60, espresso, aeropress |

---

## SKU 5 — Rwanda Nyamasheke

| Поле | Значение |
|------|----------|
| **Название PL** | Rwanda Nyamasheke |
| **accent_color** | `#FF2D9A` |
| **Тег** | funky |
| **Страна / регион** | Rwanda · Nyamasheke |
| **OBRÓBKA** | Myta |
| **SELEKCJA** | Red Bourbon |
| **ROAST** | Filter |
| **WYSOKOŚĆ** | 1900 m n.p.m. |
| **NUTKI** | Czerwona porzeczka / Brzoskwinia / Herbata |
| **Краткое описание** | Soczysta kwasowość i owocowa słodycz — dla tych, którzy szukają wyrazistego profilu. |
| **Brew** | AeroPress 1:14, 90°C, 1:45 |
| **Помол** | whole, aeropress, v60 |

---

## WooCommerce — поля ACF (маппинг)

| ACF key | Тип | Пример |
|---------|-----|--------|
| `lot_accent_color` | color picker | `#FF6A00` |
| `lot_tag` | select | everyday / espresso / funky |
| `origin_country` | text | Ethiopia |
| `origin_region` | text | Sidama |
| `processing` | text | Naturalna |
| `variety` | text | 74158 |
| `roast_type` | text | Filter |
| `altitude` | text | 2050 m n.p.m. |
| `tasting_notes` | text | Herbata / Cytrusy / Kwiaty |
| `brew_guide` | textarea | V60 1:16… |
| `roast_date` | date | динамически |

## Чеклист перед публикацией

- [ ] Cupping → финальные NUTKI
- [ ] Фото пачки (цвет = accent)
- [ ] Дата обжарки на каждом SKU
- [ ] Подтверждение цен PLN
- [ ] Остатки на складе

## Связанные документы

- [10-packaging-system.md](./10-packaging-system.md)
- [04-product-architecture.md](./04-product-architecture.md)
