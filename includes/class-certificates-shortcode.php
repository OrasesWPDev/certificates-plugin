<?php
/**
 * Shortcode for displaying certificates
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle certificate shortcodes.
 */
class Certificates_Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Register shortcodes
		add_shortcode( 'certificates', array( $this, 'certificates_grid_shortcode' ) );

		// Register the new shortcode-specific stylesheet
		add_action( 'wp_enqueue_scripts', array( $this, 'register_shortcode_styles' ) );

		// Add logging for debugging
		if ( WP_DEBUG ) {
			error_log( 'Certificates_Shortcode initialized' );
		}
	}

	/**
	 * Register shortcode-specific stylesheet
	 */
	public function register_shortcode_styles() {
		wp_register_style(
			'certificates-shortcode-style',
			CERTIFICATES_PLUGIN_URL . 'assets/css/certificates-shortcode.css',
			array(),
			CERTIFICATES_PLUGIN_VERSION
		);
	}

	/**
	 * Shortcode to display certificates in a grid layout
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function certificates_grid_shortcode( $atts ) {
		// Enqueue styles
		wp_enqueue_style( 'certificates-plugin-style' );
		wp_enqueue_style( 'certificates-responsive-style' );
		wp_enqueue_style( 'certificates-shortcode-style' );

		// Shortcode attributes
		$atts = shortcode_atts(
			array(
				'count'    => -1,         // How many to display. -1 for all.
				'columns'  => 4,          // Number of columns per row
				'category' => '',         // Filter by category slug
				'order'    => 'ASC',      // ASC or DESC
			),
			$atts,
			'certificates'
		);

		// Start output buffering
		ob_start();

		// Get certificates
		$certificates = $this->get_certificates( $atts );

		// Check if any certificates exist
		if ( $certificates && $certificates->have_posts() ) {
			// Output grid container
			echo '<div class="row justify-content-start mt-15 mb-5">';
			echo '<div class="certificates-plugin-grid row">';

			while ( $certificates->have_posts() ) {
				$certificates->the_post();

				// Get certificate data
				$title = get_the_title();
				$permalink = get_permalink();

				// Get card description from ACF field if available
				$description = '';
				if (function_exists('get_field')) {
					$card_description = get_field('card_description');
					if (!empty($card_description)) {
						$description = wp_kses_post($card_description);
					}
					else {
						// Fall back to intro field if card description is empty
						$intro_field = get_field('intro');
						if (!empty($intro_field)) {
							$description = wp_strip_all_tags($intro_field);
							$description = wp_trim_words($description, 25);
						}
					}
				}

				// If no ACF fields available, fall back to excerpt or content
				if (empty($description)) {
					$description = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20);
				}

				// Calculate column classes based on Flatsome's grid system
				$column_class = 'small-12 large-' . (12 / intval($atts['columns']));

				// Output certificate item
				?>
                <div class="certificates-plugin-item col <?php echo esc_attr($column_class); ?>">
                    <a href="<?php echo esc_url($permalink); ?>" class="certificates-plugin-link">
                        <div class="certificates-plugin-card">
                            <div class="certificates-plugin-card-body">
								<?php if (has_post_thumbnail()) : ?>
                                    <div class="certificates-plugin-image">
										<?php the_post_thumbnail('medium', array('class' => 'certificates-plugin-thumbnail rounded mx-auto d-block')); ?>
                                    </div>
								<?php endif; ?>

                                <div class="certificates-plugin-content">
                                    <h3 class="certificates-plugin-title"><?php echo esc_html($title); ?></h3>
                                    <div class="certificates-plugin-description">
										<?php echo wp_kses_post($description); ?>
                                    </div>
                                    <div class="certificates-plugin-button">
                                        <span class="button secondary">Learn More</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
				<?php
			}

			// Close grid containers
			echo '</div>';
			echo '</div>';

			// Reset post data
			wp_reset_postdata();

		} else {
			// No certificates found
			echo '<p class="certificates-plugin-none">No certificates found.</p>';
		}

		// Get buffer contents and clean buffer
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Get certificates query
	 *
	 * @param array $atts Query parameters.
	 * @return WP_Query Certificates query.
	 */
	private function get_certificates( $atts ) {
		// Query arguments
		$args = array(
			'post_type'      => 'certificate',
			'posts_per_page' => $atts['count'],
			'order'          => $atts['order'],
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_certificate_display_order',
		);

		// Add category filter if specified
		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => explode( ',', $atts['category'] ),
				),
			);
		}

		// Create and return query
		return new WP_Query( $args );
	}
}