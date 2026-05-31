<?php
/**
 * Shop archive — Voitkus lot cards.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$active_filter = voitkus_shop_active_filter();
$filters       = voitkus_shop_filters();
$lots          = voitkus_shop_lots();
$shop_url      = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$title         = __('Nasze kawy', 'voitkus');

foreach ($filters as $filter) {
    if ($filter['slug'] === $active_filter && $filter['slug'] !== '') {
        $title = $filter['label'];
        break;
    }
}
?>

<section class="shop-page lots" aria-labelledby="shop-title">
    <div class="shop-page__inner lots__inner">
        <header class="shop-page__header lots__header">
            <div class="shop-page__intro">
                <p class="shop-page__eyebrow"><?php esc_html_e('Sklep', 'voitkus'); ?></p>
                <h1 id="shop-title" class="lots__title shop-page__title"><?php echo esc_html($title); ?></h1>
                <p class="shop-page__lead">
                    <?php esc_html_e('Świeżo wypalone ziarno i mielenie pod Twój sposób parzenia.', 'voitkus'); ?>
                </p>
            </div>

            <nav class="shop-page__filters" aria-label="<?php esc_attr_e('Filtry profilu', 'voitkus'); ?>">
                <ul class="shop-page__filter-list">
                    <?php foreach ($filters as $filter) : ?>
                        <?php
                        $is_active = $filter['slug'] === $active_filter;
                        $url       = $filter['slug'] === ''
                            ? $shop_url
                            : add_query_arg('filter', $filter['slug'], $shop_url);
                        ?>
                        <li>
                            <a
                                class="shop-filter<?php echo $is_active ? ' is-active' : ''; ?>"
                                href="<?php echo esc_url($url); ?>"
                                data-lot-accent="<?php echo esc_attr($filter['accent']); ?>"
                                <?php echo $is_active ? ' aria-current="page"' : ''; ?>
                            >
                                <span class="shop-filter__dot" aria-hidden="true"></span>
                                <?php echo esc_html($filter['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </header>

        <?php if ($lots !== []) : ?>
            <div class="lots__grid shop-page__grid">
                <?php foreach ($lots as $lot) : ?>
                    <?php get_template_part('template-parts/lot-card', null, ['lot' => $lot]); ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="shop-page__empty">
                <p><?php esc_html_e('Brak kaw w tym profilu. Sprawdź inne filtry lub wróć do wszystkich.', 'voitkus'); ?></p>
                <a class="shop-page__empty-link" href="<?php echo esc_url($shop_url); ?>">
                    <?php esc_html_e('Zobacz wszystkie', 'voitkus'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
