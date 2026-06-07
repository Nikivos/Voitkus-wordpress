<?php
/**
 * My Account page layout.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

$is_logged_in = is_user_logged_in();
?>

<div class="voitkus-account-layout<?php echo $is_logged_in ? '' : ' voitkus-account-layout--guest voitkus-account-layout--auth'; ?>">
    <?php if ($is_logged_in) : ?>
        <aside class="voitkus-account-layout__sidebar">
            <?php do_action('woocommerce_account_navigation'); ?>
        </aside>
    <?php endif; ?>

    <div class="voitkus-account-layout__main">
        <?php if ($is_logged_in) : ?>
            <div class="voitkus-account-panel">
                <?php do_action('woocommerce_account_content'); ?>
            </div>
        <?php else : ?>
            <?php do_action('woocommerce_account_content'); ?>
        <?php endif; ?>
    </div>
</div>
