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
if (function_exists('is_account_page') && is_account_page()) {
    $page_classes .= ' account-page';
}
if (
    function_exists('is_wc_endpoint_url')
    && is_checkout()
    && is_wc_endpoint_url('order-received')
) {
    $page_classes .= ' order-received-page';
}
if (is_page()) {
    $ancestors = get_post_ancestors(get_queried_object_id());
    $legal_id  = voitkus_find_page_by_slug('legal');
    if ($legal_id > 0 && (get_queried_object_id() === $legal_id || in_array($legal_id, $ancestors, true))) {
        $page_classes .= ' legal-page';
    }
    if (function_exists('voitkus_is_contact_page') && voitkus_is_contact_page()) {
        $page_classes .= ' contact-page';
    }
    if (function_exists('voitkus_is_about_page') && voitkus_is_about_page()) {
        $page_classes .= ' about-page';
    }
}

$inner_classes = 'page-main__inner';
if (function_exists('voitkus_is_about_page') && voitkus_is_about_page()) {
    $inner_classes .= ' page-main__inner--site-grid';
}
if (function_exists('is_account_page') && is_account_page()) {
    $inner_classes .= ' page-main__inner--site-grid';
}
?>

<main class="<?php echo esc_attr($page_classes); ?>">
    <div class="<?php echo esc_attr($inner_classes); ?>">
        <?php
        $is_order_received = function_exists('is_wc_endpoint_url')
            && function_exists('is_checkout')
            && is_checkout()
            && is_wc_endpoint_url('order-received');
        $hide_page_header = $is_order_received
            || (function_exists('voitkus_is_about_page') && voitkus_is_about_page())
            || (
                function_exists('is_account_page')
                && is_account_page()
                && function_exists('is_wc_endpoint_url')
                && is_wc_endpoint_url('view-order')
            );
        ?>
        <?php
        $page_title = get_the_title();

        if (
            function_exists('is_account_page')
            && is_account_page()
            && ! is_user_logged_in()
        ) {
            $page_title = __('Konto', 'voitkus');
        }
        ?>
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <?php if (! $hide_page_header) : ?>
                <header class="page-main__header">
                    <h1 class="page-main__title"><?php echo esc_html($page_title); ?></h1>
                    <?php if (function_exists('is_account_page') && is_account_page() && ! is_user_logged_in()) : ?>
                        <p class="page-main__subtitle">
                            <?php esc_html_e('Zaloguj się, aby śledzić zamówienia i zarządzać danymi dostawy.', 'voitkus'); ?>
                        </p>
                    <?php endif; ?>
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
