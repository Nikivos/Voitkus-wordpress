<?php
/**
 * Stub pages for nav links not yet fully built (Parzenie, B2B).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_is_brew_guides_page(): bool
{
    return is_page('brew-guides');
}

function voitkus_is_b2b_page(): bool
{
    return is_page('b2b');
}

function voitkus_brew_guides_page_url(): string
{
    $page_id = voitkus_find_page_by_slug('brew-guides');

    if ($page_id > 0) {
        $url = get_permalink($page_id);

        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return home_url('/brew-guides/');
}

function voitkus_b2b_page_url(): string
{
    $page_id = voitkus_find_page_by_slug('b2b');

    if ($page_id > 0) {
        $url = get_permalink($page_id);

        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return home_url('/b2b/');
}

function voitkus_render_brew_guides_page(): string
{
    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');

    ob_start();
    ?>
    <div class="stub-page__content">
        <p class="stub-page__lead">
            <?php esc_html_e('Przewodniki parzenia — wkrótce. Zbieramy proste przepisy pod nasze loty: espresso, przelew i French press.', 'voitkus'); ?>
        </p>
        <p class="stub-page__text">
            <?php esc_html_e('Tymczasem wybierz kawę w sklepie — podpowiemy profil i wagę na karcie produktu.', 'voitkus'); ?>
        </p>
        <div class="stub-page__actions">
            <a class="contact-page__btn" href="<?php echo esc_url($shop_url); ?>">
                <?php esc_html_e('Przejdź do sklepu', 'voitkus'); ?>
            </a>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

function voitkus_render_b2b_page(): string
{
    $email = function_exists('voitkus_company_details')
        ? voitkus_company_details()['email']
        : 'hello@voitkuscoffee.com';

    ob_start();
    ?>
    <div class="stub-page__content">
        <p class="stub-page__lead">
            <?php esc_html_e('Oferta B2B — wkrótce. Pracujemy nad stałymi dostawami dla kawiarni, biur i partnerów hurtowych.', 'voitkus'); ?>
        </p>
        <p class="stub-page__text">
            <?php esc_html_e('Masz pilne zapytanie? Napisz — wrócimy w ciągu 1–2 dni roboczych.', 'voitkus'); ?>
        </p>
        <div class="stub-page__actions">
            <a class="contact-page__btn" href="<?php echo esc_url('mailto:' . $email); ?>">
                <?php echo esc_html($email); ?>
            </a>
            <a class="stub-page__link" href="<?php echo esc_url(voitkus_contact_page_url()); ?>">
                <?php esc_html_e('Kontakt', 'voitkus'); ?>
            </a>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

function voitkus_brew_guides_shortcode(): string
{
    return voitkus_render_brew_guides_page();
}

function voitkus_b2b_shortcode(): string
{
    return voitkus_render_b2b_page();
}

function voitkus_register_stub_page_shortcodes(): void
{
    add_shortcode('voitkus_brew_guides', 'voitkus_brew_guides_shortcode');
    add_shortcode('voitkus_b2b', 'voitkus_b2b_shortcode');
}
add_action('init', 'voitkus_register_stub_page_shortcodes');

function voitkus_filter_brew_guides_page_content(string $content): string
{
    if (! is_singular('page')) {
        return $content;
    }

    $post = get_post();

    if (! $post instanceof WP_Post || $post->post_name !== 'brew-guides') {
        return $content;
    }

    if (has_shortcode($content, 'voitkus_brew_guides')) {
        return $content;
    }

    return voitkus_render_brew_guides_page();
}
add_filter('the_content', 'voitkus_filter_brew_guides_page_content', 5);

function voitkus_filter_b2b_page_content(string $content): string
{
    if (! is_singular('page')) {
        return $content;
    }

    $post = get_post();

    if (! $post instanceof WP_Post || $post->post_name !== 'b2b') {
        return $content;
    }

    if (has_shortcode($content, 'voitkus_b2b')) {
        return $content;
    }

    return voitkus_render_b2b_page();
}
add_filter('the_content', 'voitkus_filter_b2b_page_content', 5);

function voitkus_ensure_stub_nav_pages(): void
{
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }

    $flag = 'voitkus_stub_nav_pages_v1';

    if (get_option($flag) === 'done') {
        return;
    }

    voitkus_ensure_page('brew-guides', __('Parzenie', 'voitkus'), '[voitkus_brew_guides]', 0);
    voitkus_ensure_page('b2b', 'B2B', '[voitkus_b2b]', 0);

    update_option($flag, 'done', false);
}
add_action('init', 'voitkus_ensure_stub_nav_pages', 8);
