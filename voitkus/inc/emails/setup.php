<?php
/**
 * WooCommerce transactional emails — Voitkus (faza 1).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_wc_email_legal_links(): array
{
    return [
        [
            'label' => __('Regulamin', 'voitkus'),
            'url'   => home_url('/legal/terms/'),
        ],
        [
            'label' => __('Dostawa i płatność', 'voitkus'),
            'url'   => home_url('/legal/shipping/'),
        ],
        [
            'label' => __('RODO', 'voitkus'),
            'url'   => home_url('/legal/privacy/'),
        ],
    ];
}

function voitkus_wc_email_footer_text(string $text): string
{
    unset($text);

    $c     = voitkus_company_details();
    $links = voitkus_wc_email_legal_links();
    $parts = [];

    foreach ($links as $link) {
        $parts[] = sprintf(
            '<a href="%s" style="color:#0a0a0a;text-decoration:underline;">%s</a>',
            esc_url($link['url']),
            esc_html($link['label'])
        );
    }

    return sprintf(
        '<p style="margin:0 0 12px;font-size:13px;line-height:1.5;color:#1a1a1a;"><strong>%1$s</strong><br>%2$s<br>%3$s %4$s<br><a href="mailto:%5$s" style="color:#0a0a0a;text-decoration:underline;">%5$s</a></p><p style="margin:0;font-size:12px;line-height:1.5;color:#8a8a8a;">%6$s</p>',
        esc_html($c['legal_name']),
        esc_html(voitkus_company_address_line()),
        esc_html__('NIP', 'voitkus'),
        esc_html($c['nip']),
        esc_attr($c['email']),
        implode(' · ', $parts)
    );
}
add_filter('woocommerce_email_footer_text', 'voitkus_wc_email_footer_text');

/**
 * @return array{number: string, url: string, carrier: string}|null
 */
function voitkus_wc_order_tracking_info(WC_Order $order): ?array
{
    $meta_keys = [
        '_easypack_parcel_tracking_number',
        '_inpost_tracking_number',
        '_tracking_number',
        'tracking_number',
        '_wc_shipment_tracking_number',
    ];

    foreach ($meta_keys as $key) {
        $value = $order->get_meta($key);

        if (is_string($value) && trim($value) !== '') {
            return voitkus_wc_build_tracking_payload(trim($value));
        }
    }

    $shipping_methods = $order->get_shipping_methods();

    foreach ($shipping_methods as $shipping) {
        if (! is_a($shipping, 'WC_Order_Item_Shipping')) {
            continue;
        }

        foreach ($shipping->get_meta_data() as $meta) {
            $meta_key = (string) $meta->key;

            if (! preg_match('/track|numer|przesyl|shipment/i', $meta_key)) {
                continue;
            }

            $meta_value = is_scalar($meta->value) ? trim((string) $meta->value) : '';

            if ($meta_value !== '') {
                return voitkus_wc_build_tracking_payload($meta_value);
            }
        }
    }

    return null;
}

/**
 * @return array{number: string, url: string, carrier: string}
 */
function voitkus_wc_build_tracking_payload(string $number): array
{
    return [
        'number'  => $number,
        'url'     => 'https://inpost.pl/sledzenie-przesylek?number=' . rawurlencode($number),
        'carrier' => 'InPost',
    ];
}

function voitkus_wc_email_tracking_block(WC_Order $order, bool $sent_to_admin, bool $plain_text, $email): void
{
    if ($sent_to_admin || ! is_a($email, 'WC_Email') || $email->id !== 'customer_completed_order') {
        return;
    }

    $tracking = voitkus_wc_order_tracking_info($order);

    if ($tracking === null) {
        return;
    }

    if ($plain_text) {
        echo "\n" . esc_html__('Numer przesyłki', 'voitkus') . ': ' . esc_html($tracking['number']) . "\n";
        echo esc_url($tracking['url']) . "\n";

        return;
    }
    ?>
    <div style="margin:24px 0 0;padding:20px;border:1px solid #e8e8e8;border-radius:12px;background:#f9f9f9;">
        <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8a8a8a;">
            <?php esc_html_e('Śledzenie przesyłki', 'voitkus'); ?>
        </p>
        <p style="margin:0 0 4px;font-size:15px;font-weight:700;color:#0a0a0a;">
            <?php echo esc_html($tracking['carrier']); ?>
        </p>
        <p style="margin:0 0 12px;font-size:14px;line-height:1.5;color:#1a1a1a;">
            <?php esc_html_e('Numer', 'voitkus'); ?>: <?php echo esc_html($tracking['number']); ?>
        </p>
        <a href="<?php echo esc_url($tracking['url']); ?>" style="display:inline-block;padding:10px 18px;border-radius:999px;background:#0a0a0a;color:#ffffff;font-size:12px;font-weight:700;text-decoration:none;letter-spacing:0.04em;text-transform:uppercase;">
            <?php esc_html_e('Śledź przesyłkę', 'voitkus'); ?>
        </a>
    </div>
    <?php
}
add_action('woocommerce_email_after_order_table', 'voitkus_wc_email_tracking_block', 15, 4);

function voitkus_wc_email_subject_processing(string $subject, $order, $email): string
{
    unset($subject, $email);

    if (! is_a($order, 'WC_Order')) {
        return __('Dziękujemy za zamówienie', 'voitkus');
    }

    return sprintf(
        /* translators: %s: order number */
        __('Dziękujemy za zamówienie #%s', 'voitkus'),
        $order->get_order_number()
    );
}
add_filter('woocommerce_email_subject_customer_processing_order', 'voitkus_wc_email_subject_processing', 10, 3);

function voitkus_wc_email_subject_completed(string $subject, $order, $email): string
{
    unset($subject, $email);

    if (! is_a($order, 'WC_Order')) {
        return __('Twoja kawa jest już w drodze', 'voitkus');
    }

    return sprintf(
        /* translators: %s: order number */
        __('Twoja kawa jest już w drodze — #%s', 'voitkus'),
        $order->get_order_number()
    );
}
add_filter('woocommerce_email_subject_customer_completed_order', 'voitkus_wc_email_subject_completed', 10, 3);

function voitkus_wc_email_heading_processing(string $heading, $order, $email): string
{
    unset($heading, $email);

    if (! is_a($order, 'WC_Order')) {
        return __('Potwierdzenie zamówienia', 'voitkus');
    }

    return sprintf(
        /* translators: %s: order number */
        __('Zamówienie #%s', 'voitkus'),
        $order->get_order_number()
    );
}
add_filter('woocommerce_email_heading_customer_processing_order', 'voitkus_wc_email_heading_processing', 10, 3);

function voitkus_wc_email_heading_completed(string $heading, $order, $email): string
{
    unset($heading, $order, $email);

    return __('Twoja kawa jest już w drodze', 'voitkus');
}
add_filter('woocommerce_email_heading_customer_completed_order', 'voitkus_wc_email_heading_completed', 10, 3);

function voitkus_wc_sync_email_defaults(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    $flag = 'voitkus_wc_email_defaults_v1';

    if (get_option($flag) === 'done') {
        return;
    }

    $c = voitkus_company_details();

    if (get_option('woocommerce_email_from_name') === false || get_option('woocommerce_email_from_name') === '') {
        update_option('woocommerce_email_from_name', 'Voitkus Coffee');
    }

    if (get_option('woocommerce_email_from_address') === false || get_option('woocommerce_email_from_address') === '') {
        update_option('woocommerce_email_from_address', $c['email']);
    }

    update_option($flag, 'done', false);
}
add_action('init', 'voitkus_wc_sync_email_defaults', 7);
