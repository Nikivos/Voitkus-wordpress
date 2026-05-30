<?php
/**
 * Front page — Mielenie i świeżość.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('voitkus_grind_section')) {
    return;
}

$grind = voitkus_grind_section();

$methods = [];

foreach ($grind['methods'] as $method) {
    if (! is_array($method) || $method['label'] === '') {
        continue;
    }

    $methods[] = $method;
}

$has_freshness = $grind['freshness_title'] !== '' || $grind['freshness_text'] !== '';

if ($grind['title'] === '' && $methods === [] && ! $has_freshness) {
    return;
}
?>

<section class="grind" aria-labelledby="grind-title">
    <div class="grind__inner">
        <div class="grind__layout">
            <div class="grind__main">
                <header class="grind__header">
                    <?php if ($grind['eyebrow'] !== '') : ?>
                        <p class="grind__eyebrow"><?php echo esc_html($grind['eyebrow']); ?></p>
                    <?php endif; ?>
                    <?php if ($grind['title'] !== '') : ?>
                        <h2 id="grind-title" class="grind__title"><?php echo esc_html($grind['title']); ?></h2>
                    <?php endif; ?>
                    <?php if ($grind['intro'] !== '') : ?>
                        <p class="grind__intro"><?php echo esc_html($grind['intro']); ?></p>
                    <?php endif; ?>
                </header>

                <?php if ($methods !== []) : ?>
                    <ul class="grind__methods">
                        <?php foreach ($methods as $method) : ?>
                            <li class="grind-method" data-grind="<?php echo esc_attr($method['slug']); ?>" data-lot-accent="<?php echo esc_attr($method['accent']); ?>">
                                <span class="grind-method__dot" aria-hidden="true"></span>
                                <span class="grind-method__label"><?php echo esc_html($method['label']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <?php if ($has_freshness) : ?>
                <aside class="grind__freshness">
                    <?php if ($grind['freshness_title'] !== '') : ?>
                        <h3 class="grind__freshness-title"><?php echo esc_html($grind['freshness_title']); ?></h3>
                    <?php endif; ?>
                    <?php if ($grind['freshness_text'] !== '') : ?>
                        <p class="grind__freshness-text"><?php echo esc_html($grind['freshness_text']); ?></p>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</section>
