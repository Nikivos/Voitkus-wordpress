<?php
/**
 * Brand reviews (opinie o palarni) — separate from WooCommerce product reviews.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_brand_reviews_page_slug(): string
{
    return 'opinie';
}

function voitkus_brand_reviews_page_id(): int
{
    static $page_id = null;

    if ($page_id !== null) {
        return $page_id;
    }

    $page_id = voitkus_find_page_by_slug(voitkus_brand_reviews_page_slug());

    return $page_id > 0 ? $page_id : 0;
}

function voitkus_brand_reviews_page_url(): string
{
    $page_id = voitkus_brand_reviews_page_id();

    if ($page_id <= 0) {
        return home_url('/' . voitkus_brand_reviews_page_slug() . '/');
    }

    return (string) get_permalink($page_id);
}

function voitkus_is_brand_reviews_page(): bool
{
    return is_page() && voitkus_brand_reviews_page_id() === (int) get_queried_object_id();
}

function voitkus_ensure_brand_reviews_page(): void
{
    if (is_admin() && ! wp_doing_ajax()) {
        return;
    }

    $flag = 'voitkus_brand_reviews_page_v1';

    if (get_option($flag) === 'done') {
        return;
    }

    $page_id = voitkus_ensure_page(
        voitkus_brand_reviews_page_slug(),
        __('Opinie o Voitkus', 'voitkus'),
        '[voitkus_brand_reviews]'
    );

    if ($page_id > 0) {
        wp_update_post(
            [
                'ID'             => $page_id,
                'comment_status' => 'open',
            ]
        );
    }

    update_option($flag, 'done', false);
}
add_action('init', 'voitkus_ensure_brand_reviews_page', 8);

function voitkus_register_brand_reviews_shortcode(): void
{
    add_shortcode('voitkus_brand_reviews', 'voitkus_brand_reviews_shortcode');
}
add_action('init', 'voitkus_register_brand_reviews_shortcode');

function voitkus_customer_bought_any_product(int $user_id = 0): bool
{
    if (! function_exists('wc_get_orders')) {
        return false;
    }

    if ($user_id <= 0) {
        $user_id = get_current_user_id();
    }

    if ($user_id <= 0) {
        return false;
    }

    $orders = wc_get_orders(
        [
            'customer_id' => $user_id,
            'status'      => wc_get_is_paid_statuses(),
            'limit'       => 1,
            'return'      => 'ids',
        ]
    );

    return $orders !== [];
}

function voitkus_brand_reviews_guest_comments_enabled(): bool
{
    if (function_exists('voitkus_is_brand_reviews_page') && voitkus_is_brand_reviews_page()) {
        return true;
    }

    if (isset($_POST['comment_post_ID'])) {
        return (int) wp_unslash($_POST['comment_post_ID']) === voitkus_brand_reviews_page_id();
    }

    return false;
}

/**
 * Allow guest brand reviews even when WP requires registration for other comments.
 *
 * @param mixed $pre
 * @return mixed
 */
function voitkus_brand_reviews_allow_guest_comments($pre)
{
    if (voitkus_brand_reviews_guest_comments_enabled()) {
        return '0';
    }

    return $pre;
}
add_filter('pre_option_comment_registration', 'voitkus_brand_reviews_allow_guest_comments');

/**
 * @return array<int, WP_Comment>
 */
function voitkus_get_brand_review_comments(int $limit = 0): array
{
    $page_id = voitkus_brand_reviews_page_id();

    if ($page_id <= 0) {
        return [];
    }

    $args = [
        'post_id' => $page_id,
        'status'  => 'approve',
        'type'    => 'comment',
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
        'meta_query' => [
            [
                'key'   => 'voitkus_brand_review',
                'value' => '1',
            ],
        ],
    ];

    if ($limit > 0) {
        $args['number'] = $limit;
    }

    $comments = get_comments($args);

    return is_array($comments) ? $comments : [];
}

/**
 * @return array{count: int, average: float}
 */
function voitkus_brand_reviews_summary(): array
{
    $comments = voitkus_get_brand_review_comments();
    $total    = 0.0;
    $count    = 0;

    foreach ($comments as $comment) {
        $rating = (int) get_comment_meta((int) $comment->comment_ID, 'rating', true);

        if ($rating < 1 || $rating > 5) {
            continue;
        }

        $total += $rating;
        ++$count;
    }

    return [
        'count'   => count($comments),
        'average' => $count > 0 ? round($total / $count, 1) : 0.0,
    ];
}

/**
 * @return array<int, string>
 */
function voitkus_brand_review_accents(): array
{
    return ['yellow', 'orange', 'magenta', 'cyan', 'lime'];
}

/**
 * @return array{quote: string, author: string, accent: string, rating: int, verified: bool}
 */
function voitkus_format_brand_review_item(WP_Comment $comment, int $index = 0): array
{
    $accents = voitkus_brand_review_accents();
    $rating  = (int) get_comment_meta((int) $comment->comment_ID, 'rating', true);
    $quote   = wp_strip_all_tags((string) $comment->comment_content);
    $author  = trim((string) $comment->comment_author);

    if ($author === '') {
        $author = __('Klient', 'voitkus');
    }

    return [
        'quote'    => $quote,
        'author'   => $author,
        'accent'   => $accents[ $index % count($accents) ],
        'rating'   => max(0, min(5, $rating)),
        'verified' => (string) get_comment_meta((int) $comment->comment_ID, 'verified', true) === '1',
    ];
}

/**
 * @return array{eyebrow: string, title: string, items: array<int, array{quote: string, author: string, accent: string, rating: int, verified: bool}>, summary: array{count: int, average: float}, page_url: string}
 */
function voitkus_reviews_defaults(): array
{
    return [
        'eyebrow' => __('Opinie', 'voitkus'),
        'title'   => __('Co mówią klienci', 'voitkus'),
    ];
}

function voitkus_reviews_section(): array
{
    $defaults = voitkus_reviews_defaults();
    $items    = [];
    $comments = voitkus_get_brand_review_comments(3);

    foreach ($comments as $index => $comment) {
        if (! $comment instanceof WP_Comment) {
            continue;
        }

        $item = voitkus_format_brand_review_item($comment, $index);

        if ($item['quote'] === '') {
            continue;
        }

        $items[] = $item;
    }

    return [
        'eyebrow'  => (string) get_theme_mod('voitkus_reviews_eyebrow', $defaults['eyebrow']),
        'title'    => (string) get_theme_mod('voitkus_reviews_title', $defaults['title']),
        'items'    => $items,
        'summary'  => voitkus_brand_reviews_summary(),
        'page_url' => voitkus_brand_reviews_page_url(),
    ];
}

function voitkus_customize_register_reviews(WP_Customize_Manager $wp_customize): void
{
    $defaults = voitkus_reviews_defaults();

    $wp_customize->add_section('voitkus_reviews', [
        'title'       => __('Opinie o palarni (strona główna)', 'voitkus'),
        'description' => __('Treść kart pochodzi z zatwierdzonych opinii na stronie /opinie/. Tutaj edytujesz tylko nagłówek sekcji.', 'voitkus'),
        'priority'    => 34,
    ]);

    $header_fields = [
        'voitkus_reviews_eyebrow' => [__('Eyebrow', 'voitkus'), $defaults['eyebrow']],
        'voitkus_reviews_title'   => [__('Nagłówek sekcji', 'voitkus'), $defaults['title']],
    ];

    foreach ($header_fields as $key => $field) {
        $wp_customize->add_setting($key, [
            'default'           => $field[1],
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $wp_customize->add_control($key, [
            'label'   => $field[0],
            'section' => 'voitkus_reviews',
            'type'    => 'text',
        ]);
    }
}

function voitkus_render_brand_review_stars(int $rating): string
{
    if ($rating < 1 || $rating > 5) {
        return '';
    }

    if (function_exists('wc_get_rating_html')) {
        return (string) wc_get_rating_html($rating);
    }

    $filled = str_repeat('★', $rating);
    $empty  = str_repeat('☆', 5 - $rating);

    return '<span aria-hidden="true">' . esc_html($filled . $empty) . '</span>';
}

function voitkus_render_brand_review_card(array $item, string $wrap_tag = 'li'): void
{
    $tag = $wrap_tag === 'article' ? 'article' : 'li';
    $rating_label = $item['rating'] > 0
        ? sprintf(
            /* translators: %d: rating value from 1 to 5 */
            esc_attr__('Ocena %d na 5', 'voitkus'),
            (int) $item['rating']
        )
        : '';

    echo '<' . esc_attr($tag) . ' class="review-card" data-lot-accent="' . esc_attr($item['accent']) . '">';

    if ($item['rating'] > 0) {
        echo '<div class="review-card__stars"';
        if ($rating_label !== '') {
            echo ' aria-label="' . esc_attr($rating_label) . '"';
        }
        echo '>';
        echo voitkus_render_brand_review_stars((int) $item['rating']);
        echo '</div>';
    }

    if ($item['quote'] !== '') {
        echo '<blockquote class="review-card__quote"><p>«' . esc_html($item['quote']) . '»</p></blockquote>';
    }

    echo '<p class="review-card__author">';
    echo esc_html($item['author']);
    if (! empty($item['verified'])) {
        echo ' <span class="review-card__verified">' . esc_html__('Zweryfikowany zakup', 'voitkus') . '</span>';
    }
    echo '</p>';
    echo '</' . esc_attr($tag) . '>';
}

function voitkus_brand_review_comment_textarea_field(): string
{
    return '<p class="comment-form-comment form-row">'
        . '<label for="comment">'
        . esc_html__('Twoja opinia o palarni', 'voitkus')
        . '&nbsp;<span class="required">*</span>'
        . '</label>'
        . '<textarea id="comment" name="comment" cols="45" rows="5" required placeholder="'
        . esc_attr__('Obsługa, dostawa, jakość palenia, zaufanie do marki…', 'voitkus')
        . '"></textarea></p>';
}

/**
 * @return array<string, mixed>
 */
function voitkus_brand_review_form_args(): array
{
    $commenter    = wp_get_current_commenter();
    $comment_form = [
        'title_reply'          => esc_html__('Oceń Voitkus', 'voitkus'),
        'title_reply_before'   => '<h2 id="reply-title" class="product-reviews__form-title brand-reviews-page__form-title">',
        'title_reply_after'    => '</h2>',
        'comment_notes_before' => '<p class="brand-reviews-page__moderation-note">'
            . esc_html__('Opinia pojawi się na stronie po akceptacji. Możesz ocenić palarnię po degustacji w kawiarni, zakupie online lub gdzie indziej.', 'voitkus')
            . '</p>',
        'comment_notes_after'  => '',
        'label_submit'         => esc_html__('Wyślij opinię', 'voitkus'),
        'logged_in_as'         => '',
        'must_log_in'          => '',
        'comment_field'        => '',
        'class_form'           => 'product-reviews__comment-form comment-form brand-reviews-page__comment-form',
        'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s brand-reviews-page__submit">%4$s</button>',
        'submit_field'         => '<p class="form-submit brand-reviews-page__submit-wrap">%1$s %2$s</p>',
        'fields'               => [],
    ];

    if (! is_user_logged_in()) {
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

    $comment_form['comment_field'] = voitkus_product_review_rating_field()
        . voitkus_brand_review_comment_textarea_field();

    return $comment_form;
}

function voitkus_render_brand_review_form(): void
{
    if (! comments_open(voitkus_brand_reviews_page_id())) {
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

    if (voitkus_identity_has_brand_review($user_id, $email)) {
        echo '<p class="product-reviews__verified-only">'
            . esc_html__('Twoja opinia została już wysłana i oczekuje na publikację lub jest już widoczna.', 'voitkus')
            . '</p>';

        return;
    }

    echo '<div id="review_form_wrapper" class="product-reviews__form-wrap product-reviews__form-wrap--full brand-reviews-page__form-wrap">';
    echo '<div id="review_form" class="product-reviews__form">';
    comment_form(voitkus_brand_review_form_args(), voitkus_brand_reviews_page_id());
    echo '</div></div>';
}

function voitkus_identity_has_brand_review(int $user_id = 0, string $email = ''): bool
{
    $page_id = voitkus_brand_reviews_page_id();

    if ($page_id <= 0) {
        return false;
    }

    if ($user_id <= 0) {
        $user_id = get_current_user_id();
    }

    $meta_query = [
        [
            'key'   => 'voitkus_brand_review',
            'value' => '1',
        ],
    ];

    if ($user_id > 0) {
        $count = (int) get_comments(
            [
                'post_id'    => $page_id,
                'user_id'    => $user_id,
                'count'      => true,
                'meta_query' => $meta_query,
            ]
        );

        if ($count > 0) {
            return true;
        }
    }

    if ($email === '' || ! is_email($email)) {
        return false;
    }

    $count = (int) get_comments(
        [
            'post_id'              => $page_id,
            'author_email'         => $email,
            'count'                => true,
            'include_unapproved'   => true,
            'meta_query'           => $meta_query,
        ]
    );

    return $count > 0;
}

/**
 * @param array<string, mixed> $commentdata
 * @return array<string, mixed>
 */
function voitkus_brand_review_preprocess_comment(array $commentdata): array
{
    $page_id = voitkus_brand_reviews_page_id();

    if ($page_id <= 0 || (int) ($commentdata['comment_post_ID'] ?? 0) !== $page_id) {
        return $commentdata;
    }

    $rating = isset($_POST['rating']) ? absint(wp_unslash($_POST['rating'])) : 0;

    if ($rating < 1 || $rating > 5) {
        wp_die(
            esc_html__('Wybierz ocenę od 1 do 5 gwiazdek.', 'voitkus'),
            esc_html__('Opinia o palarni', 'voitkus'),
            ['response' => 400, 'back_link' => true]
        );
    }

    $user_id = get_current_user_id();
    $email   = isset($commentdata['comment_author_email']) ? sanitize_email((string) $commentdata['comment_author_email']) : '';

    if ($user_id <= 0 && ($email === '' || ! is_email($email))) {
        wp_die(
            esc_html__('Podaj poprawny adres e-mail.', 'voitkus'),
            esc_html__('Opinia o palarni', 'voitkus'),
            ['response' => 400, 'back_link' => true]
        );
    }

    if (voitkus_identity_has_brand_review($user_id, $email)) {
        wp_die(
            esc_html__('Już przesłałeś opinię o palarni.', 'voitkus'),
            esc_html__('Opinia o palarni', 'voitkus'),
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

    return $commentdata;
}
add_filter('preprocess_comment', 'voitkus_brand_review_preprocess_comment');

function voitkus_brand_review_save_meta(int $comment_id, $approved, $commentdata = []): void
{
    $page_id = voitkus_brand_reviews_page_id();
    $comment = get_comment($comment_id);

    if ($page_id <= 0 || ! $comment instanceof WP_Comment || (int) $comment->comment_post_ID !== $page_id) {
        return;
    }

    $rating = isset($_POST['rating']) ? absint(wp_unslash($_POST['rating'])) : 0;

    add_comment_meta($comment_id, 'voitkus_brand_review', '1', true);

    if ($rating >= 1 && $rating <= 5) {
        add_comment_meta($comment_id, 'rating', $rating, true);
    }

    if (voitkus_customer_bought_any_product()) {
        add_comment_meta($comment_id, 'verified', '1', true);
    }
}
add_action('comment_post', 'voitkus_brand_review_save_meta', 10, 3);

/**
 * @param int|string $approved
 * @param array<string, mixed> $commentdata
 * @return int|string
 */
function voitkus_brand_review_hold_for_moderation($approved, array $commentdata)
{
    $page_id = voitkus_brand_reviews_page_id();

    if ($page_id <= 0 || (int) ($commentdata['comment_post_ID'] ?? 0) !== $page_id) {
        return $approved;
    }

    return 0;
}
add_filter('pre_comment_approved', 'voitkus_brand_review_hold_for_moderation', 10, 2);

function voitkus_brand_reviews_shortcode(): string
{
    $summary  = voitkus_brand_reviews_summary();
    $comments = voitkus_get_brand_review_comments();

    ob_start();
    ?>
    <div class="brand-reviews-page">
        <p class="brand-reviews-page__lead">
            <?php esc_html_e('Opinie o Voitkus Roasters — palarni, obsłudze i jakości. Spróbowałeś kawy w kawiarni, na degustacji lub kupiłeś gdzie indziej? Podziel się wrażeniami. Opinie o pojedynczym produkcie dodajesz na stronie kawy.', 'voitkus'); ?>
        </p>

        <?php if ($summary['count'] > 0) : ?>
            <div class="brand-reviews-page__summary">
                <?php if ($summary['average'] > 0 && function_exists('wc_get_rating_html')) : ?>
                    <div class="brand-reviews-page__summary-stars">
                        <?php echo wc_get_rating_html($summary['average']); ?>
                    </div>
                <?php endif; ?>
                <p class="brand-reviews-page__summary-text">
                    <?php
                    printf(
                        esc_html(
                            /* translators: 1: review count, 2: average rating */
                            _n(
                                '%1$s opinia · średnia %2$s/5',
                                '%1$s opinii · średnia %2$s/5',
                                $summary['count'],
                                'voitkus'
                            )
                        ),
                        number_format_i18n($summary['count']),
                        esc_html(number_format_i18n($summary['average'], 1))
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="brand-reviews-page__list-wrap">
            <?php if ($comments !== []) : ?>
                <ul class="reviews__grid brand-reviews-page__grid">
                    <?php foreach ($comments as $index => $comment) : ?>
                        <?php
                        if (! $comment instanceof WP_Comment) {
                            continue;
                        }
                        voitkus_render_brand_review_card(voitkus_format_brand_review_item($comment, $index));
                        ?>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="brand-reviews-page__empty">
                    <?php esc_html_e('Na razie brak opinii o palarni. Bądź pierwszy — forma poniżej.', 'voitkus'); ?>
                </p>
            <?php endif; ?>
        </div>

        <section id="form" class="brand-reviews-page__form-section" aria-labelledby="brand-review-form-title">
            <p class="brand-reviews-page__form-label" id="brand-review-form-title">
                <?php esc_html_e('Twoja opinia o palarni', 'voitkus'); ?>
            </p>
            <?php voitkus_render_brand_review_form(); ?>
        </section>
    </div>
    <?php

    return (string) ob_get_clean();
}

function voitkus_enqueue_brand_reviews_assets(): void
{
    if (is_admin()) {
        return;
    }

    if (! is_front_page() && ! voitkus_is_brand_reviews_page()) {
        return;
    }

    $script_path = get_stylesheet_directory() . '/assets/brand-reviews.js';

    wp_enqueue_script(
        'voitkus-brand-reviews',
        get_stylesheet_directory_uri() . '/assets/brand-reviews.js',
        [],
        file_exists($script_path) ? (string) filemtime($script_path) : wp_get_theme()->get('Version'),
        true
    );
}
add_action('wp_enqueue_scripts', 'voitkus_enqueue_brand_reviews_assets', 21);
