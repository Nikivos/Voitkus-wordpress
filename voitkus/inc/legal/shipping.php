<?php
/**
 * Dostawa i płatność — treść informacyjna PL.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return array<int, array{title: string, html: string}>
 */
function voitkus_shipping_info_sections(): array
{
    $c      = voitkus_company_details();
    $terms  = home_url('/legal/terms/');
    $cart   = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
    $email  = antispambot($c['email']);

    return [
        [
            'title' => __('1. Obszar dostawy', 'voitkus'),
            'html'  => sprintf(
                '<p>%s</p>',
                esc_html__(
                    'Wysyłamy zamówienia na terenie Polski. Koszt i dostępność metod dostawy są widoczne w koszyku po podaniu kodu pocztowego i wyborze metody wysyłki.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('2. Metody dostawy', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><ul class="legal-document__list">
                    <li><strong>%2$s</strong> — %3$s</li>
                </ul><p>%4$s</p>',
                esc_html__(
                    'Aktualnie w sklepie dostępna jest m.in. dostawa do Paczkomatów InPost. Lista metod zależy od konfiguracji sklepu i adresu dostawy.',
                    'voitkus'
                ),
                esc_html__('InPost Paczkomat', 'voitkus'),
                esc_html__(
                    'po wyborze tej metody w koszyku należy wskazać paczkomat na mapie (przycisk w panelu dostawy). Zamówienie z InPost bez wybranego paczkomatu nie zostanie zrealizowane.',
                    'voitkus'
                ),
                esc_html__(
                    'Po złożeniu zamówienia paczka trafia do wybranego punktu. Odbiór możliwy po otrzymaniu powiadomienia od InPost (SMS/e-mail).',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('3. Koszty i czas realizacji', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
                esc_html__(
                    'Koszt dostawy jest liczony automatycznie w koszyku (przycisk „Oblicz koszty”) i dodawany do sumy zamówienia przed płatnością.',
                    'voitkus'
                ),
                esc_html__(
                    'Zamówienia opłacone w dni robocze zwykle przygotowujemy w ciągu 1–2 dni roboczych, a następnie przekazujemy przewoźnikowi. Czas doręczenia zależy od InPost i wybranej usługi.',
                    'voitkus'
                ),
                esc_html__(
                    'Przy produktach na zamówienie lub w okresach zwiększonego ruchu termin może być dłuższy — poinformujemy Cię e-mailem.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('4. Jak zamówić z dostawą', 'voitkus'),
            'html'  => sprintf(
                '<ol class="legal-document__list">
                    <li>%1$s</li>
                    <li>%2$s</li>
                    <li>%3$s</li>
                    <li>%4$s</li>
                    <li>%5$s</li>
                </ol>',
                esc_html__('Dodaj produkty do koszyka.', 'voitkus'),
                esc_html__('W koszyku podaj kod pocztowy i miejscowość, kliknij „Oblicz koszty”.', 'voitkus'),
                esc_html__('Wybierz metodę dostawy (np. InPost) i — jeśli wymagane — paczkomat na mapie.', 'voitkus'),
                sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($cart),
                    esc_html__('Przejdź do kasy', 'voitkus')
                ),
                esc_html__('Opłać zamówienie. Potwierdzenie otrzymasz e-mailem.', 'voitkus')
            ),
        ],
        [
            'title' => __('5. Płatności', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                esc_html__(
                    'Akceptujemy płatności online dostępne przy kasie, w tym m.in. karty płatnicze oraz szybkie przelewy (np. Przelewy24, BLIK) — zależnie od wybranej bramki płatności.',
                    'voitkus'
                ),
                esc_html__(
                    'Płatność jest bezpiecznie obsługiwana przez zewnętrznego operatora płatności. Nie przechowujemy pełnych danych karty na serwerze sklepu.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('6. Problemy z dostawą', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s <a href="%3$s">%4$s</a>.</p>',
                sprintf(
                    /* translators: %s: shop email */
                    esc_html__(
                        'Uszkodzenie paczki, brak przesyłki lub inne problemy — napisz na %s z numerem zamówienia. Odpowiemy w ciągu 2 dni roboczych.',
                        'voitkus'
                    ),
                    $email
                ),
                esc_html__('Reklamacje i prawo odstąpienia opisuje', 'voitkus'),
                esc_url($terms),
                esc_html__('Regulamin sklepu', 'voitkus')
            ),
        ],
    ];
}

function voitkus_render_shipping_info(): string
{
    $sections = voitkus_shipping_info_sections();

    ob_start();
    ?>
    <article class="legal-document legal-document--shipping">
        <p class="legal-document__lead">
            <?php esc_html_e('Informacje o wysyłce kawy Voitkus, Paczkomatach InPost i płatnościach online.', 'voitkus'); ?>
        </p>
        <?php foreach ($sections as $section) : ?>
            <section class="legal-document__section">
                <h2 class="legal-document__section-title"><?php echo esc_html($section['title']); ?></h2>
                <div class="legal-document__section-body">
                    <?php echo wp_kses_post($section['html']); ?>
                </div>
            </section>
        <?php endforeach; ?>
    </article>
    <?php

    return (string) ob_get_clean();
}

function voitkus_shipping_info_shortcode(): string
{
    return voitkus_render_shipping_info();
}
