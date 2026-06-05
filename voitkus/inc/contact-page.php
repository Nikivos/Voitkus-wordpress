<?php
/**
 * Strona Kontakt.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_render_contact_page(): string
{
    $c         = voitkus_company_details();
    $footer    = function_exists('voitkus_footer_section') ? voitkus_footer_section() : [];
    $instagram = $footer['instagram'] ?? 'https://instagram.com/voitkuscoffee';
    $email     = $c['email'];
    $maps_url  = sprintf(
        'https://www.google.com/maps/search/?api=1&query=%s',
        rawurlencode($c['street'] . ', ' . $c['postcode'] . ' ' . $c['city'])
    );

    ob_start();
    ?>
    <div class="contact-page__content">
        <p class="contact-page__lead">
            <?php esc_html_e('Masz pytanie o kawę, zamówienie lub współpracę B2B? Napisz — odpowiadamy zwykle w ciągu 1–2 dni roboczych.', 'voitkus'); ?>
        </p>

        <div class="contact-page__grid">
            <article class="contact-card">
                <h2 class="contact-card__title"><?php esc_html_e('E-mail', 'voitkus'); ?></h2>
                <p class="contact-card__value">
                    <a href="<?php echo esc_url('mailto:' . $email); ?>"><?php echo esc_html($email); ?></a>
                </p>
                <p class="contact-card__hint">
                    <?php esc_html_e('Zamówienia, reklamacje, pytania o produkty.', 'voitkus'); ?>
                </p>
            </article>

            <article class="contact-card">
                <h2 class="contact-card__title"><?php esc_html_e('Instagram', 'voitkus'); ?></h2>
                <p class="contact-card__value">
                    <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer">
                        @voitkuscoffee <span aria-hidden="true">↗</span>
                    </a>
                </p>
                <p class="contact-card__hint">
                    <?php esc_html_e('Nowości z palarni i zapowiedzi lotów.', 'voitkus'); ?>
                </p>
            </article>

            <article class="contact-card">
                <h2 class="contact-card__title"><?php esc_html_e('Adres', 'voitkus'); ?></h2>
                <p class="contact-card__value">
                    <?php echo esc_html($c['legal_name']); ?><br>
                    <?php echo esc_html(voitkus_company_address_line()); ?>
                </p>
                <p class="contact-card__hint">
                    <a href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Pokaż na mapie', 'voitkus'); ?> <span aria-hidden="true">↗</span>
                    </a>
                </p>
            </article>

            <article class="contact-card">
                <h2 class="contact-card__title"><?php esc_html_e('Dane firmy', 'voitkus'); ?></h2>
                <p class="contact-card__value">
                    <?php esc_html_e('NIP', 'voitkus'); ?>: <?php echo esc_html($c['nip']); ?>
                </p>
                <p class="contact-card__hint">
                    <a href="<?php echo esc_url(home_url('/legal/terms/')); ?>"><?php esc_html_e('Regulamin', 'voitkus'); ?></a>
                    ·
                    <a href="<?php echo esc_url(home_url('/legal/privacy/')); ?>"><?php esc_html_e('RODO', 'voitkus'); ?></a>
                </p>
            </article>
        </div>

        <section class="contact-page__cta">
            <h2 class="contact-page__cta-title"><?php esc_html_e('Kawa dla kawiarni lub biura?', 'voitkus'); ?></h2>
            <p class="contact-page__cta-text">
                <?php esc_html_e('Zapytania hurtowe i stałe dostawy — przejdź do sekcji B2B.', 'voitkus'); ?>
            </p>
            <a class="contact-page__btn" href="<?php echo esc_url(home_url('/b2b/')); ?>">
                <?php esc_html_e('Oferta B2B', 'voitkus'); ?>
            </a>
        </section>
    </div>
    <?php

    return (string) ob_get_clean();
}

function voitkus_contact_shortcode(): string
{
    return voitkus_render_contact_page();
}
