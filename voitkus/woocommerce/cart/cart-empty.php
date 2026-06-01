<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined('ABSPATH') || exit;
?>

<div class="cart-empty-page">
    <div class="cart-empty-page__inner">
        <?php
        /**
         * @hooked wc_empty_cart_message - 10
         */
        do_action('woocommerce_cart_is_empty');
        ?>

        <?php if (wc_get_page_id('shop') > 0) : ?>
            <p class="return-to-shop">
                <a class="button wc-backward cart-empty-page__btn" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
                    <?php
                    /**
                     * Filter "Return To Shop" text.
                     *
                     * @since 4.6.0
                     * @param string $default_text Default text.
                     */
                    echo esc_html(apply_filters('woocommerce_return_to_shop_text', __('Wróć do sklepu', 'voitkus')));
                    ?>
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>
