<?php
/**
 * Baseline SEO meta + Open Graph (when no SEO plugin is active).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_seo_plugin_active(): bool
{
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('AIOSEO_VERSION');
}

function voitkus_seo_default_description(): string
{
    $tagline = trim((string) get_bloginfo('description', 'display'));

    if ($tagline !== '') {
        return $tagline;
    }

    return __('Palarnia specialty coffee — świeżo palona kawa, wysyłka w całej Polsce.', 'voitkus');
}

function voitkus_seo_trim_description(string $text, int $max = 158): string
{
    $text = trim(wp_strip_all_tags($text));

    if ($text === '') {
        return '';
    }

    if (mb_strlen($text) <= $max) {
        return $text;
    }

    $trimmed = mb_substr($text, 0, $max - 1);
    $last_space = mb_strrpos($trimmed, ' ');

    if ($last_space !== false && $last_space > (int) ($max * 0.6)) {
        $trimmed = mb_substr($trimmed, 0, $last_space);
    }

    return rtrim($trimmed, ".,;:!?") . '…';
}

function voitkus_seo_current_url(): string
{
    if (is_singular()) {
        $url = get_permalink();

        return is_string($url) ? $url : home_url('/');
    }

    if (function_exists('is_shop') && is_shop()) {
        $url = wc_get_page_permalink('shop');

        return is_string($url) && $url !== '' ? $url : home_url('/shop/');
    }

    if (is_front_page()) {
        return home_url('/');
    }

    $request_uri = isset($_SERVER['REQUEST_URI'])
        ? wp_unslash((string) $_SERVER['REQUEST_URI'])
        : '/';

    return home_url($request_uri);
}

function voitkus_seo_default_image_url(): string
{
    $logo_id = (int) get_theme_mod('custom_logo');

    if ($logo_id > 0) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');

        if (is_string($logo_url) && $logo_url !== '') {
            return $logo_url;
        }
    }

    $icon_url = get_site_icon_url(512);

    return is_string($icon_url) ? $icon_url : '';
}

/**
 * @return array{title: string, description: string, url: string, image: string, type: string}
 */
function voitkus_seo_context(): array
{
    $context = [
        'title'       => wp_get_document_title(),
        'description' => voitkus_seo_default_description(),
        'url'         => voitkus_seo_current_url(),
        'image'       => voitkus_seo_default_image_url(),
        'type'        => 'website',
    ];

    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());

        if ($product instanceof WC_Product) {
            $short = $product->get_short_description();

            if ($short === '' && function_exists('voitkus_lot_field')) {
                $short = voitkus_lot_field(
                    voitkus_lot_product_id($product),
                    'voitkus_hook',
                    $product
                );
            }

            $description = voitkus_seo_trim_description($short);

            if ($description !== '') {
                $context['description'] = $description;
            }

            $image_id = (int) $product->get_image_id();

            if ($image_id > 0) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');

                if (is_string($image_url) && $image_url !== '') {
                    $context['image'] = $image_url;
                }
            }

            $context['type'] = 'product';
        }
    } elseif (is_singular()) {
        $post = get_queried_object();

        if ($post instanceof WP_Post) {
            $excerpt = get_the_excerpt($post);

            if ($excerpt === '') {
                $excerpt = (string) $post->post_content;
            }

            $description = voitkus_seo_trim_description($excerpt);

            if ($description !== '') {
                $context['description'] = $description;
            }

            if (has_post_thumbnail($post)) {
                $thumb_url = get_the_post_thumbnail_url($post, 'large');

                if (is_string($thumb_url) && $thumb_url !== '') {
                    $context['image'] = $thumb_url;
                }
            }

            $context['type'] = 'article';
        }
    } elseif (function_exists('is_shop') && is_shop()) {
        $context['description'] = voitkus_seo_trim_description(
            __('Kawa specialty z palarni Voitkus — świeżo palone loty, przelew i espresso, wysyłka 24–48h.', 'voitkus')
        );
    } elseif (is_front_page()) {
        $context['url'] = home_url('/');
    }

    return $context;
}

function voitkus_seo_print_head_meta(): void
{
    if (is_admin() || voitkus_seo_plugin_active()) {
        return;
    }

    if (is_404()) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";

        return;
    }

    $context = voitkus_seo_context();

    if ($context['description'] !== '') {
        printf(
            '<meta name="description" content="%s">' . "\n",
            esc_attr($context['description'])
        );
    }

    printf('<meta property="og:locale" content="%s">' . "\n", esc_attr(get_locale()));
    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr(get_bloginfo('name', 'display')));
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($context['title']));
    printf('<meta property="og:description" content="%s">' . "\n", esc_attr($context['description']));
    printf('<meta property="og:url" content="%s">' . "\n", esc_url($context['url']));
    printf('<meta property="og:type" content="%s">' . "\n", esc_attr($context['type']));

    if ($context['image'] !== '') {
        printf('<meta property="og:image" content="%s">' . "\n", esc_url($context['image']));
    }

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    printf('<meta name="twitter:title" content="%s">' . "\n", esc_attr($context['title']));
    printf('<meta name="twitter:description" content="%s">' . "\n", esc_attr($context['description']));

    if ($context['image'] !== '') {
        printf('<meta name="twitter:image" content="%s">' . "\n", esc_url($context['image']));
    }
}
add_action('wp_head', 'voitkus_seo_print_head_meta', 1);
