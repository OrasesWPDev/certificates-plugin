<?php
/**
 * The template for displaying certificate archives
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

    <main id="main" class="certificate-archive">
        <div class="page-wrapper">
            <div class="container">
                <div class="row">
                    <div class="large-12 col">
                        <div class="col-inner">
                            <header class="page-header">
                                <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
                            </header>

                            <div class="certificates-grid row">
                                <?php if ( have_posts() ) : ?>
                                    <?php while ( have_posts() ) : the_post(); ?>
                                        <div class="certificate-item col small-12 large-3">
                                            <a href="<?php the_permalink(); ?>" class="certificate-link">
                                                <div class="certificate-card">
                                                    <div class="certificate-card-body">
                                                        <?php if ( has_post_thumbnail() ) : ?>
                                                            <div class="certificate-image">
                                                                <?php the_post_thumbnail( 'medium', array( 'class' => 'certificate-thumbnail' ) ); ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="certificate-content">
                                                            <h3 class="certificate-title"><?php the_title(); ?></h3>
                                                            <div class="certificate-description">
                                                                <?php
                                                                if (function_exists('get_field')) {
                                                                    $card_description = get_field('card_description');
                                                                    if (!empty($card_description)) {
                                                                        echo wp_kses_post($card_description);
                                                                    } else {
                                                                        the_excerpt();
                                                                    }
                                                                } else {
                                                                    the_excerpt();
                                                                }
                                                                ?>
                                                            </div>
                                                            <div class="certificate-button">
                                                                <span class="button secondary">Learn More</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <div class="col small-12">
                                        <p class="no-certificates">No certificates found.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php the_posts_pagination(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
get_footer();