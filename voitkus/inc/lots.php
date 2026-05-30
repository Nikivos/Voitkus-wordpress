<?php
/**
 * Loty — odczyt pól produktu i dane dla sekcji «Aktualne loty».
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Akcent lotu wg indeksu (cykliczny).
 *
 * @return string One of: orange|yellow|magenta|cyan|lime.
 */
function voitkus_lot_accent(int $index): string
{
    $accents = ['orange', 'yellow', 'magenta', 'cyan', 'lime'];

    return $accents[$index % count($accents)];
}

/**
 * ID produktu do odczytu pól lotu (meta na rodzicu dla wariantów).
 */
function voitkus_lot_product_id(WC_Product $product): int
{
    if ($product->is_type('variation')) {
        $parent_id = $product->get_parent_id();

        return $parent_id > 0 ? $parent_id : $product->get_id();
    }

    return $product->get_id();
}

/**
 * Pole lotu: post meta → ACF → atrybut WC.
 *
 * Klucze: voitkus_origin, voitkus_process, voitkus_region, voitkus_variety,
 * voitkus_roast_level, voitkus_weight, voitkus_flavor_notes, voitkus_label_color, voitkus_hook.
 */
function voitkus_lot_field(int $product_id, string $meta_key, ?WC_Product $product = null): string
{
    $raw = get_post_meta($product_id, $meta_key, true);

    if (is_array($raw)) {
        $raw = implode(', ', array_map('strval', $raw));
    }

    $value = is_string($raw) ? trim($raw) : '';

    if ($value === '' && function_exists('get_field')) {
        $acf = get_field($meta_key, $product_id);

        if (is_string($acf)) {
            $value = trim($acf);
        } elseif (is_numeric($acf)) {
            $value = (string) $acf;
        }
    }

    if ($value !== '' || ! $product instanceof WC_Product) {
        return $value;
    }

    $taxonomy = str_starts_with($meta_key, 'pa_') ? $meta_key : 'pa_' . $meta_key;

    if (taxonomy_exists($taxonomy)) {
        $terms = wc_get_product_terms($product->get_id(), $taxonomy, ['fields' => 'names']);

        if (! is_wp_error($terms) && ! empty($terms)) {
            return implode(', ', $terms);
        }
    }

    $attr = $product->get_attribute($meta_key);

    return is_string($attr) ? trim($attr) : '';
}

/**
 * Lista z pola tekstowego: przecinki lub ukośniki.
 *
 * @return array<int, string>
 */
function voitkus_lot_parse_list(string $raw): array
{
    if ($raw === '') {
        return [];
    }

    $normalized = str_replace(['•', '·', '|'], ',', $raw);
    $parts      = str_contains($normalized, '/') ? explode('/', $normalized) : explode(',', $normalized);

    return array_values(array_filter(array_map('trim', $parts)));
}

/**
 * Kolor etykiety: nazwa (magenta) lub hex (#6A1B9A / 6A1B9A).
 */
function voitkus_lot_label_color(string $raw): array
{
    $value = strtolower(trim($raw));

    if ($value === '') {
        return ['accent' => '', 'accent_css' => ''];
    }

    $named = ['orange', 'yellow', 'magenta', 'cyan', 'lime'];

    if (in_array($value, $named, true)) {
        return ['accent' => $value, 'accent_css' => ''];
    }

    if (preg_match('/^[0-9a-f]{3,8}$/', $value)) {
        return ['accent' => '', 'accent_css' => '#' . $value];
    }

    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/', $value, $match)) {
        return ['accent' => '', 'accent_css' => '#' . $match[1]];
    }

    return ['accent' => '', 'accent_css' => $value];
}

/**
 * Cena na karcie: zwykła lub promocja (stara przekreślona + aktualna).
 */
function voitkus_lot_price_html(WC_Product $product): string
{
    if (! function_exists('wc_price')) {
        return $product->get_price_html();
    }

    if (! $product->is_on_sale()) {
        return wc_price($product->get_price());
    }

    $regular = $product->get_regular_price();
    $sale    = $product->get_sale_price();

    if ($regular === '' || $sale === '') {
        return $product->get_price_html();
    }

    return wc_format_sale_price(
        wc_price($regular),
        wc_price($sale)
    );
}

/**
 * Aktualne loty: ostatnie produkty WooCommerce, z fallbackiem statycznym.
 *
 * @return array<int, array{url:string,image_html:string,origin:string,title:string,price_html:string,hook:string,specs:array<string,string>,notes:array<int,string>,badge:string,accent:string,accent_css:string,add_to_cart:string}>
 */
function voitkus_current_lots(int $limit = 3): array
{
    if (function_exists('wc_get_products')) {
        $products = wc_get_products([
            'status'  => 'publish',
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ]);

        if (! empty($products)) {
            $lots = [];

            foreach (array_values($products) as $index => $product) {
                $lot_id = voitkus_lot_product_id($product);
                $field  = static fn (string $key): string => voitkus_lot_field($lot_id, $key, $product);

                $origin = $field('voitkus_origin');

                if ($origin === '') {
                    $terms = get_the_terms($lot_id, 'product_cat');

                    if (is_array($terms) && ! empty($terms)) {
                        $skip  = ['uncategorized', 'misc', 'bez-kategorii'];
                        $names = [];

                        foreach ($terms as $term) {
                            if (in_array($term->slug, $skip, true)) {
                                continue;
                            }
                            $names[] = $term->name;
                        }

                        $origin = implode(' · ', array_slice($names, 0, 2));
                    }
                }

                $badge   = '';
                $created = $product->get_date_created();

                if ($product->is_featured()) {
                    $badge = __('Bestseller', 'voitkus');
                } elseif ($created instanceof WC_DateTime && $created->getTimestamp() > strtotime('-30 days')) {
                    $badge = __('Nowość', 'voitkus');
                }

                $spec_map = [
                    __('Proces', 'voitkus')  => 'voitkus_process',
                    __('Region', 'voitkus')  => 'voitkus_region',
                    __('Odmiana', 'voitkus') => 'voitkus_variety',
                    __('Palenie', 'voitkus') => 'voitkus_roast_level',
                    __('Waga', 'voitkus')    => 'voitkus_weight',
                ];

                $specs = [];
                foreach ($spec_map as $label => $meta_key) {
                    $value = $field($meta_key);
                    if ($value !== '') {
                        $specs[$label] = $value;
                    }
                }

                $notes = voitkus_lot_parse_list($field('voitkus_flavor_notes'));

                $hook = $field('voitkus_hook');
                if ($hook === '') {
                    $hook = wp_strip_all_tags($product->get_short_description());
                }
                if ($hook !== '') {
                    $hook = wp_trim_words($hook, 14, '…');
                }

                $color      = voitkus_lot_label_color($field('voitkus_label_color'));
                $accent     = $color['accent'] !== '' ? $color['accent'] : voitkus_lot_accent($index);
                $accent_css = $color['accent_css'];

                $lots[] = [
                    'url'         => (string) get_permalink($product->get_id()),
                    'image_html'  => $product->get_image('large', ['class' => 'lot-card__img']),
                    'origin'      => $origin,
                    'title'       => $product->get_name(),
                    'price_html'  => voitkus_lot_price_html($product),
                    'hook'        => $hook,
                    'specs'       => $specs,
                    'notes'       => $notes,
                    'badge'       => $badge,
                    'accent'      => $accent,
                    'accent_css'  => $accent_css,
                    'add_to_cart' => method_exists($product, 'add_to_cart_url') ? $product->add_to_cart_url() : '',
                ];
            }

            return $lots;
        }
    }

    return voitkus_fallback_lots();
}

/**
 * Statyczne loty — gdy WooCommerce nieaktywny lub brak produktów.
 *
 * @return array<int, array{url:string,image_html:string,origin:string,title:string,price_html:string,hook:string,specs:array<string,string>,notes:array<int,string>,badge:string,accent:string,accent_css:string,add_to_cart:string}>
 */
function voitkus_fallback_lots(): array
{
    $shop = home_url('/shop/');

    return [
        [
            'url'         => $shop,
            'image_html'  => '',
            'origin'      => __('Etiopia', 'voitkus'),
            'title'       => 'Ethiopia Sidama',
            'price_html'  => '62 zł',
            'hook'        => __('Pierwszy łyk jak kwiatowa herbata.', 'voitkus'),
            'specs'       => [
                __('Proces', 'voitkus')  => __('Natural', 'voitkus'),
                __('Region', 'voitkus')  => 'Sidama',
                __('Odmiana', 'voitkus') => 'Heirloom',
                __('Palenie', 'voitkus') => __('Filter', 'voitkus'),
                __('Waga', 'voitkus')    => '250 g',
            ],
            'notes'       => [__('Herbata', 'voitkus'), __('Cytrusy', 'voitkus'), __('Kwiaty', 'voitkus')],
            'badge'       => __('Nowość', 'voitkus'),
            'accent'      => 'orange',
            'accent_css'  => '',
            'add_to_cart' => '',
        ],
        [
            'url'         => $shop,
            'image_html'  => '',
            'origin'      => __('Kolumbia', 'voitkus'),
            'title'       => 'Colombia Huila',
            'price_html'  => '49 zł',
            'hook'        => __('Klasyk do codziennego kubka.', 'voitkus'),
            'specs'       => [
                __('Proces', 'voitkus')  => __('Washed', 'voitkus'),
                __('Region', 'voitkus')  => 'Huila',
                __('Odmiana', 'voitkus') => 'Caturra',
                __('Palenie', 'voitkus') => __('Omni', 'voitkus'),
                __('Waga', 'voitkus')    => '250 g',
            ],
            'notes'       => [__('Czekolada', 'voitkus'), __('Orzech', 'voitkus'), __('Czerwone owoce', 'voitkus')],
            'badge'       => '',
            'accent'      => 'yellow',
            'accent_css'  => '',
            'add_to_cart' => '',
        ],
        [
            'url'         => $shop,
            'image_html'  => '',
            'origin'      => __('Brazylia', 'voitkus'),
            'title'       => 'Brazil Cerrado',
            'price_html'  => '52 zł',
            'hook'        => __('Słodki, kremowy strzał espresso.', 'voitkus'),
            'specs'       => [
                __('Proces', 'voitkus')  => __('Natural', 'voitkus'),
                __('Region', 'voitkus')  => 'Cerrado',
                __('Odmiana', 'voitkus') => 'Mundo Novo',
                __('Palenie', 'voitkus') => __('Espresso', 'voitkus'),
                __('Waga', 'voitkus')    => '250 g',
            ],
            'notes'       => [__('Karmel', 'voitkus'), __('Orzech laskowy', 'voitkus'), __('Czekolada', 'voitkus')],
            'badge'       => __('Bestseller', 'voitkus'),
            'accent'      => 'yellow',
            'accent_css'  => '',
            'add_to_cart' => '',
        ],
    ];
}

/**
 * TYMCZASOWE: diagnostyka pól lotu. ?voitkus_debug=1 + WP_DEBUG lub admin.
 */
function voitkus_lots_debug(): void
{
    if (! isset($_GET['voitkus_debug'])) {
        return;
    }

    $allowed = current_user_can('manage_options') || (defined('WP_DEBUG') && WP_DEBUG);

    if (! $allowed) {
        return;
    }

    if (! function_exists('wc_get_products')) {
        echo '<pre class="voitkus-debug">WooCommerce nieaktywne.</pre>';

        return;
    }

    $products = wc_get_products(['status' => 'publish', 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC']);

    if (empty($products)) {
        echo '<pre class="voitkus-debug">Brak opublikowanych produktów.</pre>';

        return;
    }

    $keys = [
        'voitkus_origin',
        'voitkus_process',
        'voitkus_region',
        'voitkus_variety',
        'voitkus_roast_level',
        'voitkus_weight',
        'voitkus_flavor_notes',
        'voitkus_label_color',
        'voitkus_hook',
    ];

    $lines = ['voitkus_lot_field → post meta, potem ACF, potem atrybut WC', ''];

    foreach ($products as $product) {
        $lot_id = voitkus_lot_product_id($product);
        $lines[] = '════ #' . $lot_id . ' — ' . $product->get_name();

        foreach ($keys as $key) {
            $meta  = get_post_meta($lot_id, $key, true);
            $final = voitkus_lot_field($lot_id, $key, $product);
            $lines[] = sprintf(
                '%-22s | meta=[%s] | używane=[%s]',
                $key,
                is_string($meta) && $meta !== '' ? $meta : '—',
                $final !== '' ? $final : '—'
            );
        }

        $lines[] = '';
    }

    printf(
        '<pre class="voitkus-debug" style="position:fixed;left:0;right:0;bottom:0;z-index:99999;max-height:60vh;margin:0;padding:16px;overflow:auto;background:#0a0a0a;color:#b8f500;font:12px/1.6 ui-monospace,monospace;white-space:pre-wrap;">%s</pre>',
        esc_html(implode("\n", $lines))
    );
}
add_action('wp_footer', 'voitkus_lots_debug');
