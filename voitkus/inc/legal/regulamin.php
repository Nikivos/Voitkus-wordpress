<?php
/**
 * Regulamin sklepu Voitkus (treść prawna PL).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return array<int, array{title: string, html: string}>
 */
function voitkus_regulamin_sections(): array
{
    $c       = voitkus_company_details();
    $privacy    = home_url('/legal/privacy/');
    $shipping   = home_url('/legal/shipping/');
    $withdrawal = home_url('/legal/withdrawal/');
    $shop       = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
    $email   = antispambot($c['email']);
    $address = esc_html(voitkus_company_address_line());

    return [
        [
            'title' => __('1. Postanowienia ogólne', 'voitkus'),
            'html'  => sprintf(
                '<p>%s</p><p>%s</p>',
                esc_html__(
                    'Regulamin określa zasady korzystania ze sklepu internetowego Voitkus oraz zasady zawierania umów sprzedaży na odległość.',
                    'voitkus'
                ),
                esc_html__(
                    'Sklep prowadzony jest na terytorium Rzeczypospolitej Polskiej. Regulamin jest udostępniany nieodpłatnie przed złożeniem zamówienia i w każdej chwili na stronie sklepu.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('2. Dane Sprzedawcy', 'voitkus'),
            'html'  => sprintf(
                '<ul class="legal-document__list legal-document__list--plain">
                    <li><strong>%1$s:</strong> %2$s</li>
                    <li><strong>%3$s:</strong> %4$s</li>
                    <li><strong>%5$s:</strong> %6$s</li>
                    <li><strong>%7$s:</strong> %8$s</li>
                    <li><strong>%9$s:</strong> %10$s</li>
                    <li><strong>%11$s:</strong> <a href="mailto:%12$s">%13$s</a></li>
                    <li><strong>%14$s:</strong> <a href="tel:%15$s">%16$s</a></li>
                </ul>',
                esc_html__('Nazwa', 'voitkus'),
                esc_html($c['legal_name']),
                esc_html__('NIP', 'voitkus'),
                esc_html($c['nip']),
                esc_html__('REGON', 'voitkus'),
                esc_html($c['regon']),
                esc_html__('Adres', 'voitkus'),
                $address,
                esc_html__('Forma prawna', 'voitkus'),
                esc_html__('jednoosobowa działalność gospodarcza', 'voitkus'),
                esc_html__('E-mail', 'voitkus'),
                esc_attr($c['email']),
                $email,
                esc_html__('Telefon', 'voitkus'),
                esc_attr(voitkus_company_phone_tel()),
                esc_html($c['phone'])
            ),
        ],
        [
            'title' => __('3. Produkty i ceny', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                esc_html__(
                    'Sklep oferuje świeżo paloną kawę oraz produkty towarzyszące. Ceny podane na stronie sklepu są cenami brutto (zawierają podatek VAT) wyrażonymi w złotych polskich (PLN), o ile nie wskazano inaczej.',
                    'voitkus'
                ),
                esc_html__(
                    'Informacje o produkcie (pochodzenie, profil smakowy, waga, wariant mielenia) podawane są na stronie produktu. Zdjęcia mają charakter poglądowy.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('4. Składanie zamówienia', 'voitkus'),
            'html'  => sprintf(
                '<ol class="legal-document__list">
                    <li>%1$s</li>
                    <li>%2$s</li>
                    <li>%3$s</li>
                    <li>%4$s</li>
                    <li>%5$s</li>
                </ol>',
                esc_html__('Klient wybiera produkty w sklepie i dodaje je do koszyka.', 'voitkus'),
                esc_html__('W koszyku podaje adres dostawy, wybiera metodę wysyłki i przechodzi do kasy.', 'voitkus'),
                esc_html__('Na stronie kasy uzupełnia dane do zamówienia, akceptuje Regulamin oraz wybiera metodę płatności.', 'voitkus'),
                esc_html__('Złożenie zamówienia następuje po kliknięciu przycisku potwierdzającego zakup i pomyślnym złożeniu zamówienia.', 'voitkus'),
                esc_html__(
                    'Umowa sprzedaży zostaje zawarta z chwilą otrzymania przez Klienta wiadomości e-mail z potwierdzeniem przyjęcia zamówienia.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('5. Płatności', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                esc_html__(
                    'Dostępne metody płatności (np. karta płatnicza, Przelewy24, BLIK — w zależności od konfiguracji sklepu) są wskazane przy składaniu zamówienia.',
                    'voitkus'
                ),
                esc_html__(
                    'Realizacja zamówienia rozpoczyna się po zaksięgowaniu płatności, chyba że wybrana metoda przewiduje odroczoną płatność zgodnie z jej zasadami.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('6. Dostawa', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s <a href="%2$s">%3$s</a>.</p><p>%4$s</p>',
                esc_html__('Szczegółowe informacje o kosztach i terminach dostawy znajdują się na stronie', 'voitkus'),
                esc_url($shipping),
                esc_html__('Dostawa i płatność', 'voitkus'),
                esc_html__(
                    'Dostawa realizowana jest na terytorium Polski, w szczególności poprzez Paczkomaty InPost lub inne metody dostępne w koszyku. Klient wybiera punkt odbioru lub adres dostawy zgodnie z dostępnymi opcjami.',
                    'voitkus'
                )
            ),
        ],
        [
            'id'    => 'prawo-odstapienia',
            'title' => __('7. Prawo odstąpienia od umowy', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p>
                <p>%2$s</p>
                <p>%3$s</p>
                <p>%4$s</p>
                <p>%5$s</p>
                <p>%6$s <a href="%7$s">%8$s</a>.</p>
                <p>%9$s</p>',
                esc_html__(
                    'Konsument, który zawarł umowę na odległość, może od niej odstąpić bez podania przyczyny w terminie 14 dni od dnia objęcia rzeczy w posiadanie.',
                    'voitkus'
                ),
                esc_html__(
                    'Prawo odstąpienia od umowy nie przysługuje w przypadku produktów dostarczanych w zapieczętowanym opakowaniu, które po otwarciu nie nadają się do zwrotu ze względu na ochronę zdrowia lub względy higieniczne — w szczególności dotyczy to świeżo palonej kawy w opakowaniu, którego plomba lub zabezpieczenie zostało naruszone (art. 38 pkt 5 ustawy o prawach konsumenta).',
                    'voitkus'
                ),
                esc_html__(
                    'Aby skorzystać z prawa odstąpienia (gdy przysługuje), należy przesłać jednoznaczne oświadczenie na adres Sprzedawcy — np. e-mailem lub listownie.',
                    'voitkus'
                ),
                esc_html__(
                    'Zwrot produktu powinien nastąpić niezwłocznie, nie później niż w 14 dni od wysłania oświadczenia o odstąpieniu. Koszt odesłania towaru ponosi Klient.',
                    'voitkus'
                ),
                esc_html__(
                    'Sprzedawca zwróci Klientowi wszystkie otrzymane płatności, w tym koszt dostawy w wysokości odpowiadającej najtańszej oferowanej przez Sprzedawcę metodzie dostawy, nie później niż w terminie 14 dni od dnia otrzymania oświadczenia o odstąpieniu od umowy. Zwrot nastąpi tym samym sposobem płatności, chyba że Klient wyraźnie zgodzi się na inny sposób.',
                    'voitkus'
                ),
                esc_html__('Wzór formularza odstąpienia:', 'voitkus'),
                esc_url($withdrawal),
                esc_html__('Formularz odstąpienia od umowy', 'voitkus'),
                sprintf(
                    /* translators: %s: shop email */
                    esc_html__('Kontakt w sprawie odstąpienia: %s.', 'voitkus'),
                    $email
                )
            ),
        ],
        [
            'id'    => 'reklamacje',
            'title' => __('8. Reklamacje', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p>
                <p>%2$s</p>
                <ul class="legal-document__list">
                    <li>%3$s <a href="mailto:%4$s">%5$s</a></li>
                    <li>%6$s %7$s</li>
                </ul>
                <p>%8$s</p>
                <p>%9$s</p>',
                esc_html__(
                    'Sprzedawca odpowiada wobec Klienta będącego Konsumentem z tytułu rękojmi za wady zgodnie z przepisami Kodeksu cywilnego.',
                    'voitkus'
                ),
                esc_html__('Reklamacje można składać:', 'voitkus'),
                esc_html__('drogą elektroniczną na adres:', 'voitkus'),
                esc_attr($c['email']),
                $email,
                esc_html__('listownie na adres:', 'voitkus'),
                sprintf('%s, %s', esc_html($c['legal_name']), $address),
                esc_html__(
                    'W zgłoszeniu reklamacyjnym należy podać co najmniej: numer zamówienia, opis wady, datę jej stwierdzenia oraz oczekiwany sposób rozpatrzenia (np. wymiana, obniżenie ceny, zwrot).',
                    'voitkus'
                ),
                esc_html__(
                    'Sprzedawca rozpatrzy reklamację w terminie 14 dni od jej otrzymania i poinformuje Klienta o sposobie rozpatrzenia drogą elektroniczną na adres e-mail wskazany w zamówieniu lub w zgłoszeniu.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('9. Ochrona danych osobowych', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s <a href="%2$s">%3$s</a>.</p>',
                esc_html__('Zasady przetwarzania danych osobowych opisuje', 'voitkus'),
                esc_url($privacy),
                esc_html__('Polityka prywatności (RODO)', 'voitkus')
            ),
        ],
        [
            'title' => __('10. Postanowienia końcowe', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p><p><em>%3$s</em></p>',
                esc_html__(
                    'W sprawach nieuregulowanych Regulaminem stosuje się przepisy prawa polskiego, w szczególności Kodeksu cywilnego oraz ustawy o prawach konsumenta.',
                    'voitkus'
                ),
                esc_html__(
                    'Sprzedawca zastrzega sobie prawo do zmiany Regulaminu. Zmiany obowiązują od momentu opublikowania na stronie sklepu; do zamówień złożonych przed zmianą stosuje się wersję obowiązującą w chwili składania zamówienia.',
                    'voitkus'
                ),
                sprintf(
                    /* translators: 1: date, 2: shop url */
                    esc_html__('Regulamin obowiązuje od %1$s. Sklep: %2$s', 'voitkus'),
                    esc_html($c['activity_start']),
                    esc_url($shop)
                )
            ),
        ],
    ];
}

function voitkus_render_regulamin(): string
{
    $sections = voitkus_regulamin_sections();

    ob_start();
    ?>
    <article class="legal-document legal-document--regulamin">
        <p class="legal-document__lead">
            <?php esc_html_e('Regulamin sklepu internetowego Voitkus Coffee. Dokument obowiązuje przy zakupach online.', 'voitkus'); ?>
        </p>
        <?php foreach ($sections as $section) : ?>
            <?php
            $section_id = isset($section['id']) ? (string) $section['id'] : '';
            ?>
            <section class="legal-document__section"<?php echo $section_id !== '' ? ' id="' . esc_attr($section_id) . '"' : ''; ?>>
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

function voitkus_regulamin_shortcode(): string
{
    return voitkus_render_regulamin();
}
