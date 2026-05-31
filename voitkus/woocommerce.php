<?php
/**
 * WooCommerce wrapper.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="woocommerce-page">
    <?php woocommerce_content(); ?>
</main>

<?php
get_footer();
