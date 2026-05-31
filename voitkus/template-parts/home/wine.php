<?php
/**
 * Front page — Odkrywaj kawę jak wino.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$rows = [
    [
        'wine'   => __('Region', 'voitkus'),
        'coffee' => __('Region', 'voitkus'),
    ],
    [
        'wine'   => __('Szczep', 'voitkus'),
        'coffee' => __('Odmiana', 'voitkus'),
    ],
    [
        'wine'   => __('Terroir', 'voitkus'),
        'coffee' => __('Terroir', 'voitkus'),
    ],
    [
        'wine'   => __('Nuty smakowe', 'voitkus'),
        'coffee' => __('Nuty smakowe', 'voitkus'),
    ],
];
?>

<section class="wine" aria-labelledby="wine-title">
    <div class="wine__inner">
        <div class="wine__layout">
            <header class="wine__intro">
                <p class="wine__eyebrow" lang="en">FOR CURIOUS PEOPLE</p>
                <h2 id="wine-title" class="wine__title">
                    <?php esc_html_e('Odkrywaj kawę', 'voitkus'); ?><br>
                    <span class="wine__title-accent"><?php esc_html_e('jak wino', 'voitkus'); ?></span>
                </h2>
                <p class="wine__text">
                    <?php esc_html_e('Kawa może być tak różnorodna jak wino. My pomagamy to odkryć.', 'voitkus'); ?>
                </p>
            </header>

            <div class="wine__panel" role="group" aria-label="<?php esc_attr_e('Porównanie wina i kawy', 'voitkus'); ?>">
                <div class="wine__panel-head">
                    <span class="wine__panel-label wine__panel-label--wine"><?php esc_html_e('Wino', 'voitkus'); ?></span>
                    <span class="wine__panel-label wine__panel-label--coffee"><?php esc_html_e('Kawa', 'voitkus'); ?></span>
                </div>

                <ul class="wine__rows">
                    <?php foreach ($rows as $index => $row) : ?>
                        <li class="wine-row">
                            <span class="wine-row__index" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span>
                            <span class="wine-row__wine"><?php echo esc_html($row['wine']); ?></span>
                            <span class="wine-row__coffee"><?php echo esc_html($row['coffee']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
