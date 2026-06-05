<?php
/**
 * Email Header
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.0.0
 *
 * @var string $email_heading
 * @var bool   $plain_text
 */

defined('ABSPATH') || exit;

$store_name = wp_specialchars_decode(get_option('woocommerce_email_from_name', get_bloginfo('name', 'display')), ENT_QUOTES);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>">
    <title><?php echo esc_html($store_name); ?></title>
</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            <tr>
                <td align="center" valign="top">
                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_header">
                                    <tr>
                                        <td id="header_wrapper">
                                            <p style="margin:0 0 8px;padding:28px 40px 0;font-size:11px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:rgba(255,255,255,0.72);">
                                                VOITKUS
                                            </p>
                                            <h1><?php echo esc_html($email_heading); ?></h1>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_body">
                                    <tr>
                                        <td valign="top" id="body_content">
                                            <div id="body_content_inner">
