<?php
/**
 * Email Styles
 *
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

defined('ABSPATH') || exit;

$bg        = '#f1eee8';
$body      = '#ffffff';
$base      = '#0a0a0a';
$muted     = '#8a8a8a';
$border    = '#e8e8e8';
$text      = '#1a1a1a';
$font      = "'Helvetica Neue', Helvetica, Arial, sans-serif";
?>
<style type="text/css">
body {
	padding: 0;
	margin: 0;
	background-color: <?php echo esc_attr($bg); ?>;
	font-family: <?php echo $font; ?>;
}

#wrapper {
	background-color: <?php echo esc_attr($bg); ?>;
	padding: 40px 16px;
	width: 100%;
}

#template_container {
	background-color: <?php echo esc_attr($body); ?>;
	border: 1px solid <?php echo esc_attr($border); ?>;
	border-radius: 16px;
	overflow: hidden;
}

#template_header {
	background-color: <?php echo esc_attr($base); ?>;
	color: #ffffff;
	border-radius: 0;
}

#template_header h1 {
	color: #ffffff;
	font-family: <?php echo $font; ?>;
	font-size: 22px;
	font-weight: 700;
	line-height: 1.3;
	margin: 0;
	padding: 28px 40px;
	text-align: left;
}

#body_content {
	background-color: <?php echo esc_attr($body); ?>;
}

#body_content_inner {
	padding: 32px 40px 24px;
	color: <?php echo esc_attr($text); ?>;
	font-family: <?php echo $font; ?>;
	font-size: 15px;
	line-height: 1.6;
}

#template_footer {
	background-color: <?php echo esc_attr($body); ?>;
	border-top: 1px solid <?php echo esc_attr($border); ?>;
}

#template_footer td {
	padding: 24px 40px 32px;
}

#template_footer #credit {
	border: 0;
	color: <?php echo esc_attr($muted); ?>;
	font-family: <?php echo $font; ?>;
	font-size: 12px;
	line-height: 1.55;
	text-align: left;
}

h2, h3 {
	color: <?php echo esc_attr($base); ?>;
	font-family: <?php echo $font; ?>;
	font-size: 18px;
	font-weight: 700;
	line-height: 1.35;
	margin: 24px 0 12px;
}

p {
	margin: 0 0 16px;
}

a {
	color: <?php echo esc_attr($base); ?>;
	font-weight: 600;
}

td, th {
	font-family: <?php echo $font; ?>;
}

.td {
	border: 1px solid <?php echo esc_attr($border); ?>;
	vertical-align: middle;
}

.address {
	font-style: normal;
	padding: 16px;
	border: 1px solid <?php echo esc_attr($border); ?>;
	border-radius: 12px;
}

table.shop_table {
	width: 100%;
	border-collapse: separate;
	border-spacing: 0;
	border: 1px solid <?php echo esc_attr($border); ?>;
	border-radius: 12px;
	overflow: hidden;
	margin: 0 0 24px;
}

table.shop_table th,
table.shop_table td {
	padding: 12px 16px;
	border-bottom: 1px solid <?php echo esc_attr($border); ?>;
}

table.shop_table tr:last-child td,
table.shop_table tr:last-child th {
	border-bottom: none;
}

.order_item td {
	font-size: 14px;
}

@media only screen and (max-width: 620px) {
	#template_header h1,
	#body_content_inner,
	#template_footer td {
		padding-left: 20px !important;
		padding-right: 20px !important;
	}
}
</style>
