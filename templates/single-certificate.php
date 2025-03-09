<?php
/**
 * The template for displaying single certificate posts
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the Flatsome theme header
get_header();

// Start the main content
?>
    <main id="main" class="certificates-plugin-single">
        <div class="certificates-plugin-section-wrapper certificates-plugin-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
						<?php echo do_shortcode('[block id="certificates-header"]'); ?>
                    </div>
                </div>
            </div>
        </div>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php
			// Get field values
			$overview = get_field('overview');
			$intro = get_field('intro');
			$prepare_apply = get_field('prepare_apply');
			$earn_certificate = get_field('earn_certificate');
			$next_steps = get_field('next_steps');
			$documents = get_field('documents');
			// Get button URL
			$apply_button_url = get_field('apply_button_url') ?: '#';
			?>

            <div class="certificates-plugin-container">
                <!-- Sticky Menu Navigation -->
                <div class="certificates-plugin-sticky-top" id="sticky-menu">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <section class="certificates-plugin-sections-links">
                                    <nav>
                                        <ul class="list-inline flex-container certificates-plugin-sticky-menu">
											<?php if ($intro) : ?>
                                                <li class="list-inline-item">
                                                    <a href="#intro">INTRO</a>
                                                </li>
											<?php endif; ?>
											<?php if ($prepare_apply) : ?>
                                                <li class="list-inline-item">
                                                    <a href="#prepare-apply">PREPARE &amp; APPLY</a>
                                                </li>
											<?php endif; ?>
											<?php if ($earn_certificate) : ?>
                                                <li class="list-inline-item">
                                                    <a href="#earn-certificate">EARN YOUR CERTIFICATE</a>
                                                </li>
											<?php endif; ?>
											<?php if ($next_steps) : ?>
                                                <li class="list-inline-item">
                                                    <a href="#next-steps">NEXT STEPS</a>
                                                </li>
											<?php endif; ?>
											<?php if ($documents) : ?>
                                                <li class="list-inline-item">
                                                    <a href="#documents">DOCUMENTS</a>
                                                </li>
											<?php endif; ?>
                                        </ul>
                                    </nav>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overview Section -->
                <div id="overview" class="certificates-plugin-section-wrapper">
                    <div class="container pb-3">
                        <div class="row pt-0">
                            <div class="col-12">
                                <div class="pt-2">
									<?php if ($overview) : ?>
										<?php echo $overview; ?>
									<?php else : ?>
                                        <p><?php echo get_the_excerpt(); ?></p>
									<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Intro Section -->
				<?php if ($intro) : ?>
                    <div id="intro" class="certificates-plugin-section-wrapper alternate-background-lightgrey">
                        <div class="container pb-5 pt-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="certificates-plugin-section-content">
                                        <section class="pt-5">
                                            <h3 class="certificates-plugin-text-uppercase">INTRO</h3>
                                            <span class="certificates-plugin-back-to-top">
                                                <a href="#sticky-menu">Back to Top</a>
                                            </span>
                                            <div class="row">
                                                <div class="col-md-9">
                                                    <div class="certificates-plugin-field-content">
														<?php echo $intro; ?>
                                                        <!-- Action Buttons -->
                                                        <div class="certificates-plugin-action-buttons">
                                                            <a href="<?php echo esc_url($apply_button_url); ?>" class="button primary">APPLY NOW</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
													<?php if (has_post_thumbnail()) : ?>
                                                        <div class="certificates-plugin-featured-image">
															<?php
															$id = get_the_ID();
															echo get_the_post_thumbnail($id, 'medium', array(
																'class' => 'certificates-plugin-thumbnail',
																'loading' => 'lazy'
															));
															?>
                                                        </div>
													<?php endif; ?>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>

                <!-- Prepare & Apply Section -->
				<?php if ($prepare_apply) : ?>
                    <div id="prepare-apply" class="certificates-plugin-section-wrapper alternate-background-white">
                        <div class="container pb-5 pt-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="certificates-plugin-section-content">
                                        <section class="pt-5">
                                            <h3 class="certificates-plugin-text-uppercase">PREPARE &amp; APPLY</h3>
                                            <span class="certificates-plugin-back-to-top">
                                                <a href="#sticky-menu">Back to Top</a>
                                            </span>
                                            <div class="certificates-plugin-field-content">
												<?php echo $prepare_apply; ?>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>

                <!-- Earn Your Certificate Section -->
				<?php if ($earn_certificate) : ?>
                    <div id="earn-certificate" class="certificates-plugin-section-wrapper alternate-background-lightgrey">
                        <div class="container pb-5 pt-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="certificates-plugin-section-content">
                                        <section class="pt-5">
                                            <h3 class="certificates-plugin-text-uppercase">EARN YOUR CERTIFICATE</h3>
                                            <span class="certificates-plugin-back-to-top">
                                                <a href="#sticky-menu">Back to Top</a>
                                            </span>
                                            <div class="certificates-plugin-field-content">
												<?php echo $earn_certificate; ?>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>

                <!-- Next Steps Section -->
				<?php if ($next_steps) : ?>
                    <div id="next-steps" class="certificates-plugin-section-wrapper alternate-background-white">
                        <div class="container pb-5 pt-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="certificates-plugin-section-content">
                                        <section class="pt-5">
                                            <h3 class="certificates-plugin-text-uppercase">NEXT STEPS</h3>
                                            <span class="certificates-plugin-back-to-top">
                                                <a href="#sticky-menu">Back to Top</a>
                                            </span>
                                            <div class="certificates-plugin-field-content">
												<?php echo $next_steps; ?>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>

                <!-- Documents Section -->
				<?php if ($documents) : ?>
                    <div id="documents" class="certificates-plugin-section-wrapper alternate-background-lightgrey">
                        <div class="container pb-5 pt-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="certificates-plugin-section-content">
                                        <section class="pt-5">
                                            <h3 class="certificates-plugin-text-uppercase">DOCUMENTS</h3>
                                            <span class="certificates-plugin-back-to-top">
                                                <a href="#sticky-menu">Back to Top</a>
                                            </span>
                                            <div class="certificates-plugin-field-content">
												<?php echo $documents; ?>
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				<?php endif; ?>
            </div><!-- .certificates-plugin-container -->

		<?php endwhile; ?>
    </main>

<?php
// Get the Flatsome theme footer
get_footer();