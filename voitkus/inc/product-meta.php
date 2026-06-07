<?php
/**
 * WooCommerce — pola lotu Voitkus (post meta + zakładka w edytorze produktu).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, array{label: string, description: string, type: string, rows?: int, placeholder?: string}>
 */
function voitkus_product_meta_fields(): array
{
    return [
        'voitkus_hook' => [
            'label'       => __('Dla kogo', 'voitkus'),
            'description' => __('Krótka linia na karcie, np. „Dla fanów owocowych przelewów”.', 'voitkus'),
            'type'        => 'textarea',
            'rows'        => 2,
            'placeholder' => __('Dla fanów owocowych przelewów', 'voitkus'),
        ],
        'voitkus_country' => [
            'label'       => __('Kraj', 'voitkus'),
            'description' => __('Np. Brazylia, Etiopia — linia nad tytułem i karta produktu.', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Brazylia',
        ],
        'voitkus_region' => [
            'label'       => __('Region', 'voitkus'),
            'description' => __('Np. Campo das Vertentes, Kaffa', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Kaffa',
        ],
        'voitkus_variety' => [
            'label'       => __('Odmiana', 'voitkus'),
            'description' => __('Np. Yellow Bourbon, JARC', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Yellow Bourbon',
        ],
        'voitkus_process' => [
            'label'       => __('Obróbka', 'voitkus'),
            'description' => __('Np. Natural, Washed', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Natural',
        ],
        'voitkus_altitude' => [
            'label'       => __('Wysokość upraw', 'voitkus'),
            'description' => __('Np. 1200 m n.p.m. lub 1900–2050 m n.p.m.', 'voitkus'),
            'type'        => 'text',
            'placeholder' => '1200 m n.p.m.',
        ],
        'voitkus_roast_level' => [
            'label'       => __('Palenie', 'voitkus'),
            'description' => __('Np. Filter, Espresso, Omni', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Filter',
        ],
        'voitkus_weight' => [
            'label'       => __('Waga (domyślna)', 'voitkus'),
            'description' => __('Np. 250 g — informacyjnie na karcie', 'voitkus'),
            'type'        => 'text',
            'placeholder' => '250 g',
        ],
        'voitkus_flavor_notes' => [
            'label'       => __('Nuty smakowe', 'voitkus'),
            'description' => __('Przecinki lub ukośniki: Herbata / Cytrusy / Kwiaty', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Herbata / Cytrusy / Kwiaty',
        ],
        'voitkus_taste_profile' => [
            'label'       => __('Kierunek smaku', 'voitkus'),
            'description' => __('Jedna linia = jedna nuta. Format: Nazwa | 7, Nazwa: 7 lub Nazwa 7 (skala 0–10).', 'voitkus'),
            'type'        => 'textarea',
            'rows'        => 6,
            'placeholder' => "Czekolada | 7\nOrzech | 8\nKakao | 6\nSłodycz | 8",
        ],
        'voitkus_label_color' => [
            'label'       => __('Kolor etykiety', 'voitkus'),
            'description' => __('orange, yellow, magenta, cyan, lime lub hex (#FF6A00)', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'orange',
        ],
        'voitkus_origin' => [
            'label'       => __('Pochodzenie (nadpisanie)', 'voitkus'),
            'description' => __('Opcjonalnie — zastępuje linię Kraj · Region nad tytułem.', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Etiopia · Sidama',
        ],
    ];
}

/**
 * @return list<string>
 */
function voitkus_product_meta_admin_groups(): array
{
    return [
        'marketing' => [
            'title'  => '',
            'fields' => ['voitkus_hook'],
        ],
        'origin' => [
            'title'  => __('Pochodzenie i obróbka', 'voitkus'),
            'fields' => [
                'voitkus_country',
                'voitkus_region',
                'voitkus_variety',
                'voitkus_process',
                'voitkus_altitude',
            ],
        ],
        'card' => [
            'title'  => __('Dane na karcie produktu', 'voitkus'),
            'fields' => [
                'voitkus_roast_level',
                'voitkus_weight',
                'voitkus_flavor_notes',
                'voitkus_label_color',
                'voitkus_origin',
            ],
        ],
        'taste' => [
            'title'  => __('Profil sensoryczny', 'voitkus'),
            'fields' => ['voitkus_taste_profile'],
        ],
    ];
}

function voitkus_register_product_meta(): void
{
    if (! post_type_exists('product')) {
        return;
    }

    foreach (voitkus_product_meta_fields() as $key => $field) {
        register_post_meta('product', $key, [
            'type'              => 'string',
            'single'            => true,
            'show_in_rest'      => true,
            'auth_callback'     => 'voitkus_can_edit_products',
            'sanitize_callback' => $field['type'] === 'textarea' ? 'sanitize_textarea_field' : 'sanitize_text_field',
        ]);
    }
}
add_action('init', 'voitkus_register_product_meta');

function voitkus_can_edit_products(): bool
{
    return current_user_can('edit_products');
}

function voitkus_product_meta_bootstrap(): void
{
    if (! class_exists('WooCommerce')) {
        return;
    }

    add_filter('woocommerce_product_data_tabs', 'voitkus_product_data_tab');
    add_action('woocommerce_product_data_panels', 'voitkus_product_data_panel');
    add_action('woocommerce_process_product_meta', 'voitkus_save_product_meta');
}
add_action('init', 'voitkus_product_meta_bootstrap', 20);

function voitkus_product_data_tab(array $tabs): array
{
    $tabs['voitkus'] = [
        'label'    => __('Voitkus', 'voitkus'),
        'target'   => 'voitkus_product_data',
        'class'    => [],
        'priority' => 62,
    ];

    return $tabs;
}

function voitkus_product_data_panel(): void
{
    global $post;

    if (! $post instanceof WP_Post) {
        return;
    }

    $fields = voitkus_product_meta_fields();

    echo '<div id="voitkus_product_data" class="panel woocommerce_options_panel hidden">';

    foreach (voitkus_product_meta_admin_groups() as $group) {
        echo '<div class="options_group">';

        if (($group['title'] ?? '') !== '') {
            echo '<p class="form-field"><strong>' . esc_html($group['title']) . '</strong></p>';
        }

        foreach ($group['fields'] as $key) {
            if (! isset($fields[ $key ])) {
                continue;
            }

            voitkus_render_product_meta_field($key, $fields[ $key ], $post->ID);
        }

        echo '</div>';
    }

    echo '</div>';
}

/**
 * @param array{label: string, description: string, type: string, rows?: int, placeholder?: string} $field
 */
function voitkus_render_product_meta_field(string $key, array $field, int $post_id): void
{
    $value = (string) get_post_meta($post_id, $key, true);
    $args  = [
        'id'          => $key,
        'label'       => $field['label'],
        'value'       => $value,
        'desc_tip'    => true,
        'description' => $field['description'],
    ];

    if (($field['placeholder'] ?? '') !== '') {
        $args['placeholder'] = $field['placeholder'];
    }

    if ($field['type'] === 'textarea') {
        $args['rows'] = $field['rows'] ?? 2;
        woocommerce_wp_textarea_input($args);

        return;
    }

    woocommerce_wp_text_input($args);
}

function voitkus_save_product_meta(int $post_id): void
{
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    foreach (voitkus_product_meta_fields() as $key => $field) {
        if (! isset($_POST[$key])) {
            continue;
        }

        $raw   = wp_unslash($_POST[$key]);
        $value = $field['type'] === 'textarea'
            ? sanitize_textarea_field(is_string($raw) ? $raw : '')
            : sanitize_text_field(is_string($raw) ? $raw : '');

        update_post_meta($post_id, $key, $value);
    }
}
