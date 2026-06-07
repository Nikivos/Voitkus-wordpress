<?php
/**
 * 404 Not Found.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

$shop_url = function_exists('wc_get_page_permalink')
    ? wc_get_page_permalink('shop')
    : home_url('/shop/');
$home_url = home_url('/');
?>

<main class="page-main stub-page error-page">
    <div class="page-main__inner">
        <header class="page-main__header">
            <h1 class="page-main__title"><?php esc_html_e('Strona nie istnieje', 'voitkus'); ?></h1>
            <p class="page-main__subtitle">
                <?php esc_html_e('Adres mógł się zmienić albo wpisałeś go z literówką.', 'voitkus'); ?>
            </p>
        </header>

        <div class="page-main__content">
            <div class="stub-page__content">
                <p class="stub-page__lead">
                    <?php esc_html_e('404 — nie znaleźliśmy tej strony.', 'voitkus'); ?>
                </p>
                <p class="stub-page__text">
                    <?php esc_html_e('Wróć do sklepu i wybierz kawę — albo na stronę główną.', 'voitkus'); ?>
                </p>
                <div class="stub-page__actions">
                    <a class="contact-page__btn" href="<?php echo esc_url($shop_url); ?>">
                        <?php esc_html_e('Przejdź do sklepu', 'voitkus'); ?>
                    </a>
                    <a class="stub-page__link" href="<?php echo esc_url($home_url); ?>">
                        <?php esc_html_e('Strona główna', 'voitkus'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
