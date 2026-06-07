<?php
/**
 * Polityka cookies — osobny dokument (sklep Voitkus).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return array<int, array{title: string, html: string}>
 */
function voitkus_cookies_sections(): array
{
    $c        = voitkus_company_details();
    $privacy  = home_url('/legal/privacy/');
    $terms    = home_url('/legal/terms/');
    $email    = antispambot($c['email']);

    return [
        [
            'title' => __('1. Czym są pliki cookies', 'voitkus'),
            'html'  => sprintf(
                '<p>%s</p>',
                esc_html__(
                    'Pliki cookies to niewielkie pliki tekstowe zapisywane w Twojej przeglądarce lub urządzeniu. Umożliwiają m.in. działanie koszyka, zapamiętanie sesji oraz — za Twoją zgodą — statystyki odwiedzin.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('2. Cookies niezbędne', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><ul class="legal-document__list">
                    <li><strong>%2$s</strong> — %3$s</li>
                    <li><strong>%4$s</strong> — %5$s</li>
                    <li><strong>%6$s</strong> — %7$s</li>
                </ul>',
                esc_html__('Te pliki są konieczne do działania sklepu i nie wymagają osobnej zgody:', 'voitkus'),
                esc_html__('Sesja sklepu', 'voitkus'),
                esc_html__('utrzymanie koszyka i logowania podczas przeglądania', 'voitkus'),
                esc_html__('Bezpieczeństwo', 'voitkus'),
                esc_html__('ochrona przed nadużyciami i błędami formularzy', 'voitkus'),
                esc_html__('Preferencje checkout', 'voitkus'),
                esc_html__('zapamiętanie wyborów niezbędnych do złożenia zamówienia', 'voitkus')
            ),
        ],
        [
            'title' => __('3. Cookies opcjonalne', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                esc_html__(
                    'Cookies analityczne lub marketingowe (np. Google Analytics, Meta Pixel) — tylko jeśli je włączymy. W takim przypadku poprosimy o zgodę w banerze cookies przed ich zapisaniem.',
                    'voitkus'
                ),
                esc_html__(
                    'Jeśli baner cookies nie jest widoczny, oznacza to, że sklep korzysta wyłącznie z cookies niezbędnych opisanych powyżej.',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('4. Zarządzanie cookies', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s</p><p>%2$s</p>',
                esc_html__(
                    'Możesz w każdej chwili usunąć lub zablokować cookies w ustawieniach przeglądarki. Wyłączenie cookies niezbędnych może uniemożliwić dodanie produktów do koszyka lub złożenie zamówienia.',
                    'voitkus'
                ),
                esc_html__(
                    'Instrukcje zarządzania cookies znajdziesz w pomocy producenta Twojej przeglądarki (Chrome, Safari, Firefox itd.).',
                    'voitkus'
                )
            ),
        ],
        [
            'title' => __('5. Administrator i kontakt', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s <strong>%2$s</strong>.</p><p>%3$s <a href="mailto:%4$s">%5$s</a>.</p>',
                esc_html__('Administratorem cookies związanych ze sklepem jest', 'voitkus'),
                esc_html($c['legal_name']),
                esc_html__('Pytania dotyczące cookies i danych osobowych:', 'voitkus'),
                esc_attr($c['email']),
                $email
            ),
        ],
        [
            'title' => __('6. Powiązane dokumenty', 'voitkus'),
            'html'  => sprintf(
                '<p>%1$s <a href="%2$s">%3$s</a> %4$s <a href="%5$s">%6$s</a>.</p><p><em>%7$s</em></p>',
                esc_html__('Zobacz też:', 'voitkus'),
                esc_url($privacy),
                esc_html__('Polityka prywatności (RODO)', 'voitkus'),
                esc_html__('oraz', 'voitkus'),
                esc_url($terms),
                esc_html__('Regulamin sklepu', 'voitkus'),
                sprintf(
                    /* translators: %s: date */
                    esc_html__('Polityka cookies obowiązuje od %s.', 'voitkus'),
                    esc_html($c['activity_start'])
                )
            ),
        ],
    ];
}

function voitkus_render_cookies_policy(): string
{
    $sections = voitkus_cookies_sections();

    ob_start();
    ?>
    <article class="legal-document legal-document--cookies">
        <p class="legal-document__lead">
            <?php esc_html_e('Polityka cookies sklepu internetowego Voitkus Coffee.', 'voitkus'); ?>
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

function voitkus_cookies_shortcode(): string
{
    return voitkus_render_cookies_policy();
}
