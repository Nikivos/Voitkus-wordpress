<?php
/**
 * Order item — karta produktu (szczegóły lotu)
 *
 * @package WooCommerce\Templates
 * @version 5.2.0
 *
 * @var WC_Order $order
 * @var int $item_id
 * @var WC_Order_Item_Product $item
 * @var bool $show_purchase_note
 * @var string $purchase_note
 * @var WC_Product|null $product
 */

defined('ABSPATH') || exit;

if (! apply_filters('woocommerce_order_item_visible', true, $item)) {
    return;
}

if (
    function_exists('voitkus_order_line_item_html')
    && is_a($order, 'WC_Order')
    && is_a($item, 'WC_Order_Item_Product')
) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo voitkus_order_line_item_html($order, $item, $item_id, true);
} else {
    ?>
    <article class="voitkus-order-line order_item">
        <div class="voitkus-order-line__body">
            <h3 class="voitkus-order-line__title"><?php echo wp_kses_post($item->get_name()); ?></h3>
        </div>
        <div class="voitkus-order-line__price">
            <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
        </div>
    </article>
    <?php
}

if ($show_purchase_note && $purchase_note) :
    ?>
    <div class="voitkus-order-line__note">
        <?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
    <?php
endif;
