<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

if (! defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_single_product');

while (have_posts()) :
    the_post();

    $product = wc_get_product(get_the_ID());

    if (! $product instanceof WC_Product) {
        continue;
    }

    get_template_part('template-parts/product/single', null, ['product' => $product]);
endwhile;

do_action('woocommerce_after_single_product');
