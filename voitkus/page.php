<?php
/**
 * Default page template.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$is_woocommerce = function_exists('is_woocommerce') && (is_cart() || is_checkout() || is_account_page());
$page_classes   = 'page-main';
if ($is_woocommerce) {
    $page_classes .= ' woocommerce-page';
}
if (function_exists('is_cart') && is_cart()) {
    $page_classes .= ' cart-page';
}
if (function_exists('is_checkout') && is_checkout()) {
    $page_classes .= ' checkout-page';
}
if (
    function_exists('is_wc_endpoint_url')
    && is_checkout()
    && is_wc_endpoint_url('order-received')
) {
    $page_classes .= ' order-received-page';
}
?>

<main class="<?php echo esc_attr($page_classes); ?>">
    <div class="page-main__inner">
        <?php
        $is_order_received = function_exists('is_wc_endpoint_url')
            && function_exists('is_checkout')
            && is_checkout()
            && is_wc_endpoint_url('order-received');
        ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <?php if (! $is_order_received) : ?>
                <header class="page-main__header">
                    <h1 class="page-main__title"><?php the_title(); ?></h1>
                </header>
            <?php endif; ?>
            <div class="page-main__content<?php echo $is_order_received ? ' woocommerce' : ''; ?>">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<?php
get_footer();
