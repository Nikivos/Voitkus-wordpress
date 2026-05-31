<?php
/**
 * Single product — Voitkus layout.
 *
 * @package Voitkus
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
