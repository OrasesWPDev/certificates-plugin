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

    <main id="main" class="certificates-plugin-archive">
        <div class="page-wrapper">
            <div class="container">
                <div class="row">
                    <div class="large-12 col">
                        <div class="col-inner">
                            <header class="page-header">
                                <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
                            </header>

                            <div class="certificates-plugin-grid row">
								<?php if ( have_posts() ) : ?>
									<?php while ( have_posts() ) : the_post(); ?>
                                        <div class="certificates-plugin-item col small-12 large-3">
                                            <a href="<?php the_permalink(); ?>" class="certificates-plugin-link">
                                                <div class="certificates-plugin-card">
                                                    <div class="certificates-plugin-card-body">
														<?php if ( has_post_thumbnail() ) : ?>
                                                            <div class="certificates-plugin-image">
																<?php the_post_thumbnail( 'medium', array( 'class' => 'certificates-plugin-thumbnail' ) ); ?>
                                                            </div>
														<?php endif; ?>

                                                        <div class="certificates-plugin-content">
                                                            <h3 class="certificates-plugin-title"><?php the_title(); ?></h3>
                                                            <div class="certificates-plugin-description">
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
                                                            <div class="certificates-plugin-button">
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
                                        <p class="certificates-plugin-none">No certificates found.</p>
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