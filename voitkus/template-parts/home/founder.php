<?php
/**
 * Front page — founder block.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$founder = voitkus_founder_section();
$image   = $founder['image'];
?>

<section class="founder" aria-labelledby="founder-title">
    <div class="founder__inner">
        <div class="founder__layout">
            <figure class="founder__photo">
                <?php if ($image['has_image']) : ?>
                    <img
                        class="founder__img"
                        src="<?php echo esc_url($image['url']); ?>"
                        alt="<?php echo esc_attr($founder['name']); ?>"
                        width="<?php echo esc_attr((string) $image['width']); ?>"
                        height="<?php echo esc_attr((string) $image['height']); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                <?php else : ?>
                    <div class="founder__placeholder" role="img" aria-label="<?php echo esc_attr($founder['name']); ?>">
                        <span class="founder__placeholder-mark" aria-hidden="true">MV</span>
                        <span class="founder__placeholder-hint"><?php esc_html_e('Dodaj zdjęcie w Wygląd → Dostosuj → Założyciel', 'voitkus'); ?></span>
                    </div>
                <?php endif; ?>
            </figure>

            <div class="founder__content">
                <?php if ($founder['eyebrow'] !== '') : ?>
                    <p class="founder__eyebrow"><?php echo esc_html($founder['eyebrow']); ?></p>
                <?php endif; ?>

                <?php if ($founder['name'] !== '') : ?>
                    <h2 id="founder-title" class="founder__name"><?php echo esc_html($founder['name']); ?></h2>
                <?php endif; ?>

                <?php if ($founder['role'] !== '') : ?>
                    <p class="founder__role"><?php echo esc_html($founder['role']); ?></p>
                <?php endif; ?>

                <?php if ($founder['text_1'] !== '') : ?>
                    <p class="founder__text"><?php echo esc_html($founder['text_1']); ?></p>
                <?php endif; ?>

                <?php if ($founder['text_2'] !== '') : ?>
                    <p class="founder__text"><?php echo esc_html($founder['text_2']); ?></p>
                <?php endif; ?>

                <?php if ($founder['quote'] !== '') : ?>
                    <blockquote class="founder__quote">
                        <p>«<?php echo esc_html($founder['quote']); ?>»</p>
                    </blockquote>
                <?php endif; ?>

                <?php if ($founder['link_label'] !== '' && $founder['link_url'] !== '') : ?>
                    <a class="founder__link" href="<?php echo esc_url($founder['link_url']); ?>">
                        <?php echo esc_html($founder['link_label']); ?>
                        <span aria-hidden="true">→</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
