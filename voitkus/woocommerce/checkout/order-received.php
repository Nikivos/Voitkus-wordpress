<?php
/**
 * Order received message
 *
 * @package WooCommerce\Templates
 * @version 8.8.0
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;

$message = apply_filters(
    'woocommerce_thankyou_order_received_text',
    esc_html__('Dziękujemy — zamówienie przyjęte!', 'voitkus'),
    $order
);
?>

<div class="voitkus-order-received__hero">
    <span class="voitkus-order-received__icon" aria-hidden="true"></span>
    <?php if ($order) : ?>
        <p class="voitkus-order-received__eyebrow"><?php esc_html_e('Zamówienie', 'voitkus'); ?></p>
        <p class="voitkus-order-received__order-number">#<?php echo esc_html($order->get_order_number()); ?></p>
    <?php endif; ?>
    <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
        <?php echo $message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </p>
    <?php if ($order) : ?>
        <p class="voitkus-order-received__sub">
            <?php esc_html_e('Potwierdzenie wysłaliśmy na e-mail.', 'voitkus'); ?>
        </p>
    <?php endif; ?>
</div>
