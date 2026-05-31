<?php
/**
 * Single product layout.
 *
 * @package Voitkus
 * @var WC_Product $args['product']
 */

if (! defined('ABSPATH')) {
    exit;
}

$product = (isset($args) && is_array($args) ? ($args['product'] ?? null) : null);

if (! $product instanceof WC_Product) {
    return;
}

$lot          = voitkus_build_lot_from_product($product, 0);
$shop_url     = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$content      = apply_filters('the_content', $product->get_description());
$gallery_ids  = voitkus_product_gallery_ids($product);
$related_lots = voitkus_related_lots($product, 3);
$weight_label = $lot['specs'][__('Waga', 'voitkus')] ?? '';

if ($weight_label === '' && function_exists('voitkus_lot_field')) {
    $weight_label = voitkus_lot_field((int) $product->get_id(), 'voitkus_weight', $product);
}

$facts = $lot['specs'];

if ($weight_label !== '') {
    unset($facts[__('Waga', 'voitkus')]);
}

$style = ($lot['accent_css'] ?? '') !== ''
    ? ' style="--lot-accent: ' . esc_attr($lot['accent_css']) . ';"'
    : '';

$stock_html = '';

if (! $product->is_in_stock()) {
    $stock_html = '<p class="product-page__stock product-page__stock--out">' . esc_html__('Chwilowo niedostępna', 'voitkus') . '</p>';
} elseif ($product->is_on_backorder()) {
    $stock_html = '<p class="product-page__stock product-page__stock--backorder">' . esc_html__('Dostępna na zamówienie', 'voitkus') . '</p>';
} else {
    $stock_html = '<p class="product-page__stock product-page__stock--in">' . esc_html__('Dostępna — wysyłka 24–48h', 'voitkus') . '</p>';
}
?>

<article
    id="product-<?php echo esc_attr((string) $product->get_id()); ?>"
    <?php wc_product_class('product-page', $product); ?>
    data-lot-accent="<?php echo esc_attr($lot['accent'] ?? 'yellow'); ?>"
    <?php echo $style; ?>
>
    <div class="product-page__inner">
        <nav class="product-page__breadcrumb" aria-label="<?php esc_attr_e('Nawigacja', 'voitkus'); ?>">
            <ol class="product-page__breadcrumb-list">
                <li><a href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('Sklep', 'voitkus'); ?></a></li>
                <li aria-current="page"><?php echo esc_html($lot['title']); ?></li>
            </ol>
        </nav>

        <div class="product-page__layout">
            <div class="product-page__media">
                <div class="product-page__gallery" data-product-gallery>
                    <div class="product-page__gallery-stage">
                        <?php if (($lot['badge'] ?? '') !== '') : ?>
                            <span class="product-page__badge lot-card__badge"><?php echo esc_html($lot['badge']); ?></span>
                        <?php endif; ?>

                        <?php if ($gallery_ids !== []) : ?>
                            <?php
                            $main_id  = $gallery_ids[0];
                            $main_src = wp_get_attachment_image_url($main_id, 'large') ?: wp_get_attachment_image_url($main_id, 'full');
                            ?>
                            <img
                                class="product-page__gallery-img"
                                src="<?php echo esc_url((string) $main_src); ?>"
                                alt="<?php echo esc_attr($lot['title']); ?>"
                                decoding="async"
                            >
                        <?php else : ?>
                            <div class="product-page__gallery-placeholder"><?php esc_html_e('FOTO PACZKI', 'voitkus'); ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($gallery_ids) > 1) : ?>
                        <ul class="product-page__gallery-thumbs" role="list">
                            <?php foreach ($gallery_ids as $index => $attachment_id) : ?>
                                <?php
                                $thumb_src = wp_get_attachment_image_url($attachment_id, 'woocommerce_thumbnail')
                                    ?: wp_get_attachment_image_url($attachment_id, 'medium');
                                $full_src  = wp_get_attachment_image_url($attachment_id, 'large')
                                    ?: wp_get_attachment_image_url($attachment_id, 'full');
                                ?>
                                <li>
                                    <button
                                        type="button"
                                        class="product-page__gallery-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                        data-full-src="<?php echo esc_url((string) $full_src); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('Zdjęcie %d', 'voitkus'), $index + 1)); ?>"
                                        aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                    >
                                        <img src="<?php echo esc_url((string) $thumb_src); ?>" alt="" loading="lazy" decoding="async">
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="product-page__summary">
                <div class="product-page__summary-head">
                    <?php if (! empty($lot['brew_badges'])) : ?>
                        <div class="product-page__brew-badges">
                            <?php foreach ($lot['brew_badges'] as $brew_badge) : ?>
                                <span class="lot-card__brew-badge" data-brew="<?php echo esc_attr($brew_badge['slug']); ?>">
                                    <?php echo esc_html($brew_badge['label']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (($lot['origin'] ?? '') !== '') : ?>
                        <p class="product-page__origin"><?php echo esc_html($lot['origin']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="product-page__title-row">
                    <h1 class="product-page__title"><?php echo esc_html($lot['title']); ?></h1>
                    <div class="product-page__price-wrap">
                        <div class="product-page__price lot-card__price">
                            <?php echo wp_kses_post($lot['price_html']); ?>
                        </div>
                        <?php if ($weight_label !== '') : ?>
                            <p class="product-page__weight"><?php echo esc_html($weight_label); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (! empty($lot['hook'])) : ?>
                    <div class="product-page__lead">
                        <p class="product-page__lead-label"><?php esc_html_e('Dla kogo', 'voitkus'); ?></p>
                        <p class="product-page__hook"><?php echo esc_html($lot['hook']); ?></p>
                    </div>
                <?php endif; ?>

                <?php if (! empty($lot['notes'])) : ?>
                    <div class="product-page__notes-block">
                        <p class="product-page__section-label"><?php esc_html_e('Nuty smakowe', 'voitkus'); ?></p>
                        <ul class="lot-card__notes product-page__notes" aria-label="<?php esc_attr_e('Nuty', 'voitkus'); ?>">
                            <?php foreach ($lot['notes'] as $note) : ?>
                                <li class="lot-card__note"><?php echo esc_html($note); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (! empty($facts)) : ?>
                    <div class="product-page__facts">
                        <p class="product-page__section-label"><?php esc_html_e('Szczegóły', 'voitkus'); ?></p>
                        <dl class="product-page__facts-list">
                            <?php foreach ($facts as $label => $value) : ?>
                                <div class="product-page__fact">
                                    <dt><?php echo esc_html($label); ?></dt>
                                    <dd><?php echo esc_html($value); ?></dd>
                                </div>
                            <?php endforeach; ?>
                        </dl>
                    </div>
                <?php endif; ?>

                <div class="product-page__buybox">
                    <?php echo wp_kses_post($stock_html); ?>

                    <div class="product-page__purchase">
                        <?php woocommerce_template_single_add_to_cart(); ?>
                    </div>

                    <ul class="product-page__trust" aria-label="<?php esc_attr_e('Informacje o zamówieniu', 'voitkus'); ?>">
                        <li><?php esc_html_e('Palona co tydzień — wysyłamy świeże ziarno', 'voitkus'); ?></li>
                        <li><?php esc_html_e('Mielenie pod Twoją metodę parzenia', 'voitkus'); ?></li>
                        <li><?php esc_html_e('Bezpieczna płatność i szybka dostawa', 'voitkus'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if ($content !== '') : ?>
            <section class="product-page__description" aria-labelledby="product-description-title">
                <div class="product-page__description-grid">
                    <div class="product-page__description-intro">
                        <p class="product-page__section-label"><?php esc_html_e('O tej kawie', 'voitkus'); ?></p>
                        <h2 id="product-description-title" class="product-page__description-title">
                            <?php esc_html_e('Opis', 'voitkus'); ?>
                        </h2>
                    </div>
                    <div class="product-page__description-body">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($related_lots !== []) : ?>
            <section class="product-page__related" aria-labelledby="product-related-title">
                <div class="product-page__related-head">
                    <p class="product-page__section-label"><?php esc_html_e('Więcej do odkrycia', 'voitkus'); ?></p>
                    <h2 id="product-related-title" class="product-page__related-title">
                        <?php esc_html_e('Może Ci się spodobać', 'voitkus'); ?>
                    </h2>
                </div>
                <div class="lots__grid product-page__related-grid">
                    <?php foreach ($related_lots as $related_lot) : ?>
                        <?php get_template_part('template-parts/lot-card', null, ['lot' => $related_lot]); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</article>
