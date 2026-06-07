<?php
/**
 * Product taste profile bars (PDP only).
 *
 * @package Voitkus
 * @var array<int, array{label: string, score: int, percent: int}> $args['items']
 */

if (! defined('ABSPATH')) {
    exit;
}

$items = (isset($args) && is_array($args) ? ($args['items'] ?? null) : null);

if (! is_array($items) || $items === []) {
    return;
}
?>

    <section class="product-taste" data-voitkus-taste aria-labelledby="product-taste-title">
    <div class="product-taste__grid">
        <div class="product-taste__intro">
            <p class="product-page__section-label"><?php esc_html_e('Profil sensoryczny', 'voitkus'); ?></p>
            <h2 id="product-taste-title" class="product-taste__title">
                <?php esc_html_e('Kierunek smaku', 'voitkus'); ?>
            </h2>
        </div>

        <div class="product-taste__bars">
            <?php foreach ($items as $item) : ?>
                <div class="product-taste__row">
                    <span class="product-taste__label"><?php echo esc_html($item['label']); ?></span>
                    <div
                        class="product-taste__track"
                        role="meter"
                        aria-label="<?php echo esc_attr(sprintf('%s — %d/10', $item['label'], $item['score'])); ?>"
                        aria-valuemin="0"
                        aria-valuemax="10"
                        aria-valuenow="<?php echo esc_attr((string) $item['score']); ?>"
                    >
                        <span
                            class="product-taste__fill"
                            data-fill="<?php echo esc_attr((string) $item['percent']); ?>"
                            style="--fill: <?php echo esc_attr((string) $item['percent']); ?>;"
                        ></span>
                    </div>
                    <span class="product-taste__score"><?php echo esc_html((string) $item['score']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
