<?php
/**
 * Shipping — panel dostawy (koszyk + checkout), logika zgodna z WooCommerce.
 *
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined('ABSPATH') || exit;

$formatted_destination    = isset($formatted_destination) ? $formatted_destination : WC()->countries->get_formatted_address($package['destination'], ', ');
$display_destination      = (string) $formatted_destination;

if ($display_destination === '' && function_exists('voitkus_get_shipping_display_destination')) {
    $display_destination = voitkus_get_shipping_display_destination();
}

$has_calculated_shipping  = ! empty($has_calculated_shipping);
$show_shipping_calculator = ! empty($show_shipping_calculator);
$calc_enabled             = 'yes' === get_option('woocommerce_enable_shipping_calc');
$calculator_text          = esc_html__('Oblicz koszty', 'woocommerce');
$has_rates                = isset($available_methods) && is_array($available_methods) && $available_methods !== [];
$is_cart_page             = function_exists('is_cart') && is_cart();
$display_methods          = $has_rates && (! $is_cart_page || $has_calculated_shipping);
$methods_for_state        = $display_methods ? $available_methods : null;
$chosen_for_state         = isset($chosen_method) ? (string) $chosen_method : '';
$is_inpost_method         = $display_methods && function_exists('voitkus_is_inpost_shipping_method')
    ? voitkus_is_inpost_shipping_method($chosen_for_state !== '' ? $chosen_for_state : null, $methods_for_state)
    : false;
$panel_state              = function_exists('voitkus_get_shipping_panel_state')
    ? voitkus_get_shipping_panel_state(
        $methods_for_state,
        $chosen_for_state !== '' ? $chosen_for_state : null,
        $has_calculated_shipping ? $display_destination : ''
    )
    : ['label' => '', 'state' => 'empty'];

if ($is_cart_page && ! $has_calculated_shipping) {
    $panel_state = [
        'label' => esc_html__('Najpierw podaj adres dostawy', 'voitkus'),
        'state' => 'empty',
    ];
}

$show_address_form = $calc_enabled && ($show_shipping_calculator || ($is_cart_page && ! $has_calculated_shipping));
?>
<tr class="woocommerce-shipping-totals shipping voitkus-shipping-panel-row">
    <th class="voitkus-shipping-panel__title"><?php echo wp_kses_post($package_name); ?></th>
    <td data-title="<?php echo esc_attr($package_name); ?>">
        <div
            class="voitkus-shipping-panel is-state-<?php echo esc_attr($panel_state['state']); ?><?php echo $is_cart_page ? ' is-cart-context' : ' is-checkout-context'; ?>"
            data-voitkus-shipping-panel
            aria-live="polite"
        >
            <header class="voitkus-shipping-panel__header">
                <p class="voitkus-shipping-panel__status">
                    <span class="voitkus-shipping-panel__status-dot" aria-hidden="true"></span>
                    <?php echo esc_html($panel_state['label']); ?>
                </p>
            </header>

            <?php if ($show_address_form) : ?>
                <section
                    id="voitkus-shipping-address-step"
                    class="voitkus-shipping-step voitkus-shipping-step--address is-open"
                    aria-labelledby="voitkus-shipping-step-address-title"
                >
                    <h4 id="voitkus-shipping-step-address-title" class="voitkus-shipping-step__title">
                        <span class="voitkus-shipping-step__num" aria-hidden="true">1</span>
                        <?php esc_html_e('Adres dostawy', 'voitkus'); ?>
                    </h4>
                    <p class="voitkus-shipping-step__hint">
                        <?php esc_html_e('Wpisz kod pocztowy i miejscowość, potem kliknij „Oblicz koszty”.', 'voitkus'); ?>
                    </p>
                    <?php if ($has_calculated_shipping && $display_destination !== '') : ?>
                        <p class="voitkus-shipping-address-card__value"><?php echo esc_html($display_destination); ?></p>
                    <?php endif; ?>
                    <?php woocommerce_shipping_calculator($calculator_text); ?>
                </section>
            <?php elseif ($is_cart_page && ! $calc_enabled) : ?>
                <p class="voitkus-shipping-alert voitkus-shipping-alert--warning">
                    <?php esc_html_e('Włącz kalkulator wysyłki: WooCommerce → Ustawienia → Wysyłka → Obliczenia.', 'voitkus'); ?>
                </p>
            <?php endif; ?>

            <?php if ($display_methods) : ?>
                <section class="voitkus-shipping-step voitkus-shipping-step--methods is-open" aria-labelledby="voitkus-shipping-step-methods-title">
                    <h4 id="voitkus-shipping-step-methods-title" class="voitkus-shipping-step__title">
                        <span class="voitkus-shipping-step__num" aria-hidden="true">2</span>
                        <?php esc_html_e('Wybierz metodę dostawy', 'voitkus'); ?>
                    </h4>
                    <ul
                        id="shipping_method"
                        class="woocommerce-shipping-methods"
                        role="radiogroup"
                        aria-label="<?php esc_attr_e('Wybierz metodę dostawy', 'voitkus'); ?>"
                    >
                        <?php foreach ($available_methods as $method) : ?>
                            <?php
                            $input_id   = sprintf('shipping_method_%1$d_%2$s', $index, esc_attr(sanitize_title($method->id)));
                            $is_chosen  = $method->id === $chosen_method;
                            $show_radio = count($available_methods) > 1;
                            ?>
                            <li class="voitkus-shipping-method-item<?php echo $is_chosen ? ' is-selected' : ''; ?>" data-shipping-method-id="<?php echo esc_attr($method->id); ?>">
                                <?php if ($show_radio) : ?>
                                    <input
                                        type="radio"
                                        name="shipping_method[<?php echo esc_attr((string) $index); ?>]"
                                        data-index="<?php echo esc_attr((string) $index); ?>"
                                        id="<?php echo esc_attr($input_id); ?>"
                                        value="<?php echo esc_attr($method->id); ?>"
                                        class="shipping_method"
                                        <?php checked($method->id, $chosen_method, false); ?>
                                    />
                                <?php else : ?>
                                    <input
                                        type="hidden"
                                        name="shipping_method[<?php echo esc_attr((string) $index); ?>]"
                                        data-index="<?php echo esc_attr((string) $index); ?>"
                                        id="<?php echo esc_attr($input_id); ?>"
                                        value="<?php echo esc_attr($method->id); ?>"
                                        class="shipping_method"
                                    />
                                <?php endif; ?>
                                <div class="voitkus-shipping-method-item__body">
                                    <?php if ($show_radio) : ?>
                                        <label for="<?php echo esc_attr($input_id); ?>">
                                            <?php echo wp_kses_post(wc_cart_totals_shipping_method_label($method)); ?>
                                        </label>
                                    <?php else : ?>
                                        <?php echo wp_kses_post(wc_cart_totals_shipping_method_label($method)); ?>
                                    <?php endif; ?>
                                    <div class="voitkus-shipping-method-plugins">
                                        <?php do_action('woocommerce_after_shipping_rate', $method, $index); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($is_inpost_method) : ?>
                        <p class="voitkus-shipping-inpost-hint" data-voitkus-inpost-hint>
                            <?php esc_html_e('Po wyborze InPost użyj przycisku mapy w ramce metody powyżej.', 'voitkus'); ?>
                        </p>
                        <p class="voitkus-shipping-selected-point" data-voitkus-inpost-selected hidden></p>
                    <?php endif; ?>
                </section>
            <?php elseif ($is_cart_page && ! $has_calculated_shipping) : ?>
                <p class="voitkus-shipping-alert voitkus-shipping-alert--info">
                    <?php esc_html_e('Po obliczeniu kosztów zobaczysz dostępne metody dostawy (krok 2).', 'voitkus'); ?>
                </p>
            <?php elseif (! $has_rates) : ?>
                <div class="voitkus-shipping-panel__messages">
                    <?php
                    if (! $has_calculated_shipping || ! $formatted_destination) {
                        echo wp_kses_post(apply_filters('woocommerce_shipping_may_be_available_html', ''));
                    } elseif (! $is_cart_page) {
                        echo wp_kses_post(apply_filters('woocommerce_no_shipping_available_html', ''));
                    } else {
                        echo wp_kses_post(
                            apply_filters(
                                'woocommerce_cart_no_shipping_available_html',
                                '',
                                $formatted_destination
                            )
                        );
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($show_package_details)) : ?>
                <p class="woocommerce-shipping-contents"><small><?php echo esc_html($package_details); ?></small></p>
            <?php endif; ?>

            <?php
            if (function_exists('voitkus_render_shipping_tools_markup')) {
                voitkus_render_shipping_tools_markup();
            }
            ?>
        </div>
    </td>
</tr>
