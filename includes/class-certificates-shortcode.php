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
		add_shortcode( 'certificates', array( $this, 'certificates_shortcode' ) );
		add_shortcode( 'certificate_images', array( $this, 'certificate_images_shortcode' ) );

		// Register the shortcode-specific stylesheet
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
	 * Main shortcode function - handles both single and multiple certificates display
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function certificates_shortcode( $atts ) {
		// Enqueue styles
		wp_enqueue_style( 'certificates-plugin-style' );
		wp_enqueue_style( 'certificates-responsive-style' );
		wp_enqueue_style( 'certificates-shortcode-style' );

		// Shortcode attributes with improved defaults
		$atts = shortcode_atts(
			array(
				'id'           => 0,           // Post ID for single certificate display
				'count'        => -1,          // How many to display. -1 for all.
				'columns'      => 4,           // Number of columns per row
				'category'     => '',          // Filter by category slug
				'order'        => 'ASC',       // ASC or DESC
				'button_text'  => 'Learn More', // Custom button text
				'show_title'   => 'true',      // Whether to show titles
				'show_image'   => 'true',      // Whether to show images
				'desc_length'  => 25,          // Word count for descriptions
				'custom_class' => '',          // Additional custom CSS class
			),
			$atts,
			'certificates'
		);

		// Convert string boolean values to actual booleans
		$atts['show_title'] = $this->string_to_bool($atts['show_title']);
		$atts['show_image'] = $this->string_to_bool($atts['show_image']);

		// Convert desc_length to integer
		$atts['desc_length'] = intval($atts['desc_length']);

		// If ID is provided, display a single certificate
		if (!empty($atts['id']) && $atts['id'] > 0) {
			return $this->render_single_certificate($atts);
		}

		// Otherwise, display certificates grid
		return $this->render_certificates_grid($atts);
	}

	/**
	 * Renders a single certificate
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_single_certificate($atts) {
		// Get the certificate post
		$certificate = get_post($atts['id']);

		// If post doesn't exist or isn't a certificate, return error message
		if (!$certificate || get_post_type($certificate) !== 'certificate') {
			return '<p class="certificates-plugin-error">Certificate not found.</p>';
		}

		// Start output buffering
		ob_start();

		// Prepare certificate data
		$title = get_the_title($certificate);
		$permalink = get_permalink($certificate);
		$description = $this->get_certificate_description($certificate, $atts['desc_length']);

		// Add custom class if provided
		$custom_class = !empty($atts['custom_class']) ? ' ' . esc_attr($atts['custom_class']) : '';

		// Output single certificate
		?>
        <div class="certificates-plugin-single-shortcode<?php echo $custom_class; ?>">
            <div class="certificates-plugin-card">
                <div class="certificates-plugin-card-body">
					<?php if ($atts['show_image'] && has_post_thumbnail($certificate)): ?>
                        <div class="certificates-plugin-image">
							<?php echo get_the_post_thumbnail($certificate, 'medium', array('class' => 'certificates-plugin-thumbnail')); ?>
                        </div>
					<?php endif; ?>
                    <div class="certificates-plugin-content">
						<?php if ($atts['show_title']): ?>
                            <h3 class="certificates-plugin-title"><?php echo esc_html($title); ?></h3>
						<?php endif; ?>
                        <div class="certificates-plugin-description">
							<?php echo wp_kses_post($description); ?>
                        </div>
                        <div class="certificates-plugin-button">
                            <a href="<?php echo esc_url($permalink); ?>" class="button secondary">
								<?php echo esc_html($atts['button_text']); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<?php

		// Get buffer contents and clean buffer
		return ob_get_clean();
	}

	/**
	 * Renders certificates in a grid layout
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	private function render_certificates_grid($atts) {
		// Get certificates
		$certificates = $this->get_certificates($atts);

		// Start output buffering
		ob_start();

		// Add custom class if provided
		$custom_class = !empty($atts['custom_class']) ? ' ' . esc_attr($atts['custom_class']) : '';

		// Check if any certificates exist
		if ($certificates && $certificates->have_posts()) {
			// Output grid container
			echo '<div class="certificates-plugin-grid-container' . $custom_class . '">';
			echo '<div class="certificates-plugin-grid row">';

			while ($certificates->have_posts()) {
				$certificates->the_post();

				// Get certificate data
				$title = get_the_title();
				$permalink = get_permalink();
				$description = $this->get_certificate_description(get_the_ID(), $atts['desc_length']);

				// Calculate column classes based on Flatsome's grid system
				$column_class = 'small-12 large-' . (12 / intval($atts['columns']));

				// Output certificate item
				?>
                <div class="certificates-plugin-item col <?php echo esc_attr($column_class); ?>">
                    <a href="<?php echo esc_url($permalink); ?>" class="certificates-plugin-link">
                        <div class="certificates-plugin-card">
                            <div class="certificates-plugin-card-body">
								<?php if ($atts['show_image'] && has_post_thumbnail()): ?>
                                    <div class="certificates-plugin-image">
										<?php the_post_thumbnail('medium', array('class' => 'certificates-plugin-thumbnail')); ?>
                                    </div>
								<?php endif; ?>
                                <div class="certificates-plugin-content">
									<?php if ($atts['show_title']): ?>
                                        <h3 class="certificates-plugin-title"><?php echo esc_html($title); ?></h3>
									<?php endif; ?>
                                    <div class="certificates-plugin-description">
										<?php echo wp_kses_post($description); ?>
                                    </div>
                                    <div class="certificates-plugin-button">
                                        <span class="button secondary"><?php echo esc_html($atts['button_text']); ?></span>
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
		return ob_get_clean();
	}

	/**
	 * Get certificate description
	 *
	 * @param int $post_id Certificate post ID
	 * @param int $word_count Maximum word count
	 * @return string Formatted description
	 */
	private function get_certificate_description($post_id, $word_count = 25) {
		$description = '';

		// Try to get card description from ACF
		if (function_exists('get_field')) {
			$card_description = get_field('card_description', $post_id);
			if (!empty($card_description)) {
				$description = wp_kses_post($card_description);
			} else {
				// Fall back to intro field if card description is empty
				$intro_field = get_field('intro', $post_id);
				if (!empty($intro_field)) {
					$description = wp_strip_all_tags($intro_field);
					$description = wp_trim_words($description, $word_count);
				}
			}
		}

		// If no ACF fields available, fall back to excerpt or content
		if (empty($description)) {
			$post = get_post($post_id);
			if (has_excerpt($post_id)) {
				$description = get_the_excerpt($post_id);
			} else {
				$description = wp_trim_words($post->post_content, $word_count);
			}
		}

		return $description;
	}

	/**
	 * Shortcode to display certificate featured images in a grid
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function certificate_images_shortcode( $atts ) {
		// Enqueue styles
		wp_enqueue_style( 'certificates-plugin-style' );
		wp_enqueue_style( 'certificates-responsive-style' );
		wp_enqueue_style( 'certificates-shortcode-style' );

		// Default attributes
		$atts = shortcode_atts(
			array(
				'cache' => 'true',  // Whether to cache results
			),
			$atts,
			'certificate_images'
		);

		// Convert string boolean to actual boolean
		$cache = filter_var($atts['cache'], FILTER_VALIDATE_BOOLEAN);

		// Start output buffering
		ob_start();

		// Get cached output if caching is enabled
		$cache_key = 'certificate_images_' . md5(serialize($atts));
		$cached_output = $cache ? get_transient($cache_key) : false;
		if ($cached_output !== false) {
			echo $cached_output;
			return ob_get_clean();
		}

		// Query all certificate posts ordered by meta value for display order
		$certificates = new WP_Query( array(
			'post_type'      => 'certificate',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_certificate_display_order',
			'order'          => 'ASC',
		) );

		if ( $certificates->have_posts() ) {
			// Modified HTML structure with clearer class names
			echo '<div class="certificates-images-container">';
			echo '<div class="certificates-images-row">'; // This is the flex container

			$counter = 0;
			while ( $certificates->have_posts() ) {
				$certificates->the_post();

				if ( has_post_thumbnail() ) {
					// Only create a new row after every 4 items (not before the first item)
					if ( $counter > 0 && $counter % 4 === 0 ) {
						echo '</div><div class="certificates-images-row">';
					}

					// Each item is explicitly 25% width
					echo '<div class="certificates-image-item">';
					echo '<a href="' . esc_url( get_permalink() ) . '">';
					the_post_thumbnail( 'medium', array( 'class' => 'certificates-image-thumbnail' ) );
					echo '</a>';
					echo '</div>';

					$counter++;
				}
			}

			echo '</div>'; // Close the last row
			echo '</div>'; // Close the container

			wp_reset_postdata();
		} else {
			echo '<p class="certificates-no-results">No certificate images found.</p>';
		}

		// Get buffer contents
		$output = ob_get_clean();

		// Cache the output if caching is enabled
		if ($cache) {
			set_transient($cache_key, $output, HOUR_IN_SECONDS);
		}

		return $output;
	}

	/**
	 * Get certificates query
	 *
	 * @param array $atts Query parameters.
	 * @return WP_Query Certificates query.
	 */
	private function get_certificates($atts) {
		// Query arguments
		$args = array(
			'post_type'      => 'certificate',
			'posts_per_page' => $atts['count'],
			'order'          => $atts['order'],
			'orderby'        => 'meta_value_num',
			'meta_key'       => '_certificate_display_order',
		);

		// Add category filter if specified
		if (!empty($atts['category'])) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => explode(',', $atts['category']),
				),
			);
		}

		// Create and return query
		return new WP_Query($args);
	}

	/**
	 * Convert string boolean values to actual booleans
	 *
	 * @param string|boolean $value The value to convert
	 * @return boolean The converted value
	 */
	private function string_to_bool($value) {
		if (is_bool($value)) {
			return $value;
		}

		return ($value === 'true' || $value === '1' || $value === 'yes' || $value === 'on');
	}
}