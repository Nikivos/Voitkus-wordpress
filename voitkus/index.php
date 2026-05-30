<?php
/**
 * Default template.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main class="hero">
    <section>
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : ?>
                <?php the_post(); ?>
                <h1><?php the_title(); ?></h1>
                <div><?php the_content(); ?></div>
            <?php endwhile; ?>
        <?php else : ?>
            <h1><?php esc_html_e('Voitkus Coffee', 'voitkus'); ?></h1>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
