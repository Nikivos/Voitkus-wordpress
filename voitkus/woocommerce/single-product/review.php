<?php
/**
 * Single product review item.
 *
 * @package Voitkus
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

global $comment;
?>

<li <?php comment_class('product-review', $comment); ?> id="comment-<?php comment_ID(); ?>">
    <article class="product-review__card review-card">
        <?php
        $rating = (int) get_comment_meta($comment->comment_ID, 'rating', true);

        if ($rating > 0 && wc_review_ratings_enabled()) {
            echo '<div class="product-review__stars" aria-label="'
                . esc_attr(sprintf(__('Ocena %d na 5', 'voitkus'), $rating))
                . '">';
            echo wc_get_rating_html($rating);
            echo '</div>';
        }
        ?>

        <div class="product-review__body">
            <?php comment_text(); ?>
        </div>

        <footer class="product-review__meta">
            <cite class="product-review__author review-card__author"><?php comment_author(); ?></cite>
            <?php if ((string) get_comment_meta($comment->comment_ID, 'verified', true) === '1') : ?>
                <span class="product-review__verified"><?php esc_html_e('Zweryfikowany zakup', 'voitkus'); ?></span>
            <?php endif; ?>
            <time class="product-review__date" datetime="<?php echo esc_attr(get_comment_date('c')); ?>">
                <?php echo esc_html(get_comment_date()); ?>
            </time>
        </footer>
    </article>
</li>
