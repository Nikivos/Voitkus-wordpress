<?php
/**
 * View order — ten sam układ co thank you.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 *
 * @var int $order_id
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if (! $order || ! current_user_can('view_order', $order_id)) {
    return;
}
?>

<div class="woocommerce-order voitkus-order-received voitkus-order-received--view-order">
    <div class="voitkus-order-received-layout">
        <aside class="voitkus-order-received__aside">
            <div class="voitkus-order-received__hero">
                <span class="voitkus-order-received__icon" aria-hidden="true"></span>
                <p class="voitkus-order-received__eyebrow"><?php esc_html_e('Zamówienie', 'voitkus'); ?></p>
                <p class="voitkus-order-received__order-number">#<?php echo esc_html($order->get_order_number()); ?></p>
                <p class="voitkus-order-received__status"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></p>
            </div>

            <div class="voitkus-order-received__summary-card">
                <dl class="voitkus-order-received__summary-list">
                    <div class="voitkus-order-received__summary-row">
                        <dt><?php esc_html_e('Data', 'voitkus'); ?></dt>
                        <dd><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></dd>
                    </div>
                    <?php if ($order->get_payment_method_title()) : ?>
                        <div class="voitkus-order-received__summary-row">
                            <dt><?php esc_html_e('Płatność', 'voitkus'); ?></dt>
                            <dd><?php echo wp_kses_post($order->get_payment_method_title()); ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
                <div class="voitkus-order-received__total">
                    <span class="voitkus-order-received__total-label"><?php esc_html_e('Razem', 'voitkus'); ?></span>
                    <strong class="voitkus-order-received__total-value"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong>
                </div>
                <div class="voitkus-order-received__actions">
                    <a class="voitkus-order-received__btn voitkus-order-received__btn--ghost" href="<?php echo esc_url(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))); ?>">
                        <?php esc_html_e('Wróć do zamówień', 'voitkus'); ?>
                    </a>
                </div>
            </div>
        </aside>

        <div class="voitkus-order-received__main">
            <?php wc_get_template('order/order-details.php', ['order_id' => $order_id]); ?>
        </div>
    </div>
</div>
