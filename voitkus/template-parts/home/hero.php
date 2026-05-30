<?php
/**
 * Front page — hero.
 *
 * @package Voitkus
 * @var array{has_image:bool,url:string,width:int,height:int} $args['hero_bag']
 */

if (! defined('ABSPATH')) {
    exit;
}

$hero_bag = (isset($args) && is_array($args) ? ($args['hero_bag'] ?? null) : null) ?? voitkus_hero_bag_image();
?>

<main class="hero">
    <div class="hero__main">
        <div class="hero__intro">
            <p class="hero__eyebrow" lang="en">COFFEE ROASTERY · POLAND</p>

            <h1 id="hero-title" lang="en">
                Roasted<br>
                for curious<br>
                people.
            </h1>
        </div>

        <figure class="hero__product" aria-label="<?php esc_attr_e('Opakowanie kawy Voitkus', 'voitkus'); ?>">
            <?php if ($hero_bag['has_image']) : ?>
                <img
                    class="hero-bag"
                    src="<?php echo esc_url($hero_bag['url']); ?>"
                    alt="<?php esc_attr_e('Opakowanie kawy Voitkus', 'voitkus'); ?>"
                    width="<?php echo esc_attr((string) $hero_bag['width']); ?>"
                    height="<?php echo esc_attr((string) $hero_bag['height']); ?>"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            <?php else : ?>
                <div class="hero-bag hero-bag--placeholder" role="img" aria-label="<?php esc_attr_e('Opakowanie kawy Voitkus', 'voitkus'); ?>">
                    <span lang="en">VOITKUS PACK</span>
                </div>
            <?php endif; ?>
        </figure>

        <div class="hero__copy">
            <p class="hero__text">
                <?php esc_html_e('Czysta, wyrazista kawa speciality — terroir, przejrzystość i słodycz w filiżance.', 'voitkus'); ?>
            </p>
            <a class="hero__cta" href="<?php echo esc_url(home_url('/shop/')); ?>">
                <?php esc_html_e('Kup kawę', 'voitkus'); ?>
            </a>
        </div>
    </div>
</main>
