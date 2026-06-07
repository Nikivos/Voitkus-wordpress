<?php
/**
 * WooCommerce product reviews helpers.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_product_review_product_ids(WC_Product $product): array
{
    $ids = [(int) $product->get_id()];

    if ($product->is_type('variation')) {
        $parent_id = (int) $product->get_parent_id();

        if ($parent_id > 0) {
            $ids[] = $parent_id;
        }
    }

    if ($product->is_type('variable')) {
        foreach ($product->get_children() as $child_id) {
            $ids[] = (int) $child_id;
        }
    }

    return array_values(array_unique(array_filter($ids)));
}

/**
 * @return array<int, string>
 */
function voitkus_product_review_customer_emails(int $user_id): array
{
    $emails = [];

    if ($user_id > 0) {
        $user = get_user_by('id', $user_id);

        if ($user instanceof WP_User && is_email($user->user_email)) {
            $emails[] = $user->user_email;
        }
    }

    if ($user_id > 0 && function_exists('wc_get_orders')) {
        $orders = wc_get_orders(
            [
                'customer' => $user_id,
                'status'   => wc_get_is_paid_statuses(),
                'limit'    => 50,
                'orderby'  => 'date',
                'order'    => 'DESC',
            ]
        );

        foreach ($orders as $order) {
            if (! $order instanceof WC_Order) {
                continue;
            }

            $billing_email = $order->get_billing_email();

            if (is_email($billing_email)) {
                $emails[] = $billing_email;
            }
        }
    }

    return array_values(array_unique(array_filter($emails)));
}

function voitkus_customer_bought_product(WC_Product $product, int $user_id = 0, string $customer_email = ''): bool
{
    if ($user_id <= 0) {
        $user_id = get_current_user_id();
    }

    if ($customer_email === '' && $user_id > 0) {
        $user = get_user_by('id', $user_id);

        if ($user instanceof WP_User && is_email($user->user_email)) {
            $customer_email = $user->user_email;
        }
    }

    $emails = voitkus_product_review_customer_emails($user_id);

    if ($customer_email !== '' && is_email($customer_email) && ! in_array($customer_email, $emails, true)) {
        $emails[] = $customer_email;
    }

    if ($emails === [] && ! is_email($customer_email)) {
        return false;
    }

    if ($emails === [] && is_email($customer_email)) {
        $emails = [$customer_email];
    }

    foreach (voitkus_product_review_product_ids($product) as $product_id) {
        foreach ($emails as $email) {
            if (wc_customer_bought_product($email, $user_id, $product_id)) {
                return true;
            }
        }

        if ($user_id > 0 && wc_customer_bought_product('', $user_id, $product_id)) {
            return true;
        }
    }

    return false;
}

function voitkus_product_reviews_guest_comments_enabled(): bool
{
    if (function_exists('is_product') && is_product()) {
        return true;
    }

    if (isset($_POST['comment_post_ID'])) {
        return get_post_type((int) wp_unslash($_POST['comment_post_ID'])) === 'product';
    }

    return false;
}

/**
 * @param mixed $pre
 * @return mixed
 */
function voitkus_product_reviews_allow_guest_comments($pre)
{
    if (voitkus_product_reviews_guest_comments_enabled()) {
        return '0';
    }

    return $pre;
}
add_filter('pre_option_comment_registration', 'voitkus_product_reviews_allow_guest_comments');

function voitkus_product_review_post_id(WC_Product $product): int
{
    if ($product->is_type('variation')) {
        return (int) $product->get_parent_id();
    }

    return (int) $product->get_id();
}

function voitkus_identity_has_product_review(WC_Product $product, int $user_id = 0, string $email = ''): bool
{
    $post_id = voitkus_product_review_post_id($product);

    if ($post_id <= 0) {
        return false;
    }

    if ($user_id <= 0) {
        $user_id = get_current_user_id();
    }

    if ($user_id > 0) {
        $count = (int) get_comments(
            [
                'post_id'            => $post_id,
                'user_id'            => $user_id,
                'type'               => 'review',
                'count'              => true,
                'include_unapproved' => true,
            ]
        );

        if ($count > 0) {
            return true;
        }
    }

    if ($email === '' || ! is_email($email)) {
        return false;
    }

    return (int) get_comments(
        [
            'post_id'            => $post_id,
            'author_email'       => $email,
            'type'               => 'review',
            'count'              => true,
            'include_unapproved' => true,
        ]
    ) > 0;
}

function voitkus_product_can_review(?WC_Product $product = null): bool
{
    if (! $product instanceof WC_Product) {
        $product = wc_get_product(get_the_ID());
    }

    return $product instanceof WC_Product
        && $product->get_reviews_allowed()
        && comments_open((int) $product->get_id());
}

function voitkus_product_reviews_enabled(WC_Product $product): bool
{
    return $product->get_reviews_allowed() && (comments_open() || $product->get_review_count() > 0);
}

/**
 * @return array<int, WP_Comment>
 */
function voitkus_get_product_review_comments(?int $post_id = null): array
{
    if ($post_id === null || $post_id <= 0) {
        $post_id = get_the_ID();
    }

    if ($post_id <= 0) {
        return [];
    }

    $per_page = get_option('page_comments') ? max(1, (int) get_option('comments_per_page')) : 0;
    $cpage    = $per_page > 0 ? max(1, (int) get_query_var('cpage')) : 1;

    $args = [
        'post_id' => $post_id,
        'status'  => 'approve',
        'type'    => 'review',
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
    ];

    if ($per_page > 0) {
        $args['number'] = $per_page;
        $args['offset'] = ($cpage - 1) * $per_page;
    }

    $comments = get_comments($args);

    if ($comments === []) {
        $fallback_args = $args;
        unset($fallback_args['type']);
        $comments = get_comments($fallback_args);
    }

    return is_array($comments) ? $comments : [];
}

function voitkus_setup_product_review_query(?int $post_id = null): array
{
    global $wp_query;

    $comments  = voitkus_get_product_review_comments($post_id);
    $post_id   = $post_id ?? get_the_ID();
    $per_page  = get_option('page_comments') ? max(1, (int) get_option('comments_per_page')) : 0;
    $cpage     = $per_page > 0 ? max(1, (int) get_query_var('cpage')) : 1;
    $total     = 0;

    if ($post_id > 0) {
        $total = (int) get_comments(
            [
                'post_id' => $post_id,
                'status'  => 'approve',
                'type'    => 'review',
                'count'   => true,
            ]
        );

        if ($total === 0) {
            $total = (int) get_comments(
                [
                    'post_id' => $post_id,
                    'status'  => 'approve',
                    'count'   => true,
                ]
            );
        }
    }

    $wp_query->comments                 = $comments;
    $wp_query->comment_count            = $total;
    $wp_query->max_num_comment_pages    = $per_page > 0 ? (int) ceil($total / $per_page) : 1;
    $wp_query->query_vars['cpage']      = $cpage;

    return $comments;
}

function voitkus_product_has_review_comments(?int $post_id = null): bool
{
    if ($post_id === null || $post_id <= 0) {
        $post_id = get_the_ID();
    }

    $product = wc_get_product($post_id);

    return $product instanceof WC_Product && $product->get_review_count() > 0;
}

function voitkus_product_review_rating_field(): string
{
    if (! function_exists('wc_review_ratings_enabled') || ! wc_review_ratings_enabled()) {
        return '';
    }

    $required      = function_exists('wc_review_ratings_required') && wc_review_ratings_required();
    $required_attr = $required ? ' required' : '';
    $required_mark = $required ? '&nbsp;<span class="required">*</span>' : '';

    $html  = '<div class="comment-form-rating form-row product-reviews__rating">';
    $html .= '<div class="product-reviews__rating-head">';
    $html .= '<span class="product-reviews__rating-label" id="comment-form-rating-label">'
        . esc_html__('Ocena', 'voitkus')
        . $required_mark
        . '</span>';
    $html .= '<span class="product-reviews__rating-value" data-rating-value aria-live="polite"></span>';
    $html .= '</div>';
    $html .= '<div class="product-reviews__stars" role="radiogroup" aria-labelledby="comment-form-rating-label">';

    for ($stars = 5; $stars >= 1; $stars--) {
        $input_id = 'voitkus-rating-' . $stars;
        $label    = sprintf(
            /* translators: %d: rating value from 1 to 5 */
            esc_html__('%d z 5 gwiazdek', 'voitkus'),
            $stars
        );

        $html .= sprintf(
            '<input class="product-reviews__rating-input" type="radio" id="%1$s" name="rating" value="%2$d"%3$s />',
            esc_attr($input_id),
            $stars,
            $required_attr
        );
        $html .= sprintf(
            '<label for="%1$s" title="%2$s"><span class="screen-reader-text">%2$s</span></label>',
            esc_attr($input_id),
            $label
        );
    }

    $html .= '</div></div>';

    return $html;
}

function voitkus_product_review_comment_textarea_field(): string
{
    return '<p class="comment-form-comment form-row">'
        . '<label for="comment">'
        . esc_html__('Twoja opinia', 'voitkus')
        . '&nbsp;<span class="required">*</span>'
        . '</label>'
        . '<textarea id="comment" name="comment" cols="45" rows="4" required placeholder="'
        . esc_attr__('Smak, parzenie, dla kogo polecasz…', 'voitkus')
        . '"></textarea></p>';
}

/**
 * @return array<string, mixed>
 */
function voitkus_product_review_form_args(): array
{
    $commenter    = wp_get_current_commenter();
    $comment_form = [
        /* translators: %s: product title */
        'title_reply'          => voitkus_product_has_review_comments()
            ? esc_html__('Dodaj opinię', 'voitkus')
            : sprintf(esc_html__('Napisz pierwszą opinię o „%s”', 'voitkus'), get_the_title()),
        'title_reply_to'       => esc_html__('Odpowiedz na opinię %s', 'voitkus'),
        'title_reply_before'   => '<h3 id="reply-title" class="product-reviews__form-title">',
        'title_reply_after'    => '</h3>',
        'comment_notes_before' => '<p class="product-reviews__moderation-note">'
            . esc_html__('Opinia pojawi się po akceptacji. Kupiłeś online, próbowałeś w kawiarni lub gdzie indziej — podziel się wrażeniami o tej kawie.', 'voitkus')
            . '</p>',
        'comment_notes_after'  => '',
        'label_submit'         => esc_html__('Wyślij opinię', 'voitkus'),
        'logged_in_as'         => '',
        'must_log_in'          => '',
        'comment_field'        => '',
        'class_form'           => 'product-reviews__comment-form comment-form',
    ];

    $name_email_required = (bool) get_option('require_name_email', 1);
    $fields              = [
        'author' => [
            'label'        => __('Imię', 'voitkus'),
            'type'         => 'text',
            'value'        => $commenter['comment_author'],
            'required'     => $name_email_required,
            'autocomplete' => 'name',
        ],
        'email'  => [
            'label'        => __('E-mail', 'voitkus'),
            'type'         => 'email',
            'value'        => $commenter['comment_author_email'],
            'required'     => $name_email_required,
            'autocomplete' => 'email',
        ],
    ];

    $comment_form['fields'] = [];

    if (! is_user_logged_in()) {
        foreach ($fields as $key => $field) {
            $required_attr = $field['required'] ? ' required' : '';
            $comment_form['fields'][ $key ] = sprintf(
                '<p class="comment-form-%1$s form-row"><label for="%1$s">%2$s%3$s</label><input id="%1$s" name="%1$s" type="%4$s" autocomplete="%5$s" value="%6$s" size="30"%7$s /></p>',
                esc_attr($key),
                esc_html($field['label']),
                $field['required'] ? '&nbsp;<span class="required">*</span>' : '',
                esc_attr($field['type']),
                esc_attr($field['autocomplete']),
                esc_attr($field['value']),
                $required_attr
            );
        }
    }

    $comment_form['comment_field'] = '';

    $comment_form = apply_filters('woocommerce_product_review_comment_form_args', $comment_form);

    $comment_form['comment_field'] = voitkus_product_review_rating_field()
        . voitkus_product_review_comment_textarea_field();

    return $comment_form;
}

function voitkus_render_product_review_form(?WC_Product $product = null, bool $compact = false): void
{
    if (! $product instanceof WC_Product) {
        $product = wc_get_product(get_the_ID());
    }

    if (! $product instanceof WC_Product || ! $product->get_reviews_allowed() || ! comments_open()) {
        return;
    }

    $wrap_class = $compact ? 'product-reviews__form-wrap--compact' : 'product-reviews__form-wrap--full';

    if (! voitkus_product_can_review($product)) {
        return;
    }

    $user_id = get_current_user_id();
    $email   = '';

    if ($user_id > 0) {
        $user = get_user_by('id', $user_id);

        if ($user instanceof WP_User && is_email($user->user_email)) {
            $email = $user->user_email;
        }
    } else {
        $commenter = wp_get_current_commenter();
        $email     = isset($commenter['comment_author_email']) ? (string) $commenter['comment_author_email'] : '';
    }

    if (voitkus_identity_has_product_review($product, $user_id, $email)) {
        echo '<p class="product-reviews__verified-only">'
            . esc_html__('Twoja opinia o tym produkcie została już wysłana i oczekuje na publikację lub jest już widoczna.', 'voitkus')
            . '</p>';

        return;
    }

    echo '<div id="review_form_wrapper" class="product-reviews__form-wrap ' . esc_attr($wrap_class) . '">';
    echo '<div id="review_form" class="product-reviews__form">';
    comment_form(voitkus_product_review_form_args());
    echo '</div></div>';
}

function voitkus_render_product_review_list(): void
{
    $comments = voitkus_setup_product_review_query();

    if (! comments_open() && $comments === []) {
        return;
    }

    echo '<div id="comments" class="product-reviews__list-wrap">';

    if ($comments !== []) {
        echo '<ol class="commentlist product-reviews__list">';
        wp_list_comments(
            apply_filters(
                'woocommerce_product_review_list_args',
                [
                    'callback' => 'woocommerce_comments',
                    'style'    => 'ol',
                ]
            ),
            $comments
        );
        echo '</ol>';

        if (get_comment_pages_count() > 1 && get_option('page_comments')) {
            echo '<nav class="woocommerce-pagination product-reviews__pagination" aria-label="'
                . esc_attr__('Opinie — nawigacja', 'voitkus')
                . '">';
            paginate_comments_links(
                apply_filters(
                    'woocommerce_comment_pagination_args',
                    [
                        'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
                        'next_text' => is_rtl() ? '&larr;' : '&rarr;',
                        'type'      => 'list',
                    ]
                )
            );
            echo '</nav>';
        }
    } else {
        echo '<p class="product-reviews__empty woocommerce-noreviews">'
            . esc_html__('Na razie brak opinii.', 'voitkus')
            . '</p>';
    }

    echo '</div>';
}

function voitkus_product_review_is_submission(int $post_id): bool
{
    return $post_id > 0
        && get_post_type($post_id) === 'product'
        && isset($_POST['rating'])
        && absint(wp_unslash($_POST['rating'])) >= 1;
}

/**
 * @param array<string, mixed> $commentdata
 * @return array<string, mixed>
 */
function voitkus_product_review_preprocess_comment(array $commentdata): array
{
    $post_id = (int) ($commentdata['comment_post_ID'] ?? 0);

    if (! voitkus_product_review_is_submission($post_id)) {
        return $commentdata;
    }

    $product = wc_get_product($post_id);

    if (! $product instanceof WC_Product) {
        return $commentdata;
    }

    $rating = absint(wp_unslash($_POST['rating']));

    if ($rating < 1 || $rating > 5) {
        wp_die(
            esc_html__('Wybierz ocenę od 1 do 5 gwiazdek.', 'voitkus'),
            esc_html__('Opinia o produkcie', 'voitkus'),
            ['response' => 400, 'back_link' => true]
        );
    }

    $user_id = get_current_user_id();
    $email   = isset($commentdata['comment_author_email']) ? sanitize_email((string) $commentdata['comment_author_email']) : '';

    if ($user_id <= 0 && ($email === '' || ! is_email($email))) {
        wp_die(
            esc_html__('Podaj poprawny adres e-mail.', 'voitkus'),
            esc_html__('Opinia o produkcie', 'voitkus'),
            ['response' => 400, 'back_link' => true]
        );
    }

    if (voitkus_identity_has_product_review($product, $user_id, $email)) {
        wp_die(
            esc_html__('Już przesłałeś opinię o tym produkcie.', 'voitkus'),
            esc_html__('Opinia o produkcie', 'voitkus'),
            ['response' => 403, 'back_link' => true]
        );
    }

    $user = wp_get_current_user();

    if ($user instanceof WP_User && $user_id > 0) {
        if (trim((string) ($commentdata['comment_author'] ?? '')) === '') {
            $commentdata['comment_author'] = $user->display_name !== '' ? $user->display_name : $user->user_login;
        }

        $commentdata['comment_author_email'] = $user->user_email;
    }

    $commentdata['comment_type'] = 'review';

    return $commentdata;
}
add_filter('preprocess_comment', 'voitkus_product_review_preprocess_comment');

function voitkus_product_review_save_meta(int $comment_id, $approved, $commentdata = []): void
{
    $comment = get_comment($comment_id);

    if (! $comment instanceof WP_Comment || get_post_type((int) $comment->comment_post_ID) !== 'product') {
        return;
    }

    if ((string) $comment->comment_type !== 'review') {
        return;
    }

    $product = wc_get_product((int) $comment->comment_post_ID);

    if (! $product instanceof WC_Product) {
        return;
    }

    if (voitkus_customer_bought_product($product, (int) $comment->user_id, (string) $comment->comment_author_email)) {
        update_comment_meta($comment_id, 'verified', '1');
    }
}
add_action('comment_post', 'voitkus_product_review_save_meta', 10, 3);

/**
 * @param int|string $approved
 * @param array<string, mixed> $commentdata
 * @return int|string
 */
function voitkus_product_review_moderation($approved, array $commentdata)
{
    $post_id = (int) ($commentdata['comment_post_ID'] ?? 0);

    if (! voitkus_product_review_is_submission($post_id)) {
        return $approved;
    }

    $product = wc_get_product($post_id);

    if (! $product instanceof WC_Product) {
        return $approved;
    }

    $email = isset($commentdata['comment_author_email']) ? sanitize_email((string) $commentdata['comment_author_email']) : '';

    if (voitkus_customer_bought_product($product, get_current_user_id(), $email)) {
        return $approved;
    }

    return 0;
}
add_filter('pre_comment_approved', 'voitkus_product_review_moderation', 10, 2);

function voitkus_product_reviews_disable_verification_required(string $value): string
{
    return 'no';
}
add_filter('option_woocommerce_review_rating_verification_required', 'voitkus_product_reviews_disable_verification_required');
