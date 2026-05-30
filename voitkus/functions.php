<?php
/**
 * Voitkus theme setup.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/lots.php';

function voitkus_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('custom-logo', [
        'height'      => 70,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary Menu', 'voitkus'),
    ]);
}
add_action('after_setup_theme', 'voitkus_setup');

function voitkus_enqueue_assets(): void
{
    $tokens_path = get_stylesheet_directory() . '/assets/design-tokens.css';
    $style_path = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style(
        'voitkus-tokens',
        get_stylesheet_directory_uri() . '/assets/design-tokens.css',
        [],
        file_exists($tokens_path) ? (string) filemtime($tokens_path) : wp_get_theme()->get('Version')
    );

    wp_enqueue_style(
        'voitkus-style',
        get_stylesheet_uri(),
        ['voitkus-tokens'],
        file_exists($style_path) ? (string) filemtime($style_path) : wp_get_theme()->get('Version')
    );

    $script_path = get_stylesheet_directory() . '/assets/menu.js';

    wp_enqueue_script(
        'voitkus-menu',
        get_stylesheet_directory_uri() . '/assets/menu.js',
        [],
        file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'voitkus_enqueue_assets');

function voitkus_default_menu(): void
{
    $items = [
        __('Kawa', 'voitkus')     => home_url('/shop/'),
        __('Parzenie', 'voitkus') => home_url('/brew-guides/'),
        __('O nas', 'voitkus')    => home_url('/about/'),
        __('B2B', 'voitkus')      => home_url('/b2b/'),
        __('Kontakt', 'voitkus')  => home_url('/contact/'),
    ];

    echo '<ul class="site-nav__list">';
    foreach ($items as $label => $url) {
        printf(
            '<li><a href="%s">%s</a></li>',
            esc_url($url),
            esc_html($label)
        );
    }
    echo '</ul>';
}

function voitkus_cart_url(): string
{
    if (function_exists('wc_get_cart_url')) {
        return wc_get_cart_url();
    }

    return home_url('/cart/');
}

function voitkus_account_url(): string
{
    if (function_exists('wc_get_page_permalink')) {
        return wc_get_page_permalink('myaccount');
    }

    return home_url('/my-account/');
}

function voitkus_cart_count(): int
{
    if (function_exists('WC') && WC()->cart) {
        return WC()->cart->get_cart_contents_count();
    }

    return 0;
}

function voitkus_customize_register(WP_Customize_Manager $wp_customize): void
{
    $wp_customize->add_section('voitkus_hero', [
        'title'    => __('Hero', 'voitkus'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('voitkus_hero_bag_image', [
        'default'           => '',
        'sanitize_callback' => 'voitkus_sanitize_hero_bag_image',
    ]);

    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'voitkus_hero_bag_image',
            [
                'label'       => __('Zdjęcie opakowania (hero)', 'voitkus'),
                'description' => __('PNG/WebP z przezroczystym tłem — zastąpi różowy placeholder.', 'voitkus'),
                'section'     => 'voitkus_hero',
                'settings'    => 'voitkus_hero_bag_image',
            ]
        )
    );
}
add_action('customize_register', 'voitkus_customize_register');

function voitkus_sanitize_hero_bag_image($value): string
{
    if (is_numeric($value)) {
        $attachment_id = (int) $value;

        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);

            return $url ? esc_url_raw($url) : '';
        }

        return '';
    }

    return esc_url_raw((string) $value);
}

/**
 * Hero packaging image: Customizer upload, fallback theme file.
 *
 * @return array{has_image: bool, url: string, width: int, height: int}
 */
function voitkus_hero_bag_image(): array
{
    $fallback = [
        'has_image' => false,
        'url'       => '',
        'width'     => 380,
        'height'    => 560,
    ];

    $stored = get_theme_mod('voitkus_hero_bag_image', '');

    if (is_numeric($stored)) {
        $stored = wp_get_attachment_url((int) $stored) ?: '';
    }

    $url = esc_url_raw((string) $stored);

    if ($url !== '') {
        $attachment_id = attachment_url_to_postid($url);
        $image = $attachment_id > 0 ? wp_get_attachment_image_src($attachment_id, 'full') : false;

        if ($image) {
            return [
                'has_image' => true,
                'url'       => $image[0],
                'width'     => (int) $image[1],
                'height'    => (int) $image[2],
            ];
        }

        return [
            'has_image' => true,
            'url'       => $url,
            'width'     => $fallback['width'],
            'height'    => $fallback['height'],
        ];
    }

    $relative = '/assets/images/hero-bag.png';
    $path = get_stylesheet_directory() . $relative;

    if (! file_exists($path)) {
        return $fallback;
    }

    $size = getimagesize($path);

    return [
        'has_image' => true,
        'url'       => get_stylesheet_directory_uri() . $relative,
        'width'     => $size[0] ?? $fallback['width'],
        'height'    => $size[1] ?? $fallback['height'],
    ];
}
