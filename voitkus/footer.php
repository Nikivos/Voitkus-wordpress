<?php
/**
 * Site footer.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

$footer = voitkus_footer_section();
?>

<footer class="site-footer" aria-label="<?php esc_attr_e('Stopka strony', 'voitkus'); ?>">
    <div class="site-footer__inner">
        <div class="site-footer__top">
            <div class="site-footer__brand">
                <?php if (has_custom_logo()) : ?>
                    <div class="site-footer__logo"><?php the_custom_logo(); ?></div>
                <?php else : ?>
                    <a class="site-footer__wordmark" href="<?php echo esc_url(home_url('/')); ?>">
                        VOITKUS
                    </a>
                <?php endif; ?>

                <?php if ($footer['tagline'] !== '') : ?>
                    <p class="site-footer__tagline"><?php echo esc_html($footer['tagline']); ?></p>
                <?php endif; ?>
            </div>

            <nav class="site-footer__nav" aria-label="<?php esc_attr_e('Linki w stopce', 'voitkus'); ?>">
                <?php foreach ($footer['link_groups'] as $group) : ?>
                    <div class="site-footer__col">
                        <?php if ($group['title'] !== '') : ?>
                            <h2 class="site-footer__col-title"><?php echo esc_html($group['title']); ?></h2>
                        <?php endif; ?>
                        <ul class="site-footer__list">
                            <?php foreach ($group['links'] as $link) : ?>
                                <li>
                                    <a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['label']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php $company = voitkus_company_details(); ?>
        <section class="site-footer__legal" aria-label="<?php esc_attr_e('Dane firmy', 'voitkus'); ?>">
            <h2 class="site-footer__legal-title"><?php esc_html_e('Dane firmy', 'voitkus'); ?></h2>
            <dl class="site-footer__legal-list">
                <div class="site-footer__legal-row">
                    <dt><?php esc_html_e('Nazwa', 'voitkus'); ?></dt>
                    <dd><?php echo esc_html($company['legal_name']); ?></dd>
                </div>
                <div class="site-footer__legal-row">
                    <dt><?php esc_html_e('NIP', 'voitkus'); ?></dt>
                    <dd><?php echo esc_html($company['nip']); ?></dd>
                </div>
                <div class="site-footer__legal-row">
                    <dt><?php esc_html_e('Adres', 'voitkus'); ?></dt>
                    <dd><?php echo esc_html(voitkus_company_address_line()); ?></dd>
                </div>
            </dl>
        </section>

        <div class="site-footer__bottom">
            <div class="site-footer__contact">
                <?php if ($footer['instagram'] !== '') : ?>
                    <a class="site-footer__social" href="<?php echo esc_url($footer['instagram']); ?>" target="_blank" rel="noopener noreferrer">
                        Instagram
                        <span aria-hidden="true">↗</span>
                    </a>
                <?php endif; ?>

                <?php if ($footer['email'] !== '') : ?>
                    <a class="site-footer__email" href="<?php echo esc_url('mailto:' . $footer['email']); ?>">
                        <?php echo esc_html($footer['email']); ?>
                    </a>
                <?php endif; ?>
            </div>

            <p class="site-footer__copy">
                <?php
                printf(
                    /* translators: %1$s: year, %2$s: legal company name */
                    esc_html__('© %1$s %2$s. Wszelkie prawa zastrzeżone.', 'voitkus'),
                    esc_html((string) $footer['year']),
                    esc_html($company['legal_name'])
                );
                ?>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
