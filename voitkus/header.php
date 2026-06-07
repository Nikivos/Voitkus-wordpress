<?php
/**
 * Site header.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$cart_count = voitkus_cart_count();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" data-component="site-header">
    <div class="site-header__inner">
        <div class="header-start">
            <button
                class="mobile-menu-toggle"
                type="button"
                aria-controls="site-nav"
                aria-expanded="false"
                data-label-open="<?php echo esc_attr__('Open menu', 'voitkus'); ?>"
                data-label-close="<?php echo esc_attr__('Close menu', 'voitkus'); ?>"
                aria-label="<?php esc_attr_e('Open menu', 'voitkus'); ?>"
            >
                <span class="mobile-menu-toggle__icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                </span>
                <span class="mobile-menu-toggle__label"><?php esc_html_e('Menu', 'voitkus'); ?></span>
            </button>
        </div>

        <div class="site-branding">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a class="site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Voitkus homepage', 'voitkus'); ?>">
                    VOITKUS
                </a>
            <?php endif; ?>
        </div>

        <nav id="site-nav" class="site-nav" aria-label="<?php esc_attr_e('Primary navigation', 'voitkus'); ?>">
            <div class="site-nav__panel">
                <div class="site-nav__head">
                    <p class="site-nav__title"><?php esc_html_e('Menu', 'voitkus'); ?></p>
                    <button type="button" class="site-nav__close" aria-label="<?php esc_attr_e('Close menu', 'voitkus'); ?>">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="site-nav__body">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'site-nav__list',
                        'fallback_cb'    => 'voitkus_default_menu',
                        'depth'          => 1,
                    ]);
                    ?>
                </div>

                <div class="site-nav__footer">
                    <a class="site-nav__utility" href="<?php echo esc_url(home_url('/')); ?>">
                        <?php esc_html_e('Język: PL', 'voitkus'); ?>
                    </a>
                    <a class="site-nav__utility" href="<?php echo esc_url(voitkus_account_url()); ?>">
                        <?php esc_html_e('Konto', 'voitkus'); ?>
                    </a>
                </div>
            </div>
        </nav>

        <div class="header-actions header-actions--desktop" aria-label="<?php esc_attr_e('Header actions', 'voitkus'); ?>">
            <a class="header-action header-action--language" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Change language', 'voitkus'); ?>">
                PL
            </a>
            <a class="header-action header-action--account" href="<?php echo esc_url(voitkus_account_url()); ?>" aria-label="<?php esc_attr_e('Konto', 'voitkus'); ?>">
                <svg class="header-action__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </a>
            <a class="header-action header-action--cart" href="<?php echo esc_url(voitkus_cart_url()); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %d: cart items */ __('Koszyk, %d produktów', 'voitkus'), $cart_count)); ?>">
                <svg class="header-action__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6h15l-1.5 9h-12L6 6z"/>
                    <path d="M6 6L5 3H2"/>
                    <circle cx="9" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
                <?php if ($cart_count > 0) : ?>
                    <span class="cart-count" data-voitkus-cart-count="desktop"><?php echo esc_html((string) $cart_count); ?></span>
                <?php else : ?>
                    <span class="cart-count" data-voitkus-cart-count="desktop" style="display: none;" aria-hidden="true">0</span>
                <?php endif; ?>
            </a>
        </div>

        <div class="header-actions header-actions--mobile" aria-label="<?php esc_attr_e('Header actions', 'voitkus'); ?>">
            <a class="header-icon-btn" href="<?php echo esc_url(voitkus_account_url()); ?>" aria-label="<?php esc_attr_e('Konto', 'voitkus'); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </a>
            <a class="header-icon-btn header-icon-btn--cart" href="<?php echo esc_url(voitkus_cart_url()); ?>" aria-label="<?php echo esc_attr(sprintf(/* translators: %d: cart items */ __('Koszyk, %d produktów', 'voitkus'), $cart_count)); ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6h15l-1.5 9h-12L6 6z"/>
                    <path d="M6 6L5 3H2"/>
                    <circle cx="9" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
                <?php if ($cart_count > 0) : ?>
                    <span class="header-icon-btn__badge" data-voitkus-cart-count="mobile"><?php echo esc_html((string) $cart_count); ?></span>
                <?php else : ?>
                    <span class="header-icon-btn__badge" data-voitkus-cart-count="mobile" style="display: none;" aria-hidden="true">0</span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <button type="button" class="mobile-nav-backdrop" aria-hidden="true" tabindex="-1"></button>
</header>
