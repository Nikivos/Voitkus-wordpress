<?php
/**
 * My Account navigation.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined('ABSPATH') || exit;
?>

<nav class="woocommerce-MyAccount-navigation voitkus-account-nav" aria-label="<?php esc_attr_e('Menu konta', 'voitkus'); ?>">
    <p class="voitkus-account-nav__eyebrow"><?php esc_html_e('Panel', 'voitkus'); ?></p>
    <ul>
        <?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
            <li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                    <?php echo esc_html($label); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
