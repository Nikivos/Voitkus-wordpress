<?php
/**
 * Faktura VAT na checkout — pola firmy + NIP, meta zamówienia, admin i e-mail.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_invoice_meta_key(): string
{
    return '_voitkus_invoice_requested';
}

function voitkus_invoice_nip_meta_key(): string
{
    return '_voitkus_invoice_nip';
}

function voitkus_normalize_nip(string $nip): string
{
    return preg_replace('/\D+/', '', $nip) ?? '';
}

function voitkus_format_nip_display(string $nip): string
{
    $digits = voitkus_normalize_nip($nip);

    if (strlen($digits) !== 10) {
        return $digits;
    }

    return sprintf(
        '%s-%s-%s-%s',
        substr($digits, 0, 3),
        substr($digits, 3, 3),
        substr($digits, 6, 2),
        substr($digits, 8, 2)
    );
}

function voitkus_is_valid_polish_nip(string $nip): bool
{
    $digits = voitkus_normalize_nip($nip);

    if (strlen($digits) !== 10 || ! ctype_digit($digits)) {
        return false;
    }

    $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    $sum     = 0;

    for ($i = 0; $i < 9; $i++) {
        $sum += ((int) $digits[$i]) * $weights[$i];
    }

    $checksum = $sum % 11;

    if ($checksum === 10) {
        return false;
    }

    return $checksum === (int) $digits[9];
}

function voitkus_checkout_invoice_requested(array $data = []): bool
{
    if ($data !== []) {
        return ! empty($data['billing_voitkus_invoice']);
    }

    return isset($_POST['billing_voitkus_invoice']) && wc_string_to_bool((string) wp_unslash($_POST['billing_voitkus_invoice']));
}

/**
 * @return array{company: string, nip: string, nip_display: string}|null
 */
function voitkus_order_invoice_details(WC_Order $order): ?array
{
    if ($order->get_meta(voitkus_invoice_meta_key()) !== 'yes') {
        return null;
    }

    $company = trim((string) $order->get_billing_company());
    $nip     = voitkus_normalize_nip((string) $order->get_meta(voitkus_invoice_nip_meta_key()));

    if ($company === '' && $nip === '') {
        return null;
    }

    return [
        'company'     => $company,
        'nip'         => $nip,
        'nip_display' => voitkus_format_nip_display($nip),
    ];
}

function voitkus_register_invoice_checkout_fields(array $fields): array
{
    if (! isset($fields['billing']) || ! is_array($fields['billing'])) {
        return $fields;
    }

    $fields['billing']['billing_voitkus_invoice'] = [
        'type'     => 'checkbox',
        'label'    => __('Potrzebuję faktury VAT', 'voitkus'),
        'class'    => ['form-row-wide', 'voitkus-invoice-toggle'],
        'priority' => 28,
        'default'  => 0,
    ];

    $fields['billing']['billing_company'] = array_merge(
        $fields['billing']['billing_company'] ?? [],
        [
            'label'       => __('Nazwa firmy', 'voitkus'),
            'placeholder' => __('Pełna nazwa firmy na fakturze', 'voitkus'),
            'class'       => ['form-row-wide', 'voitkus-invoice-field'],
            'priority'    => 29,
            'required'    => false,
        ]
    );

    $fields['billing']['billing_voitkus_nip'] = [
        'type'              => 'text',
        'label'             => __('NIP', 'voitkus'),
        'placeholder'       => '0000000000',
        'class'             => ['form-row-wide', 'voitkus-invoice-field'],
        'priority'          => 30,
        'required'          => false,
        'custom_attributes' => [
            'inputmode'    => 'numeric',
            'autocomplete' => 'off',
            'maxlength'    => '13',
        ],
    ];

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'voitkus_register_invoice_checkout_fields', 20);

function voitkus_checkout_needs_shipping_phone(): bool
{
    return function_exists('WC')
        && WC()->cart instanceof WC_Cart
        && WC()->cart->needs_shipping();
}

function voitkus_require_checkout_billing_phone(array $fields): array
{
    if (! voitkus_checkout_needs_shipping_phone()) {
        return $fields;
    }

    if (isset($fields['billing']['billing_phone'])) {
        $fields['billing']['billing_phone']['required'] = true;
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'voitkus_require_checkout_billing_phone', 15);

function voitkus_validate_checkout_billing_phone(array $data, WP_Error $errors): void
{
    if (! voitkus_checkout_needs_shipping_phone()) {
        return;
    }

    $phone = trim((string) ($data['billing_phone'] ?? ''));

    if ($phone === '') {
        $errors->add(
            'billing_phone_required',
            __('Podaj numer telefonu — jest wymagany przy dostawie.', 'voitkus')
        );
    }
}
add_action('woocommerce_after_checkout_validation', 'voitkus_validate_checkout_billing_phone', 10, 2);

function voitkus_validate_invoice_checkout_fields(array $data, WP_Error $errors): void
{
    if (! voitkus_checkout_invoice_requested($data)) {
        return;
    }

    $company = trim((string) ($data['billing_company'] ?? ''));

    if ($company === '') {
        $errors->add(
            'billing_company_required',
            __('Podaj nazwę firmy na fakturę VAT.', 'voitkus')
        );
    }

    $nip = voitkus_normalize_nip((string) ($data['billing_voitkus_nip'] ?? ''));

    if ($nip === '') {
        $errors->add(
            'billing_voitkus_nip_required',
            __('Podaj NIP firmy na fakturę VAT.', 'voitkus')
        );

        return;
    }

    if (! voitkus_is_valid_polish_nip($nip)) {
        $errors->add(
            'billing_voitkus_nip_invalid',
            __('Podaj poprawny numer NIP (10 cyfr).', 'voitkus')
        );
    }
}
add_action('woocommerce_after_checkout_validation', 'voitkus_validate_invoice_checkout_fields', 10, 2);

function voitkus_save_invoice_checkout_fields(int $order_id): void
{
    $order = wc_get_order($order_id);

    if (! $order instanceof WC_Order) {
        return;
    }

    $requested = voitkus_checkout_invoice_requested() ? 'yes' : 'no';
    $order->update_meta_data(voitkus_invoice_meta_key(), $requested);

    if ($requested === 'yes') {
        $nip = voitkus_normalize_nip((string) ($_POST['billing_voitkus_nip'] ?? ''));
        $order->update_meta_data(voitkus_invoice_nip_meta_key(), $nip);
    } else {
        $order->set_billing_company('');
        $order->delete_meta_data(voitkus_invoice_nip_meta_key());
    }

    $order->save();
}
add_action('woocommerce_checkout_update_order_meta', 'voitkus_save_invoice_checkout_fields', 10, 1);

function voitkus_admin_order_invoice_details(WC_Order $order): void
{
    $details = voitkus_order_invoice_details($order);

    echo '<div class="voitkus-admin-invoice-details">';

    if ($details === null) {
        echo '<p><strong>' . esc_html__('Faktura VAT', 'voitkus') . ':</strong> ';
        echo esc_html__('Nie', 'voitkus') . '</p>';
        echo '</div>';

        return;
    }

    echo '<p><strong>' . esc_html__('Faktura VAT', 'voitkus') . ':</strong> ';
    echo esc_html__('Tak — wystaw ręcznie w systemie księgowym', 'voitkus') . '</p>';
    echo '<p><strong>' . esc_html__('Nazwa firmy', 'voitkus') . ':</strong> ';
    echo esc_html($details['company']) . '</p>';
    echo '<p><strong>' . esc_html__('NIP', 'voitkus') . ':</strong> ';
    echo esc_html($details['nip_display']) . '</p>';
    echo '</div>';
}
add_action('woocommerce_admin_order_data_after_billing_address', 'voitkus_admin_order_invoice_details', 15, 1);

function voitkus_email_order_invoice_fields(array $fields, bool $sent_to_admin, WC_Order $order): array
{
    $details = voitkus_order_invoice_details($order);

    if ($details === null) {
        return $fields;
    }

    $fields['voitkus_invoice_company'] = [
        'label' => __('Faktura VAT — firma', 'voitkus'),
        'value' => $details['company'],
    ];

    $fields['voitkus_invoice_nip'] = [
        'label' => __('Faktura VAT — NIP', 'voitkus'),
        'value' => $details['nip_display'],
    ];

    if ($sent_to_admin) {
        $fields['voitkus_invoice_action'] = [
            'label' => __('Faktura VAT', 'voitkus'),
            'value' => __('Wystaw fakturę VAT na powyższe dane.', 'voitkus'),
        ];
    }

    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'voitkus_email_order_invoice_fields', 10, 3);

function voitkus_render_order_invoice_details(WC_Order $order): void
{
    $details = voitkus_order_invoice_details($order);

    if ($details === null) {
        return;
    }

    ?>
    <section class="voitkus-order-invoice woocommerce-customer-details" aria-labelledby="voitkus-order-invoice-title">
        <div class="voitkus-order-received__address-card">
            <h2 id="voitkus-order-invoice-title" class="woocommerce-column__title">
                <?php esc_html_e('Faktura VAT', 'voitkus'); ?>
            </h2>
            <dl class="voitkus-order-invoice__list">
                <div class="voitkus-order-invoice__row">
                    <dt><?php esc_html_e('Firma', 'voitkus'); ?></dt>
                    <dd><?php echo esc_html($details['company']); ?></dd>
                </div>
                <div class="voitkus-order-invoice__row">
                    <dt><?php esc_html_e('NIP', 'voitkus'); ?></dt>
                    <dd><?php echo esc_html($details['nip_display']); ?></dd>
                </div>
            </dl>
            <p class="voitkus-order-invoice__hint">
                <?php esc_html_e('Fakturę VAT wyślemy na adres e-mail z zamówienia po jego opłaceniu.', 'voitkus'); ?>
            </p>
        </div>
    </section>
    <?php
}
add_action('woocommerce_order_details_after_customer_details', 'voitkus_render_order_invoice_details', 15, 1);

function voitkus_add_invoice_checkout_order_note(int $order_id): void
{
    if (! voitkus_checkout_invoice_requested()) {
        return;
    }

    $order = wc_get_order($order_id);

    if (! $order instanceof WC_Order) {
        return;
    }

    $details = voitkus_order_invoice_details($order);

    if ($details === null) {
        return;
    }

    $order->add_order_note(
        sprintf(
            /* translators: 1: company name, 2: NIP */
            __('Klient prosi o fakturę VAT: %1$s, NIP %2$s.', 'voitkus'),
            $details['company'],
            $details['nip_display']
        ),
        false,
        true
    );
}
add_action('woocommerce_checkout_update_order_meta', 'voitkus_add_invoice_checkout_order_note', 20, 1);
