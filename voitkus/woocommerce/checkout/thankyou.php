<?php
/**
 * Thank you page
 *
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order|false $order
 */

defined('ABSPATH') || exit;
?>

<div class="woocommerce-order voitkus-order-received">

    <?php if ($order) : ?>

        <?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>

        <?php if ($order->has_status('failed')) : ?>

            <div class="voitkus-order-received__card voitkus-order-received__card--error">
                <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
                    <?php esc_html_e('Niestety nie udało się przetworzyć płatności. Spróbuj ponownie lub wybierz inną metodę.', 'voitkus'); ?>
                </p>
                <p class="woocommerce-thankyou-order-failed-actions">
                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button voitkus-order-received__btn">
                        <?php esc_html_e('Opłać zamówienie', 'voitkus'); ?>
                    </a>
                    <?php if (is_user_logged_in()) : ?>
                        <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="button voitkus-order-received__btn voitkus-order-received__btn--ghost">
                            <?php esc_html_e('Moje konto', 'voitkus'); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </div>

        <?php else : ?>

            <div class="voitkus-order-received-layout">
                <aside class="voitkus-order-received__aside">
                    <?php wc_get_template('checkout/order-received.php', ['order' => $order]); ?>

                    <div class="voitkus-order-received__summary-card">
                        <dl class="voitkus-order-received__summary-list">
                            <div class="voitkus-order-received__summary-row">
                                <dt><?php esc_html_e('Data', 'voitkus'); ?></dt>
                                <dd><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></dd>
                            </div>
                            <?php if ($order->get_billing_email()) : ?>
                                <div class="voitkus-order-received__summary-row">
                                    <dt><?php esc_html_e('E-mail', 'voitkus'); ?></dt>
                                    <dd><?php echo esc_html($order->get_billing_email()); ?></dd>
                                </div>
                            <?php endif; ?>
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
                            <a class="voitkus-order-received__btn" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">
                                <?php esc_html_e('Kontynuuj zakupy', 'voitkus'); ?>
                            </a>
                            <?php if (is_user_logged_in()) : ?>
                                <a class="voitkus-order-received__btn voitkus-order-received__btn--ghost" href="<?php echo esc_url(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))); ?>">
                                    <?php esc_html_e('Moje zamówienia', 'voitkus'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </aside>

                <div class="voitkus-order-received__main">
                    <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
                    <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
                </div>
            </div>

        <?php endif; ?>

    <?php else : ?>

        <?php wc_get_template('checkout/order-received.php', ['order' => false]); ?>

    <?php endif; ?>

</div>
