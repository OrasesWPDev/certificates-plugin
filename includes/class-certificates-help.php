<?php
/**
 * Certificates Plugin Help Documentation
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle help documentation for the Certificates Plugin.
 */
class Certificates_Help {
	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add help pages to admin menu
		add_action( 'admin_menu', array( $this, 'add_help_pages' ), 20 );

		// Add scripts and styles for the help page
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register help pages in admin menu.
	 */
	public function add_help_pages() {
		// Main documentation page
		add_submenu_page(
			'edit.php?post_type=certificate',  // Parent menu slug
			'Certificates Help',               // Page title
			'How to Use',                      // Menu title
			'edit_posts',                      // Capability
			'certificates-help',               // Menu slug
			array( $this, 'render_help_page' ) // Callback function
		);
	}

	/**
	 * Enqueue scripts and styles for the help page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our help page
		if ( 'certificate_page_certificates-help' !== $hook ) {
			return;
		}

		// Admin styles for help page
		wp_enqueue_style(
			'certificates-admin-style',
			CERTIFICATES_PLUGIN_URL . 'assets/css/admin-style.css',
			array(),
			CERTIFICATES_PLUGIN_VERSION
		);

		// Tab functionality for help page
		wp_enqueue_script(
			'certificates-admin-script',
			CERTIFICATES_PLUGIN_URL . 'assets/js/admin-script.js',
			array( 'jquery' ),
			CERTIFICATES_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Render the help page content with tabs.
	 */
	public function render_help_page() {
		// Get the active tab, default to 'shortcodes'
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'shortcodes';
		?>
        <div class="wrap certificates-plugin-help-page">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <h2 class="nav-tab-wrapper">
                <a href="?post_type=certificate&page=certificates-help&tab=shortcodes"
                   class="nav-tab <?php echo $active_tab === 'shortcodes' ? 'nav-tab-active' : ''; ?>">
                    Shortcodes
                </a>
                <a href="?post_type=certificate&page=certificates-help&tab=custom-fields"
                   class="nav-tab <?php echo $active_tab === 'custom-fields' ? 'nav-tab-active' : ''; ?>">
                    Custom Fields
                </a>
                <a href="?post_type=certificate&page=certificates-help&tab=templates"
                   class="nav-tab <?php echo $active_tab === 'templates' ? 'nav-tab-active' : ''; ?>">
                    Templates
                </a>
                <a href="?post_type=certificate&page=certificates-help&tab=faq"
                   class="nav-tab <?php echo $active_tab === 'faq' ? 'nav-tab-active' : ''; ?>">
                    FAQ
                </a>
            </h2>

            <div class="tab-content">
				<?php
				// Display content based on the active tab
				switch ( $active_tab ) {
					case 'shortcodes':
						$this->render_shortcodes_tab();
						break;
					case 'custom-fields':
						$this->render_custom_fields_tab();
						break;
					case 'templates':
						$this->render_templates_tab();
						break;
					case 'faq':
						$this->render_faq_tab();
						break;
					default:
						$this->render_shortcodes_tab();
						break;
				}
				?>
            </div>
        </div>
		<?php
	}

	/**
	 * Render the shortcodes documentation tab.
	 */
	public function render_shortcodes_tab() {
		?>
        <div class="certificates-plugin-help-section">
            <h2>Shortcode: [certificates]</h2>
            <p>This shortcode displays a grid of certificates with various customization options.</p>

            <h3>Basic Usage</h3>
            <div class="certificates-plugin-code-block">
                <code>[certificates]</code>
            </div>

            <h3>Display Options</h3>
            <table class="certificates-plugin-help-table">
                <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Description</th>
                    <th>Default</th>
                    <th>Options</th>
                    <th>Examples</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><code>id</code></td>
                    <td>Display a specific certificate by ID</td>
                    <td>0</td>
                    <td>Any valid certificate post ID</td>
                    <td><code>id="123"</code></td>
                </tr>
                <tr>
                    <td><code>count</code></td>
                    <td>Number of certificates to display</td>
                    <td>-1</td>
                    <td>Any number, -1 for all</td>
                    <td><code>count="6"</code><br><code>count="-1"</code></td>
                </tr>
                <tr>
                    <td><code>columns</code></td>
                    <td>Number of columns in grid layout</td>
                    <td>4</td>
                    <td>Any number (1-6 recommended)</td>
                    <td><code>columns="3"</code></td>
                </tr>
                <tr>
                    <td><code>show_title</code></td>
                    <td>Whether to display certificate titles</td>
                    <td>true</td>
                    <td>true, false</td>
                    <td><code>show_title="false"</code></td>
                </tr>
                <tr>
                    <td><code>show_image</code></td>
                    <td>Whether to display certificate images</td>
                    <td>true</td>
                    <td>true, false</td>
                    <td><code>show_image="false"</code></td>
                </tr>
                <tr>
                    <td><code>desc_length</code></td>
                    <td>Number of words to show in description</td>
                    <td>25</td>
                    <td>Any positive number</td>
                    <td><code>desc_length="15"</code></td>
                </tr>
                <tr>
                    <td><code>button_text</code></td>
                    <td>Text to display on the button</td>
                    <td>Learn More</td>
                    <td>Any text</td>
                    <td><code>button_text="View Details"</code></td>
                </tr>
                <tr>
                    <td><code>custom_class</code></td>
                    <td>Additional CSS class for styling</td>
                    <td></td>
                    <td>Any valid CSS class name</td>
                    <td><code>custom_class="featured-certificates"</code></td>
                </tr>
                </tbody>
            </table>

            <h3>Ordering Parameters</h3>
            <table class="certificates-plugin-help-table">
                <thead>
                <tr>
                    <th>Parameter</th>
                    <th>Description</th>
                    <th>Default</th>
                    <th>Options</th>
                    <th>Examples</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><code>order</code></td>
                    <td>Sort order direction</td>
                    <td>ASC</td>
                    <td>ASC, DESC</td>
                    <td><code>order="DESC"</code></td>
                </tr>
                <tr>
                    <td><code>category</code></td>
                    <td>Filter by category slug(s)</td>
                    <td></td>
                    <td>Any valid category slug(s)</td>
                    <td><code>category="featured"</code><br><code>category="featured,popular"</code></td>
                </tr>
                </tbody>
            </table>

            <h3>Examples</h3>

            <h4>Display a Single Certificate</h4>
            <div class="certificates-plugin-code-block">
                <code>[certificates id="123"]</code>
            </div>
            <p>This displays a single certificate with ID 123.</p>

            <h4>Display Featured Certificates</h4>
            <div class="certificates-plugin-code-block">
                <code>[certificates category="featured" count="6" order="DESC"]</code>
            </div>
            <p>This displays up to 6 certificates from the "featured" category in descending order.</p>

            <h4>Custom Display Options</h4>
            <div class="certificates-plugin-code-block">
                <code>[certificates columns="3" button_text="View Certificate" desc_length="10" custom_class="homepage-certificates"]</code>
            </div>
            <p>This displays certificates in a 3-column grid with custom button text, shorter descriptions, and a custom CSS class.</p>

            <h4>Hide Titles and Show Only Images</h4>
            <div class="certificates-plugin-code-block">
                <code>[certificates show_title="false" desc_length="0"]</code>
            </div>
            <p>This displays certificates without titles and descriptions, showing only the images and buttons.</p>
        </div>
		<?php
	}

	/**
	 * Render the custom fields documentation tab.
	 */
	public function render_custom_fields_tab() {
		?>
        <div class="certificates-plugin-help-section">
            <h2>Certificate Custom Fields</h2>
            <p>Certificates Plugin uses Advanced Custom Fields to provide a structured way to enter certificate data.</p>

            <div class="certificates-plugin-card">
                <h3>Available Custom Fields</h3>
                <p>Each certificate post type includes the following custom fields:</p>

                <table class="certificates-plugin-help-table">
                    <thead>
                    <tr>
                        <th>Field</th>
                        <th>Description</th>
                        <th>Usage</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Card Description</td>
                        <td>Short description displayed on certificate cards</td>
                        <td>Used in grid/archive views and shortcodes</td>
                    </tr>
                    <tr>
                        <td>Overview</td>
                        <td>Brief overview of the certificate</td>
                        <td>Displayed at the top of single certificate pages</td>
                    </tr>
                    <tr>
                        <td>Intro</td>
                        <td>Introduction to the certificate</td>
                        <td>First section on single certificate pages</td>
                    </tr>
                    <tr>
                        <td>Prepare & Apply</td>
                        <td>Information about preparation and application</td>
                        <td>Second section on single certificate pages</td>
                    </tr>
                    <tr>
                        <td>Earn Certificate</td>
                        <td>Requirements for earning the certificate</td>
                        <td>Third section on single certificate pages</td>
                    </tr>
                    <tr>
                        <td>Next Steps</td>
                        <td>What to do after earning the certificate</td>
                        <td>Fourth section on single certificate pages</td>
                    </tr>
                    <tr>
                        <td>Documents</td>
                        <td>Related documents and resources</td>
                        <td>Final section on single certificate pages</td>
                    </tr>
                    <tr>
                        <td>Apply Button URL</td>
                        <td>URL for the "Apply Now" button</td>
                        <td>Used in the action buttons on single certificate pages</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="certificates-plugin-card">
                <h3>Best Practices for Custom Fields</h3>
                <ul>
                    <li>Always provide a concise <strong>Card Description</strong> for better display in archives and grids</li>
                    <li>Use the WYSIWYG editor to format text with headers, lists, and links</li>
                    <li>Add images within field content by using the Add Media button</li>
                    <li>Keep the <strong>Overview</strong> brief and engaging to capture visitor interest</li>
                    <li>Include complete information in section fields to provide comprehensive certificate details</li>
                </ul>
            </div>
        </div>
		<?php
	}

	/**
	 * Render the templates documentation tab.
	 */
	public function render_templates_tab() {
		?>
        <div class="certificates-plugin-help-section">
            <h2>Template System</h2>
            <p>Certificates Plugin includes custom templates for displaying certificates on your website.</p>

            <div class="certificates-plugin-card">
                <h3>Template Structure</h3>
                <p>The plugin includes the following templates:</p>
                <ul>
                    <li><strong>Single Certificate</strong>: Displays detailed information about a specific certificate</li>
                    <li><strong>Archive</strong>: Displays a grid of all certificates</li>
                </ul>
                <p>These templates automatically override your theme's default templates for the certificate post type.</p>
            </div>

            <div class="certificates-plugin-card">
                <h3>Customizing Templates</h3>
                <p>If you need to customize the templates, follow these steps:</p>
                <ol>
                    <li>Create a directory called <code>certificates-plugin</code> in your theme folder</li>
                    <li>Copy the template file you want to customize from the plugin's <code>templates</code> directory to your theme's <code>certificates-plugin</code> directory</li>
                    <li>Modify the copied template file as needed</li>
                </ol>
                <p>Your customized template will automatically override the plugin's default template.</p>
            </div>

            <div class="certificates-plugin-card">
                <h3>Available Template Files</h3>
                <table class="certificates-plugin-help-table">
                    <thead>
                    <tr>
                        <th>Template File</th>
                        <th>Purpose</th>
                        <th>Location in Plugin</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><code>single-certificate.php</code></td>
                        <td>Single certificate display</td>
                        <td><code>templates/single-certificate.php</code></td>
                    </tr>
                    <tr>
                        <td><code>archive-certificate.php</code></td>
                        <td>Certificate archive/listing page</td>
                        <td><code>templates/archive-certificate.php</code></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
		<?php
	}

	/**
	 * Render the FAQ documentation tab.
	 */
	public function render_faq_tab() {
		?>
        <div class="certificates-plugin-help-section">
            <h2>Frequently Asked Questions</h2>

            <div class="certificates-plugin-card">
                <h3>Common Questions</h3>

                <div class="certificates-plugin-faq-item">
                    <h4>Can I display certificates from a specific category?</h4>
                    <div class="certificates-plugin-faq-answer">
                        <p>Yes, you can use the <code>category</code> parameter in the shortcode:</p>
                        <div class="certificates-plugin-code-block">
                            <code>[certificates category="featured"]</code>
                        </div>
                        <p>You can also specify multiple categories by separating them with commas:</p>
                        <div class="certificates-plugin-code-block">
                            <code>[certificates category="featured,popular"]</code>
                        </div>
                    </div>
                </div>

                <div class="certificates-plugin-faq-item">
                    <h4>How do I change the number of columns in the grid?</h4>
                    <div class="certificates-plugin-faq-answer">
                        <p>Use the <code>columns</code> parameter in the shortcode:</p>
                        <div class="certificates-plugin-code-block">
                            <code>[certificates columns="3"]</code>
                        </div>
                        <p>This will display certificates in a 3-column grid. The default is 4 columns.</p>
                    </div>
                </div>

                <div class="certificates-plugin-faq-item">
                    <h4>How do I display a single certificate?</h4>
                    <div class="certificates-plugin-faq-answer">
                        <p>Use the <code>id</code> parameter with the certificate's post ID:</p>
                        <div class="certificates-plugin-code-block">
                            <code>[certificates id="123"]</code>
                        </div>
                        <p>You can find the certificate ID in the URL when editing a certificate or by hovering over the certificate title in the admin list.</p>
                    </div>
                </div>

                <div class="certificates-plugin-faq-item">
                    <h4>Can I change the "Learn More" button text?</h4>
                    <div class="certificates-plugin-faq-answer">
                        <p>Yes, use the <code>button_text</code> parameter:</p>
                        <div class="certificates-plugin-code-block">
                            <code>[certificates button_text="View Details"]</code>
                        </div>
                        <p>This will change the button text on all certificates displayed by this shortcode.</p>
                    </div>
                </div>

                <div class="certificates-plugin-faq-item">
                    <h4>How do I limit the description length?</h4>
                    <div class="certificates-plugin-faq-answer">
                        <p>Use the <code>desc_length</code> parameter to specify the number of words:</p>
                        <div class="certificates-plugin-code-block">
                            <code>[certificates desc_length="15"]</code>
                        </div>
                        <p>This will limit the description to 15 words. The default is 25 words.</p>
                    </div>
                </div>
            </div>

            <div class="certificates-plugin-card">
                <h3>Troubleshooting</h3>

                <div class="certificates-plugin-faq-item">
                    <h4>The certificates are not displaying correctly</h4>
                    <div class="certificates-plugin-faq-answer">
                        <ol>
                            <li>Make sure you have created certificate posts in the admin area</li>
                            <li>Check if you've added featured images to your certificates</li>
                            <li>Verify that your theme doesn't have conflicting styles</li>
                            <li>Try adding the <code>custom_class</code> parameter to add your own styling</li>
                        </ol>
                    </div>
                </div>

                <div class="certificates-plugin-faq-item">
                    <h4>Custom fields are not displaying</h4>
                    <div class="certificates-plugin-faq-answer">
                        <ol>
                            <li>Verify that Advanced Custom Fields plugin is active</li>
                            <li>Check that you have entered content in the custom fields for your certificates</li>
                            <li>Try resaving your certificate posts</li>
                            <li>If you're using a custom template, make sure it's correctly calling the ACF functions</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="certificates-plugin-card">
                <h3>Getting Support</h3>
                <p>If you need additional support for the Certificates Plugin, please contact:</p>
                <ul>
                    <li>Email: <a href="mailto:support@example.com">support@example.com</a></li>
                    <li>Support ticket: <a href="https://example.com/support" target="_blank">https://example.com/support</a></li>
                </ul>
                <p>When requesting support, please provide:</p>
                <ol>
                    <li>WordPress version</li>
                    <li>Theme name and version</li>
                    <li>List of active plugins</li>
                    <li>Screenshots of the issue (if applicable)</li>
                </ol>
            </div>
        </div>
		<?php
	}
}