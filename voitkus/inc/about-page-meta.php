<?php
/**
 * Palarnia — treść edytowalna w Customizerze (Wygląd → Dostosuj).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_get_about_page_id(): int
{
    static $page_id = null;

    if ($page_id !== null) {
        return $page_id;
    }

    $page_id = function_exists('voitkus_find_page_by_slug')
        ? voitkus_find_page_by_slug('about')
        : 0;

    return (int) $page_id;
}

/**
 * @return array<string, string>
 */
function voitkus_about_defaults(): array
{
    return [
        'hero_eyebrow'     => __('Palarnia specialty · Warszawa', 'voitkus'),
        'hero_brand'       => 'VOITKUS',
        'hero_title'       => __('Małe partie. Konkretny smak.', 'voitkus'),
        'hero_lead'        => __('Palarnia założona przez Mikita Voitkusa. Każdy lot osobno — z testami parzenia, zanim trafi do sklepu. Nie sprzedajemy obietnic. Sprzedajemy ziarno, które da się obronić w filiżance.', 'voitkus'),
        'story_title'      => __('Skąd to się wzięło', 'voitkus'),
        'story_text'       => __('Kawa zaczęła się dla mnie w Japonii — tam po raz pierwszy zobaczyłem, że smak może być precyzyjny i przemyślany. Po powrocie poszedłem w specialty: barista, konkursy, praca za barem, a potem palenie — moment, w którym ziarno naprawdę się formuje. VOITKUS powstał z tego doświadczenia: palimy małe partie tak, żeby w filiżance słychać pochodzenie i pracę palarni.', 'voitkus'),
        'practice_title'   => __('Co robimy w palarni', 'voitkus'),
        'practice_1'       => __('Wybieramy ziarno, którego pochodzenie da się obronić w filiżance — nie pod „ładną nazwę”.', 'voitkus'),
        'practice_2'       => __('Profil palenia budujemy na cuppingu i testach ekstrakcji, nie na zgadywaniu.', 'voitkus'),
        'practice_3'       => __('Partia trafia do sklepu dopiero wtedy, gdy smakuje tak, jak ją opisujemy.', 'voitkus'),
        'values_title'     => __('Na czym nam zależy', 'voitkus'),
        'value_1_title'    => __('Pochodzenie na wierzchu', 'voitkus'),
        'value_1_text'     => __('Region, proces, profil smakowy — podajemy wprost, na stronie produktu i etykiecie. Bez ogólników.', 'voitkus'),
        'value_2_title'    => __('Smak zgodny z opisem', 'voitkus'),
        'value_2_text'     => __('Jeśli piszemy cytrusy i herbata, filiżanka ma brzmieć podobnie. Nie upiększamy profilu pod marketing.', 'voitkus'),
        'value_3_title'    => __('Kontrola każdej partii', 'voitkus'),
        'value_3_text'     => __('Notujemy parametry palenia i sprawdzamy powtarzalność między partiami tego samego lotu.', 'voitkus'),
        'manifesto'        => __('Palimy tak, żeby w filiżance było widać pochodzenie — czysty, wyrazisty profil.', 'voitkus'),
        'cta_title'        => __('Zobacz, co teraz w palarni', 'voitkus'),
        'cta_text'         => __('Aktualne loty z opisem smaku, pochodzeniem i rekomendacją parzenia.', 'voitkus'),
        'cta_button'       => __('Co teraz palimy →', 'voitkus'),
    ];
}

/**
 * @return string[]
 */
function voitkus_about_textarea_keys(): array
{
    return [
        'hero_lead',
        'story_text',
        'practice_1',
        'practice_2',
        'practice_3',
        'value_1_text',
        'value_2_text',
        'value_3_text',
        'manifesto',
        'cta_text',
    ];
}

function voitkus_about_theme_mod_key(string $key): string
{
    return 'voitkus_about_' . $key;
}

/**
 * @return array{url: string, path: string, width: int, height: int, version?: string}
 */
function voitkus_about_theme_logo_file(): array
{
    $relative = 'assets/voitkus-logo-about.png';
    $path     = get_stylesheet_directory() . '/' . $relative;

    if (! is_readable($path)) {
        return [
            'url'    => '',
            'path'   => '',
            'width'  => 800,
            'height' => 320,
        ];
    }

    return [
        'url'     => get_stylesheet_directory_uri() . '/' . $relative,
        'path'    => $path,
        'width'   => 800,
        'height'  => 320,
        'version' => (string) filemtime($path),
    ];
}

function voitkus_about_default_logo_url(): string
{
    $file = voitkus_about_theme_logo_file();

    return (string) ($file['url'] ?? '');
}

function voitkus_customize_register_about_logo_control(WP_Customize_Manager $wp_customize): void
{
    $wp_customize->add_setting('voitkus_about_logo_image', [
        'default'           => voitkus_about_default_logo_url(),
        'sanitize_callback' => 'voitkus_sanitize_hero_bag_image',
    ]);

    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'voitkus_about_logo_image',
            [
                'label'       => __('Historia — logo (czarny blok)', 'voitkus'),
                'description' => __('PNG na czarnym tle pod tekstem historii. Usuń, żeby pokazać tekst VOITKUS.', 'voitkus'),
                'section'     => 'voitkus_about',
                'settings'    => 'voitkus_about_logo_image',
            ]
        )
    );
}

function voitkus_about_content(?int $post_id = null): array
{
    unset($post_id);

    $defaults = voitkus_about_defaults();
    $content  = $defaults;

    foreach ($defaults as $key => $default) {
        $content[ $key ] = (string) get_theme_mod(voitkus_about_theme_mod_key($key), $default);
    }

    $content['principles'] = voitkus_about_principles_from_content($content);

    return $content;
}

/**
 * @param array<string, mixed> $content
 * @return array<int, array{title: string, text: string}>
 */
function voitkus_about_principles_from_content(array $content): array
{
    return [
        [
            'title' => (string) ($content['value_1_title'] ?? ''),
            'text'  => (string) ($content['value_1_text'] ?? ''),
        ],
        [
            'title' => (string) ($content['value_2_title'] ?? ''),
            'text'  => (string) ($content['value_2_text'] ?? ''),
        ],
        [
            'title' => (string) ($content['value_3_title'] ?? ''),
            'text'  => (string) ($content['value_3_text'] ?? ''),
        ],
    ];
}

function voitkus_customize_register_about(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_about_defaults();

    $wp_customize->add_section('voitkus_about', [
        'title'       => __('Palarnia (/about/)', 'voitkus'),
        'description' => __('Treść strony Palarnia. W podglądzie po lewej otwórz /about/, żeby zobaczyć zmiany na żywo.', 'voitkus'),
        'priority'    => 35,
    ]);

    $fields = [
        'hero_eyebrow'   => __('Nagłówek — eyebrow', 'voitkus'),
        'hero_brand'      => __('Nagłówek — marka (VOITKUS)', 'voitkus'),
        'hero_title'      => __('Nagłówek — podtytuł', 'voitkus'),
        'hero_lead'       => __('Nagłówek — lead', 'voitkus'),
        'story_title'     => __('Historia — tytuł', 'voitkus'),
        'story_text'      => __('Historia — tekst', 'voitkus'),
        'practice_title'  => __('Praktyka — tytuł sekcji', 'voitkus'),
        'practice_1'      => __('Praktyka — zasada 1', 'voitkus'),
        'practice_2'      => __('Praktyka — zasada 2', 'voitkus'),
        'practice_3'      => __('Praktyka — proces', 'voitkus'),
        'values_title'    => __('Wartości — tytuł sekcji', 'voitkus'),
        'value_1_title'   => __('Wartości — karta 1 tytuł', 'voitkus'),
        'value_1_text'    => __('Wartości — karta 1 tekst', 'voitkus'),
        'value_2_title'   => __('Wartości — karta 2 tytuł', 'voitkus'),
        'value_2_text'    => __('Wartości — karta 2 tekst', 'voitkus'),
        'value_3_title'   => __('Wartości — karta 3 tytuł', 'voitkus'),
        'value_3_text'    => __('Wartości — karta 3 tekst', 'voitkus'),
        'manifesto'       => __('Manifest', 'voitkus'),
        'cta_title'       => __('CTA — tytuł', 'voitkus'),
        'cta_text'        => __('CTA — tekst', 'voitkus'),
        'cta_button'      => __('CTA — przycisk', 'voitkus'),
    ];

    $textarea_keys = voitkus_about_textarea_keys();

    foreach ($fields as $key => $label) {
        $mod_key = voitkus_about_theme_mod_key($key);
        $type    = in_array($key, $textarea_keys, true) ? 'textarea' : 'text';

        $wp_customize->add_setting($mod_key, [
            'default'           => $defaults[ $key ],
            'sanitize_callback' => $type === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ]);

        $wp_customize->add_control($mod_key, [
            'label'   => $label,
            'section' => 'voitkus_about',
            'type'    => $type,
        ]);

        if ($key === 'story_text') {
            voitkus_customize_register_about_logo_control($wp_customize);
        }
    }
}

function voitkus_about_page_admin_notice(): void
{
    if (! function_exists('get_current_screen')) {
        return;
    }

    $screen = get_current_screen();

    if (! $screen instanceof WP_Screen || $screen->base !== 'post' || $screen->post_type !== 'page') {
        return;
    }

    $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

    if ($post_id <= 0) {
        return;
    }

    $slug = (string) get_post_field('post_name', $post_id);

    if (! function_exists('voitkus_is_about_page_slug') || ! voitkus_is_about_page_slug($slug)) {
        return;
    }

    $customize_url = add_query_arg(
        [
            'url'                  => voitkus_about_page_url(),
            'autofocus[section]'   => 'voitkus_about',
        ],
        admin_url('customize.php')
    );

    echo '<div class="notice notice-info"><p>'
        . wp_kses(
            sprintf(
                /* translators: %s: link to Customizer */
                __('Treść Palarnia edytujesz w %s. W edytorze zostaw shortcode [voitkus_about].', 'voitkus'),
                '<a href="' . esc_url($customize_url) . '">' . esc_html__('Wygląd → Dostosuj → Palarnia', 'voitkus') . '</a>'
            ),
            [
                'a' => [
                    'href' => [],
                ],
            ]
        )
        . '</p></div>';
}
add_action('admin_notices', 'voitkus_about_page_admin_notice');

function voitkus_migrate_about_content_to_customizer(): void
{
    $flag = 'voitkus_about_page_v4';

    if (get_option($flag) === 'done') {
        return;
    }

    $page_id = voitkus_get_about_page_id();
    $mods    = get_theme_mods();
    $mods    = is_array($mods) ? $mods : [];

    foreach (voitkus_about_defaults() as $key => $default) {
        $mod_key = voitkus_about_theme_mod_key($key);

        if (array_key_exists($mod_key, $mods)) {
            continue;
        }

        if ($page_id > 0) {
            $meta = get_post_meta($page_id, $mod_key, true);

            if (is_string($meta) && $meta !== '') {
                set_theme_mod($mod_key, $meta);
                continue;
            }
        }
    }

    if ($page_id > 0 && ! array_key_exists('voitkus_about_logo_image', $mods)) {
        $logo_id = (int) get_post_meta($page_id, 'voitkus_about_logo_id', true);

        if ($logo_id > 0) {
            $url = wp_get_attachment_url($logo_id);

            if (is_string($url) && $url !== '') {
                set_theme_mod('voitkus_about_logo_image', $url);
            }
        }
    }

    voitkus_seed_about_default_logo(false);

    update_option($flag, 'done', false);
}
add_action('init', 'voitkus_migrate_about_content_to_customizer', 25);

function voitkus_seed_about_default_logo(bool $mark_done = true): void
{
    $flag = 'voitkus_about_page_v5';

    if ($mark_done && get_option($flag) === 'done') {
        return;
    }

    $mods = get_theme_mods();
    $mods = is_array($mods) ? $mods : [];

    if (! array_key_exists('voitkus_about_logo_image', $mods)) {
        $url = voitkus_about_default_logo_url();

        if ($url !== '') {
            set_theme_mod('voitkus_about_logo_image', $url);
        }
    }

    if ($mark_done) {
        update_option($flag, 'done', false);
    }
}
add_action('init', 'voitkus_seed_about_default_logo', 26);
