<?php
/**
 * Front page — profile cards.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}
?>

<section class="profiles" aria-labelledby="profiles-title">
    <div class="profiles__inner">
        <header class="profiles__header">
            <div class="profiles__header-text">
                <p class="profiles__eyebrow"><?php esc_html_e('Po czym wybieramy kawę', 'voitkus'); ?></p>
                <h2 id="profiles-title"><?php esc_html_e('Dla ludzi, którzy pytają przed pierwszym łykiem', 'voitkus'); ?></h2>
            </div>
            <div class="profiles__swipe-hint" aria-hidden="true">
                <?php esc_html_e('Przesuń', 'voitkus'); ?> <span style="font-size: 14px;">→</span>
            </div>
        </header>

        <div class="profiles__grid">
            <a href="<?php echo esc_url(home_url('/shop/?filter=everyday')); ?>" class="profile-card" data-profile="everyday">
                <div class="profile-card__header">
                    <div class="profile-card__title-group">
                        <span class="profile-card__dot"></span>
                        <h3 class="profile-card__sense"><?php esc_html_e('BALANS', 'voitkus'); ?></h3>
                    </div>
                    <span class="profile-card__arrow">→</span>
                </div>
                <p class="profile-card__feelings"><?php esc_html_e('Czekolada · orzech · czytelność', 'voitkus'); ?></p>
                <p class="profile-card__text">
                    <?php esc_html_e('Czysta, czytelna filiżanka. Widzisz warstwy smaku, nie musisz zgadywać.', 'voitkus'); ?>
                </p>
            </a>

            <a href="<?php echo esc_url(home_url('/shop/?filter=espresso')); ?>" class="profile-card" data-profile="espresso">
                <div class="profile-card__header">
                    <div class="profile-card__title-group">
                        <span class="profile-card__dot"></span>
                        <h3 class="profile-card__sense"><?php esc_html_e('TERROIR', 'voitkus'); ?></h3>
                    </div>
                    <span class="profile-card__arrow">→</span>
                </div>
                <p class="profile-card__feelings"><?php esc_html_e('Słońce · owoc · pochodzenie', 'voitkus'); ?></p>
                <p class="profile-card__text">
                    <?php esc_html_e('Kawa, która smakuje jak miejsce, w którym wyrosła. Rozpoznajesz region w ustach.', 'voitkus'); ?>
                </p>
            </a>

            <a href="<?php echo esc_url(home_url('/shop/?filter=funky')); ?>" class="profile-card" data-profile="funky">
                <div class="profile-card__header">
                    <div class="profile-card__title-group">
                        <span class="profile-card__dot"></span>
                        <h3 class="profile-card__sense"><?php esc_html_e('EKSPRESJA', 'voitkus'); ?></h3>
                    </div>
                    <span class="profile-card__arrow">→</span>
                </div>
                <p class="profile-card__feelings"><?php esc_html_e('Kwiat · jagoda · zaskoczenie', 'voitkus'); ?></p>
                <p class="profile-card__text">
                    <?php esc_html_e('Odważne nuty i nietypowa obróbka. Zatrzymuje Cię i każe sięgnąć po drugi łyk.', 'voitkus'); ?>
                </p>
            </a>

            <a href="<?php echo esc_url(home_url('/shop/?filter=cyan')); ?>" class="profile-card" data-profile="cyan">
                <div class="profile-card__header">
                    <div class="profile-card__title-group">
                        <span class="profile-card__dot"></span>
                        <h3 class="profile-card__sense"><?php esc_html_e('DETAL', 'voitkus'); ?></h3>
                    </div>
                    <span class="profile-card__arrow">→</span>
                </div>
                <p class="profile-card__feelings"><?php esc_html_e('Herbata · cytrus · precyzja', 'voitkus'); ?></p>
                <p class="profile-card__text">
                    <?php esc_html_e('Wysoka kwasowość i herbaciane nuty. Wymaga skupienia, by usłyszeć wszystko.', 'voitkus'); ?>
                </p>
            </a>

            <a href="<?php echo esc_url(home_url('/shop/?filter=limited')); ?>" class="profile-card" data-profile="lime">
                <div class="profile-card__header">
                    <div class="profile-card__title-group">
                        <span class="profile-card__dot"></span>
                        <h3 class="profile-card__sense"><?php esc_html_e('MIKROLOT', 'voitkus'); ?></h3>
                    </div>
                    <span class="profile-card__arrow">→</span>
                </div>
                <p class="profile-card__feelings"><?php esc_html_e('Eksperyment · krótki drop', 'voitkus'); ?></p>
                <p class="profile-card__text">
                    <?php esc_html_e('Mała partia, która zaraz zniknie. Pijesz teraz, bo za miesiąc już jej nie będzie.', 'voitkus'); ?>
                </p>
            </a>
        </div>
    </div>
</section>
