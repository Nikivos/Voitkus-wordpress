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
require_once get_template_directory() . '/inc/product-meta.php';

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

    voitkus_customize_register_why($wp_customize);
    voitkus_customize_register_grind($wp_customize);
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

/**
 * @return array<string, mixed>
 */
function voitkus_why_defaults(): array
{
    return [
        'eyebrow' => 'Czym się wyróżniamy',
        'title'   => 'Dlaczego Voitkus',
        'pillars' => [
            [
                'slug'   => 'terroir',
                'title'  => 'Terytorium',
                'text'   => 'Palimy tak, żeby w filiżance było widać pochodzenie — czysty, wyrazisty profil.',
                'accent' => 'orange',
            ],
            [
                'slug'   => 'control',
                'title'  => 'Kontrola',
                'text'   => 'Jakość przechodzi przez palarnię: od profilu po finalną filiżankę.',
                'accent' => 'yellow',
            ],
            [
                'slug'   => 'grind',
                'title'  => 'Pod Ciebie',
                'text'   => 'Ziarno lub mielenie pod V60, AeroPress, espresso i inne metody.',
                'accent' => 'cyan',
            ],
        ],
    ];
}

/**
 * @return array<string, string>
 */
function voitkus_why_lot_accents(): array
{
    return [
        'yellow'  => __('Żółty', 'voitkus'),
        'orange'  => __('Pomarańczowy', 'voitkus'),
        'magenta' => __('Magenta', 'voitkus'),
        'cyan'    => __('Cyjan', 'voitkus'),
        'lime'    => __('Limonka', 'voitkus'),
    ];
}

function voitkus_sanitize_why_accent($value): string
{
    $allowed = array_keys(voitkus_why_lot_accents());
    $value   = is_string($value) ? $value : '';

    return in_array($value, $allowed, true) ? $value : 'yellow';
}

/**
 * @return array{eyebrow: string, title: string, pillars: array<int, array{slug: string, title: string, text: string, accent: string}>}
 */
function voitkus_why_section(): array
{
    $defaults = voitkus_why_defaults();
    $pillars  = [];

    foreach ($defaults['pillars'] as $index => $default_pillar) {
        $n = $index + 1;

        $pillars[] = [
            'slug'   => $default_pillar['slug'],
            'title'  => (string) get_theme_mod("voitkus_why_pillar_{$n}_title", $default_pillar['title']),
            'text'   => (string) get_theme_mod("voitkus_why_pillar_{$n}_text", $default_pillar['text']),
            'accent' => voitkus_sanitize_why_accent(get_theme_mod("voitkus_why_pillar_{$n}_accent", $default_pillar['accent'])),
        ];
    }

    return [
        'eyebrow' => (string) get_theme_mod('voitkus_why_eyebrow', $defaults['eyebrow']),
        'title'   => (string) get_theme_mod('voitkus_why_title', $defaults['title']),
        'pillars' => $pillars,
    ];
}

function voitkus_customize_register_why(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_why_defaults();

    $wp_customize->add_section('voitkus_why', [
        'title'    => __('Dlaczego Voitkus', 'voitkus'),
        'priority' => 31,
    ]);

    $wp_customize->add_setting('voitkus_why_eyebrow', [
        'default'           => $defaults['eyebrow'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('voitkus_why_eyebrow', [
        'label'   => __('Eyebrow', 'voitkus'),
        'section' => 'voitkus_why',
        'type'    => 'text',
    ]);

    $wp_customize->add_setting('voitkus_why_title', [
        'default'           => $defaults['title'],
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    $wp_customize->add_control('voitkus_why_title', [
        'label'   => __('Nagłówek sekcji', 'voitkus'),
        'section' => 'voitkus_why',
        'type'    => 'text',
    ]);

    foreach ($defaults['pillars'] as $index => $pillar) {
        $n     = $index + 1;
        $label = sprintf(
            /* translators: %d: pillar column number (1–3) */
            __('Kolumna %d', 'voitkus'),
            $n
        );

        $wp_customize->add_setting("voitkus_why_pillar_{$n}_title", [
            'default'           => $pillar['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control("voitkus_why_pillar_{$n}_title", [
            'label'   => $label . ' — ' . __('tytuł', 'voitkus'),
            'section' => 'voitkus_why',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("voitkus_why_pillar_{$n}_text", [
            'default'           => $pillar['text'],
            'sanitize_callback' => 'sanitize_textarea_field',
        ]);

        $wp_customize->add_control("voitkus_why_pillar_{$n}_text", [
            'label'   => $label . ' — ' . __('tekst', 'voitkus'),
            'section' => 'voitkus_why',
            'type'    => 'textarea',
        ]);

        $wp_customize->add_setting("voitkus_why_pillar_{$n}_accent", [
            'default'           => $pillar['accent'],
            'sanitize_callback' => 'voitkus_sanitize_why_accent',
        ]);

        $wp_customize->add_control("voitkus_why_pillar_{$n}_accent", [
            'label'   => $label . ' — ' . __('kolor akcentu', 'voitkus'),
            'section' => 'voitkus_why',
            'type'    => 'select',
            'choices' => voitkus_why_lot_accents(),
        ]);
    }
}

/**
 * @return array<string, mixed>
 */
function voitkus_grind_defaults(): array
{
    return [
        'eyebrow'          => 'Świeżość i przygotowanie',
        'title'            => 'Mielimy pod Twój sposób parzenia',
        'intro'            => 'Ziarno lub mielenie dopasowane do metody — bez zgadywania w sklepie.',
        'freshness_title'  => 'Data palenia na każdej paczce',
        'freshness_text'   => 'Wiesz dokładnie, kiedy kawa opuściła palarnię — świeżość, której możesz zaufać.',
        'methods'          => [
            ['slug' => 'v60', 'label' => 'V60', 'accent' => 'cyan'],
            ['slug' => 'aeropress', 'label' => 'AeroPress', 'accent' => 'magenta'],
            ['slug' => 'espresso', 'label' => 'Espresso', 'accent' => 'orange'],
            ['slug' => 'french-press', 'label' => 'French press', 'accent' => 'yellow'],
            ['slug' => 'beans', 'label' => 'Ziarno', 'accent' => 'lime'],
        ],
    ];
}

/**
 * @return array{eyebrow: string, title: string, intro: string, freshness_title: string, freshness_text: string, methods: array<int, array{slug: string, label: string, accent: string}>}
 */
function voitkus_grind_section(): array
{
    $defaults = voitkus_grind_defaults();
    $methods  = [];

    foreach ($defaults['methods'] as $index => $default_method) {
        $n = $index + 1;

        $methods[] = [
            'slug'   => $default_method['slug'],
            'label'  => (string) get_theme_mod("voitkus_grind_method_{$n}_label", $default_method['label']),
            'accent' => voitkus_sanitize_why_accent(get_theme_mod("voitkus_grind_method_{$n}_accent", $default_method['accent'])),
        ];
    }

    return [
        'eyebrow'         => (string) get_theme_mod('voitkus_grind_eyebrow', $defaults['eyebrow']),
        'title'           => (string) get_theme_mod('voitkus_grind_title', $defaults['title']),
        'intro'           => (string) get_theme_mod('voitkus_grind_intro', $defaults['intro']),
        'freshness_title' => (string) get_theme_mod('voitkus_grind_freshness_title', $defaults['freshness_title']),
        'freshness_text'  => (string) get_theme_mod('voitkus_grind_freshness_text', $defaults['freshness_text']),
        'methods'         => $methods,
    ];
}

function voitkus_customize_register_grind(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_grind_defaults();

    $wp_customize->add_section('voitkus_grind', [
        'title'    => __('Mielenie i świeżość', 'voitkus'),
        'priority' => 32,
    ]);

    $text_fields = [
        'voitkus_grind_eyebrow'         => [__('Eyebrow', 'voitkus'), $defaults['eyebrow']],
        'voitkus_grind_title'           => [__('Nagłówek sekcji', 'voitkus'), $defaults['title']],
        'voitkus_grind_intro'           => [__('Tekst wprowadzający', 'voitkus'), $defaults['intro']],
        'voitkus_grind_freshness_title' => [__('Świeżość — tytuł', 'voitkus'), $defaults['freshness_title']],
        'voitkus_grind_freshness_text'  => [__('Świeżość — tekst', 'voitkus'), $defaults['freshness_text']],
    ];

    foreach ($text_fields as $setting_id => [$label, $default]) {
        $is_textarea = str_contains($setting_id, '_text') || str_contains($setting_id, '_intro');

        $wp_customize->add_setting($setting_id, [
            'default'           => $default,
            'sanitize_callback' => $is_textarea ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ]);

        $wp_customize->add_control($setting_id, [
            'label'   => $label,
            'section' => 'voitkus_grind',
            'type'    => $is_textarea ? 'textarea' : 'text',
        ]);
    }

    foreach ($defaults['methods'] as $index => $method) {
        $n     = $index + 1;
        $label = sprintf(
            /* translators: %d: brew method slot number (1–5) */
            __('Metoda %d', 'voitkus'),
            $n
        );

        $wp_customize->add_setting("voitkus_grind_method_{$n}_label", [
            'default'           => $method['label'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control("voitkus_grind_method_{$n}_label", [
            'label'   => $label . ' — ' . __('nazwa', 'voitkus'),
            'section' => 'voitkus_grind',
            'type'    => 'text',
        ]);

        $wp_customize->add_setting("voitkus_grind_method_{$n}_accent", [
            'default'           => $method['accent'],
            'sanitize_callback' => 'voitkus_sanitize_why_accent',
        ]);

        $wp_customize->add_control("voitkus_grind_method_{$n}_accent", [
            'label'   => $label . ' — ' . __('kolor akcentu', 'voitkus'),
            'section' => 'voitkus_grind',
            'type'    => 'select',
            'choices' => voitkus_why_lot_accents(),
        ]);
    }
}
