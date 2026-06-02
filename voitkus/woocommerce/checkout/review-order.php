<?php
/**
 * Review order table
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined('ABSPATH') || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table">
    <thead>
        <tr>
            <th class="product-name"><?php esc_html_e('Produkt', 'voitkus'); ?></th>
            <th class="product-total"><?php esc_html_e('Cena', 'voitkus'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        do_action('woocommerce_review_order_before_cart_contents');

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

            if (! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                continue;
            }
            ?>
            <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'checkout-line-item-row cart_item', $cart_item, $cart_item_key)); ?>">
                <td class="product-name" data-title="<?php esc_attr_e('Produkt', 'voitkus'); ?>">
                    <?php echo voitkus_checkout_line_item_html($cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </td>
                <td class="product-total" data-title="<?php esc_attr_e('Cena', 'voitkus'); ?>">
                    <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </td>
            </tr>
            <?php
        }

        do_action('woocommerce_review_order_after_cart_contents');
        ?>
    </tbody>
    <tfoot>
        <?php /* Suma pozycji jest w kolumnie Cena — bez duplikatu „Suma częściowa”. */ ?>

        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
            <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                <td><?php wc_cart_totals_coupon_html($coupon); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (WC()->cart->needs_shipping() && (WC()->cart->show_shipping() || 'yes' === get_option('woocommerce_enable_shipping_calc'))) : ?>

            <?php do_action('woocommerce_review_order_before_shipping'); ?>

            <?php wc_cart_totals_shipping_html(); ?>

            <?php do_action('woocommerce_review_order_after_shipping'); ?>

        <?php elseif (WC()->cart->needs_shipping()) : ?>
            <tr class="voitkus-shipping-placeholder-row">
                <th><?php esc_html_e('Dostawa', 'voitkus'); ?></th>
                <td>
                    <div class="voitkus-shipping-alert voitkus-shipping-alert--info">
                        <?php esc_html_e('Uzupełnij kod pocztowy i miasto w formularzu po lewej, aby zobaczyć dostawę.', 'voitkus'); ?>
                    </div>
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach (WC()->cart->get_fees() as $fee) : ?>
            <tr class="fee">
                <th><?php echo esc_html($fee->name); ?></th>
                <td><?php wc_cart_totals_fee_html($fee); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if (wc_tax_enabled() && ! WC()->cart->display_prices_including_tax()) : ?>
            <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
                    <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                        <th><?php echo esc_html($tax->label); ?></th>
                        <td><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="tax-total">
                    <th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th>
                    <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <?php do_action('woocommerce_review_order_before_order_total'); ?>

        <tr class="order-total">
            <th><?php esc_html_e('Łącznie', 'voitkus'); ?></th>
            <td><?php wc_cart_totals_order_total_html(); ?></td>
        </tr>

        <?php do_action('woocommerce_review_order_after_order_total'); ?>

    </tfoot>
</table>
