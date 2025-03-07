<?php
/**
 * Plugin Name: Certificates Plugin
 * Plugin URI: https://yourwebsite.com/
 * Description: A custom plugin for managing and displaying certificates with ACF integration.
 * Version: 1.0.0
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
define( 'CERTIFICATES_PLUGIN_VERSION', '1.0.0' );
define( 'CERTIFICATES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CERTIFICATES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CERTIFICATES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );