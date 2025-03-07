<?php
/**
 * Plugin Name: Certificates Plugin
 * Plugin URI: https://yourwebsite.com/
 * Description: A custom plugin for managing and displaying certificates with ACF integration.
 * Version: 1.0.1
 * Author: Orases
 * Author URI: https://orases.com/
 * Text Domain: certificates-plugin
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'CERTIFICATES_PLUGIN_VERSION', '1.0.1' );
define( 'CERTIFICATES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CERTIFICATES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CERTIFICATES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Check if ACF is active
function certificates_plugin_has_acf() {
    return class_exists( 'ACF' );
}

// Plugin initialization
function certificates_plugin_init() {
    // Load plugin textdomain
    load_plugin_textdomain( 'certificates-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // Include required files
    require_once CERTIFICATES_PLUGIN_PATH . 'includes/class-certificates-cpt.php';

    // Only load ACF integration if ACF is active
    if ( certificates_plugin_has_acf() ) {
        require_once CERTIFICATES_PLUGIN_PATH . 'includes/class-certificates-acf.php';
    } else {
        // Admin notice if ACF is not active
        add_action( 'admin_notices', 'certificates_plugin_acf_missing_notice' );
    }

    // Load shortcode functionality
    require_once CERTIFICATES_PLUGIN_PATH . 'includes/class-certificates-shortcode.php';

    // Initialize classes
    new Certificates_CPT();
    if ( certificates_plugin_has_acf() ) {
        new Certificates_ACF();
    }
    new Certificates_Shortcode();

    // Register assets
    add_action( 'wp_enqueue_scripts', 'certificates_plugin_register_assets' );
    add_action( 'admin_enqueue_scripts', 'certificates_plugin_register_admin_assets' );
}
add_action( 'plugins_loaded', 'certificates_plugin_init' );

// Admin notice for missing ACF
function certificates_plugin_acf_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e( 'Certificates Plugin requires Advanced Custom Fields PRO to be installed and activated.', 'certificates-plugin' ); ?></p>
    </div>
    <?php
}

// Register front-end assets
function certificates_plugin_register_assets() {
    // Main CSS
    wp_register_style(
        'certificates-plugin-style',
        CERTIFICATES_PLUGIN_URL . 'assets/css/certificates.css',
        array(),
        CERTIFICATES_PLUGIN_VERSION
    );

    // Responsive CSS
    wp_register_style(
        'certificates-plugin-responsive-style',
        CERTIFICATES_PLUGIN_URL . 'assets/css/responsive-certificates.css',
        array('certificates-plugin-style'), // This makes it load after the main CSS
        CERTIFICATES_PLUGIN_VERSION
    );

    // Shortcode CSS
    wp_register_style(
        'certificates-shortcode-style',
        CERTIFICATES_PLUGIN_URL . 'assets/css/certificates-shortcode.css',
        array('certificates-plugin-style'),
        CERTIFICATES_PLUGIN_VERSION
    );

    // JavaScript
    wp_register_script(
        'certificates-plugin-script',
        CERTIFICATES_PLUGIN_URL . 'assets/js/certificates.js',
        array( 'jquery' ),
        CERTIFICATES_PLUGIN_VERSION,
        true
    );

    // Enqueue the assets
    wp_enqueue_style( 'certificates-plugin-style' );
    wp_enqueue_style( 'certificates-plugin-responsive-style' );
    wp_enqueue_script( 'certificates-plugin-script' );

    // Note: We don't enqueue shortcode CSS here because it will be enqueued
    // by the shortcode function only when the shortcode is used
}

// Register admin assets
function certificates_plugin_register_admin_assets($hook) {
    // Only load on specific admin pages if needed
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        global $post;
        if ($post && 'certificate' === $post->post_type) {
            // Main CSS
            wp_enqueue_style(
                'certificates-plugin-style',
                CERTIFICATES_PLUGIN_URL . 'assets/css/certificates.css',
                array(),
                CERTIFICATES_PLUGIN_VERSION
            );

            // Shortcode CSS
            wp_enqueue_style(
                'certificates-shortcode-style',
                CERTIFICATES_PLUGIN_URL . 'assets/css/certificates-shortcode.css',
                array('certificates-plugin-style'),
                CERTIFICATES_PLUGIN_VERSION
            );

            // Responsive CSS
            wp_enqueue_style(
                'certificates-responsive-style',
                CERTIFICATES_PLUGIN_URL . 'assets/css/responsive-certificates.css',
                array('certificates-plugin-style'),
                CERTIFICATES_PLUGIN_VERSION
            );

            // Main JS
            wp_enqueue_script(
                'certificates-plugin-script',
                CERTIFICATES_PLUGIN_URL . 'assets/js/certificates.js',
                array('jquery'),
                CERTIFICATES_PLUGIN_VERSION,
                true
            );
        }
    }
}

// Custom breadcrumbs shortcode for certificate pages
add_shortcode('certificate_breadcrumbs', 'certificates_breadcrumbs_shortcode');
function certificates_breadcrumbs_shortcode() {
    if (!is_singular('certificate')) {
        return do_shortcode('[wpseo_breadcrumb]');
    }

    ob_start();

    $post_title = get_the_title();
    ?>
    <span>
        <span><a href="<?php echo home_url(); ?>">Home</a></span>
        <span class="yoast-divider">/</span>
        <span><a href="<?php echo home_url('/credentials/'); ?>">Credentials</a></span>
        <span class="yoast-divider">/</span>
        <span><a href="<?php echo home_url('/credentials/certificate/'); ?>">Certificate</a></span>
        <span class="yoast-divider">/</span>
        <span class="breadcrumb_last" aria-current="page"><?php echo esc_html($post_title); ?></span>
    </span>
    <?php

    return ob_get_clean();
}

// Force registration of ACF field groups from JSON
function certificates_force_acf_sync() {
    if (!function_exists('acf_get_field_groups') || !function_exists('acf_add_local_field_group')) {
        return;
    }

    // Path to the ACF JSON file
    $json_file = CERTIFICATES_PLUGIN_PATH . 'acf-json/group_67bf615a25b23.json';
    if (file_exists($json_file)) {
        $json_content = file_get_contents($json_file);

        // Check if json content is valid
        if (!$json_content) {
            error_log('Failed to read JSON file: ' . $json_file);
            return;
        }

        $json_data = json_decode($json_content, true);

        // Check if json_decode was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON decoding error: ' . json_last_error_msg());
            return;
        }

        // Verify json_data is an array
        if (!is_array($json_data)) {
            error_log('JSON data is not an array');
            return;
        }

        acf_add_local_field_group($json_data);
        if (WP_DEBUG) {
            error_log('Registered field group: ' . (isset($json_data['title']) ? $json_data['title'] : 'Unknown'));
        }
    } else {
        error_log('ACF JSON file not found: ' . $json_file);
    }
}
add_action('acf/init', 'certificates_force_acf_sync', 20);

// Activation hook
register_activation_hook( __FILE__, 'certificates_plugin_activate' );
function certificates_plugin_activate() {
    // Flush rewrite rules on activation
    require_once CERTIFICATES_PLUGIN_PATH . 'includes/class-certificates-cpt.php';
    $cpt = new Certificates_CPT();
    $cpt->register_post_type();
    flush_rewrite_rules();

    // Debug log on activation
    if ( WP_DEBUG ) {
        error_log( 'Certificates Plugin activated' );
    }
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'certificates_plugin_deactivate' );
function certificates_plugin_deactivate() {
    // Flush rewrite rules on deactivation
    flush_rewrite_rules();

    // Debug log on deactivation
    if ( WP_DEBUG ) {
        error_log( 'Certificates Plugin deactivated' );
    }
}