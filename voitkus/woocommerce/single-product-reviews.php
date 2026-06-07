<?php
/**
 * Product reviews fallback (comments_template).
 *
 * @package Voitkus
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! comments_open()) {
    return;
}
?>

<div id="reviews" class="woocommerce-Reviews product-reviews__panel">
    <?php voitkus_render_product_review_list(); ?>
    <?php voitkus_render_product_review_form($product, false); ?>
</div>
