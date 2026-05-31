<?php
/**
 * Front page — customer reviews.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$reviews = voitkus_reviews_section();
?>

<section class="reviews" aria-labelledby="reviews-title">
    <div class="reviews__inner">
        <header class="reviews__header">
            <?php if ($reviews['eyebrow'] !== '') : ?>
                <p class="reviews__eyebrow"><?php echo esc_html($reviews['eyebrow']); ?></p>
            <?php endif; ?>
            <?php if ($reviews['title'] !== '') : ?>
                <h2 id="reviews-title" class="reviews__title"><?php echo esc_html($reviews['title']); ?></h2>
            <?php endif; ?>
        </header>

        <?php if ($reviews['items'] !== []) : ?>
            <ul class="reviews__grid">
                <?php foreach ($reviews['items'] as $index => $item) : ?>
                    <li class="review-card" data-lot-accent="<?php echo esc_attr($item['accent']); ?>">
                        <div class="review-card__stars" aria-label="<?php esc_attr_e('Ocena 5 na 5', 'voitkus'); ?>">
                            <span aria-hidden="true">★★★★★</span>
                        </div>
                        <?php if ($item['quote'] !== '') : ?>
                            <blockquote class="review-card__quote">
                                <p>«<?php echo esc_html($item['quote']); ?>»</p>
                            </blockquote>
                        <?php endif; ?>
                        <?php if ($item['author'] !== '') : ?>
                            <p class="review-card__author"><?php echo esc_html($item['author']); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
