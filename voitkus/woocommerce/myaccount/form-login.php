<?php
/**
 * Login Form
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_customer_login_form');
?>

<div class="voitkus-auth<?php echo 'yes' === get_option('woocommerce_enable_myaccount_registration') ? '' : ' voitkus-auth--solo'; ?>">
    <?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
        <div class="voitkus-auth__grid">
            <section class="voitkus-auth__panel" aria-labelledby="voitkus-auth-login-title">
                <p class="voitkus-auth__eyebrow"><?php esc_html_e('Masz konto', 'voitkus'); ?></p>
                <h2 id="voitkus-auth-login-title" class="voitkus-auth__title"><?php esc_html_e('Logowanie', 'voitkus'); ?></h2>
                <p class="voitkus-auth__lead"><?php esc_html_e('Zaloguj się, aby śledzić zamówienia i zapisać adres dostawy.', 'voitkus'); ?></p>

                <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
                    <?php do_action('woocommerce_login_form_start'); ?>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="username">
                            <?php esc_html_e('Nazwa użytkownika lub adres e-mail', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                        </label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (! empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" />
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="password">
                            <?php esc_html_e('Hasło', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                        </label>
                        <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
                    </p>

                    <?php do_action('woocommerce_login_form'); ?>

                    <p class="form-row voitkus-auth__remember">
                        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                            <span><?php esc_html_e('Zapamiętaj mnie', 'voitkus'); ?></span>
                        </label>
                    </p>

                    <div class="voitkus-auth__foot">
                        <p class="form-row voitkus-auth__actions">
                            <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                            <button type="submit" class="woocommerce-button button woocommerce-form-login__submit voitkus-auth__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="login" value="<?php esc_attr_e('Zaloguj się', 'voitkus'); ?>">
                                <?php esc_html_e('Zaloguj się', 'voitkus'); ?>
                            </button>
                        </p>

                        <p class="woocommerce-LostPassword lost_password voitkus-auth__lost">
                            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Nie pamiętasz hasła?', 'voitkus'); ?></a>
                        </p>
                    </div>

                    <?php do_action('woocommerce_login_form_end'); ?>
                </form>
            </section>

            <section class="voitkus-auth__panel" aria-labelledby="voitkus-auth-register-title">
                <p class="voitkus-auth__eyebrow"><?php esc_html_e('Nowy klient', 'voitkus'); ?></p>
                <h2 id="voitkus-auth-register-title" class="voitkus-auth__title"><?php esc_html_e('Rejestracja', 'voitkus'); ?></h2>
                <p class="voitkus-auth__lead"><?php esc_html_e('Załóż konto w minutę — bez dodatkowych kosztów.', 'voitkus'); ?></p>

                <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>
                    <?php do_action('woocommerce_register_form_start'); ?>

                    <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="reg_username">
                                <?php esc_html_e('Nazwa użytkownika', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (! empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" />
                        </p>
                    <?php endif; ?>

                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_email">
                            <?php esc_html_e('Adres e-mail', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                        </label>
                        <input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (! empty($_POST['email']) && is_string($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required aria-required="true" />
                    </p>

                    <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <label for="reg_password">
                                <?php esc_html_e('Hasło', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                            </label>
                            <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
                        </p>
                    <?php else : ?>
                        <p class="voitkus-auth__hint">
                            <?php esc_html_e('Link do ustawienia hasła wyślemy na Twój e-mail.', 'voitkus'); ?>
                        </p>
                    <?php endif; ?>

                    <?php do_action('woocommerce_register_form'); ?>

                    <div class="voitkus-auth__foot">
                        <p class="woocommerce-form-row form-row voitkus-auth__actions">
                            <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                            <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit voitkus-auth__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="register" value="<?php esc_attr_e('Zarejestruj się', 'voitkus'); ?>">
                                <?php esc_html_e('Zarejestruj się', 'voitkus'); ?>
                            </button>
                        </p>

                        <p class="voitkus-auth__lost voitkus-auth__lost--placeholder" aria-hidden="true">&nbsp;</p>
                    </div>

                    <?php do_action('woocommerce_register_form_end'); ?>
                </form>
            </section>
        </div>
    <?php else : ?>
        <section class="voitkus-auth__panel voitkus-auth__panel--solo" aria-labelledby="voitkus-auth-login-title">
            <p class="voitkus-auth__eyebrow"><?php esc_html_e('Konto klienta', 'voitkus'); ?></p>
            <h2 id="voitkus-auth-login-title" class="voitkus-auth__title"><?php esc_html_e('Logowanie', 'voitkus'); ?></h2>
            <p class="voitkus-auth__lead"><?php esc_html_e('Zaloguj się, aby śledzić zamówienia i zapisać adres dostawy.', 'voitkus'); ?></p>

            <form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>
                <?php do_action('woocommerce_login_form_start'); ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="username">
                        <?php esc_html_e('Nazwa użytkownika lub adres e-mail', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                    </label>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (! empty($_POST['username']) && is_string($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" />
                </p>
                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="password">
                        <?php esc_html_e('Hasło', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                    </label>
                    <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
                </p>

                <?php do_action('woocommerce_login_form'); ?>

                <p class="form-row voitkus-auth__remember">
                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                        <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                        <span><?php esc_html_e('Zapamiętaj mnie', 'voitkus'); ?></span>
                    </label>
                </p>

                <div class="voitkus-auth__foot">
                    <p class="form-row voitkus-auth__actions">
                        <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                        <button type="submit" class="woocommerce-button button woocommerce-form-login__submit voitkus-auth__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="login" value="<?php esc_attr_e('Zaloguj się', 'voitkus'); ?>">
                            <?php esc_html_e('Zaloguj się', 'voitkus'); ?>
                        </button>
                    </p>

                    <p class="woocommerce-LostPassword lost_password voitkus-auth__lost">
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Nie pamiętasz hasła?', 'voitkus'); ?></a>
                    </p>
                </div>

                <?php do_action('woocommerce_login_form_end'); ?>
            </form>
        </section>
    <?php endif; ?>
</div>

<?php
do_action('woocommerce_after_customer_login_form');
