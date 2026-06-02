<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.8.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div class="cart-layout">
    <div class="cart-layout__main">
        <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
            <?php do_action('woocommerce_before_cart_table'); ?>

            <div class="cart-items">
                <?php do_action('woocommerce_before_cart_contents'); ?>

                <?php
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                    $visible    = apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key);

                    if ($_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible) {
                        /** @var string $product_name */
                        $product_name      = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        ?>
                        <div class="cart-item woocommerce-cart-form__cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">

                            <div class="cart-item__media">
                                <?php
                                $thumbnail = apply_filters(
                                    'woocommerce_cart_item_thumbnail',
                                    voitkus_product_thumbnail_html($_product, 'cart-item__img'),
                                    $cart_item,
                                    $cart_item_key
                                );

                                if (! $product_permalink) {
                                    echo $thumbnail; // PHPCS: XSS ok.
                                } else {
                                    printf('<a href="%s" class="cart-item__img-link">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                                }
                                ?>
                            </div>

                            <div class="cart-item__body">
                                <div class="cart-item__info">
                                    <?php
                                    if (! $product_permalink) {
                                        /** @var string $product_name */
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<span class="cart-item__title">%s</span>', $_product->get_name()), $cart_item, $cart_item_key));
                                    } else {
                                        /** @var string $product_name */
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s" class="cart-item__title">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                    }

                                    do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                                    echo wc_get_formatted_cart_item_data($cart_item); // PHPCS: XSS ok.

                                    if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                        echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                    }
                                    ?>
                                </div>

                                <div class="cart-item__price">
                                    <?php
                                    echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                                    ?>
                                </div>

                                <div class="cart-item__qty">
                                    <?php
                                    if ($_product->is_sold_individually()) {
                                        $min_quantity = 1;
                                        $max_quantity = 1;
                                    } else {
                                        $min_quantity = 0;
                                        $max_quantity = $_product->get_max_purchase_quantity();
                                    }

                                    $product_quantity = woocommerce_quantity_input(
                                        array(
                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $max_quantity,
                                            'min_value'    => $min_quantity,
                                            'product_name' => $product_name,
                                        ),
                                        $_product,
                                        false
                                    );

                                    echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item); // PHPCS: XSS ok.
                                    ?>
                                </div>

                                <div class="cart-item__remove">
                                    <?php
                                    echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        'woocommerce_cart_item_remove_link',
                                        sprintf(
                                            '<a role="button" href="%s" class="remove cart-item__remove-link" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
                                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                                            /* translators: %s is the product name */
                                            esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                                            esc_attr($product_id),
                                            esc_attr($_product->get_sku()),
                                            esc_html__('Usuń', 'voitkus')
                                        ),
                                        $cart_item_key
                                    );
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>

                <?php do_action('woocommerce_cart_contents'); ?>

                <div class="cart-actions">
                    <?php if (wc_coupons_enabled()) { ?>
                        <div class="cart-coupon">
                            <label for="coupon_code" class="screen-reader-text"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label>
                            <input type="text" name="coupon_code" class="cart-coupon__input" id="coupon_code" value="" placeholder="<?php esc_attr_e('Kod kuponu', 'voitkus'); ?>" />
                            <button type="submit" class="cart-coupon__btn button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="apply_coupon" value="<?php esc_attr_e('Zastosuj', 'voitkus'); ?>"><?php esc_html_e('Zastosuj', 'voitkus'); ?></button>
                            <?php do_action('woocommerce_cart_coupon'); ?>
                        </div>
                    <?php } ?>

                    <button type="submit" class="cart-update button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="update_cart" value="<?php esc_attr_e('Zaktualizuj koszyk', 'voitkus'); ?>"><?php esc_html_e('Zaktualizuj koszyk', 'voitkus'); ?></button>

                    <?php do_action('woocommerce_cart_actions'); ?>

                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                </div>

                <?php do_action('woocommerce_after_cart_contents'); ?>
            </div>

            <?php do_action('woocommerce_after_cart_table'); ?>
        </form>
    </div>

    <div class="cart-layout__sidebar">
        <?php do_action('woocommerce_before_cart_collaterals'); ?>

        <div class="cart-collaterals">
            <?php
                /**
                 * Cart collaterals hook.
                 *
                 * @hooked woocommerce_cross_sell_display
                 * @hooked woocommerce_cart_totals - 10
                 */
                do_action('woocommerce_cart_collaterals');
            ?>
        </div>
    </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>
