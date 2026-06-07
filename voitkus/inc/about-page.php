<?php
/**
 * Strona Palarnia (/about/).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_about_logo(?int $post_id = null): array
{
    unset($post_id);

    $mods = get_theme_mods();
    $mods = is_array($mods) ? $mods : [];

    if (array_key_exists('voitkus_about_logo_image', $mods)) {
        $stored = get_theme_mod('voitkus_about_logo_image', '');

        if (is_numeric($stored)) {
            $stored = wp_get_attachment_url((int) $stored) ?: '';
        }

        $url = esc_url_raw((string) $stored);

        if ($url === '') {
            return [
                'has_image' => false,
                'url'       => '',
                'width'     => 800,
                'height'    => 320,
            ];
        }

        $attachment_id = attachment_url_to_postid($url);
        $image         = $attachment_id > 0 ? wp_get_attachment_image_src($attachment_id, 'large') : false;

        if ($image) {
            return [
                'has_image' => true,
                'url'       => $image[0],
                'width'     => (int) $image[1],
                'height'    => (int) $image[2],
                'version'   => (string) ($attachment_id ?: md5($url)),
            ];
        }

        return [
            'has_image' => true,
            'url'       => $url,
            'width'     => 800,
            'height'    => 320,
            'version'   => md5($url),
        ];
    }

    $file = voitkus_about_theme_logo_file();

    if (($file['url'] ?? '') !== '') {
        return [
            'has_image' => true,
            'url'       => $file['url'],
            'width'     => (int) $file['width'],
            'height'    => (int) $file['height'],
            'version'   => (string) ($file['version'] ?? ''),
        ];
    }

    return [
        'has_image' => false,
        'url'       => '',
        'width'     => 800,
        'height'    => 320,
    ];
}

function voitkus_render_about_page(): string
{
    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');
    $content  = voitkus_about_content();
    $logo     = voitkus_about_logo();
    $principles = is_array($content['principles'] ?? null) ? $content['principles'] : [];

    ob_start();
    ?>
    <div class="about-page__content">
        <header class="about-hero">
            <?php if (($content['hero_eyebrow'] ?? '') !== '') : ?>
                <p class="about-hero__eyebrow"><?php echo esc_html($content['hero_eyebrow']); ?></p>
            <?php endif; ?>
            <?php if (($content['hero_brand'] ?? '') !== '') : ?>
                <h1 class="about-hero__brand"><?php echo esc_html($content['hero_brand']); ?></h1>
            <?php endif; ?>
            <?php if (($content['hero_title'] ?? '') !== '') : ?>
                <p class="about-hero__title"><?php echo esc_html($content['hero_title']); ?></p>
            <?php endif; ?>
            <?php if (($content['hero_lead'] ?? '') !== '') : ?>
                <p class="about-hero__lead"><?php echo esc_html($content['hero_lead']); ?></p>
            <?php endif; ?>
        </header>

        <section class="about-section about-story" aria-labelledby="about-story-title">
            <?php if (($content['story_title'] ?? '') !== '') : ?>
                <h2 id="about-story-title" class="about-section__title"><?php echo esc_html($content['story_title']); ?></h2>
            <?php endif; ?>
            <?php if (($content['story_text'] ?? '') !== '') : ?>
                <p class="about-story__text"><?php echo esc_html($content['story_text']); ?></p>
            <?php endif; ?>
            <?php if ($logo['has_image']) : ?>
                <figure class="about-story__accent about-story__accent--logo">
                    <img
                        class="about-story__accent-logo"
                        src="<?php echo esc_url($logo['url']); ?>?v=<?php echo esc_attr($logo['version'] ?? ''); ?>"
                        alt="<?php esc_attr_e('VOITKUS coffee roastery', 'voitkus'); ?>"
                        width="<?php echo esc_attr((string) $logo['width']); ?>"
                        height="<?php echo esc_attr((string) $logo['height']); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
            <?php else : ?>
                <div class="about-story__accent" aria-hidden="true">
                    <span class="about-story__accent-word"><?php echo esc_html($content['hero_brand'] ?? 'VOITKUS'); ?></span>
                    <span class="about-story__accent-tag"><?php esc_html_e('Roasted for curious people', 'voitkus'); ?></span>
                </div>
            <?php endif; ?>
        </section>

        <section class="about-section about-practice" aria-labelledby="about-practice-title">
            <?php if (($content['practice_title'] ?? '') !== '') : ?>
                <h2 id="about-practice-title" class="about-section__title"><?php echo esc_html($content['practice_title']); ?></h2>
            <?php endif; ?>
            <div class="about-practice__groups">
                <div class="about-practice__group">
                    <p class="about-practice__label"><?php esc_html_e('Zasada', 'voitkus'); ?></p>
                    <ul class="about-list about-list--check">
                        <?php if (($content['practice_1'] ?? '') !== '') : ?>
                            <li><?php echo esc_html($content['practice_1']); ?></li>
                        <?php endif; ?>
                        <?php if (($content['practice_2'] ?? '') !== '') : ?>
                            <li><?php echo esc_html($content['practice_2']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="about-practice__group">
                    <p class="about-practice__label"><?php esc_html_e('Proces', 'voitkus'); ?></p>
                    <ul class="about-list about-list--check">
                        <?php if (($content['practice_3'] ?? '') !== '') : ?>
                            <li><?php echo esc_html($content['practice_3']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>

        <section class="about-section about-values" aria-labelledby="about-principles-title">
            <?php if (($content['values_title'] ?? '') !== '') : ?>
                <h2 id="about-principles-title" class="about-section__title"><?php echo esc_html($content['values_title']); ?></h2>
            <?php endif; ?>
            <div class="about-values__grid about-values__grid--three">
                <?php foreach ($principles as $principle) : ?>
                    <?php if (($principle['title'] ?? '') === '' && ($principle['text'] ?? '') === '') : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <article class="about-value-card">
                        <?php if (($principle['title'] ?? '') !== '') : ?>
                            <h3 class="about-value-card__title"><?php echo esc_html($principle['title']); ?></h3>
                        <?php endif; ?>
                        <?php if (($principle['text'] ?? '') !== '') : ?>
                            <p class="about-value-card__text"><?php echo esc_html($principle['text']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <?php if (($content['manifesto'] ?? '') !== '') : ?>
            <div class="about-manifesto" role="note">
                <p class="about-manifesto__text"><?php echo esc_html($content['manifesto']); ?></p>
            </div>
        <?php endif; ?>

        <section class="about-page__cta">
            <?php if (($content['cta_title'] ?? '') !== '') : ?>
                <h2 class="about-page__cta-title"><?php echo esc_html($content['cta_title']); ?></h2>
            <?php endif; ?>
            <?php if (($content['cta_text'] ?? '') !== '') : ?>
                <p class="about-page__cta-text"><?php echo esc_html($content['cta_text']); ?></p>
            <?php endif; ?>
            <a class="about-page__btn" href="<?php echo esc_url($shop_url); ?>">
                <?php echo esc_html($content['cta_button'] ?? __('Co teraz palimy →', 'voitkus')); ?>
            </a>
        </section>
    </div>
    <?php

    return (string) ob_get_clean();
}

function voitkus_about_shortcode(): string
{
    return voitkus_render_about_page();
}
