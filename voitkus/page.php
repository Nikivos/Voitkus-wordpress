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
?>

<main class="<?php echo esc_attr($page_classes); ?>">
    <div class="page-main__inner">
        <?php while (have_posts()) : ?>
            <?php the_post(); ?>
            <header class="page-main__header">
                <h1 class="page-main__title"><?php the_title(); ?></h1>
            </header>
            <div class="page-main__content">
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<?php
get_footer();
