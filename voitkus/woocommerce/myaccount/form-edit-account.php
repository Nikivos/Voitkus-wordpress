<?php
/**
 * Edit account form
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

defined('ABSPATH') || exit;

/**
 * @var WP_User $user
 */

do_action('woocommerce_before_edit_account_form');
?>

<form class="woocommerce-EditAccountForm edit-account voitkus-account-form" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
    <?php do_action('woocommerce_edit_account_form_start'); ?>

    <section class="voitkus-account-form__section" aria-labelledby="voitkus-account-profile-title">
        <h2 id="voitkus-account-profile-title" class="voitkus-account-form__title">
            <?php esc_html_e('Dane profilu', 'voitkus'); ?>
        </h2>

        <div class="voitkus-account-form__grid voitkus-account-form__grid--2">
            <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                <label for="account_first_name">
                    <?php esc_html_e('Imię', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" aria-required="true" />
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
                <label for="account_last_name">
                    <?php esc_html_e('Nazwisko', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" aria-required="true" />
            </p>
        </div>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="account_display_name">
                <?php esc_html_e('Wyświetlana nazwa', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
            </label>
            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr($user->display_name); ?>" aria-required="true" />
            <span id="account_display_name_description" class="voitkus-account-form__hint">
                <?php esc_html_e('Tak Twoje imię będzie widoczne w koncie i opiniach.', 'voitkus'); ?>
            </span>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="account_email">
                <?php esc_html_e('Adres e-mail', 'voitkus'); ?>&nbsp;<span class="required" aria-hidden="true">*</span>
            </label>
            <input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" aria-required="true" />
        </p>
    </section>

    <?php do_action('woocommerce_edit_account_form_fields'); ?>

    <section class="voitkus-account-form__section voitkus-account-form__section--password" aria-labelledby="voitkus-account-password-title">
        <h2 id="voitkus-account-password-title" class="voitkus-account-form__title">
            <?php esc_html_e('Zmiana hasła', 'voitkus'); ?>
        </h2>
        <p class="voitkus-account-form__lead">
            <?php esc_html_e('Pozostaw puste, jeśli nie chcesz zmieniać hasła.', 'voitkus'); ?>
        </p>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="password_current"><?php esc_html_e('Aktualne hasło', 'voitkus'); ?></label>
            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="password_1"><?php esc_html_e('Nowe hasło', 'voitkus'); ?></label>
            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="new-password" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="password_2"><?php esc_html_e('Potwierdź nowe hasło', 'voitkus'); ?></label>
            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="new-password" />
        </p>
    </section>

    <?php do_action('woocommerce_edit_account_form'); ?>

    <div class="voitkus-account-form__actions">
        <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
        <button type="submit" class="woocommerce-Button button voitkus-account-form__submit<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" name="save_account_details" value="<?php esc_attr_e('Zapisz zmiany', 'voitkus'); ?>">
            <?php esc_html_e('Zapisz zmiany', 'voitkus'); ?>
        </button>
        <input type="hidden" name="action" value="save_account_details" />
    </div>

    <?php do_action('woocommerce_edit_account_form_end'); ?>
</form>

<?php
do_action('woocommerce_after_edit_account_form');
