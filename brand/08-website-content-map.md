# 08. Карта контента сайта (MVP WooCommerce)

## Sitemap

```
/                          Главная
/shop                      Каталог (все лоты)
/shop/?filter=everyday     Категория/тег
/shop/?filter=espresso
/shop/?filter=funky
/product/{slug}            Карточка товара
/subscription              Подписка (фаза 2 — опционально на MVP)
/b2b                       Для кафе и офисов
/about                     О нас (brand story)
/brew-guides               Как заваривать (фаза 1.5)
/contact                   Контакты + форма
/cart, /checkout           WooCommerce
/legal/privacy             RODO
/legal/terms               Регламент
/legal/shipping            Доставка и оплата
```

## Главная — блоки

| # | Блок | Контент | Источник |
|---|------|---------|----------|
| 1 | Hero | Заголовок + CTA | 05-messaging |
| 2 | Три профиля | Everyday / Espresso / Funky | 04-product |
| 3 | Актуальные лоты | 4–5 карточек WooCommerce | Каталог |
| 4 | Почему Voitkus | 3 колонки | 05-messaging |
| 5 | Помол и свежесть | Иконки методов + дата обжарки | 04-product |
| 6 | B2B тизер | Кафе и офисы | 05-messaging |
| 7 | О нас (коротко) | 2–3 абзаца + фото | 06-brand-story |
| 8 | Instagram / отзывы | Feed или 6 постов | TBD |
| 9 | Footer | Меню, соцсети, legal | — |

## Карточка товара — блоки

1. Галерея  
2. Название + цена (250 / 1 кг)  
3. Вариации: вес, помол  
4. Вкусовой профиль (ноты)  
5. Происхождение (таблица)  
6. Дата обжарки  
7. Brew guide (кратко)  
8. «Лот ограничен» если мало  
9. Related / «также попробуйте»  

## Страница B2B

- Текст предложения  
- Форма: имя, компания, email, телефон, тип (кафе/офис), комментарий  
- SLA ответа (например: 24–48 ч)  

## WooCommerce — настройки (чеклист dev)

- [ ] Вариативные товары: вес × помол  
- [ ] Атрибуты: страна, обработка, тег (everyday/espresso/funky)  
- [ ] Поле «дата обжарки» (ACF или plugin)  
- [ ] Низкий остаток → badge  
- [ ] Платежи: *TBD* (Przelewy24, Stripe, BLIK…)  
- [ ] Доставка: *TBD* (InPost, DPD…)  
- [ ] Язык: *TBD* (PL приоритет?)  
- [ ] RODO / cookies  

## Контент до запуска (минимум)

| Элемент | Кол-во |
|---------|--------|
| Товары с полными карточками | 4–5 |
| Фото лотов | 4–5 × 3 ракурса |
| О нас | 1 страница + фото основателя |
| Legal | 3 страницы (шаблон + правки юристом) |
| Favicon + OG | 1 комплект |

## Фазы

| Фаза | Scope |
|------|-------|
| **MVP** | Главная, shop, product, cart, checkout, about, contact, legal, B2B форма |
| **1.5** | Brew guides, блог, отзывы |
| **2** | Подписка WooCommerce Subscriptions, мультиязык |

## Связанные документы

- [04-product-architecture.md](./04-product-architecture.md)
- [09-open-questions.md](./09-open-questions.md)
