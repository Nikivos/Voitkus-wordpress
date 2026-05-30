<?php
/**
 * Front page — Aktualne loty.
 *
 * @package Voitkus
 * @var array<int, array<string, mixed>> $args['lots']
 */

if (! defined('ABSPATH')) {
    exit;
}

$lots = (isset($args) && is_array($args) ? ($args['lots'] ?? null) : null) ?? voitkus_current_lots(3);

if (empty($lots)) {
    return;
}
?>

<section class="lots" aria-labelledby="lots-title">
    <div class="lots__inner">
        <header class="lots__header">
            <div class="lots__heading">
                <h2 id="lots-title" class="lots__title"><?php esc_html_e('Aktualne loty', 'voitkus'); ?></h2>
            </div>
            <a href="<?php echo esc_url(home_url('/shop/')); ?>" class="lots__link"><?php esc_html_e('Zobacz wszystkie', 'voitkus'); ?></a>
        </header>

        <div class="lots__grid">
            <?php foreach ($lots as $lot) : ?>
                <a href="<?php echo esc_url($lot['url']); ?>" class="lot-card" data-lot-accent="<?php echo esc_attr($lot['accent']); ?>"<?php echo $lot['accent_css'] !== '' ? ' style="--lot-accent: ' . esc_attr($lot['accent_css']) . ';"' : ''; ?>>
                    <div class="lot-card__visual">
                        <?php if ($lot['badge'] !== '') : ?>
                            <span class="lot-card__badge"><?php echo esc_html($lot['badge']); ?></span>
                        <?php endif; ?>

                        <?php if ($lot['image_html'] !== '') : ?>
                            <div class="lot-card__image"><?php echo wp_kses_post($lot['image_html']); ?></div>
                        <?php else : ?>
                            <div class="lot-card__image lot-card__image--placeholder"><?php esc_html_e('FOTO PACZKI', 'voitkus'); ?></div>
                        <?php endif; ?>

                        <span class="lot-card__quick-add">
                            <span><?php esc_html_e('Do koszyka', 'voitkus'); ?></span>
                            <span class="lot-card__quick-add-icon" aria-hidden="true">+</span>
                        </span>
                    </div>
                    <div class="lot-card__content">
                        <?php if ($lot['origin'] !== '') : ?>
                            <p class="lot-card__origin"><?php echo esc_html($lot['origin']); ?></p>
                        <?php endif; ?>
                        <div class="lot-card__title-row">
                            <h3 class="lot-card__title"><?php echo esc_html($lot['title']); ?></h3>
                            <span class="lot-card__price"><?php echo wp_kses_post($lot['price_html']); ?></span>
                        </div>

                        <?php if (! empty($lot['hook'])) : ?>
                            <p class="lot-card__hook"><?php echo esc_html($lot['hook']); ?></p>
                        <?php endif; ?>

                        <?php if (! empty($lot['notes'])) : ?>
                            <ul class="lot-card__notes" aria-label="<?php esc_attr_e('Nuty', 'voitkus'); ?>">
                                <?php foreach ($lot['notes'] as $note) : ?>
                                    <li class="lot-card__note"><?php echo esc_html($note); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (! empty($lot['specs'])) : ?>
                            <dl class="lot-card__specs">
                                <?php foreach ($lot['specs'] as $label => $value) : ?>
                                    <div class="lot-card__spec">
                                        <dt class="lot-card__spec-label"><?php echo esc_html($label); ?></dt>
                                        <dd class="lot-card__spec-value"><?php echo esc_html($value); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
