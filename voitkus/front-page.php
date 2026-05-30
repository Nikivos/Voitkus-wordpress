<?php
/**
 * Front page template.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

get_template_part('template-parts/home/hero', null, [
    'hero_bag' => voitkus_hero_bag_image(),
]);

get_template_part('template-parts/home/profiles');

get_template_part('template-parts/home/lots', null, [
    'lots' => voitkus_current_lots(3),
]);

get_template_part('template-parts/home/why');

get_footer();
