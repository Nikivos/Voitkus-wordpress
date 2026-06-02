<?php
/**
 * Order customer details
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 *
 * @var WC_Order $order
 */

defined('ABSPATH') || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details voitkus-order-received__addresses">

    <?php if ($show_shipping) : ?>
        <div class="voitkus-order-received__address-card woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
            <h2 class="woocommerce-column__title"><?php esc_html_e('Adres rozliczeniowy', 'voitkus'); ?></h2>
            <address>
                <?php echo wp_kses_post($order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce'))); ?>
                <?php if ($order->get_billing_phone()) : ?>
                    <p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_billing_phone()); ?></p>
                <?php endif; ?>
                <?php if ($order->get_billing_email()) : ?>
                    <p class="woocommerce-customer-details--email"><?php echo esc_html($order->get_billing_email()); ?></p>
                <?php endif; ?>
            </address>
        </div>

        <div class="voitkus-order-received__address-card woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
            <h2 class="woocommerce-column__title"><?php esc_html_e('Adres dostawy', 'voitkus'); ?></h2>
            <address>
                <?php echo wp_kses_post($order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce'))); ?>
            </address>
        </div>
    <?php else : ?>
        <div class="voitkus-order-received__address-card woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">
            <h2 class="woocommerce-column__title"><?php esc_html_e('Adres rozliczeniowy', 'voitkus'); ?></h2>
            <address>
                <?php echo wp_kses_post($order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce'))); ?>
                <?php if ($order->get_billing_phone()) : ?>
                    <p class="woocommerce-customer-details--phone"><?php echo esc_html($order->get_billing_phone()); ?></p>
                <?php endif; ?>
                <?php if ($order->get_billing_email()) : ?>
                    <p class="woocommerce-customer-details--email"><?php echo esc_html($order->get_billing_email()); ?></p>
                <?php endif; ?>
            </address>
        </div>
    <?php endif; ?>

</section>
