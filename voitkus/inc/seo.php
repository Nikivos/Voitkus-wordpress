<?php
/**
 * Baseline SEO: meta, Open Graph, noindex rules, sitemap filters (no SEO plugin).
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

function voitkus_seo_brand_name(): string
{
    return trim((string) get_bloginfo('name', 'display'));
}

function voitkus_seo_product_title(WC_Product $product): string
{
    $title = $product->get_name();

    if (function_exists('voitkus_lot_field')) {
        $weight = trim(
            voitkus_lot_field(
                voitkus_lot_product_id($product),
                'voitkus_weight',
                $product
            )
        );

        if ($weight !== '' && stripos($title, $weight) === false) {
            $title .= ', ' . $weight;
        }
    }

    return $title;
}

/**
 * @param array<string, string> $parts
 * @return array<string, string>
 */
function voitkus_seo_document_title_parts(array $parts): array
{
    if (is_admin() || voitkus_seo_plugin_active()) {
        return $parts;
    }

    $brand = voitkus_seo_brand_name();

    if ($brand === '') {
        return $parts;
    }

    if (is_front_page()) {
        $tagline = trim((string) get_bloginfo('description', 'display'));
        $parts['title']   = $tagline !== '' ? $brand . ' — ' . voitkus_seo_trim_description($tagline, 52) : $brand;
        $parts['site']    = '';
        $parts['tagline'] = '';

        return $parts;
    }

    if (is_404()) {
        $parts['title'] = __('Strona nie istnieje', 'voitkus');
        $parts['site']  = $brand;

        return $parts;
    }

    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product(get_queried_object_id());

        if ($product instanceof WC_Product) {
            $parts['title'] = voitkus_seo_product_title($product);
            $parts['site']  = $brand;
        }

        return $parts;
    }

    if (function_exists('is_shop') && is_shop()) {
        $parts['title'] = __('Kawa specialty', 'voitkus');
        $parts['site']  = $brand;

        return $parts;
    }

    if (function_exists('is_cart') && is_cart()) {
        $parts['title'] = __('Koszyk', 'voitkus');
        $parts['site']  = $brand;

        return $parts;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        $parts['title'] = __('Kasa', 'voitkus');
        $parts['site']  = $brand;

        return $parts;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        $parts['title'] = __('Konto', 'voitkus');
        $parts['site']  = $brand;

        return $parts;
    }

    if (function_exists('is_product_taxonomy') && is_product_taxonomy()) {
        $term = get_queried_object();

        if ($term instanceof WP_Term && $term->name !== '') {
            $parts['title'] = $term->name;
            $parts['site']  = $brand;
        }

        return $parts;
    }

    if (is_singular()) {
        $parts['site'] = $brand;
    }

    return $parts;
}
add_filter('document_title_parts', 'voitkus_seo_document_title_parts', 20);

function voitkus_seo_document_title_separator(string $separator): string
{
    if (is_admin() || voitkus_seo_plugin_active()) {
        return $separator;
    }

    return '|';
}
add_filter('document_title_separator', 'voitkus_seo_document_title_separator', 20);

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
    } elseif (is_front_page()) {
        $context['url']  = home_url('/');
        $context['type'] = 'website';
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
    }

    return $context;
}

/**
 * Page slugs excluded from sitemap and marked noindex.
 *
 * @return string[]
 */
function voitkus_seo_noindex_page_slugs(): array
{
    return [
        'kontakt',
        'o-nas',
        'polityka-prywatnosci',
        'cart',
        'subskrypcja',
    ];
}

/**
 * @return int[]
 */
function voitkus_seo_sitemap_excluded_page_ids(): array
{
    static $ids = null;

    if (is_array($ids)) {
        return $ids;
    }

    $ids = [];

    if (function_exists('wc_get_page_id')) {
        foreach (['cart', 'checkout', 'myaccount'] as $wc_page) {
            $page_id = (int) wc_get_page_id($wc_page);

            if ($page_id > 0) {
                $ids[] = $page_id;
            }
        }
    }

    foreach (voitkus_seo_noindex_page_slugs() as $slug) {
        $page = get_page_by_path($slug);

        if ($page instanceof WP_Post) {
            $ids[] = (int) $page->ID;
        }
    }

    return $ids = array_values(array_unique($ids));
}

function voitkus_seo_should_noindex(): bool
{
    if (is_404() || is_search() || is_author()) {
        return true;
    }

    if (is_category() || is_tag() || is_date()) {
        return true;
    }

    if (function_exists('is_product_taxonomy') && is_product_taxonomy()) {
        return true;
    }

    if (function_exists('is_cart') && is_cart()) {
        return true;
    }

    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    if (function_exists('is_account_page') && is_account_page()) {
        return true;
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
        return true;
    }

    if (is_singular('post')) {
        return true;
    }

    if (is_singular('page')) {
        $slug = (string) get_post_field('post_name', get_queried_object_id());

        return in_array($slug, voitkus_seo_noindex_page_slugs(), true);
    }

    return false;
}

function voitkus_seo_redirect_product_tag_archive(): void
{
    if (is_admin() || voitkus_seo_plugin_active() || ! is_tax('product_tag')) {
        return;
    }

    $term = get_queried_object();

    if (! $term instanceof WP_Term || $term->slug === '') {
        return;
    }

    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '';

    if (! is_string($shop_url) || $shop_url === '') {
        $shop_url = home_url('/shop/');
    }

    wp_safe_redirect(add_query_arg('filter', $term->slug, $shop_url), 301);
    exit;
}
add_action('template_redirect', 'voitkus_seo_redirect_product_tag_archive', 1);

/**
 * @param array<string, WP_Post_Type> $post_types
 * @return array<string, WP_Post_Type>
 */
function voitkus_seo_filter_sitemap_post_types(array $post_types): array
{
    if (voitkus_seo_plugin_active()) {
        return $post_types;
    }

    unset($post_types['post']);

    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'voitkus_seo_filter_sitemap_post_types');

/**
 * @param array<string, WP_Taxonomy> $taxonomies
 * @return array<string, WP_Taxonomy>
 */
function voitkus_seo_filter_sitemap_taxonomies(array $taxonomies): array
{
    if (voitkus_seo_plugin_active()) {
        return $taxonomies;
    }

    unset($taxonomies['category'], $taxonomies['post_tag'], $taxonomies['product_tag'], $taxonomies['product_cat']);

    return $taxonomies;
}
add_filter('wp_sitemaps_taxonomies', 'voitkus_seo_filter_sitemap_taxonomies');

/**
 * @param mixed $provider
 * @return mixed
 */
function voitkus_seo_remove_users_sitemap_provider($provider, string $name)
{
    if (voitkus_seo_plugin_active() || $name !== 'users') {
        return $provider;
    }

    return false;
}
add_filter('wp_sitemaps_add_provider', 'voitkus_seo_remove_users_sitemap_provider', 10, 2);

/**
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function voitkus_seo_filter_sitemap_posts_query_args(array $args, string $post_type): array
{
    if (voitkus_seo_plugin_active() || $post_type !== 'page') {
        return $args;
    }

    $exclude = voitkus_seo_sitemap_excluded_page_ids();

    if ($exclude === []) {
        return $args;
    }

    $args['post__not_in'] = array_values(
        array_unique(
            array_merge(
                isset($args['post__not_in']) && is_array($args['post__not_in']) ? $args['post__not_in'] : [],
                $exclude
            )
        )
    );

    return $args;
}
add_filter('wp_sitemaps_posts_query_args', 'voitkus_seo_filter_sitemap_posts_query_args', 10, 2);

function voitkus_seo_print_head_meta(): void
{
    if (is_admin() || voitkus_seo_plugin_active()) {
        return;
    }

    if (voitkus_seo_should_noindex()) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";

        if (is_404()) {
            return;
        }
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
