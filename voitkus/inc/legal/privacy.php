<?php
/**
 * Polityka prywatności (RODO).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return array<int, array{title: string, html: string}>
 */
function voitkus_privacy_sections(): array
{
    $c        = voitkus_company_details();
    $terms    = home_url('/legal/terms/');
    $shipping = home_url('/legal/shipping/');
    $email    = antispambot($c['email']);
    $address  = esc_html(voitkus_company_address_line());

    return [
        [
            'title' => __('1. Administrator danych', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><ul class="legal-document__list legal-document__list--plain">
                    <li><strong>%2$s:</strong> %3$s</li>
                    <li><strong>%4$s:</strong> %5$s</li>
                    <li><strong>%6$s:</strong> %7$s</li>
                    <li><strong>%8$s:</strong> %9$s</li>
                    <li><strong>%10$s:</strong> <a href="mailto:%11$s">%12$s</a></li>
                    <li><strong>%13$s:</strong> <a href="tel:%14$s">%15$s</a></li>
                </ul>',
                esc_html__(
                    'Administratorem Twoich danych osobowych w związku ze sklepem internetowym Voitkus jest:',
                    'voitkus'
                ),
                esc_html__('Nazwa', 'voitkus'),
                esc_html($c['legal_name']),
                esc_html__('NIP', 'voitkus'),
                esc_html($c['nip']),
                esc_html__('REGON', 'voitkus'),
                esc_html($c['regon']),
                esc_html__('Adres', 'voitkus'),
                $address,
                esc_html__('E-mail', 'voitkus'),
                esc_attr($c['email']),
                $email,
                esc_html__('Telefon', 'voitkus'),
                esc_attr(voitkus_company_phone_tel()),
                esc_html($c['phone'])
            ),
        ],
        [
            'title' => __('2. Cele i podstawy prawne', 'voitkus'),
            'html'  => sprintf(
                '<p>%s</p><ul class="legal-document__list">
                    <li><strong>%s</strong> — %s</li>
                    <li><strong>%s</strong> — %s</li>
                    <li><strong>%s</strong> — %s</li>
                    <li><strong>%s</strong> — %s</li>
                    <li><strong>%s</strong> — %s</li>
                </ul>',
                esc_html__('Przetwarzamy dane w następujących celach:', 'voitkus'),
                esc_html__('Realizacja zamówienia', 'voitkus'),
                esc_html__('art. 6 ust. 1 lit. b RODO (umowa)', 'voitkus'),
                esc_html__('Obsługa płatności', 'voitkus'),
                esc_html__('art. 6 ust. 1 lit. b RODO (umowa)', 'voitkus'),
                esc_html__('Dostawa', 'voitkus'),
                esc_html__('art. 6 ust. 1 lit. b RODO (umowa)', 'voitkus'),
                esc_html__('Kontakt i reklamacje', 'voitkus'),
                esc_html__('art. 6 ust. 1 lit. b RODO (umowa) lub lit. f (prawnie uzasadniony interes)', 'voitkus'),
                esc_html__('Obowiązki prawne', 'voitkus'),
                esc_html__('art. 6 ust. 1 lit. c RODO (np. rachunkowość, podatki)', 'voitkus')
            ),
        ],
        [
            'title' => __('3. Jakie dane zbieramy', 'voitkus'),
            'html'  => sprintf(
                '<p>%s</p><ul class="legal-document__list">
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                </ul>',
                esc_html__('W zależności od korzystania ze sklepu możemy przetwarzać m.in.:', 'voitkus'),
                esc_html__('imię, nazwisko, adres e-mail, telefon', 'voitkus'),
                esc_html__('adres dostawy / rozliczeniowy, wybrany paczkomat InPost', 'voitkus'),
                esc_html__('dane zamówienia (produkty, kwoty, historia)', 'voitkus'),
                esc_html__('dane techniczne: adres IP, logi serwera, pliki cookies', 'voitkus'),
                esc_html__('dane przekazywane operatorowi płatności (bez przechowywania pełnych danych karty u nas)', 'voitkus')
            ),
        ],
        [
            'title' => __('4. Odbiorcy danych', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><ul class="legal-document__list">
                    <li>%2$s</li>
                    <li>%3$s</li>
                    <li>%4$s</li>
                    <li>%5$s</li>
                </ul><p>%6$s</p>',
                esc_html__(
                    'Dane mogą być przekazywane podmiotom wspierającym naszą działalność, wyłącznie w zakresie niezbędnym:',
                    'voitkus'
                ),
                esc_html__('operatorom płatności (np. Stripe, Przelewy24)', 'voitkus'),
                esc_html__('firmie kurierskiej / InPost (realizacja dostawy)', 'voitkus'),
                esc_html__('dostawcy hostingu i poczty e-mail sklepu', 'voitkus'),
                esc_html__(
                    'dostawcom narzędzi IT (np. system sklepu WooCommerce), pod umową powierzenia gdy wymagane',
                    'voitkus'
                ),
                esc_html__(
                    'Niektórzy dostawcy mogą przetwarzać dane poza EOG — wtedy stosowane są standardowe mechanizmy prawne (np. klauzule umowne UE).',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('5. Okres przechowywania', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                esc_html__(
                    'Dane z zamówienia przechowujemy przez czas realizacji umowy oraz przez okres wymagany przepisami (np. rachunkowość — do 5 lat od końca roku podatkowego).',
                    'voitkus'
                ),
                esc_html__(
                    'Dane konta klienta (jeśli zakładasz konto) — do czasu usunięcia konta lub żądania usunięcia, z zastrzeżeniem obowiązków prawnych.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('6. Twoje prawa', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><ul class="legal-document__list">
                    <li>%2$s</li>
                    <li>%3$s</li>
                    <li>%4$s</li>
                    <li>%5$s</li>
                    <li>%6$s</li>
                    <li>%7$s</li>
                    <li>%8$s</li>
                </ul><p>%9$s</p>',
                esc_html__('Przysługują Ci m.in. prawa:', 'voitkus'),
                esc_html__('dostępu do danych i otrzymania kopii', 'voitkus'),
                esc_html__('sprostowania (poprawienia)', 'voitkus'),
                esc_html__('usunięcia („prawo do bycia zapomnianym”)', 'voitkus'),
                esc_html__('ograniczenia przetwarzania', 'voitkus'),
                esc_html__('przenoszenia danych', 'voitkus'),
                esc_html__('wniesienia sprzeciwu wobec przetwarzania na podstawie uzasadnionego interesu', 'voitkus'),
                esc_html__('cofnięcia zgody — gdy przetwarzanie opiera się na zgodzie', 'voitkus'),
                sprintf(
                    /* translators: %s: contact email */
                    esc_html__(
                        'Wnioski realizujemy na adres %s. Odpowiadamy bez zbędnej zwłoki, najpóźniej w ciągu miesiąca.',
                        'voitkus'
                    ),
                    $email
                ),
                esc_html__(
                    'Masz prawo złożyć skargę do Prezesa Urzędu Ochrony Danych Osobowych (UODO), ul. Stawki 2, 00-193 Warszawa.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('7. Pliki cookies', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s <a href="%2$s">%3$s</a>.</p><p>%4$s</p>',
                esc_html__(
                    'Szczegółowe zasady dotyczą plików cookies opisuje',
                    'voitkus'
                ),
                esc_url(home_url('/legal/cookies/')),
                esc_html__('Polityka cookies', 'voitkus'),
                esc_html__(
                    'Możesz zarządzać cookies w ustawieniach przeglądarki. Wyłączenie cookies niezbędnych może uniemożliwić złożenie zamówienia.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('8. Dobrowolność podania danych', 'voitkus'),
            'html'  => sprintf(
                '<p>%s</p>',
                esc_html__(
                    'Podanie danych przy zamówieniu jest dobrowolne, ale niezbędne do jego realizacji. Brak danych uniemożliwi dostawę i kontakt w sprawie zamówienia.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('9. Zmiany i inne dokumenty', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s <a href="%3$s">%4$s</a>, %5$s <a href="%6$s">%7$s</a>.</p><p><em>%8$s</em></p>',
                esc_html__(
                    'Polityka może być aktualizowana. Aktualna wersja jest zawsze na tej stronie.',
                    'voitkus'
                ),
                esc_html__('Zobacz też:', 'voitkus'),
                esc_url($terms),
                esc_html__('Regulamin', 'voitkus'),
                esc_html__('oraz', 'voitkus'),
                esc_url($shipping),
                esc_html__('Dostawa i płatność', 'voitkus'),
                sprintf(
                    /* translators: %s: date */
                    esc_html__('Polityka obowiązuje od %s.', 'voitkus'),
                    esc_html($c['activity_start'])
                )
            ),
        ],
    ];
}

function voitkus_render_privacy_policy(): string
{
    $sections = voitkus_privacy_sections();

    ob_start();
    ?>
    <article class="legal-document legal-document--privacy">
        <p class="legal-document__lead">
            <?php esc_html_e('Polityka prywatności sklepu Voitkus Coffee (RODO). Informujemy, kto i jak przetwarza Twoje dane.', 'voitkus'); ?>
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

function voitkus_privacy_shortcode(): string
{
    return voitkus_render_privacy_policy();
}
