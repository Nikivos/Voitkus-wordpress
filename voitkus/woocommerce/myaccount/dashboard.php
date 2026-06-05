<?php
/**
 * My Account dashboard.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined('ABSPATH') || exit;

if (! isset($current_user)) {
    $current_user = wp_get_current_user();
}

$allowed_html = [
    'a'      => ['href' => []],
    'strong' => [],
];
?>

<div class="voitkus-account-dashboard">
    <header class="voitkus-account-dashboard__hero">
        <p class="voitkus-account-dashboard__eyebrow"><?php esc_html_e('Witaj z powrotem', 'voitkus'); ?></p>
        <p class="voitkus-account-dashboard__hello">
            <?php
            printf(
                /* translators: 1: user display name 2: logout url */
                wp_kses(__('Cześć <strong>%1$s</strong> — <a href="%2$s">wyloguj się</a>', 'voitkus'), $allowed_html),
                esc_html($current_user->display_name),
                esc_url(wc_logout_url())
            );
            ?>
        </p>
        <p class="voitkus-account-dashboard__lead">
            <?php esc_html_e('Tu sprawdzisz zamówienia, adres dostawy i dane logowania.', 'voitkus'); ?>
        </p>
    </header>

    <div class="voitkus-account-dashboard__grid">
        <a class="voitkus-account-tile" href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>">
            <span class="voitkus-account-tile__label"><?php esc_html_e('Zamówienia', 'voitkus'); ?></span>
            <span class="voitkus-account-tile__hint"><?php esc_html_e('Historia i statusy', 'voitkus'); ?></span>
        </a>
        <a class="voitkus-account-tile" href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>">
            <span class="voitkus-account-tile__label"><?php esc_html_e('Adres', 'voitkus'); ?></span>
            <span class="voitkus-account-tile__hint"><?php esc_html_e('Dostawa i rozliczenia', 'voitkus'); ?></span>
        </a>
        <a class="voitkus-account-tile" href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>">
            <span class="voitkus-account-tile__label"><?php esc_html_e('Dane konta', 'voitkus'); ?></span>
            <span class="voitkus-account-tile__hint"><?php esc_html_e('E-mail i hasło', 'voitkus'); ?></span>
        </a>
        <?php if (function_exists('wc_get_page_permalink')) : ?>
            <a class="voitkus-account-tile voitkus-account-tile--accent" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                <span class="voitkus-account-tile__label"><?php esc_html_e('Sklep', 'voitkus'); ?></span>
                <span class="voitkus-account-tile__hint"><?php esc_html_e('Aktualne loty kawy', 'voitkus'); ?></span>
            </a>
        <?php endif; ?>
    </div>
</div>
