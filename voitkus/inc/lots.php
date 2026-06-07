<?php
/**
 * Loty — odczyt pól produktu i dane dla sekcji «Nasze kawy».
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
 * Klucze: voitkus_country, voitkus_origin, voitkus_process, voitkus_region, voitkus_variety,
 * voitkus_altitude, voitkus_roast_level, voitkus_weight, voitkus_flavor_notes,
 * voitkus_taste_profile, voitkus_label_color, voitkus_hook.
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
 * Profil smakowy: linie „Nazwa | 7”, „Nazwa: 7” lub „Nazwa 7” (skala 0–10).
 *
 * @return array<int, array{label: string, score: int, percent: int}>
 */
function voitkus_lot_parse_taste_profile(string $raw): array
{
    if ($raw === '') {
        return [];
    }

    $items = [];

    foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        $matched = preg_match('/^(.+?)\s*[|:]\s*(\d+(?:[.,]\d+)?)\s*$/u', $line, $match)
            || preg_match('/^(.+?)\s+(\d+)\s*$/u', $line, $match);

        if (! $matched) {
            continue;
        }

        $label = trim($match[1]);
        $score = (int) round((float) str_replace(',', '.', $match[2]));
        $score = max(0, min(10, $score));

        if ($label === '') {
            continue;
        }

        $items[] = [
            'label'   => $label,
            'score'   => $score,
            'percent' => $score * 10,
        ];

        if (count($items) >= 6) {
            break;
        }
    }

    return $items;
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
 * Badge parzenia na karcie (Espresso, Filter, Omni) z pola Palenie.
 *
 * @return array<int, array{slug: string, label: string}>
 */
function voitkus_lot_brew_badges(string $roast_level): array
{
    if ($roast_level === '') {
        return [];
    }

    $map = [
        'espresso' => __('Espresso', 'voitkus'),
        'filter'   => __('Filter', 'voitkus'),
        'omni'     => __('Omni', 'voitkus'),
    ];

    $parts  = preg_split('/[\s\/|,]+/', strtolower($roast_level)) ?: [];
    $badges = [];
    $seen   = [];

    foreach ($parts as $part) {
        $part = trim($part);

        if ($part === '' || ! isset($map[$part]) || isset($seen[$part])) {
            continue;
        }

        $seen[$part] = true;
        $badges[]    = [
            'slug'  => $part,
            'label' => $map[$part],
        ];
    }

    return $badges;
}

/**
 * @return array{can: bool, product_id: int, variation_id: int}
 */
function voitkus_lot_ajax_add_context(WC_Product $product): array
{
    $empty = [
        'can'          => false,
        'product_id'   => 0,
        'variation_id' => 0,
    ];

    if (! $product->is_purchasable() || ! $product->is_in_stock()) {
        return $empty;
    }

    if ($product->is_type('simple')) {
        return [
            'can'          => true,
            'product_id'   => $product->get_id(),
            'variation_id' => 0,
        ];
    }

    if (! $product->is_type('variable')) {
        return $empty;
    }

    $data_store   = WC_Data_Store::load('product');
    $variation_id = (int) $data_store->find_matching_product_variation($product, $product->get_default_attributes());

    if ($variation_id <= 0) {
        $children = $product->get_children();

        foreach ($children as $child_id) {
            $variation = wc_get_product((int) $child_id);

            if ($variation instanceof WC_Product_Variation && $variation->is_purchasable() && $variation->is_in_stock()) {
                $variation_id = (int) $child_id;
                break;
            }
        }
    }

    if ($variation_id <= 0) {
        return $empty;
    }

    $variation = wc_get_product($variation_id);

    if (! $variation instanceof WC_Product_Variation || ! $variation->is_purchasable() || ! $variation->is_in_stock()) {
        return $empty;
    }

    return [
        'can'          => true,
        'product_id'   => $product->get_id(),
        'variation_id' => $variation_id,
    ];
}

/**
 * @return array{url:string,image_html:string,origin:string,title:string,price_html:string,hook:string,specs:array<string,string>,notes:array<int,string>,badge:string,brew_badges:array<int,array{slug:string,label:string}>,accent:string,accent_css:string,add_to_cart:string}
 */
function voitkus_build_lot_from_product(WC_Product $product, int $index): array
{
    $lot_id = voitkus_lot_product_id($product);
    $field  = static fn (string $key): string => voitkus_lot_field($lot_id, $key, $product);

    $origin = $field('voitkus_origin');

    if ($origin === '') {
        $country = $field('voitkus_country');
        $region  = $field('voitkus_region');

        if ($country !== '' && $region !== '') {
            $origin = $country . ' · ' . $region;
        } elseif ($country !== '') {
            $origin = $country;
        } elseif ($region !== '') {
            $origin = $region;
        }
    }

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

    $roast_level = $field('voitkus_roast_level');
    $brew_badges = voitkus_lot_brew_badges($roast_level);

    $spec_map = [
        __('Region', 'voitkus')         => 'voitkus_region',
        __('Odmiana', 'voitkus')        => 'voitkus_variety',
        __('Obróbka', 'voitkus')        => 'voitkus_process',
        __('Wysokość upraw', 'voitkus') => 'voitkus_altitude',
        __('Palenie', 'voitkus')        => 'voitkus_roast_level',
        __('Waga', 'voitkus')           => 'voitkus_weight',
    ];

    $specs = [];
    foreach ($spec_map as $label => $meta_key) {
        if ($meta_key === 'voitkus_roast_level' && $brew_badges !== []) {
            continue;
        }

        $value = $field($meta_key);
        if ($value !== '') {
            $specs[$label] = $value;
        }
    }

    $notes = voitkus_lot_parse_list($field('voitkus_flavor_notes'));
    $taste = voitkus_lot_parse_taste_profile($field('voitkus_taste_profile'));

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
    $ajax_add   = voitkus_lot_ajax_add_context($product);

    return [
        'url'          => (string) get_permalink($product->get_id()),
        'product_id'   => (int) $ajax_add['product_id'] > 0 ? (int) $ajax_add['product_id'] : $lot_id,
        'variation_id' => (int) ($ajax_add['variation_id'] ?? 0),
        'can_ajax_add' => ! empty($ajax_add['can']),
        'image_html'   => $product->get_image('large', ['class' => 'lot-card__img']),
        'origin'      => $origin,
        'title'       => $product->get_name(),
        'price_html'  => voitkus_lot_price_html($product),
        'hook'        => $hook,
        'specs'       => $specs,
        'notes'          => $notes,
        'taste_profile'  => $taste,
        'badge'          => $badge,
        'brew_badges' => $brew_badges,
        'accent'      => $accent,
        'accent_css'  => $accent_css,
        'add_to_cart' => method_exists($product, 'add_to_cart_url') ? $product->add_to_cart_url() : '',
    ];
}

/**
 * @return array<int, array{slug: string, label: string, accent: string}>
 */
function voitkus_shop_filters(): array
{
    return [
        ['slug' => '', 'label' => __('Wszystkie', 'voitkus'), 'accent' => 'yellow'],
        ['slug' => 'everyday', 'label' => __('Balans', 'voitkus'), 'accent' => 'yellow'],
        ['slug' => 'espresso', 'label' => __('Terroir', 'voitkus'), 'accent' => 'orange'],
        ['slug' => 'funky', 'label' => __('Ekspresja', 'voitkus'), 'accent' => 'magenta'],
        ['slug' => 'cyan', 'label' => __('Detal', 'voitkus'), 'accent' => 'cyan'],
        ['slug' => 'limited', 'label' => __('Mikrolot', 'voitkus'), 'accent' => 'lime'],
    ];
}

function voitkus_shop_active_filter(): string
{
    if (! isset($_GET['filter'])) {
        return '';
    }

    $filter = sanitize_key(wp_unslash((string) $_GET['filter']));

    foreach (voitkus_shop_filters() as $item) {
        if ($item['slug'] === $filter) {
            return $filter;
        }
    }

    return '';
}

/**
 * @return array<string, mixed>
 */
function voitkus_shop_product_query_args(): array
{
    $args = [
        'status'  => 'publish',
        'limit'   => -1,
        'orderby' => 'date',
        'order'   => 'DESC',
    ];

    $filter = voitkus_shop_active_filter();

    if ($filter === '') {
        return $args;
    }

    if (taxonomy_exists('product_tag')) {
        $args['tag'] = [$filter];
    } elseif (taxonomy_exists('product_cat')) {
        $args['category'] = [$filter];
    }

    return $args;
}

/**
 * @return array<int, array{url:string,image_html:string,origin:string,title:string,price_html:string,hook:string,specs:array<string,string>,notes:array<int,string>,badge:string,accent:string,accent_css:string,add_to_cart:string}>
 */
function voitkus_shop_lots(): array
{
    if (! function_exists('wc_get_products')) {
        return voitkus_fallback_lots();
    }

    $products = wc_get_products(voitkus_shop_product_query_args());

    if ($products === []) {
        return voitkus_shop_active_filter() === '' ? voitkus_fallback_lots() : [];
    }

    $lots = [];

    foreach (array_values($products) as $index => $product) {
        $lots[] = voitkus_build_lot_from_product($product, $index);
    }

    return $lots;
}

/**
 * @return array<int, int>
 */
function voitkus_product_gallery_ids(WC_Product $product): array
{
    $ids  = [];
    $main = (int) $product->get_image_id();

    if ($main > 0) {
        $ids[] = $main;
    }

    foreach ($product->get_gallery_image_ids() as $attachment_id) {
        $attachment_id = (int) $attachment_id;

        if ($attachment_id > 0 && ! in_array($attachment_id, $ids, true)) {
            $ids[] = $attachment_id;
        }
    }

    return $ids;
}

/**
 * @return array<int, array{url:string,image_html:string,origin:string,title:string,price_html:string,hook:string,specs:array<string,string>,notes:array<int,string>,badge:string,brew_badges:array<int,array{slug:string,label:string}>,accent:string,accent_css:string,add_to_cart:string}>
 */
function voitkus_related_lots(WC_Product $product, int $limit = 3): array
{
    if (! function_exists('wc_get_products')) {
        return [];
    }

    $exclude = [$product->get_id()];
    $ids     = function_exists('wc_get_related_products')
        ? wc_get_related_products($product->get_id(), $limit, $exclude)
        : [];

    $products = [];

    foreach ($ids as $id) {
        $related = wc_get_product((int) $id);

        if ($related instanceof WC_Product && $related->is_visible()) {
            $products[] = $related;
        }
    }

    if ($products === []) {
        $products = wc_get_products([
            'status'  => 'publish',
            'limit'   => $limit + 1,
            'orderby' => 'date',
            'order'   => 'DESC',
            'exclude' => $exclude,
        ]);
    }

    $lots = [];

    foreach (array_slice($products, 0, $limit) as $index => $related) {
        if ($related instanceof WC_Product) {
            $lots[] = voitkus_build_lot_from_product($related, $index);
        }
    }

    return $lots;
}

/**
 * Nasze kawy: ostatnie produkty WooCommerce, z fallbackiem statycznym.
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
                $lots[] = voitkus_build_lot_from_product($product, $index);
            }

            return $lots;
        }
    }

    return array_slice(voitkus_fallback_lots(), 0, $limit);
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
                __('Obróbka', 'voitkus')  => __('Natural', 'voitkus'),
                __('Region', 'voitkus')  => 'Sidama',
                __('Odmiana', 'voitkus') => 'Heirloom',
                __('Palenie', 'voitkus') => __('Filter', 'voitkus'),
                __('Waga', 'voitkus')    => '250 g',
            ],
            'notes'       => [__('Herbata', 'voitkus'), __('Cytrusy', 'voitkus'), __('Kwiaty', 'voitkus')],
            'badge'       => __('Nowość', 'voitkus'),
            'brew_badges' => voitkus_lot_brew_badges('Filter'),
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
                __('Obróbka', 'voitkus')  => __('Washed', 'voitkus'),
                __('Region', 'voitkus')  => 'Huila',
                __('Odmiana', 'voitkus') => 'Caturra',
                __('Palenie', 'voitkus') => __('Omni', 'voitkus'),
                __('Waga', 'voitkus')    => '250 g',
            ],
            'notes'       => [__('Czekolada', 'voitkus'), __('Orzech', 'voitkus'), __('Czerwone owoce', 'voitkus')],
            'badge'       => '',
            'brew_badges' => voitkus_lot_brew_badges('Omni'),
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
                __('Obróbka', 'voitkus')  => __('Natural', 'voitkus'),
                __('Region', 'voitkus')  => 'Cerrado',
                __('Odmiana', 'voitkus') => 'Mundo Novo',
                __('Palenie', 'voitkus') => __('Espresso', 'voitkus'),
                __('Waga', 'voitkus')    => '250 g',
            ],
            'notes'       => [__('Karmel', 'voitkus'), __('Orzech laskowy', 'voitkus'), __('Czekolada', 'voitkus')],
            'badge'       => __('Bestseller', 'voitkus'),
            'brew_badges' => voitkus_lot_brew_badges('Espresso'),
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
        'voitkus_country',
        'voitkus_origin',
        'voitkus_process',
        'voitkus_region',
        'voitkus_variety',
        'voitkus_altitude',
        'voitkus_roast_level',
        'voitkus_weight',
        'voitkus_flavor_notes',
        'voitkus_taste_profile',
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
