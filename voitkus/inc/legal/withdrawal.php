<?php
/**
 * Formularz odstąpienia od umowy (wzór dla Konsumenta).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_render_withdrawal_form(): string
{
    $c       = voitkus_company_details();
    $terms   = home_url('/legal/terms/#prawo-odstapienia');
    $email   = $c['email'];
    $address = voitkus_company_address_line();

    ob_start();
    ?>
    <article class="legal-document legal-document--withdrawal">
        <p class="legal-document__lead">
            <?php esc_html_e('Wzór oświadczenia o odstąpieniu od umowy zawartej na odległość. Wypełnij, podpisz i wyślij na adres e-mail Sprzedawcy.', 'voitkus'); ?>
        </p>

        <section class="legal-document__section">
            <h2 class="legal-document__section-title"><?php esc_html_e('Jak złożyć odstąpienie', 'voitkus'); ?></h2>
            <div class="legal-document__section-body">
                <ol class="legal-document__list">
                    <li><?php esc_html_e('Wypełnij poniższy formularz (możesz go skopiować do wiadomości e-mail).', 'voitkus'); ?></li>
                    <li>
                        <?php
                        printf(
                            /* translators: %s: shop email */
                            esc_html__('Wyślij na adres: %s', 'voitkus'),
                            '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>'
                        );
                        ?>
                    </li>
                    <li><?php esc_html_e('Odeślij produkt niezwłocznie, nie później niż w 14 dni od wysłania oświadczenia (koszt odesłania ponosi Klient).', 'voitkus'); ?></li>
                </ol>
                <p>
                    <?php
                    printf(
                        /* translators: %s: link to regulamin section */
                        esc_html__('Szczegóły prawa odstąpienia i wyłączenia dla produktów spożywczych: %s.', 'voitkus'),
                        '<a href="' . esc_url($terms) . '">' . esc_html__('Regulamin — §7', 'voitkus') . '</a>'
                    );
                    ?>
                </p>
                <p>
                    <?php
                    printf(
                        /* translators: %s: link to reklamacje anchor */
                        esc_html__('Wady produktu (reklamacja, nie odstąpienie): %s.', 'voitkus'),
                        '<a href="' . esc_url(home_url('/legal/terms/#reklamacje')) . '">' . esc_html__('Regulamin — §8 Reklamacje', 'voitkus') . '</a>'
                    );
                    ?>
                </p>
            </div>
        </section>

        <section class="legal-document__section">
            <h2 class="legal-document__section-title"><?php esc_html_e('Formularz odstąpienia od umowy', 'voitkus'); ?></h2>
            <div class="legal-document__section-body">
                <div class="legal-withdrawal-form">
                    <p><strong><?php esc_html_e('Adnotacja dla Sprzedawcy:', 'voitkus'); ?></strong></p>
                    <p>
                        <?php
                        printf(
                            esc_html__('%1$s, %2$s, %3$s', 'voitkus'),
                            esc_html($c['legal_name']),
                            esc_html($address),
                            esc_html($email)
                        );
                        ?>
                    </p>

                    <p><strong><?php esc_html_e('Ja/My(*) niniejszym informuję/informujemy(*) o moim/naszym odstąpieniu od umowy sprzedaży następujących rzeczy:', 'voitkus'); ?></strong></p>

                    <dl class="legal-withdrawal-form__fields">
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Imię i nazwisko Konsumenta(-ów)', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line" aria-hidden="true"></dd>
                        </div>
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Adres Konsumenta(-ów)', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line" aria-hidden="true"></dd>
                        </div>
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Adres e-mail', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line" aria-hidden="true"></dd>
                        </div>
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Numer zamówienia', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line" aria-hidden="true"></dd>
                        </div>
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Data złożenia zamówienia', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line" aria-hidden="true"></dd>
                        </div>
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Data otrzymania towaru', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line" aria-hidden="true"></dd>
                        </div>
                        <div class="legal-withdrawal-form__row">
                            <dt><?php esc_html_e('Nazwa i ilość produktu(-ów)', 'voitkus'); ?></dt>
                            <dd class="legal-withdrawal-form__line legal-withdrawal-form__line--tall" aria-hidden="true"></dd>
                        </div>
                    </dl>

                    <p><?php esc_html_e('Data złożenia oświadczenia o odstąpieniu:', 'voitkus'); ?> <span class="legal-withdrawal-form__line legal-withdrawal-form__line--inline"></span></p>
                    <p><?php esc_html_e('Podpis Konsumenta(-ów) (wystarczy imię i nazwisko przy formularzu wysyłanym e-mailem):', 'voitkus'); ?> <span class="legal-withdrawal-form__line legal-withdrawal-form__line--inline"></span></p>

                    <p class="legal-withdrawal-form__note"><em><?php esc_html_e('(*) Niepotrzebne skreślić.', 'voitkus'); ?></em></p>
                </div>
            </div>
        </section>
    </article>
    <?php

    return (string) ob_get_clean();
}

function voitkus_withdrawal_shortcode(): string
{
    return voitkus_render_withdrawal_form();
}
