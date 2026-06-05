<?php
/**
 * Customer completed order email
 *
 * @package WooCommerce\Templates\Emails
 * @version 9.9.0
 *
 * @var WC_Order $order
 * @var bool     $sent_to_admin
 * @var bool     $plain_text
 * @var string   $email_heading
 * @var string   $additional_content
 * @var WC_Email $email
 */

defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);

if ($plain_text) {
    if ($order->get_billing_first_name()) {
        echo esc_html(sprintf(__('Cześć %s,', 'voitkus'), $order->get_billing_first_name())) . "\n\n";
    } else {
        echo esc_html__('Cześć,', 'voitkus') . "\n\n";
    }

    echo esc_html__('Twoje zamówienie zostało wysłane.', 'voitkus') . "\n\n";
} else {
    if ($order->get_billing_first_name()) {
        printf('<p>%s</p>', esc_html(sprintf(__('Cześć %s,', 'voitkus'), $order->get_billing_first_name())));
    } else {
        echo '<p>' . esc_html__('Cześć,', 'voitkus') . '</p>';
    }

    echo '<p>' . esc_html__('Twoje zamówienie zostało wysłane.', 'voitkus') . '</p>';
}

if ($additional_content) {
    echo $plain_text ? wp_strip_all_tags($additional_content) : wp_kses_post(wpautop(wptexturize($additional_content)));
}

do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email);
do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email);

if ($plain_text) {
    echo "\n\n----------------------------------------\n\n";
}

do_action('woocommerce_email_footer', $email);
