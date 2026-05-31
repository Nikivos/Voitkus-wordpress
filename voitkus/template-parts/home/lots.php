<?php
/**
 * Front page — Nasze kawy.
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
                <h2 id="lots-title" class="lots__title"><?php esc_html_e('Nasze kawy', 'voitkus'); ?></h2>
            </div>
            <a href="<?php echo esc_url(home_url('/shop/')); ?>" class="lots__link"><?php esc_html_e('Zobacz wszystkie', 'voitkus'); ?></a>
        </header>

        <div class="lots__grid">
            <?php foreach ($lots as $lot) : ?>
                <?php get_template_part('template-parts/lot-card', null, ['lot' => $lot]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
