<?php
/**
 * Front page — Dlaczego Voitkus (3 pillars).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('voitkus_why_section')) {
    return;
}

$why = voitkus_why_section();
?>

<section class="why" aria-labelledby="why-title">
    <div class="why__inner">
        <header class="why__header">
            <?php if ($why['eyebrow'] !== '') : ?>
                <p class="why__eyebrow"><?php echo esc_html($why['eyebrow']); ?></p>
            <?php endif; ?>
            <?php if ($why['title'] !== '') : ?>
                <h2 id="why-title" class="why__title"><?php echo esc_html($why['title']); ?></h2>
            <?php endif; ?>
        </header>

        <div class="why__grid">
            <?php foreach ($why['pillars'] as $pillar) : ?>
                <article class="why-card" data-why="<?php echo esc_attr($pillar['slug']); ?>" data-lot-accent="<?php echo esc_attr($pillar['accent']); ?>">
                    <span class="why-card__dot" aria-hidden="true"></span>
                    <?php if ($pillar['title'] !== '') : ?>
                        <h3 class="why-card__title"><?php echo esc_html($pillar['title']); ?></h3>
                    <?php endif; ?>
                    <?php if ($pillar['text'] !== '') : ?>
                        <p class="why-card__text"><?php echo esc_html($pillar['text']); ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
