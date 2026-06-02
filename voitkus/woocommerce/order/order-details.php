<?php
/**
 * Order details
 *
 * @package WooCommerce\Templates
 * @version 9.0.0
 *
 * @var bool $show_downloads
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (! $order) {
    return;
}

$order_items           = $order->get_items(apply_filters('woocommerce_purchase_order_item_types', 'line_item'));
$show_purchase_note    = $order->has_status(apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing']));
$downloads             = $order->get_downloadable_items();
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ($show_downloads) {
    wc_get_template(
        'order/order-downloads.php',
        [
            'downloads'  => $downloads,
            'show_title' => true,
        ]
    );
}
?>
<section class="woocommerce-order-details voitkus-order-received__details">
    <?php do_action('woocommerce_order_details_before_order_table', $order); ?>

    <h2 class="woocommerce-order-details__title voitkus-order-received__section-title">
        <?php esc_html_e('Szczegóły zamówienia', 'voitkus'); ?>
    </h2>

    <div class="voitkus-order-received__products">
        <?php
        do_action('woocommerce_order_details_before_order_table_items', $order);

        foreach ($order_items as $item_id => $item) {
            $product = $item->get_product();

            wc_get_template(
                'order/order-details-item.php',
                [
                    'order'              => $order,
                    'item_id'            => $item_id,
                    'item'               => $item,
                    'show_purchase_note' => $show_purchase_note,
                    'purchase_note'      => $product ? $product->get_purchase_note() : '',
                    'product'            => $product,
                ]
            );
        }

        do_action('woocommerce_order_details_after_order_table_items', $order);
        ?>
    </div>

    <dl class="voitkus-order-received__totals">
        <?php foreach ($order->get_order_item_totals() as $key => $total) : ?>
            <div class="voitkus-order-received__totals-row<?php echo 'order_total' === $key ? ' is-total' : ''; ?>">
                <dt><?php echo esc_html($total['label']); ?></dt>
                <dd><?php echo wp_kses_post($total['value']); ?></dd>
            </div>
        <?php endforeach; ?>

        <?php if ($order->get_customer_note()) : ?>
            <div class="voitkus-order-received__totals-row voitkus-order-received__totals-row--note">
                <dt><?php esc_html_e('Uwagi', 'voitkus'); ?></dt>
                <dd><?php echo wp_kses(nl2br(wptexturize($order->get_customer_note())), []); ?></dd>
            </div>
        <?php endif; ?>
    </dl>

    <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
</section>

<?php
do_action('woocommerce_after_order_details', $order);

if ($show_customer_details) {
    wc_get_template('order/order-details-customer.php', ['order' => $order]);
}
