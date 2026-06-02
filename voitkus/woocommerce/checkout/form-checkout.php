<?php
/**
 * Checkout Form
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if (! defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}
?>

<form
    name="checkout"
    method="post"
    class="checkout woocommerce-checkout"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>"
    enctype="multipart/form-data"
    aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>"
>

    <div class="checkout-layout">
        <div class="checkout-layout__main">
            <?php if ($checkout->get_checkout_fields()) : ?>
                <?php do_action('woocommerce_checkout_before_customer_details'); ?>

                <div class="checkout-customer-details" id="customer_details">
                    <div class="checkout-customer-details__billing">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>

                    <div class="checkout-customer-details__shipping">
                        <?php do_action('woocommerce_checkout_shipping'); ?>
                    </div>
                </div>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>
            <?php endif; ?>
        </div>

        <aside class="checkout-layout__sidebar" aria-labelledby="order_review_heading">
            <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>

            <h3 id="order_review_heading" class="checkout-order-review__title">
                <?php esc_html_e('Twoje zamówienie', 'voitkus'); ?>
            </h3>

            <?php do_action('woocommerce_checkout_before_order_review'); ?>

            <div id="order_review" class="woocommerce-checkout-review-order checkout-order-review">
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>

            <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </aside>
    </div>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
