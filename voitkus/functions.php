<?php
/**
 * Voitkus theme setup.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$voitkus_includes = [
    '/inc/lots.php',
    '/inc/product-meta.php',
    '/inc/legal/regulamin.php',
];

foreach ($voitkus_includes as $relative) {
    $path = get_template_directory() . $relative;

    if (is_readable($path)) {
        require_once $path;
    }
}

function voitkus_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('custom-logo', [
        'height'      => 70,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Menu', 'voitkus'),
    ]);
}
add_action('after_setup_theme', 'voitkus_setup');

function voitkus_woocommerce_setup(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    add_filter('woocommerce_enqueue_styles', '__return_empty_array');
    add_filter('woocommerce_show_page_title', '__return_false');
    add_filter('woocommerce_has_block_template', '__return_false');

    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

    // Sidebar koszyka: tylko podsumowanie (bez cross-sells).
    remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', 10);

}
add_action('after_setup_theme', 'voitkus_woocommerce_setup', 20);

/**
 * Classic Cart/Checkout + kalkulator wysyłki zgodnie z wymaganiami WooCommerce (nie blocks).
 */
function voitkus_ensure_woocommerce_classic_store(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    $theme_version = wp_get_theme()->get('Version') ?: '0';
    $sync_flag     = 'voitkus_wc_classic_sync_v' . preg_replace('/[^a-z0-9._-]/i', '', $theme_version) . '_20250601';

    if (get_option($sync_flag) === 'done') {
        return;
    }

    if (get_option('woocommerce_enable_shipping_calc') !== 'yes') {
        update_option('woocommerce_enable_shipping_calc', 'yes');
    }

    voitkus_convert_wc_page_to_shortcode('cart', '[woocommerce_cart]');
    voitkus_convert_wc_page_to_shortcode('checkout', '[woocommerce_checkout]');
    voitkus_disable_cart_checkout_blocks_feature();

    update_option($sync_flag, 'done', false);
}
add_action('init', 'voitkus_ensure_woocommerce_classic_store', 5);
add_action('after_switch_theme', 'voitkus_ensure_woocommerce_classic_store');

function voitkus_convert_wc_page_to_shortcode(string $page_key, string $shortcode): void
{
    $page_id = wc_get_page_id($page_key);

    if ($page_id <= 0) {
        return;
    }

    $post = get_post($page_id);

    if (! $post instanceof WP_Post) {
        return;
    }

    $block_map = [
        'cart'     => 'woocommerce/cart',
        'checkout' => 'woocommerce/checkout',
    ];
    $shortcode_tag = 'woocommerce_' . $page_key;
    $block_name    = $block_map[ $page_key ] ?? '';
    $uses_block    = $block_name !== '' && function_exists('has_block') && has_block($block_name, $post);
    $uses_shortcode = has_shortcode($post->post_content, $shortcode_tag);

    if ($uses_block || ! $uses_shortcode) {
        wp_update_post(
            [
                'ID'           => $post->ID,
                'post_content' => $shortcode,
            ]
        );
    }
}

function voitkus_disable_cart_checkout_blocks_feature(): void
{
    if (! function_exists('wc_get_container')) {
        return;
    }

    try {
        $container = wc_get_container();

        if (! $container || ! is_object($container) || ! method_exists($container, 'get')) {
            return;
        }

        $controller = $container->get(
            \Automattic\WooCommerce\Internal\Features\FeaturesController::class
        );

        if ($controller && method_exists($controller, 'change_feature_enable')) {
            $controller->change_feature_enable('cart_checkout_blocks', false);
        }
    } catch (Throwable $e) {
        unset($e);
    }
}

/**
 * JS: kalkulator wysyłki, wybór metody, podświetlenie InPost.
 */
function voitkus_enqueue_checkout_helpers(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/checkout.js';

    if (! is_readable($path)) {
        return;
    }

    $deps = ['jquery'];

    if (function_exists('is_cart') && is_cart()) {
        wp_enqueue_script('wc-cart');
        $deps[] = 'wc-cart';
    }

    if (function_exists('is_checkout') && is_checkout() && (! function_exists('is_wc_endpoint_url') || ! is_wc_endpoint_url())) {
        wp_enqueue_script('wc-checkout');
        $deps[] = 'wc-checkout';
    }

    if (count($deps) === 1) {
        return;
    }

    wp_enqueue_script(
        'voitkus-checkout',
        get_stylesheet_directory_uri() . '/assets/checkout.js',
        array_values(array_unique($deps)),
        (string) filemtime($path),
        true
    );
}
add_action('wp_enqueue_scripts', 'voitkus_enqueue_checkout_helpers', 25);

/**
 * Podpowiedź dla admina, gdy brak aktywnych bramek płatności (checkout).
 */
function voitkus_checkout_payment_gateways_admin_notice(): void
{
    if (! function_exists('is_checkout') || ! is_checkout() || ! current_user_can('manage_woocommerce')) {
        return;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
        return;
    }

    $gateways = WC()->payment_gateways()->get_available_payment_gateways();

    if ($gateways !== []) {
        return;
    }

    echo '<div class="woocommerce-info voitkus-checkout-admin-hint">';
    echo esc_html__(
        'Brak metod płatności dla klientów. Włącz bramkę w WooCommerce → Ustawienia → Płatności (np. Przelewy24) i tryb testowy.',
        'voitkus'
    );
    echo '</div>';
}
add_action('woocommerce_before_checkout_form', 'voitkus_checkout_payment_gateways_admin_notice', 6);

/**
 * Dostawa: zawsze kalkulator, zmiana adresu, wyczyszczenie wyboru (koszyk + checkout).
 */
function voitkus_register_shipping_tools(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    add_filter('woocommerce_shipping_show_shipping_calculator', 'voitkus_always_show_shipping_calculator', 10, 3);
    add_filter('woocommerce_cart_no_shipping_available_html', 'voitkus_cart_no_shipping_message', 10, 2);
    add_filter('woocommerce_shipping_may_be_available_html', 'voitkus_shipping_enter_address_message');
    add_filter('woocommerce_no_shipping_available_html', 'voitkus_no_shipping_checkout_message');
    add_action('wp_loaded', 'voitkus_handle_reset_shipping_address', 20);
}
add_action('after_setup_theme', 'voitkus_register_shipping_tools', 21);

function voitkus_always_show_shipping_calculator(bool $show, int $index, array $package): bool
{
    unset($index, $package);

    if ('yes' !== get_option('woocommerce_enable_shipping_calc')) {
        return $show;
    }

    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    if (function_exists('is_checkout') && is_checkout() && (! function_exists('is_wc_endpoint_url') || ! is_wc_endpoint_url())) {
        return true;
    }

    return $show;
}

function voitkus_shipping_alert(string $message, string $type = 'info'): string
{
    $allowed = [
        'info'    => true,
        'warning' => true,
        'success' => true,
    ];
    $type = isset($allowed[$type]) ? $type : 'info';

    return sprintf(
        '<div class="voitkus-shipping-alert voitkus-shipping-alert--%1$s" role="status">%2$s</div>',
        esc_attr($type),
        wp_kses_post($message)
    );
}

function voitkus_cart_no_shipping_message(string $html, string $formatted_destination): string
{
    unset($html);

    if ($formatted_destination !== '') {
        $message = sprintf(
            /* translators: %s: formatted shipping address */
            __('Brak dostawy dla %s. Sprawdź kod pocztowy lub wybierz inny adres w kroku 1.', 'voitkus'),
            '<strong>' . esc_html($formatted_destination) . '</strong>'
        );
    } else {
        $message = esc_html__('Brak dostawy dla podanego adresu. Sprawdź kod pocztowy w kroku 1.', 'voitkus');
    }

    return voitkus_shipping_alert($message, 'warning');
}

function voitkus_shipping_enter_address_message(string $html): string
{
    unset($html);

    return voitkus_shipping_alert(
        esc_html__('Podaj kod pocztowy i miejscowość, aby zobaczyć metody dostawy.', 'voitkus'),
        'info'
    );
}

function voitkus_no_shipping_checkout_message(string $html): string
{
    unset($html);

    return voitkus_shipping_alert(
        esc_html__(
            'Brak metod dostawy dla tego adresu. Uzupełnij lub popraw dane w formularzu po lewej stronie.',
            'voitkus'
        ),
        'warning'
    );
}

/**
 * Stan panelu dostawy — bez WC()->shipping()->get_packages() (rekurencja w szablonie).
 *
 * @param array<int, WC_Shipping_Rate>|null $available_methods
 * @return array{label: string, state: string}
 */
function voitkus_get_shipping_panel_state(?array $available_methods = null, ?string $chosen_method = null, string $formatted_destination = ''): array
{
    $has_methods = is_array($available_methods) && $available_methods !== [];

    if ($has_methods) {
        if ($chosen_method !== null && $chosen_method !== '') {
            return [
                'label' => esc_html__('Metoda dostawy wybrana', 'voitkus'),
                'state' => 'ready',
            ];
        }

        return [
            'label' => esc_html__('Wybierz metodę dostawy (krok 2)', 'voitkus'),
            'state' => 'methods',
        ];
    }

    $has_addr = $formatted_destination !== '';

    if (! $has_addr && WC()->customer) {
        $customer = WC()->customer;
        $has_addr = trim((string) $customer->get_shipping_postcode()) !== ''
            || trim((string) $customer->get_shipping_city()) !== '';
    }

    if ($has_addr) {
        return [
            'label' => esc_html__('Sprawdź adres — brak dostępnych metod', 'voitkus'),
            'state' => 'warning',
        ];
    }

    return [
        'label' => esc_html__('Podaj adres dostawy (krok 1)', 'voitkus'),
        'state' => 'empty',
    ];
}

/**
 * @param array<int, WC_Shipping_Rate>|null $available_methods
 */
function voitkus_get_shipping_panel_summary(?array $available_methods = null, ?string $chosen_method = null, string $formatted_destination = ''): string
{
    $parts = [];

    if ($formatted_destination !== '') {
        $parts[] = $formatted_destination;
    }

    if (is_array($available_methods) && $chosen_method !== null && $chosen_method !== '') {
        foreach ($available_methods as $method) {
            if (! is_object($method) || ! isset($method->id) || $method->id !== $chosen_method) {
                continue;
            }

            $parts[] = wp_strip_all_tags(wc_cart_totals_shipping_method_label($method));
            break;
        }
    }

    return implode(' · ', array_filter($parts));
}

/**
 * Adres do wyświetlenia w panelu (gdy WC nie zwróci formatted_destination).
 */
function voitkus_get_shipping_display_destination(): string
{
    if (! WC()->customer) {
        return '';
    }

    $customer = WC()->customer;
    $dest     = WC()->countries->get_formatted_address(
        [
            'country'  => $customer->get_shipping_country(),
            'state'    => $customer->get_shipping_state(),
            'postcode' => $customer->get_shipping_postcode(),
            'city'     => $customer->get_shipping_city(),
            'address_1' => $customer->get_shipping_address_1(),
        ],
        ', '
    );

    if ($dest !== '') {
        return $dest;
    }

    $parts = array_filter(
        [
            trim((string) $customer->get_shipping_postcode()),
            trim((string) $customer->get_shipping_city()),
        ]
    );

    return implode(' ', $parts);
}

/**
 * @param array<int, WC_Shipping_Rate>|null $available_methods
 */
function voitkus_is_inpost_shipping_method(?string $chosen_method, ?array $available_methods = null): bool
{
    if ($chosen_method !== null && $chosen_method !== '') {
        $haystack = strtolower($chosen_method);

        if (str_contains($haystack, 'inpost') || str_contains($haystack, 'paczkomat') || str_contains($haystack, 'easypack')) {
            return true;
        }
    }

    if (! is_array($available_methods)) {
        return false;
    }

    foreach ($available_methods as $method) {
        if (! is_object($method) || ! isset($method->id)) {
            continue;
        }

        $id = strtolower((string) $method->id);

        if (str_contains($id, 'inpost') || str_contains($id, 'paczkomat') || str_contains($id, 'easypack')) {
            return true;
        }
    }

    return false;
}

function voitkus_render_shipping_tools_markup(): void
{
    if (! WC()->cart || ! WC()->cart->needs_shipping()) {
        return;
    }

    $is_checkout  = function_exists('is_checkout') && is_checkout() && (! function_exists('is_wc_endpoint_url') || ! is_wc_endpoint_url());
    $reset_action = $is_checkout ? wc_get_checkout_url() : wc_get_cart_url();
    ?>
    <footer class="voitkus-shipping-panel__footer">
        <div class="voitkus-shipping-tools" role="group" aria-label="<?php esc_attr_e('Zarządzanie dostawą', 'voitkus'); ?>">
            <?php if ($is_checkout) : ?>
                <button type="button" class="voitkus-shipping-tools__edit-address">
                    <?php esc_html_e('Edytuj adres w formularzu', 'voitkus'); ?>
                </button>
            <?php else : ?>
                <button
                    type="button"
                    class="voitkus-shipping-tools__scroll-address"
                    data-voitkus-scroll-address
                ><?php esc_html_e('Przejdź do formularza adresu', 'voitkus'); ?></button>
            <?php endif; ?>
            <form
                class="voitkus-shipping-reset-form"
                method="post"
                action="<?php echo esc_url($reset_action); ?>"
            >
                <?php wp_nonce_field('voitkus_reset_shipping', 'voitkus_reset_shipping_nonce'); ?>
                <input type="hidden" name="voitkus_reset_shipping" value="1" />
                <button type="submit" class="voitkus-shipping-tools__reset" data-voitkus-reset-shipping>
                    <?php esc_html_e('Wyczyść i zacznij od nowa', 'voitkus'); ?>
                </button>
            </form>
        </div>
    </footer>
    <?php
}

function voitkus_handle_reset_shipping_address(): void
{
    if (! class_exists('WooCommerce') || ! function_exists('WC')) {
        return;
    }

    $is_post = isset($_POST['voitkus_reset_shipping']);
    $is_get  = isset($_GET['voitkus_reset_shipping']);

    if (! $is_post && ! $is_get) {
        return;
    }

    $nonce = '';

    if ($is_post && isset($_POST['voitkus_reset_shipping_nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['voitkus_reset_shipping_nonce']));
    } elseif ($is_get && isset($_GET['_wpnonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
    }

    if ($nonce === '' || ! wp_verify_nonce($nonce, 'voitkus_reset_shipping')) {
        wc_add_notice(esc_html__('Nie udało się wyczyścić adresu. Odśwież stronę i spróbuj ponownie.', 'voitkus'), 'error');

        return;
    }

    $on_checkout = function_exists('is_checkout') && is_checkout() && (! function_exists('is_wc_endpoint_url') || ! is_wc_endpoint_url());
    $on_cart     = ! $on_checkout;

    $customer = WC()->customer;

    if (! $customer) {
        return;
    }

    $base_country = WC()->countries->get_base_country();

    $customer->set_shipping_country($base_country);
    $customer->set_shipping_state('');
    $customer->set_shipping_postcode('');
    $customer->set_shipping_city('');
    $customer->set_shipping_address_1('');
    $customer->set_shipping_address_2('');
    $customer->set_calculated_shipping(false);
    $customer->set_billing_country($base_country);
    $customer->set_billing_postcode('');
    $customer->set_billing_city('');
    $customer->set_billing_state('');
    $customer->set_billing_address_1('');
    $customer->set_billing_address_2('');
    $customer->save();

    if (WC()->session) {
        WC()->session->set('chosen_shipping_methods', []);
        WC()->session->set('shipping_for_package_0', null);
        WC()->session->set('previous_shipping_methods', null);
        foreach (array_keys(WC()->session->get_session_data()) as $session_key) {
            if (preg_match('/inpost|paczkomat|easypack|parcel.?locker|shipping_for_package/i', (string) $session_key)) {
                WC()->session->set($session_key, null);
            }
        }
    }

    if (WC()->cart) {
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();
    }

    wc_add_notice(
        esc_html__('Adres dostawy wyczyszczony. Wybierz adres i metodę dostawy ponownie.', 'voitkus'),
        'notice'
    );

    $redirect = $on_checkout ? wc_get_checkout_url() : wc_get_cart_url();
    wp_safe_redirect($redirect);
    exit;
}

/**
 * Wymusza własny katalog zamiast domyślnej pętli WooCommerce / bloków.
 */
function voitkus_render_custom_shop(): void
{
    if (is_admin() || ! function_exists('is_shop')) {
        return;
    }

    if (! is_shop() || isset($_GET['wc-ajax'])) {
        return;
    }

    if (! defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }

    $template = get_template_directory() . '/woocommerce/archive-product.php';

    if (! is_readable($template)) {
        return;
    }

    get_header();
    echo '<main class="woocommerce-page">';
    wc_get_template('archive-product.php');
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'voitkus_render_custom_shop', 5);

/**
 * Wymusza własną kartę produktu zamiast domyślnego WooCommerce.
 */
function voitkus_render_custom_single_product(): void
{
    if (is_admin() || ! function_exists('is_product')) {
        return;
    }

    if (! is_product() || isset($_GET['wc-ajax'])) {
        return;
    }

    $template = get_template_directory() . '/woocommerce/single-product.php';

    if (! is_readable($template)) {
        return;
    }

    get_header();
    echo '<main class="woocommerce-page">';
    wc_get_template('single-product.php');
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'voitkus_render_custom_single_product', 5);

/**
 * Wymusza własny koszyk zamiast domyślnego WooCommerce (jeśli włączone są bloki).
 */
function voitkus_render_custom_cart(): void
{
    if (is_admin() || ! function_exists('is_cart')) {
        return;
    }

    if (! is_cart() || isset($_GET['wc-ajax'])) {
        return;
    }

    $template = get_template_directory() . '/woocommerce/cart/cart.php';

    if (! is_readable($template)) {
        return;
    }

    get_header();
    echo '<main class="woocommerce-page page-main cart-page">';
    echo '<div class="page-main__inner">';
    echo '<header class="page-main__header"><h1 class="page-main__title">' . esc_html__('Koszyk', 'voitkus') . '</h1></header>';
    echo '<div class="page-main__content">';
    echo do_shortcode('[woocommerce_cart]');
    echo '</div>';
    echo '</div>';
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'voitkus_render_custom_cart', 5);

/**
 * Wymusza własne zamówienie (checkout) w układzie jak koszyk.
 */
function voitkus_render_custom_checkout(): void
{
    if (is_admin() || ! function_exists('is_checkout')) {
        return;
    }

    if (! is_checkout() || isset($_GET['wc-ajax'])) {
        return;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
        return;
    }

    if (! defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }

    get_header();
    echo '<main class="woocommerce-page page-main checkout-page">';
    echo '<div class="page-main__inner">';
    echo '<header class="page-main__header"><h1 class="page-main__title">' . esc_html__('Zamówienie', 'voitkus') . '</h1></header>';
    echo '<div class="page-main__content">';
    echo do_shortcode('[woocommerce_checkout]');
    echo '</div>';
    echo '</div>';
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'voitkus_render_custom_checkout', 5);

/**
 * Wymusza szablony WooCommerce z motywu voitkus.
 */
function voitkus_locate_woocommerce_template(string $template, string $template_name): string
{
    $paths = [
        trailingslashit(get_stylesheet_directory()) . 'woocommerce/' . $template_name,
        trailingslashit(get_template_directory()) . 'woocommerce/' . $template_name,
    ];

    foreach ($paths as $path) {
        if (is_readable($path)) {
            return $path;
        }
    }

    return $template;
}
add_filter('woocommerce_locate_template', 'voitkus_locate_woocommerce_template', 50, 2);

function voitkus_is_order_received_page(): bool
{
    return function_exists('is_checkout')
        && is_checkout()
        && function_exists('is_wc_endpoint_url')
        && is_wc_endpoint_url('order-received');
}

/**
 * Thank you — layout motywu + logika WooCommerce (shortcode).
 */
function voitkus_render_custom_order_received(): void
{
    if (is_admin() || ! voitkus_is_order_received_page()) {
        return;
    }

    if (isset($_GET['wc-ajax'])) {
        return;
    }

    if (! defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }

    get_header();
    echo '<main class="woocommerce-page page-main order-received-page">';
    echo '<div class="page-main__inner">';
    echo '<div class="page-main__content woocommerce">';
    echo do_shortcode('[woocommerce_checkout]');
    echo '</div>';
    echo '</div>';
    echo '</main>';
    get_footer();
    exit;
}
add_action('template_redirect', 'voitkus_render_custom_order_received', 4);

function voitkus_order_received_body_class(array $classes): array
{
    if (voitkus_is_order_received_page()) {
        $classes[] = 'order-received-page';
        $classes[] = 'voitkus-order-received-active';
    }

    if (function_exists('is_account_page') && is_account_page() && function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('view-order')) {
        $classes[] = 'order-received-page';
        $classes[] = 'voitkus-view-order-active';
    }

    return $classes;
}
add_filter('body_class', 'voitkus_order_received_body_class');

/**
 * Bez skryptów checkoutu / fragmentów na stronie podziękowania.
 */
function voitkus_optimize_order_received_assets(): void
{
    if (! voitkus_is_order_received_page()) {
        return;
    }

    $script_handles = [
        'wc-checkout',
        'wc-cart',
        'wc-cart-fragments',
        'voitkus-checkout',
        'selectWoo',
        'select2',
    ];

    foreach ($script_handles as $handle) {
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }

    $style_handles = [
        'select2',
    ];

    foreach ($style_handles as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'voitkus_optimize_order_received_assets', 100);

function voitkus_enqueue_assets(): void
{
    $tokens_path = get_stylesheet_directory() . '/assets/design-tokens.css';
    $style_path = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style(
        'voitkus-tokens',
        get_stylesheet_directory_uri() . '/assets/design-tokens.css',
        [],
        file_exists($tokens_path) ? (string) filemtime($tokens_path) : wp_get_theme()->get('Version')
    );

    wp_enqueue_style(
        'voitkus-style',
        get_stylesheet_uri(),
        ['voitkus-tokens'],
        file_exists($style_path) ? (string) filemtime($style_path) : wp_get_theme()->get('Version')
    );

    $script_path = get_stylesheet_directory() . '/assets/menu.js';

    wp_enqueue_script(
        'voitkus-menu',
        get_stylesheet_directory_uri() . '/assets/menu.js',
        [],
        file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version'),
        true
    );

    if (function_exists('is_woocommerce') && (is_product() || is_cart() || is_checkout())) {
        $product_script_path = get_stylesheet_directory() . '/assets/product.js';
        $product_deps        = ['jquery', 'wc-cart-fragments'];

        if (function_exists('is_cart') && is_cart()) {
            wp_enqueue_script('wc-cart');
            $product_deps[] = 'wc-cart';
        }

        if (function_exists('is_checkout') && is_checkout() && ! is_wc_endpoint_url()) {
            wp_enqueue_script('wc-checkout');
            wp_enqueue_script('wc-cart-fragments');
            $product_deps[] = 'wc-checkout';
        }

        if (is_product()) {
            wp_enqueue_script('wc-add-to-cart');
            $product_deps[] = 'wc-add-to-cart';
            wp_enqueue_script('wc-cart-fragments');
        }

        wp_enqueue_script(
            'voitkus-product',
            get_stylesheet_directory_uri() . '/assets/product.js',
            $product_deps,
            file_exists($product_script_path) ? (string) filemtime($product_script_path) : wp_get_theme()->get('Version'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'voitkus_enqueue_assets', 20);

/**
 * Fragmenty koszyka na wszystkich stronach (w tym Kawa / shop) — domyślnie WC wyłącza poza koszykiem.
 */
add_filter('woocommerce_cart_fragments_enabled', static function (): bool {
    if (
        function_exists('is_checkout')
        && is_checkout()
        && function_exists('is_wc_endpoint_url')
        && is_wc_endpoint_url('order-received')
    ) {
        return false;
    }

    return true;
});

/**
 * Upewnia się, że sesja koszyka WooCommerce jest załadowana (ważne dla shop + wc-ajax).
 */
function voitkus_ensure_cart_loaded(): void
{
    if (is_admin() || ! function_exists('WC')) {
        return;
    }

    if (
        function_exists('is_checkout')
        && is_checkout()
        && function_exists('is_wc_endpoint_url')
        && is_wc_endpoint_url('order-received')
    ) {
        return;
    }

    if (null === WC()->cart && function_exists('wc_load_cart')) {
        wc_load_cart();
    }
}
add_action('woocommerce_init', 'voitkus_ensure_cart_loaded', 5);
add_action('wc_ajax_get_refreshed_fragments', 'voitkus_ensure_cart_loaded', 1);
add_action('wc_ajax_add_to_cart', 'voitkus_ensure_cart_loaded', 1);

/**
 * Po dodaniu do koszyka — zapis sesji przed zwróceniem fragmentów AJAX.
 */
function voitkus_persist_cart_session(): void
{
    if (! function_exists('WC') || ! WC()->cart || ! WC()->session) {
        return;
    }

    WC()->session->set('cart', WC()->cart->get_cart_for_session());
    WC()->session->set_customer_session_cookie(true);
}
add_action('woocommerce_add_to_cart', 'voitkus_persist_cart_session', 20);
add_action('woocommerce_cart_item_removed', 'voitkus_persist_cart_session', 20);
add_action('woocommerce_cart_item_restored', 'voitkus_persist_cart_session', 20);

/**
 * Wyłącza domyślne powiadomienie WooCommerce "Dodano do koszyka" przy dodawaniu przez AJAX.
 */
function voitkus_disable_add_to_cart_message($message, $products)
{
    if (wp_doing_ajax() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        return false;
    }
    return $message;
}
add_filter('wc_add_to_cart_message_html', 'voitkus_disable_add_to_cart_message', 10, 2);

/**
 * Czy komunikat to domyślne „koszyk zaktualizowany” WooCommerce.
 */
function voitkus_is_cart_updated_notice_message(string $message): bool
{
    $lower = mb_strtolower(wp_strip_all_tags($message));

    $needles = [
        'cart updated',
        'cart has been updated',
        'корзина обновлена',
        'koszyk został zaktualizowany',
        'koszyk zaktualizowany',
        'koszyk odświeżony',
        'koszyk odswiezony',
    ];

    foreach ($needles as $needle) {
        if (str_contains($lower, $needle)) {
            return true;
        }
    }

    if (str_contains($lower, 'koszyk') && (str_contains($lower, 'zaktualiz') || str_contains($lower, 'odśwież') || str_contains($lower, 'odswiez'))) {
        return true;
    }

    return false;
}

/**
 * Ukrywa zielony pasek „Cart updated” na stronie koszyka (qty stepper / update).
 * Kupon i błędy zostają.
 */
function voitkus_filter_cart_page_notices(array $notices): array
{
    if (! function_exists('is_cart') || ! is_cart()) {
        return $notices;
    }

    foreach (['success', 'notice'] as $type) {
        if (empty($notices[$type]) || ! is_array($notices[$type])) {
            continue;
        }

        $notices[$type] = array_values(array_filter($notices[$type], static function ($notice): bool {
            $message = '';
            if (is_array($notice) && isset($notice['notice'])) {
                $message = (string) $notice['notice'];
            } elseif (is_string($notice)) {
                $message = $notice;
            }

            return ! voitkus_is_cart_updated_notice_message($message);
        }));
    }

    return $notices;
}
add_filter('woocommerce_get_notices', 'voitkus_filter_cart_page_notices', 99);

function voitkus_default_menu(): void
{
    $items = [
        __('Kawa', 'voitkus')     => home_url('/shop/'),
        __('Parzenie', 'voitkus') => home_url('/brew-guides/'),
        __('O nas', 'voitkus')    => home_url('/about/'),
        __('B2B', 'voitkus')      => home_url('/b2b/'),
        __('Kontakt', 'voitkus')  => home_url('/contact/'),
    ];

    echo '<ul class="site-nav__list">';
    foreach ($items as $label => $url) {
        printf(
            '<li><a href="%s">%s</a></li>',
            esc_url($url),
            esc_html($label)
        );
    }
    echo '</ul>';
}

function voitkus_cart_url(): string
{
    if (function_exists('wc_get_cart_url')) {
        return wc_get_cart_url();
    }

    return home_url('/cart/');
}

function voitkus_account_url(): string
{
    if (function_exists('wc_get_page_permalink')) {
        return wc_get_page_permalink('myaccount');
    }

    return home_url('/my-account/');
}

/**
 * ID miniatury produktu (wariant → rodzic, potem galeria).
 */
function voitkus_resolve_product_image_id(WC_Product $product): int
{
    $image_id = (int) $product->get_image_id();

    if ($image_id > 0) {
        return $image_id;
    }

    if ($product->is_type('variation')) {
        $parent = wc_get_product($product->get_parent_id());

        if ($parent instanceof WC_Product) {
            $image_id = (int) $parent->get_image_id();

            if ($image_id > 0) {
                return $image_id;
            }

            $parent_gallery = $parent->get_gallery_image_ids();

            if ($parent_gallery !== []) {
                return (int) $parent_gallery[0];
            }
        }
    }

    $gallery = $product->get_gallery_image_ids();

    if ($gallery !== []) {
        return (int) $gallery[0];
    }

    return 0;
}

/**
 * Miniatura produktu — te same rozmiary co katalog (large), z fallback URL.
 */
function voitkus_product_thumbnail_html(WC_Product $product, string $class): string
{
    $image_id = voitkus_resolve_product_image_id($product);
    $alt      = $product->get_name();

    if ($image_id > 0) {
        foreach (['large', 'medium', 'woocommerce_thumbnail', 'thumbnail', 'full'] as $size) {
            $url = wp_get_attachment_image_url($image_id, $size);

            if ($url) {
                return sprintf(
                    '<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async" />',
                    esc_url($url),
                    esc_attr($alt),
                    esc_attr($class)
                );
            }
        }

        $html = wp_get_attachment_image(
            $image_id,
            'large',
            false,
            [
                'class'    => $class,
                'loading'  => 'lazy',
                'decoding' => 'async',
            ]
        );

        if ($html !== '') {
            return $html;
        }
    }

    return sprintf(
        '<span class="%1$s %1$s--empty" role="img" aria-label="%2$s"><span class="%1$s__label" aria-hidden="true">%3$s</span></span>',
        esc_attr($class),
        esc_attr($alt),
        esc_html__('Kawa', 'voitkus')
    );
}

/**
 * HTML wiersza produktu w podsumowaniu checkout (zdjęcie + nazwa + meta).
 */
function voitkus_checkout_line_item_html(array $cart_item, string $cart_item_key): string
{
    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

    if (! $_product instanceof WC_Product || ! $_product->exists()) {
        return '';
    }

    $product_id = function_exists('voitkus_lot_product_id') ? voitkus_lot_product_id($_product) : (int) $_product->get_id();
    $title      = $_product->get_name();
    $permalink  = $_product->is_visible() ? $_product->get_permalink($cart_item) : '';
    $origin     = function_exists('voitkus_lot_field') ? voitkus_lot_field($product_id, 'voitkus_origin', $_product) : '';
    $quantity   = (int) $cart_item['quantity'];
    $thumbnail  = apply_filters(
        'woocommerce_cart_item_thumbnail',
        voitkus_product_thumbnail_html($_product, 'checkout-line-item__img'),
        $cart_item,
        $cart_item_key
    );

    $item_data = wc_get_formatted_cart_item_data($cart_item);

    ob_start();
    ?>
    <div class="checkout-line-item">
        <div class="checkout-line-item__media">
            <?php if ($permalink !== '') : ?>
                <a href="<?php echo esc_url($permalink); ?>" class="checkout-line-item__img-link">
                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php else : ?>
                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </div>
        <div class="checkout-line-item__body">
            <?php if ($permalink !== '') : ?>
                <a href="<?php echo esc_url($permalink); ?>" class="checkout-line-item__title"><?php echo esc_html($title); ?></a>
            <?php else : ?>
                <span class="checkout-line-item__title"><?php echo esc_html($title); ?></span>
            <?php endif; ?>

            <?php if ($origin !== '') : ?>
                <span class="checkout-line-item__origin"><?php echo esc_html($origin); ?></span>
            <?php endif; ?>

            <?php if ($item_data !== '') : ?>
                <div class="checkout-line-item__meta"><?php echo $item_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>

            <span class="checkout-line-item__qty">
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %d: quantity */
                        _n('Ilość: %d szt.', 'Ilość: %d szt.', $quantity, 'voitkus'),
                        $quantity
                    )
                );
                ?>
            </span>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Thank you / view-order — kontekst szczegółów zamówienia.
 */
function voitkus_is_order_details_context(): bool
{
    if (function_exists('voitkus_is_order_received_page') && voitkus_is_order_received_page()) {
        return true;
    }

    return function_exists('is_account_page')
        && is_account_page()
        && function_exists('is_wc_endpoint_url')
        && is_wc_endpoint_url('view-order');
}

/**
 * @return list<array{label: string, value: string}>
 */
function voitkus_order_item_lot_specs(int $product_id, ?WC_Product $product): array
{
    if (! function_exists('voitkus_lot_field') || ! function_exists('voitkus_product_meta_fields')) {
        return [];
    }

    $field_defs = voitkus_product_meta_fields();
    $keys       = ['voitkus_process', 'voitkus_region', 'voitkus_variety', 'voitkus_roast_level', 'voitkus_weight'];
    $specs      = [];

    foreach ($keys as $key) {
        $value = voitkus_lot_field($product_id, $key, $product);

        if ($value === '') {
            continue;
        }

        $specs[] = [
            'label' => $field_defs[ $key ]['label'] ?? $key,
            'value' => $value,
        ];
    }

    return $specs;
}

/**
 * Karta produktu na stronie podziękowania / zamówienia (foto + lot + meta).
 *
 * @param WC_Order|mixed              $order
 * @param WC_Order_Item_Product|mixed $item
 */
function voitkus_order_line_item_html($order, $item, int $item_id, bool $show_price = true): string
{
    static $rendering = false;

    if ($rendering) {
        return '';
    }

    if (! is_a($order, 'WC_Order') || ! is_a($item, 'WC_Order_Item_Product')) {
        return '';
    }

    $product = $item->get_product();

    if (! $product instanceof WC_Product) {
        return '';
    }

    $rendering = true;

    $product_id = function_exists('voitkus_lot_product_id') ? voitkus_lot_product_id($product) : (int) $product->get_id();
    $title      = $item->get_name();
    $is_visible = $product->is_visible();
    $permalink  = apply_filters('woocommerce_order_item_permalink', $is_visible ? $product->get_permalink($item) : '', $item, $order);
    $origin     = function_exists('voitkus_lot_field') ? voitkus_lot_field($product_id, 'voitkus_origin', $product) : '';
    $hook       = function_exists('voitkus_lot_field') ? voitkus_lot_field($product_id, 'voitkus_hook', $product) : '';
    $flavor     = function_exists('voitkus_lot_field') ? voitkus_lot_field($product_id, 'voitkus_flavor_notes', $product) : '';
    $specs      = voitkus_order_item_lot_specs($product_id, $product);
    $sku        = $product->get_sku();
    $qty        = (int) $item->get_quantity();
    $refunded   = $order->get_qty_refunded_for_item($item_id);

    if ($refunded) {
        $qty_label = sprintf(
            /* translators: 1: ordered qty, 2: qty after refund */
            __('Ilość: %1$s szt. (po zwrocie: %2$s szt.)', 'voitkus'),
            (string) $qty,
            (string) ($qty + $refunded)
        );
    } else {
        $qty_label = sprintf(
            /* translators: %d: quantity */
            _n('Ilość: %d szt.', 'Ilość: %d szt.', $qty, 'voitkus'),
            $qty
        );
    }

    $thumbnail = voitkus_product_thumbnail_html($product, 'voitkus-order-line__img');

    $item_meta = '';
    if (function_exists('wc_display_item_meta')) {
        ob_start();
        do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);
        wc_display_item_meta($item);
        do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
        $item_meta = trim((string) ob_get_clean());
    }

    ob_start();
    ?>
    <div class="voitkus-order-line<?php echo $show_price ? '' : ' voitkus-order-line--no-price'; ?>">
        <div class="voitkus-order-line__media">
            <?php if ($permalink !== '') : ?>
                <a href="<?php echo esc_url($permalink); ?>" class="voitkus-order-line__img-link">
                    <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            <?php else : ?>
                <?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endif; ?>
        </div>
        <div class="voitkus-order-line__body">
            <h3 class="voitkus-order-line__title">
                <?php if ($permalink !== '') : ?>
                    <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                <?php else : ?>
                    <?php echo esc_html($title); ?>
                <?php endif; ?>
            </h3>

            <?php if ($hook !== '') : ?>
                <p class="voitkus-order-line__hook"><?php echo esc_html($hook); ?></p>
            <?php endif; ?>

            <?php if ($origin !== '') : ?>
                <p class="voitkus-order-line__origin"><?php echo esc_html($origin); ?></p>
            <?php endif; ?>

            <?php if ($flavor !== '') : ?>
                <p class="voitkus-order-line__flavor">
                    <span class="voitkus-order-line__flavor-label"><?php esc_html_e('Nuty', 'voitkus'); ?>:</span>
                    <?php echo esc_html($flavor); ?>
                </p>
            <?php endif; ?>

            <?php if ($specs !== []) : ?>
                <dl class="voitkus-order-line__specs">
                    <?php foreach ($specs as $spec) : ?>
                        <div class="voitkus-order-line__spec">
                            <dt><?php echo esc_html($spec['label']); ?></dt>
                            <dd><?php echo esc_html($spec['value']); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <?php if ($sku !== '') : ?>
                <p class="voitkus-order-line__sku">
                    <span class="voitkus-order-line__sku-label"><?php esc_html_e('SKU', 'voitkus'); ?>:</span>
                    <?php echo esc_html($sku); ?>
                </p>
            <?php endif; ?>

            <?php if ($item_meta !== '') : ?>
                <div class="voitkus-order-line__meta"><?php echo $item_meta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php endif; ?>

            <p class="voitkus-order-line__qty"><?php echo esc_html($qty_label); ?></p>
        </div>
        <?php if ($show_price) : ?>
            <div class="voitkus-order-line__price">
                <span class="voitkus-order-line__price-label"><?php esc_html_e('Łącznie', 'voitkus'); ?></span>
                <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php

    $html = (string) ob_get_clean();
    $rendering = false;

    return $html;
}

/**
 * Gdy WC ładuje starą tabelę — w komórce produktu pełna karta.
 */
function voitkus_order_item_name_rich_markup(string $name, $item, bool $is_visible): string
{
    static $in_filter = false;

    unset($is_visible);

    if ($in_filter || ! voitkus_is_order_details_context()) {
        return $name;
    }

    if (! is_a($item, 'WC_Order_Item_Product')) {
        return $name;
    }

    $order = $item->get_order();

    if (! is_a($order, 'WC_Order')) {
        return $name;
    }

    $in_filter = true;
    $html      = voitkus_order_line_item_html($order, $item, (int) $item->get_id(), false);
    $in_filter = false;

    return $html !== '' ? $html : $name;
}

function voitkus_register_order_item_display_filters(): void
{
    add_filter('woocommerce_order_item_name', 'voitkus_order_item_name_rich_markup', 20, 3);
}
add_action('woocommerce_init', 'voitkus_register_order_item_display_filters');

/**
 * Czy jesteśmy na checkout (bez endpointów typu order-received).
 */
function voitkus_is_active_checkout(): bool
{
    if (! function_exists('is_checkout') || ! is_checkout()) {
        return false;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
        return false;
    }

    return true;
}

/**
 * Wymusza kartę produktu z foto w podsumowaniu — gdy WC ładuje domyślny review-order.php.
 */
function voitkus_checkout_cart_item_name(string $name, array $cart_item, string $cart_item_key): string
{
    if (! voitkus_is_active_checkout()) {
        return $name;
    }

    $html = voitkus_checkout_line_item_html($cart_item, $cart_item_key);

    return $html !== '' ? $html : $name;
}
add_filter('woocommerce_cart_item_name', 'voitkus_checkout_cart_item_name', 20, 3);

/**
 * Ilość jest w voitkus_checkout_line_item_html — nie doklejaj „× 6” z core.
 */
function voitkus_checkout_cart_item_quantity(string $quantity_html, array $cart_item, string $cart_item_key): string
{
    if (! voitkus_is_active_checkout()) {
        return $quantity_html;
    }

    return '';
}
add_filter('woocommerce_checkout_cart_item_quantity', 'voitkus_checkout_cart_item_quantity', 20, 3);

add_filter('woocommerce_locate_template', static function (string $template, string $template_name): string {
    if ($template_name !== 'checkout/review-order.php') {
        return $template;
    }

    $theme_file = voitkus_locate_wc_template('checkout/review-order.php');

    return $theme_file !== '' ? $theme_file : $template;
}, 20, 2);

/**
 * Ścieżka do szablonów WooCommerce w aktywnym motywie (child lub parent).
 */
function voitkus_locate_wc_template(string $relative): string
{
    $relative = ltrim($relative, '/');
    $child    = get_stylesheet_directory() . '/woocommerce/' . $relative;

    if (is_readable($child)) {
        return $child;
    }

    $parent = get_template_directory() . '/woocommerce/' . $relative;

    if (is_readable($parent)) {
        return $parent;
    }

    return '';
}

function voitkus_cart_count(): int
{
    if (! function_exists('WC')) {
        return 0;
    }

    voitkus_ensure_cart_loaded();

    if (WC()->cart) {
        return (int) WC()->cart->get_cart_contents_count();
    }

    return 0;
}

function voitkus_cart_fragments(array $fragments): array
{
    voitkus_ensure_cart_loaded();

    $count = voitkus_cart_count();

    ob_start();
    if ($count > 0) {
        ?>
        <span class="cart-count" data-voitkus-cart-count="desktop"><?php echo esc_html((string) $count); ?></span>
        <?php
    } else {
        ?>
        <span class="cart-count" data-voitkus-cart-count="desktop" style="display: none;" aria-hidden="true">0</span>
        <?php
    }
    $desktop_html = ob_get_clean() ?: '';
    $fragments['[data-voitkus-cart-count="desktop"]'] = $desktop_html;
    $fragments['.header-action--cart .cart-count']           = $desktop_html;

    ob_start();
    if ($count > 0) {
        ?>
        <span class="header-icon-btn__badge" data-voitkus-cart-count="mobile"><?php echo esc_html((string) $count); ?></span>
        <?php
    } else {
        ?>
        <span class="header-icon-btn__badge" data-voitkus-cart-count="mobile" style="display: none;" aria-hidden="true">0</span>
        <?php
    }
    $mobile_html = ob_get_clean() ?: '';
    $fragments['[data-voitkus-cart-count="mobile"]'] = $mobile_html;
    $fragments['.header-icon-btn--cart .header-icon-btn__badge'] = $mobile_html;

    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'voitkus_cart_fragments');
add_filter('woocommerce_cart_fragments', 'voitkus_cart_fragments');

function voitkus_customize_register(WP_Customize_Manager $wp_customize): void
{
    $wp_customize->add_section('voitkus_hero', [
        'title'    => __('Hero', 'voitkus'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('voitkus_hero_bag_image', [
        'default'           => '',
        'sanitize_callback' => 'voitkus_sanitize_hero_bag_image',
    ]);

    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'voitkus_hero_bag_image',
            [
                'label'       => __('Zdjęcie opakowania (hero)', 'voitkus'),
                'description' => __('PNG/WebP z przezroczystym tłem — zastąpi różowy placeholder.', 'voitkus'),
                'section'     => 'voitkus_hero',
                'settings'    => 'voitkus_hero_bag_image',
            ]
        )
    );

    voitkus_customize_register_why($wp_customize);
    voitkus_customize_register_grind($wp_customize);
    voitkus_customize_register_founder($wp_customize);
    voitkus_customize_register_reviews($wp_customize);
    voitkus_customize_register_footer($wp_customize);
}
add_action('customize_register', 'voitkus_customize_register');

function voitkus_sanitize_hero_bag_image($value): string
{
    if (is_numeric($value)) {
        $attachment_id = (int) $value;

        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);

            return $url ? esc_url_raw($url) : '';
        }

        return '';
    }

    return esc_url_raw((string) $value);
}

/**
 * Hero packaging image: Customizer upload, fallback theme file.
 *
 * @return array{has_image: bool, url: string, width: int, height: int}
 */
function voitkus_hero_bag_image(): array
{
    $fallback = [
        'has_image' => false,
        'url'       => '',
        'width'     => 380,
        'height'    => 560,
    ];

    $stored = get_theme_mod('voitkus_hero_bag_image', '');

    if (is_numeric($stored)) {
        $stored = wp_get_attachment_url((int) $stored) ?: '';
    }

    $url = esc_url_raw((string) $stored);

    if ($url !== '') {
        $attachment_id = attachment_url_to_postid($url);
        $image = $attachment_id > 0 ? wp_get_attachment_image_src($attachment_id, 'full') : false;

        if ($image) {
            return [
                'has_image' => true,
                'url'       => $image[0],
                'width'     => (int) $image[1],
                'height'    => (int) $image[2],
            ];
        }

        return [
            'has_image' => true,
            'url'       => $url,
            'width'     => $fallback['width'],
            'height'    => $fallback['height'],
        ];
    }

    $relative = '/assets/images/hero-bag.png';
    $path = get_stylesheet_directory() . $relative;

    if (! file_exists($path)) {
        return $fallback;
    }

    $size = getimagesize($path);

    return [
        'has_image' => true,
        'url'       => get_stylesheet_directory_uri() . $relative,
        'width'     => $size[0] ?? $fallback['width'],
        'height'    => $size[1] ?? $fallback['height'],
    ];
}

/**
 * @return array<string, mixed>
 */
function voitkus_why_defaults(): array
{
    return [
        'eyebrow' => 'Czym się wyróżniamy',
        'title'   => 'Dlaczego Voitkus',
        'pillars' => [
            [
                'slug'   => 'terroir',
                'title'  => 'Terytorium',
                'text'   => 'Palimy tak, żeby w filiżance było widać pochodzenie — czysty, wyrazisty profil.',
                'accent' => 'orange',
            ],
            [
                'slug'   => 'control',
                'title'  => 'Kontrola',
                'text'   => 'Jakość przechodzi przez palarnię: od profilu po finalną filiżankę.',
                'accent' => 'yellow',
            ],
            [
                'slug'   => 'grind',
                'title'  => 'Pod Ciebie',
                'text'   => 'Ziarno lub mielenie pod V60, AeroPress, espresso i inne metody.',
                'accent' => 'cyan',
            ],
        ],
    ];
}

/**
 * @return array<string, string>
 */
function voitkus_why_lot_accents(): array
{
    return [
        'yellow'  => __('Żółty', 'voitkus'),
        'orange'  => __('Pomarańczowy', 'voitkus'),
        'magenta' => __('Magenta', 'voitkus'),
        'cyan'    => __('Cyjan', 'voitkus'),
        'lime'    => __('Limonka', 'voitkus'),
    ];
}

function voitkus_sanitize_why_accent($value): string
{
    $allowed = array_keys(voitkus_why_lot_accents());
    $value   = is_string($value) ? $value : '';

    return in_array($value, $allowed, true) ? $value : 'yellow';
}

/**
 * @return array{eyebrow: string, title: string, pillars: array<int, array{slug: string, title: string, text: string, accent: string}>}
 */
function voitkus_why_section(): array
{
    $defaults = voitkus_why_defaults();
    $pillars  = [];

    foreach ($defaults['pillars'] as $index => $default_pillar) {
        $n = $index + 1;

        $pillars[] = [
            'slug'   => $default_pillar['slug'],
            'title'  => (string) get_theme_mod("voitkus_why_pillar_{$n}_title", $default_pillar['title']),
            'text'   => (string) get_theme_mod("voitkus_why_pillar_{$n}_text", $default_pillar['text']),
            'accent' => voitkus_sanitize_why_accent(get_theme_mod("voitkus_why_pillar_{$n}_accent", $default_pillar['accent'])),
        ];
    }

    return [
        'eyebrow' => (string) get_theme_mod('voitkus_why_eyebrow', $defaults['eyebrow']),
        'title'   => (string) get_theme_mod('voitkus_why_title', $defaults['title']),
        'pillars' => $pillars,
    ];
}

function voitkus_customize_register_why(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_why_defaults();

    $wp_customize->add_section('voitkus_why', [
        'title'    => __('Dlaczego Voitkus', 'voitkus'),
        'priority' => 31,
    ]);

    $wp_customize->add_setting('voitkus_why_eyebrow', [
        'default'           => $defaults['eyebrow'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('voitkus_why_eyebrow', [
        'label'   => __('Eyebrow', 'voitkus'),
        'section' => 'voitkus_why',
        'type'    => 'text',
    ]);

    $wp_customize->add_setting('voitkus_why_title', [
        'default'           => $defaults['title'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('voitkus_why_title', [
        'label'   => __('Nagłówek sekcji', 'voitkus'),
        'section' => 'voitkus_why',
        'type'    => 'text',
    ]);

    foreach ($defaults['pillars'] as $index => $pillar) {
        $n     = $index + 1;
        $label = sprintf(
            /* translators: %d: pillar column number (1–3) */
            __('Kolumna %d', 'voitkus'),
            $n
        );

        $wp_customize->add_setting("voitkus_why_pillar_{$n}_title", [
            'default'           => $pillar['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control("voitkus_why_pillar_{$n}_title", [
            'label'   => $label . ' — ' . __('tytuł', 'voitkus'),
            'section' => 'voitkus_why',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("voitkus_why_pillar_{$n}_text", [
            'default'           => $pillar['text'],
            'sanitize_callback' => 'sanitize_textarea_field',
        ]);

        $wp_customize->add_control("voitkus_why_pillar_{$n}_text", [
            'label'   => $label . ' — ' . __('tekst', 'voitkus'),
            'section' => 'voitkus_why',
            'type'    => 'textarea',
        ]);

        $wp_customize->add_setting("voitkus_why_pillar_{$n}_accent", [
            'default'           => $pillar['accent'],
            'sanitize_callback' => 'voitkus_sanitize_why_accent',
        ]);

        $wp_customize->add_control("voitkus_why_pillar_{$n}_accent", [
            'label'   => $label . ' — ' . __('kolor akcentu', 'voitkus'),
            'section' => 'voitkus_why',
            'type'    => 'select',
            'choices' => voitkus_why_lot_accents(),
        ]);
    }
}

/**
 * @return array<string, mixed>
 */
function voitkus_grind_defaults(): array
{
    return [
        'eyebrow'          => 'Świeżość i przygotowanie',
        'title'            => 'Mielimy pod Twój sposób parzenia',
        'intro'            => 'Ziarno lub mielenie dopasowane do metody — bez zgadywania w sklepie.',
        'freshness_title'  => 'Data palenia na każdej paczce',
        'freshness_text'   => 'Wiesz dokładnie, kiedy kawa opuściła palarnię — świeżość, której możesz zaufać.',
        'methods'          => [
            ['slug' => 'v60', 'label' => 'V60', 'accent' => 'cyan'],
            ['slug' => 'aeropress', 'label' => 'AeroPress', 'accent' => 'magenta'],
            ['slug' => 'espresso', 'label' => 'Espresso', 'accent' => 'orange'],
            ['slug' => 'french-press', 'label' => 'French press', 'accent' => 'yellow'],
            ['slug' => 'beans', 'label' => 'Ziarno', 'accent' => 'lime'],
        ],
    ];
}

/**
 * @return array{eyebrow: string, title: string, intro: string, freshness_title: string, freshness_text: string, methods: array<int, array{slug: string, label: string, accent: string}>}
 */
function voitkus_grind_section(): array
{
    $defaults = voitkus_grind_defaults();
    $methods  = [];

    foreach ($defaults['methods'] as $index => $default_method) {
        $n = $index + 1;

        $methods[] = [
            'slug'   => $default_method['slug'],
            'label'  => (string) get_theme_mod("voitkus_grind_method_{$n}_label", $default_method['label']),
            'accent' => voitkus_sanitize_why_accent(get_theme_mod("voitkus_grind_method_{$n}_accent", $default_method['accent'])),
        ];
    }

    return [
        'eyebrow'         => (string) get_theme_mod('voitkus_grind_eyebrow', $defaults['eyebrow']),
        'title'           => (string) get_theme_mod('voitkus_grind_title', $defaults['title']),
        'intro'           => (string) get_theme_mod('voitkus_grind_intro', $defaults['intro']),
        'freshness_title' => (string) get_theme_mod('voitkus_grind_freshness_title', $defaults['freshness_title']),
        'freshness_text'  => (string) get_theme_mod('voitkus_grind_freshness_text', $defaults['freshness_text']),
        'methods'         => $methods,
    ];
}

function voitkus_customize_register_grind(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_grind_defaults();

    $wp_customize->add_section('voitkus_grind', [
        'title'    => __('Mielenie i świeżość', 'voitkus'),
        'priority' => 32,
    ]);

    $text_fields = [
        'voitkus_grind_eyebrow'         => [__('Eyebrow', 'voitkus'), $defaults['eyebrow']],
        'voitkus_grind_title'           => [__('Nagłówek sekcji', 'voitkus'), $defaults['title']],
        'voitkus_grind_intro'           => [__('Tekst wprowadzający', 'voitkus'), $defaults['intro']],
        'voitkus_grind_freshness_title' => [__('Świeżość — tytuł', 'voitkus'), $defaults['freshness_title']],
        'voitkus_grind_freshness_text'  => [__('Świeżość — tekst', 'voitkus'), $defaults['freshness_text']],
    ];

    foreach ($text_fields as $setting_id => [$label, $default]) {
        $is_textarea = str_contains($setting_id, '_text') || str_contains($setting_id, '_intro');

        $wp_customize->add_setting($setting_id, [
            'default'           => $default,
            'sanitize_callback' => $is_textarea ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ]);

        $wp_customize->add_control($setting_id, [
            'label'   => $label,
            'section' => 'voitkus_grind',
            'type'    => $is_textarea ? 'textarea' : 'text',
        ]);
    }

    foreach ($defaults['methods'] as $index => $method) {
        $n     = $index + 1;
        $label = sprintf(
            /* translators: %d: brew method slot number (1–5) */
            __('Metoda %d', 'voitkus'),
            $n
        );

        $wp_customize->add_setting("voitkus_grind_method_{$n}_label", [
            'default'           => $method['label'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control("voitkus_grind_method_{$n}_label", [
            'label'   => $label . ' — ' . __('nazwa', 'voitkus'),
            'section' => 'voitkus_grind',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("voitkus_grind_method_{$n}_accent", [
            'default'           => $method['accent'],
            'sanitize_callback' => 'voitkus_sanitize_why_accent',
        ]);

        $wp_customize->add_control("voitkus_grind_method_{$n}_accent", [
            'label'   => $label . ' — ' . __('kolor akcentu', 'voitkus'),
            'section' => 'voitkus_grind',
            'type'    => 'select',
            'choices' => voitkus_why_lot_accents(),
        ]);
    }
}

/**
 * @return array<string, string>
 */
function voitkus_founder_defaults(): array
{
    return [
        'eyebrow'     => 'Za palarnią',
        'name'        => 'Cześć, jestem Mikita',
        'role'        => 'Założyciel · Voitkus Coffee Roastery · Warszawa',
        'text_1'      => 'Voitkus zaczął się od prostego pytania: dlaczego kawa nie może smakować tak wyraziście i ciekawie jak dobre wino? Z domowego parzenia przeszedłem do małych partii i pracy z ziarnem, w którym słychać pochodzenie.',
        'text_2'      => 'Dziś palimy w Warszawie — każdy lot z własnym profilem i kontrolą jakości w palarni. Nie gonimy za modą. Pomagamy odkrywać smak — od codziennej filiżanki po odważne, ograniczone loty.',
        'quote'       => 'Palimy tak, żeby w filiżance było widać pochodzenie — czysty, wyrazisty profil.',
        'link_label'  => 'Poznaj historię',
        'link_url'    => '/about/',
    ];
}

/**
 * @return array{eyebrow: string, name: string, role: string, text_1: string, text_2: string, quote: string, link_label: string, link_url: string, image: array{has_image: bool, url: string, width: int, height: int}}
 */
function voitkus_founder_section(): array
{
    $defaults = voitkus_founder_defaults();
    $link_url = (string) get_theme_mod('voitkus_founder_link_url', $defaults['link_url']);

    if ($link_url !== '' && strpos($link_url, 'http') !== 0) {
        $link_url = home_url($link_url);
    }

    return [
        'eyebrow'    => (string) get_theme_mod('voitkus_founder_eyebrow', $defaults['eyebrow']),
        'name'       => (string) get_theme_mod('voitkus_founder_name', $defaults['name']),
        'role'       => (string) get_theme_mod('voitkus_founder_role', $defaults['role']),
        'text_1'     => (string) get_theme_mod('voitkus_founder_text_1', $defaults['text_1']),
        'text_2'     => (string) get_theme_mod('voitkus_founder_text_2', $defaults['text_2']),
        'quote'      => (string) get_theme_mod('voitkus_founder_quote', $defaults['quote']),
        'link_label' => (string) get_theme_mod('voitkus_founder_link_label', $defaults['link_label']),
        'link_url'   => esc_url($link_url),
        'image'      => voitkus_founder_image(),
    ];
}

/**
 * @return array{has_image: bool, url: string, width: int, height: int}
 */
function voitkus_founder_image(): array
{
    $fallback = [
        'has_image' => false,
        'url'       => '',
        'width'     => 480,
        'height'    => 600,
    ];

    $stored = get_theme_mod('voitkus_founder_image', '');

    if (is_numeric($stored)) {
        $stored = wp_get_attachment_url((int) $stored) ?: '';
    }

    $url = esc_url_raw((string) $stored);

    if ($url === '') {
        return $fallback;
    }

    $attachment_id = attachment_url_to_postid($url);
    $image         = $attachment_id > 0 ? wp_get_attachment_image_src($attachment_id, 'large') : false;

    if ($image) {
        return [
            'has_image' => true,
            'url'       => $image[0],
            'width'     => (int) $image[1],
            'height'    => (int) $image[2],
        ];
    }

    return [
        'has_image' => true,
        'url'       => $url,
        'width'     => $fallback['width'],
        'height'    => $fallback['height'],
    ];
}

function voitkus_customize_register_founder(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_founder_defaults();

    $wp_customize->add_section('voitkus_founder', [
        'title'    => __('Założyciel (strona główna)', 'voitkus'),
        'priority' => 33,
    ]);

    $wp_customize->add_setting('voitkus_founder_image', [
        'default'           => '',
        'sanitize_callback' => 'voitkus_sanitize_hero_bag_image',
    ]);

    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'voitkus_founder_image',
            [
                'label'       => __('Zdjęcie założyciela', 'voitkus'),
                'description' => __('Portret w pionie — najlepiej 3:4.', 'voitkus'),
                'section'     => 'voitkus_founder',
                'settings'    => 'voitkus_founder_image',
            ]
        )
    );

    $text_fields = [
        'voitkus_founder_eyebrow'    => [__('Eyebrow', 'voitkus'), $defaults['eyebrow']],
        'voitkus_founder_name'       => [__('Nagłówek', 'voitkus'), $defaults['name']],
        'voitkus_founder_role'       => [__('Rola / podpis', 'voitkus'), $defaults['role']],
        'voitkus_founder_text_1'     => [__('Akapit 1', 'voitkus'), $defaults['text_1']],
        'voitkus_founder_text_2'     => [__('Akapit 2', 'voitkus'), $defaults['text_2']],
        'voitkus_founder_quote'      => [__('Cytat', 'voitkus'), $defaults['quote']],
        'voitkus_founder_link_label' => [__('Link — etykieta', 'voitkus'), $defaults['link_label']],
        'voitkus_founder_link_url'   => [__('Link — URL (np. /about/)', 'voitkus'), $defaults['link_url']],
    ];

    foreach ($text_fields as $key => $field) {
        $label   = $field[0];
        $default = $field[1];
        $type    = (strpos($key, '_text_') !== false || $key === 'voitkus_founder_quote')
            ? 'textarea'
            : 'text';

        $wp_customize->add_setting($key, [
            'default'           => $default,
            'sanitize_callback' => $type === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ]);

        $wp_customize->add_control($key, [
            'label'   => $label,
            'section' => 'voitkus_founder',
            'type'    => $type,
        ]);
    }
}

/**
 * @return array{eyebrow: string, title: string, items: array<int, array{quote: string, author: string, accent: string}>}
 */
function voitkus_reviews_defaults(): array
{
    return [
        'eyebrow' => 'Opinie',
        'title'   => 'Co mówią klienci',
        'items'   => [
            [
                'quote'  => 'Pierwsza Etiopia która smakowała jak jagody.',
                'author' => 'Klient Voitkus',
                'accent' => 'magenta',
            ],
            [
                'quote'  => 'Najlepsze espresso jakie zrobiłem w domu.',
                'author' => 'Klient Voitkus',
                'accent' => 'orange',
            ],
            [
                'quote'  => 'Świetnie opisane profile.',
                'author' => 'Klient Voitkus',
                'accent' => 'cyan',
            ],
        ],
    ];
}

function voitkus_reviews_section(): array
{
    $defaults = voitkus_reviews_defaults();
    $items    = [];

    foreach ($defaults['items'] as $index => $default_item) {
        $n = $index + 1;

        $quote = trim((string) get_theme_mod("voitkus_review_{$n}_quote", $default_item['quote']));

        if ($quote === '') {
            continue;
        }

        $items[] = [
            'quote'  => $quote,
            'author' => trim((string) get_theme_mod("voitkus_review_{$n}_author", $default_item['author'])),
            'accent' => voitkus_sanitize_why_accent(get_theme_mod("voitkus_review_{$n}_accent", $default_item['accent'])),
        ];
    }

    return [
        'eyebrow' => (string) get_theme_mod('voitkus_reviews_eyebrow', $defaults['eyebrow']),
        'title'   => (string) get_theme_mod('voitkus_reviews_title', $defaults['title']),
        'items'   => $items,
    ];
}

function voitkus_customize_register_reviews(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_reviews_defaults();

    $wp_customize->add_section('voitkus_reviews', [
        'title'    => __('Opinie klientów (strona główna)', 'voitkus'),
        'priority' => 34,
    ]);

    $header_fields = [
        'voitkus_reviews_eyebrow' => [__('Eyebrow', 'voitkus'), $defaults['eyebrow']],
        'voitkus_reviews_title'   => [__('Nagłówek sekcji', 'voitkus'), $defaults['title']],
    ];

    foreach ($header_fields as $key => $field) {
        $wp_customize->add_setting($key, [
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control($key, [
            'label'   => $field[0],
            'section' => 'voitkus_reviews',
            'type'    => 'text',
        ]);
    }

    foreach ($defaults['items'] as $index => $item) {
        $n     = $index + 1;
        $label = sprintf(
            /* translators: %d: review number (1–3) */
            __('Opinia %d', 'voitkus'),
            $n
        );

        $wp_customize->add_setting("voitkus_review_{$n}_quote", [
            'default'           => $item['quote'],
            'sanitize_callback' => 'sanitize_textarea_field',
        ]);

        $wp_customize->add_control("voitkus_review_{$n}_quote", [
            'label'   => $label . ' — ' . __('cytat', 'voitkus'),
            'section' => 'voitkus_reviews',
            'type'    => 'textarea',
        ]);

        $wp_customize->add_setting("voitkus_review_{$n}_author", [
            'default'           => $item['author'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control("voitkus_review_{$n}_author", [
            'label'   => $label . ' — ' . __('autor', 'voitkus'),
            'section' => 'voitkus_reviews',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("voitkus_review_{$n}_accent", [
            'default'           => $item['accent'],
            'sanitize_callback' => 'voitkus_sanitize_why_accent',
        ]);

        $wp_customize->add_control("voitkus_review_{$n}_accent", [
            'label'   => $label . ' — ' . __('kolor akcentu', 'voitkus'),
            'section' => 'voitkus_reviews',
            'type'    => 'select',
            'choices' => voitkus_why_lot_accents(),
        ]);
    }
}

/**
 * @return array<int, array{title: string, links: array<int, array{label: string, url: string}>}>
 */
function voitkus_footer_link_groups(): array
{
    return [
        [
            'title' => __('Sklep', 'voitkus'),
            'links' => [
                ['label' => __('Kawa', 'voitkus'), 'url' => home_url('/shop/')],
                ['label' => __('Koszyk', 'voitkus'), 'url' => voitkus_cart_url()],
                ['label' => __('Konto', 'voitkus'), 'url' => voitkus_account_url()],
            ],
        ],
        [
            'title' => 'Voitkus',
            'links' => [
                ['label' => __('O nas', 'voitkus'), 'url' => home_url('/about/')],
                ['label' => __('B2B', 'voitkus'), 'url' => home_url('/b2b/')],
                ['label' => __('Kontakt', 'voitkus'), 'url' => home_url('/contact/')],
                ['label' => __('Parzenie', 'voitkus'), 'url' => home_url('/brew-guides/')],
            ],
        ],
        [
            'title' => __('Informacje', 'voitkus'),
            'links' => [
                ['label' => __('Dostawa i płatność', 'voitkus'), 'url' => home_url('/legal/shipping/')],
                ['label' => __('Regulamin', 'voitkus'), 'url' => home_url('/legal/terms/')],
                ['label' => __('RODO', 'voitkus'), 'url' => home_url('/legal/privacy/')],
            ],
        ],
    ];
}

/**
 * Oficjalne dane JDG (CEIDG, NIP potwierdzony).
 *
 * @return array{
 *   legal_name: string,
 *   short_name: string,
 *   owner: string,
 *   nip: string,
 *   street: string,
 *   postcode: string,
 *   city: string,
 *   country: string,
 *   email: string,
 *   activity_start: string
 * }
 */
function voitkus_company_details(): array
{
    return [
        'legal_name'     => 'Mikita Voitkus VOITKUSCOFFEE',
        'short_name'     => 'MIKITA VOITKUS',
        'owner'          => 'Mikita Voitkus',
        'nip'            => '5253092710',
        'street'         => 'ul. Adama Mickiewicza 38',
        'postcode'       => '01-650',
        'city'           => 'Warszawa',
        'country'        => 'PL',
        'email'          => 'voitkus.coffee@gmail.com',
        'activity_start' => '2026-06-04',
    ];
}

function voitkus_company_address_line(): string
{
    $c = voitkus_company_details();

    return sprintf('%s, %s %s', $c['street'], $c['postcode'], $c['city']);
}

/**
 * Jednorazowa synchronizacja adresu sklepu WooCommerce.
 */
function voitkus_sync_woocommerce_company_address(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    $flag = 'voitkus_wc_company_sync_v1';

    if (get_option($flag) === 'done') {
        return;
    }

    $c = voitkus_company_details();

    update_option('woocommerce_store_address', 'Adama Mickiewicza 38');
    update_option('woocommerce_store_city', $c['city']);
    update_option('woocommerce_store_postcode', $c['postcode']);
    update_option('woocommerce_default_country', $c['country']);
    update_option('woocommerce_store_address_2', '');

    if (get_option('woocommerce_email_from_name') === false || get_option('woocommerce_email_from_name') === '') {
        update_option('woocommerce_email_from_name', $c['short_name']);
    }

    update_option($flag, 'done', false);
}
add_action('init', 'voitkus_sync_woocommerce_company_address', 6);

/**
 * Strony prawne: /legal/terms/ + przypisanie do WooCommerce.
 */
function voitkus_find_page_by_slug(string $slug, int $parent_id = 0): int
{
    $query = new WP_Query(
        [
            'post_type'              => 'page',
            'post_status'            => 'publish',
            'name'                   => $slug,
            'post_parent'            => $parent_id,
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]
    );

    if ($query->have_posts()) {
        return (int) $query->posts[0];
    }

    return 0;
}

function voitkus_ensure_page(string $slug, string $title, string $content, int $parent_id = 0): int
{
    $existing_id = voitkus_find_page_by_slug($slug, $parent_id);

    if ($existing_id > 0) {
        if ($content !== '') {
            wp_update_post(
                [
                    'ID'           => $existing_id,
                    'post_content' => $content,
                ]
            );
        }

        return $existing_id;
    }

    $page_id = wp_insert_post(
        [
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_parent'  => $parent_id,
        ],
        true
    );

    return is_wp_error($page_id) ? 0 : (int) $page_id;
}

function voitkus_ensure_legal_pages(): void
{
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }

    $flag = 'voitkus_legal_pages_v1';

    if (get_option($flag) === 'done') {
        return;
    }

    $legal_id = voitkus_ensure_page('legal', 'Informacje prawne', '');

    if ($legal_id <= 0) {
        return;
    }

    $terms_id = voitkus_ensure_page('terms', 'Regulamin', '[voitkus_regulamin]', $legal_id);

    if ($terms_id > 0 && class_exists('WooCommerce')) {
        update_option('woocommerce_terms_page_id', $terms_id);
    }

    update_option($flag, 'done', false);
}
add_action('init', 'voitkus_ensure_legal_pages', 8);

function voitkus_register_legal_shortcodes(): void
{
    add_shortcode('voitkus_regulamin', 'voitkus_regulamin_shortcode');
}
add_action('init', 'voitkus_register_legal_shortcodes');

/**
 * @return array{tagline: string, instagram: string, email: string}
 */
function voitkus_footer_defaults(): array
{
    $company = voitkus_company_details();

    return [
        'tagline'   => 'Świeża palarnia · Warszawa · Małe partie',
        'instagram' => 'https://instagram.com/voitkuscoffee',
        'email'     => $company['email'],
    ];
}

/**
 * @return array{tagline: string, instagram: string, email: string, year: int, link_groups: array<int, array{title: string, links: array<int, array{label: string, url: string}>}>}
 */
function voitkus_footer_section(): array
{
    $defaults = voitkus_footer_defaults();

    return [
        'tagline'     => (string) get_theme_mod('voitkus_footer_tagline', $defaults['tagline']),
        'instagram'   => esc_url((string) get_theme_mod('voitkus_footer_instagram', $defaults['instagram'])),
        'email'       => sanitize_email((string) get_theme_mod('voitkus_footer_email', $defaults['email'])),
        'year'        => (int) gmdate('Y'),
        'link_groups' => voitkus_footer_link_groups(),
    ];
}

function voitkus_customize_register_footer(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_footer_defaults();

    $wp_customize->add_section('voitkus_footer', [
        'title'    => __('Footer', 'voitkus'),
        'priority' => 35,
    ]);

    $fields = [
        'voitkus_footer_tagline'   => [__('Tagline', 'voitkus'), $defaults['tagline'], 'text'],
        'voitkus_footer_instagram' => [__('Instagram URL', 'voitkus'), $defaults['instagram'], 'url'],
        'voitkus_footer_email'     => [__('Email', 'voitkus'), $defaults['email'], 'email'],
    ];

    foreach ($fields as $key => $field) {
        $sanitize = $field[2] === 'email' ? 'sanitize_email' : ($field[2] === 'url' ? 'esc_url_raw' : 'sanitize_text_field');

        $wp_customize->add_setting($key, [
            'default'           => $field[1],
            'sanitize_callback' => $sanitize,
        ]);

        $wp_customize->add_control($key, [
            'label'   => $field[0],
            'section' => 'voitkus_footer',
            'type'    => $field[2] === 'url' ? 'url' : 'text',
        ]);
    }
}
