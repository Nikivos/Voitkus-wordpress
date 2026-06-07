<?php
/**
 * Cookie consent banner — niezbędne + opcjonalne (analytics/marketing).
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

function voitkus_cookie_consent_storage_key(): string
{
    return 'voitkus_cookie_consent_v1';
}

/**
 * @return array{
 *   necessary: bool,
 *   analytics: bool,
 *   marketing: bool,
 *   version: int,
 *   updated: int
 * }
 */
function voitkus_cookie_consent_defaults(): array
{
    return [
        'necessary' => true,
        'analytics' => false,
        'marketing' => false,
        'version'   => 1,
        'updated'   => 0,
    ];
}

/**
 * @return array<string, mixed>
 */
function voitkus_cookie_consent_config(): array
{
    $config = [
        'storageKey'   => voitkus_cookie_consent_storage_key(),
        'cookiesUrl'   => home_url('/legal/cookies/'),
        'privacyUrl'   => home_url('/legal/privacy/'),
        'analyticsId'  => (string) get_theme_mod('voitkus_ga_measurement_id', ''),
        'marketingId'  => (string) get_theme_mod('voitkus_meta_pixel_id', ''),
        'labels'       => [
            'title'          => __('Pliki cookies', 'voitkus'),
            'description'    => __(
                'Używamy cookies niezbędnych do działania sklepu (koszyk, sesja, zamówienia). Za Twoją zgodą możemy włączyć statystyki odwiedzin i marketing — wtedy zapisujemy je dopiero po akceptacji.',
                'voitkus'
            ),
            'acceptAll'      => __('Akceptuj wszystkie', 'voitkus'),
            'rejectOptional' => __('Tylko niezbędne', 'voitkus'),
            'settings'       => __('Ustawienia', 'voitkus'),
            'save'           => __('Zapisz wybór', 'voitkus'),
            'analytics'      => __('Statystyka (np. Google Analytics)', 'voitkus'),
            'marketing'      => __('Marketing (np. Meta Pixel)', 'voitkus'),
            'necessary'      => __('Niezbędne (zawsze aktywne)', 'voitkus'),
            'policy'         => __('Polityka cookies', 'voitkus'),
        ],
    ];

    return apply_filters('voitkus_cookie_consent_config', $config);
}

function voitkus_enqueue_cookie_consent_assets(): void
{
    if (is_admin()) {
        return;
    }

    $path = get_stylesheet_directory() . '/assets/cookie-consent.js';

    if (! is_readable($path)) {
        return;
    }

    wp_enqueue_script(
        'voitkus-cookie-consent',
        get_stylesheet_directory_uri() . '/assets/cookie-consent.js',
        [],
        (string) filemtime($path),
        true
    );

    wp_localize_script('voitkus-cookie-consent', 'voitkusCookieConsent', voitkus_cookie_consent_config());
}
add_action('wp_enqueue_scripts', 'voitkus_enqueue_cookie_consent_assets', 30);

function voitkus_render_cookie_consent_banner(): void
{
    if (is_admin()) {
        return;
    }

    $cookies_url = esc_url(home_url('/legal/cookies/'));
    ?>
    <div
        id="voitkus-cookie-banner"
        class="voitkus-cookie-banner"
        hidden
        role="dialog"
        aria-modal="true"
        aria-labelledby="voitkus-cookie-banner-title"
        aria-describedby="voitkus-cookie-banner-desc"
    >
        <div class="voitkus-cookie-banner__panel">
            <div class="voitkus-cookie-banner__main">
                <h2 id="voitkus-cookie-banner-title" class="voitkus-cookie-banner__title">
                    <?php esc_html_e('Pliki cookies', 'voitkus'); ?>
                </h2>
                <p id="voitkus-cookie-banner-desc" class="voitkus-cookie-banner__text">
                    <?php esc_html_e(
                        'Używamy cookies niezbędnych do działania sklepu (koszyk, sesja, zamówienia). Za Twoją zgodą możemy włączyć statystyki odwiedzin i marketing — wtedy zapisujemy je dopiero po akceptacji.',
                        'voitkus'
                    ); ?>
                </p>
                <p class="voitkus-cookie-banner__links">
                    <a href="<?php echo $cookies_url; ?>">
                        <?php esc_html_e('Polityka cookies', 'voitkus'); ?>
                    </a>
                </p>
            </div>

            <div class="voitkus-cookie-banner__settings" id="voitkus-cookie-settings" hidden>
                <ul class="voitkus-cookie-banner__categories">
                    <li class="voitkus-cookie-banner__category">
                        <label class="voitkus-cookie-banner__category-label">
                            <input type="checkbox" checked disabled>
                            <span><?php esc_html_e('Niezbędne (zawsze aktywne)', 'voitkus'); ?></span>
                        </label>
                    </li>
                    <li class="voitkus-cookie-banner__category">
                        <label class="voitkus-cookie-banner__category-label">
                            <input type="checkbox" id="voitkus-cookie-analytics" value="analytics">
                            <span><?php esc_html_e('Statystyka (np. Google Analytics)', 'voitkus'); ?></span>
                        </label>
                    </li>
                    <li class="voitkus-cookie-banner__category">
                        <label class="voitkus-cookie-banner__category-label">
                            <input type="checkbox" id="voitkus-cookie-marketing" value="marketing">
                            <span><?php esc_html_e('Marketing (np. Meta Pixel)', 'voitkus'); ?></span>
                        </label>
                    </li>
                </ul>
            </div>

            <div class="voitkus-cookie-banner__actions">
                <button type="button" class="voitkus-cookie-banner__btn voitkus-cookie-banner__btn--ghost" data-voitkus-cookie-action="settings">
                    <?php esc_html_e('Ustawienia', 'voitkus'); ?>
                </button>
                <button type="button" class="voitkus-cookie-banner__btn voitkus-cookie-banner__btn--ghost" data-voitkus-cookie-action="reject">
                    <?php esc_html_e('Tylko niezbędne', 'voitkus'); ?>
                </button>
                <button type="button" class="voitkus-cookie-banner__btn voitkus-cookie-banner__btn--primary" data-voitkus-cookie-action="accept">
                    <?php esc_html_e('Akceptuj wszystkie', 'voitkus'); ?>
                </button>
                <button type="button" class="voitkus-cookie-banner__btn voitkus-cookie-banner__btn--primary" data-voitkus-cookie-action="save" hidden>
                    <?php esc_html_e('Zapisz wybór', 'voitkus'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'voitkus_render_cookie_consent_banner', 5);
