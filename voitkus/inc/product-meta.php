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
        'voitkus_origin' => [
            'label'       => __('Pochodzenie (krótko)', 'voitkus'),
            'description' => __('Np. Etiopia · Sidama. Gdy puste — kategoria produktu.', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Etiopia · Sidama',
        ],
        'voitkus_flavor_notes' => [
            'label'       => __('Nuty smakowe', 'voitkus'),
            'description' => __('Przecinki lub ukośniki: Herbata / Cytrusy / Kwiaty', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Herbata / Cytrusy / Kwiaty',
        ],
        'voitkus_process' => [
            'label'       => __('Proces', 'voitkus'),
            'description' => __('Np. Naturalna, Myta', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Naturalna',
        ],
        'voitkus_region' => [
            'label'       => __('Region', 'voitkus'),
            'description' => __('Np. Sidama, Huila', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Sidama',
        ],
        'voitkus_variety' => [
            'label'       => __('Odmiana', 'voitkus'),
            'description' => __('Np. Heirloom, Caturra', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'Heirloom',
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
        'voitkus_label_color' => [
            'label'       => __('Kolor etykiety', 'voitkus'),
            'description' => __('orange, yellow, magenta, cyan, lime lub hex (#FF6A00)', 'voitkus'),
            'type'        => 'text',
            'placeholder' => 'orange',
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
add_action('plugins_loaded', 'voitkus_product_meta_bootstrap');

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

    echo '<div id="voitkus_product_data" class="panel woocommerce_options_panel hidden">';

    $fields = voitkus_product_meta_fields();
    $hook   = $fields['voitkus_hook'];
    unset($fields['voitkus_hook']);

    echo '<div class="options_group">';
    voitkus_render_product_meta_field('voitkus_hook', $hook, $post->ID);
    echo '</div>';

    echo '<div class="options_group">';
    echo '<p class="form-field"><strong>' . esc_html__('Dane na karcie produktu', 'voitkus') . '</strong></p>';

    foreach ($fields as $key => $field) {
        voitkus_render_product_meta_field($key, $field, $post->ID);
    }

    echo '</div>';
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
