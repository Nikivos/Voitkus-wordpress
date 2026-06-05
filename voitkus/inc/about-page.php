<?php
/**
 * Strona O nas.
 *
 * @package Voitkus
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return array<int, array{title: string, text: string}>
 */
function voitkus_about_principles(): array
{
    return [
        [
            'title' => __('Pochodzenie na wierzchu', 'voitkus'),
            'text'  => __('Region, proces, profil smakowy — podajemy wprost, na stronie produktu i etykiecie. Bez ogólników.', 'voitkus'),
        ],
        [
            'title' => __('Smak zgodny z opisem', 'voitkus'),
            'text'  => __('Jeśli piszemy cytrusy i herbata, filiżanka ma brzmieć podobnie. Nie upiększamy profilu pod marketing.', 'voitkus'),
        ],
        [
            'title' => __('Kontrola każdej partii', 'voitkus'),
            'text'  => __('Notujemy parametry palenia i sprawdzamy powtarzalność między partiami tego samego lotu.', 'voitkus'),
        ],
    ];
}

function voitkus_render_about_page(): string
{
    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');
    $image = function_exists('voitkus_founder_image') ? voitkus_founder_image() : ['has_image' => false, 'url' => '', 'width' => 0, 'height' => 0];
    $founder = function_exists('voitkus_founder_section') ? voitkus_founder_section() : ['name' => 'Mikita Voitkus', 'role' => '', 'quote' => ''];

    ob_start();
    ?>
    <div class="about-page__content">
        <header class="about-hero">
            <p class="about-hero__eyebrow"><?php esc_html_e('Palarnia specialty · Warszawa', 'voitkus'); ?></p>
            <p class="about-hero__title"><?php esc_html_e('Małe partie. Konkretny smak. Bez nadęcia.', 'voitkus'); ?></p>
            <p class="about-hero__lead">
                <?php esc_html_e('VOITKUS to palarnia założona przez Mikita Voitkusa. Palimy niewielkie partie kawy specialty — każdy lot osobno, z testami parzenia zanim trafi do sklepu. Nie sprzedajemy obietnic. Sprzedajemy ziarno, które da się obronić w filiżance.', 'voitkus'); ?>
            </p>
        </header>

        <section class="about-section about-story" aria-labelledby="about-story-title">
            <div class="about-story__layout">
                <div class="about-story__text">
                    <h2 id="about-story-title" class="about-section__title"><?php esc_html_e('Skąd to się wzięło', 'voitkus'); ?></h2>
                    <p>
                        <?php esc_html_e('Moja droga w kawie zaczęła się w Japonii. Tam po raz pierwszy zobaczyłem, że kawa może być czysta, precyzyjna i przemyślana — efekt pracy z detalem, a nie przypadku. To nie był „kolejny napój”, tylko proces, który widać w smaku.', 'voitkus'); ?>
                    </p>
                    <p>
                        <?php esc_html_e('Po powrocie do Polski poszedłem w głąb branży: specialty kawiarnie, szkolenie baristy, praca za barem, konkursy parzenia. Z czasem zainteresowała mnie cała droga ziarna — od farmy do filiżanki. Najbardziej przekonało mnie palenie: to moment, w którym smak naprawdę się formuje.', 'voitkus'); ?>
                    </p>
                    <p>
                        <?php esc_html_e('Palenie to dla mnie rzemiosło i interpretacja ziarna jednocześnie. Każdy roaster wychodzi z tego samego worka z innym wynikiem — i właśnie dlatego liczy się kontrola, notatki i powtarzalność, a nie hasła na opakowaniu.', 'voitkus'); ?>
                    </p>
                    <p>
                        <?php esc_html_e('Voitkus powstał z tego doświadczenia: chciałem palić kawę, w której słychać pochodzenie i pracę palarni — bez udawania, że każda paczka to „niesamowita podróż smaków”.', 'voitkus'); ?>
                    </p>
                </div>

                <figure class="about-story__photo">
                    <?php if ($image['has_image']) : ?>
                        <img
                            class="about-story__img"
                            src="<?php echo esc_url($image['url']); ?>"
                            alt="<?php echo esc_attr($founder['name']); ?>"
                            width="<?php echo esc_attr((string) $image['width']); ?>"
                            height="<?php echo esc_attr((string) $image['height']); ?>"
                            loading="lazy"
                            decoding="async"
                        >
                    <?php else : ?>
                        <div class="about-story__placeholder" role="img" aria-label="<?php echo esc_attr($founder['name']); ?>">
                            <span class="about-story__placeholder-mark" aria-hidden="true">MV</span>
                        </div>
                    <?php endif; ?>
                    <figcaption class="about-story__caption">
                        <?php echo esc_html($founder['name']); ?>
                        <?php if (! empty($founder['role'])) : ?>
                            · <?php echo esc_html($founder['role']); ?>
                        <?php endif; ?>
                    </figcaption>
                </figure>
            </div>
        </section>

        <section class="about-section about-practice" aria-labelledby="about-practice-title">
            <h2 id="about-practice-title" class="about-section__title"><?php esc_html_e('Co robimy w palarni', 'voitkus'); ?></h2>
            <ul class="about-list about-list--check">
                <li><?php esc_html_e('Wybieramy ziarno, którego pochodzenie da się obronić w filiżance — nie pod „ładną nazwę”.', 'voitkus'); ?></li>
                <li><?php esc_html_e('Profil palenia budujemy na cuppingu i testach ekstrakcji, nie na zgadywaniu.', 'voitkus'); ?></li>
                <li><?php esc_html_e('Partia trafia do sklepu dopiero wtedy, gdy smakuje tak, jak ją opisujemy.', 'voitkus'); ?></li>
                <li><?php esc_html_e('Mała skala to dla nas plus: wiemy, co weszło do bębna, kiedy wyszło i co dokładnie pakujemy.', 'voitkus'); ?></li>
            </ul>
        </section>

        <section class="about-section about-values" aria-labelledby="about-principles-title">
            <h2 id="about-principles-title" class="about-section__title"><?php esc_html_e('Na czym nam zależy', 'voitkus'); ?></h2>
            <div class="about-values__grid about-values__grid--three">
                <?php foreach (voitkus_about_principles() as $principle) : ?>
                    <article class="about-value-card">
                        <h3 class="about-value-card__title"><?php echo esc_html($principle['title']); ?></h3>
                        <p class="about-value-card__text"><?php echo esc_html($principle['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <blockquote class="about-manifesto">
            <p class="about-manifesto__text">
                <?php
                $quote = $founder['quote'] !== ''
                    ? $founder['quote']
                    : __('Palimy tak, żeby w filiżance było widać pochodzenie — czysty, czytelny profil.', 'voitkus');
                echo esc_html($quote);
                ?>
            </p>
            <cite class="about-manifesto__cite"><?php echo esc_html($founder['name']); ?></cite>
        </blockquote>

        <section class="about-page__cta">
            <h2 class="about-page__cta-title"><?php esc_html_e('Zobacz, co teraz w palarni', 'voitkus'); ?></h2>
            <p class="about-page__cta-text">
                <?php esc_html_e('Aktualne loty z opisem smaku, pochodzeniem i rekomendacją parzenia.', 'voitkus'); ?>
            </p>
            <a class="about-page__btn" href="<?php echo esc_url($shop_url); ?>">
                <?php esc_html_e('Przejdź do sklepu', 'voitkus'); ?>
            </a>
        </section>
    </div>
    <?php

    return (string) ob_get_clean();
}

function voitkus_about_shortcode(): string
{
    return voitkus_render_about_page();
}
