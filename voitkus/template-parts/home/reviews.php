<?php
/**
 * Front page — brand reviews (collapsible).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$reviews   = voitkus_reviews_section();
$has_items = $reviews['items'] !== [];
$summary   = $reviews['summary'];
$page_url  = $reviews['page_url'];
?>

<section class="reviews" aria-labelledby="reviews-title">
    <div class="reviews__inner">
        <details
            class="reviews__disclosure"
            id="home-brand-reviews"
            data-reviews-disclosure
            data-has-items="<?php echo $has_items ? '1' : '0'; ?>"
        >
            <summary class="reviews__summary">
                <span class="reviews__summary-main">
                    <?php if ($reviews['eyebrow'] !== '') : ?>
                        <span class="reviews__eyebrow"><?php echo esc_html($reviews['eyebrow']); ?></span>
                    <?php endif; ?>
                    <?php if ($reviews['title'] !== '') : ?>
                        <span id="reviews-title" class="reviews__title"><?php echo esc_html($reviews['title']); ?></span>
                    <?php endif; ?>
                    <span class="reviews__summary-meta">
                        <?php if ($has_items && $summary['average'] > 0) : ?>
                            <?php
                            printf(
                                esc_html(
                                    /* translators: 1: review count, 2: average rating */
                                    _n('%1$s opinia · śr. %2$s/5', '%1$s opinii · śr. %2$s/5', $summary['count'], 'voitkus')
                                ),
                                number_format_i18n($summary['count']),
                                esc_html(number_format_i18n($summary['average'], 1))
                            );
                            ?>
                        <?php else : ?>
                            <?php esc_html_e('Brak opinii o palarni', 'voitkus'); ?>
                        <?php endif; ?>
                    </span>
                </span>
                <span class="reviews__summary-icon" aria-hidden="true"></span>
            </summary>

            <div class="reviews__panel">
                <?php if ($has_items) : ?>
                    <ul class="reviews__grid">
                        <?php foreach ($reviews['items'] as $item) : ?>
                            <?php voitkus_render_brand_review_card($item); ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="reviews__empty">
                        <?php esc_html_e('Na razie brak opinii o palarni. Spróbowałeś naszej kawy? Dodaj swoją.', 'voitkus'); ?>
                    </p>
                <?php endif; ?>

                <div class="reviews__actions">
                    <a class="reviews__cta" href="<?php echo esc_url($page_url . '#form'); ?>">
                        <?php esc_html_e('Oceń Voitkus', 'voitkus'); ?>
                    </a>
                    <?php if ($has_items && $summary['count'] > count($reviews['items'])) : ?>
                        <a class="reviews__link" href="<?php echo esc_url($page_url); ?>">
                            <?php esc_html_e('Zobacz wszystkie', 'voitkus'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </details>
    </div>
</section>
