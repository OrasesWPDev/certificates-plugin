<?php
/**
 * ACF Field Group Registration
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class to handle registration and synchronization of ACF field groups.
 */
class Certificates_ACF {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register local JSON save point
        add_filter('acf/settings/save_json', array($this, 'acf_json_save_point'));

        // Register local JSON load point
        add_filter('acf/settings/load_json', array($this, 'acf_json_load_point'));

        // Hook into ACF initialization - important for correct loading order
        add_action('acf/init', array($this, 'initialize_acf_sync'), 5);

        // Add a notice if there are field groups that need syncing
        add_action('admin_notices', array($this, 'sync_admin_notice'));

        // Add an action to handle syncing
        add_action('admin_post_certificates_sync_acf', array($this, 'handle_sync_action'));

        // Add logging for debugging
        if (WP_DEBUG) {
            error_log('Certificates_ACF initialized');
        }
    }

    /**
     * Define ACF JSON save point
     *
     * @param string $path The path to save ACF JSON files.
     * @return string The modified path.
     */
    public function acf_json_save_point( $path ) {
        // Create acf-json directory in plugin if it doesn't exist
        $plugin_acf_path = CERTIFICATES_PLUGIN_PATH . 'acf-json';

        if ( ! file_exists( $plugin_acf_path ) ) {
            mkdir( $plugin_acf_path, 0755, true );

            if ( WP_DEBUG ) {
                error_log( 'Created ACF JSON directory at: ' . $plugin_acf_path );
            }
        }

        // Set save point to plugin directory
        return $plugin_acf_path;
    }

    /**
     * Register ACF JSON load point
     *
     * @param array $paths Array of paths ACF will load JSON files from.
     * @return array Modified array of paths.
     */
    public function acf_json_load_point( $paths ) {
        // Ensure paths is an array
        if (!is_array($paths)) {
            $paths = array();
        }

        // Add our path to the load paths
        $paths[] = CERTIFICATES_PLUGIN_PATH . 'acf-json';

        if ( WP_DEBUG ) {
            error_log( 'Added ACF JSON load path: ' . CERTIFICATES_PLUGIN_PATH . 'acf-json' );
        }

        return $paths;
    }

    /**
     * Initialize ACF sync
     *
     * @param mixed $context The context parameter passed by WordPress.
     */
    public function initialize_acf_sync($context = null) {
        // Check if we're in the admin and have ACF functions
        if (!is_admin() || !function_exists('acf_get_field_group')) {
            return;
        }

        // Get all JSON field groups
        $json_groups = $this->get_acf_json_files();

        if (empty($json_groups) || !is_array($json_groups)) {
            if (WP_DEBUG) {
                error_log('No valid JSON field groups found for import');
            }
            return;
        }

        foreach ($json_groups as $json_group) {
            if (!is_array($json_group) || !isset($json_group['key'])) {
                if (WP_DEBUG) {
                    error_log('Invalid field group format: ' . print_r($json_group, true));
                }
                continue;
            }

            // Try to get existing field group
            $existing = acf_get_field_group($json_group['key']);

            // If this field group doesn't exist in the database yet, create it
            if (!$existing) {
                // Import the field group
                acf_import_field_group($json_group);
                if (WP_DEBUG) {
                    error_log('Imported field group: ' . (isset($json_group['title']) ? $json_group['title'] : 'Unknown'));
                }
            }
        }
    }

    /**
     * Get all field groups from JSON files
     *
     * @return array Array of field groups from JSON files
     */
    private function get_acf_json_files() {
        $json_groups = array();
        $path = CERTIFICATES_PLUGIN_PATH . 'acf-json';

        if (!is_dir($path)) {
            if (WP_DEBUG) {
                error_log('ACF JSON directory does not exist: ' . $path);
            }
            return $json_groups;
        }

        $files = scandir($path);
        if (!$files || !is_array($files)) {
            if (WP_DEBUG) {
                error_log('Could not scan ACF JSON directory: ' . $path);
            }
            return $json_groups;
        }

        foreach ($files as $file) {
            // Skip non-JSON files and hidden files
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json' || substr($file, 0, 1) === '.') {
                continue;
            }

            $json_file = $path . '/' . $file;
            if (!file_exists($json_file) || !is_readable($json_file)) {
                if (WP_DEBUG) {
                    error_log('Cannot read JSON file: ' . $json_file);
                }
                continue;
            }

            $json_content = file_get_contents($json_file);
            if ($json_content === false) {
                if (WP_DEBUG) {
                    error_log('Failed to read JSON file content: ' . $json_file);
                }
                continue;
            }

            $json_data = json_decode($json_content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                if (WP_DEBUG) {
                    error_log('JSON decode error for file ' . $file . ': ' . json_last_error_msg());
                }
                continue;
            }

            if (!is_array($json_data)) {
                if (WP_DEBUG) {
                    error_log('JSON data is not an array: ' . $file);
                }
                continue;
            }

            foreach ($json_data as $field_group) {
                if (is_array($field_group) && isset($field_group['key'])) {
                    $json_groups[] = $field_group;
                } else {
                    if (WP_DEBUG) {
                        error_log('Invalid field group structure in ' . $file);
                    }
                }
            }
        }

        return $json_groups;
    }

    /**
     * Display admin notice if there are field groups that need syncing
     *
     * @param string $context The context parameter passed by WordPress.
     */
    public function sync_admin_notice($context = '') {
        // Only show on ACF admin pages
        $screen = get_current_screen();
        if (!$screen || !is_object($screen) || !isset($screen->id) || strpos($screen->id, 'acf-field-group') === false) {
            return;
        }

        $sync_required = $this->get_field_groups_requiring_sync();

        if (!empty($sync_required) && is_array($sync_required)) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php
                    printf(
                        _n(
                            'There is %d Certificates field group that requires synchronization.',
                            'There are %d Certificates field groups that require synchronization.',
                            count($sync_required),
                            'certificates-plugin'
                        ),
                        count($sync_required)
                    );
                    ?>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=certificates_sync_acf'), 'certificates_sync_acf')); ?>" class="button button-primary">
                        <?php _e('Sync Field Groups', 'certificates-plugin'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Get field groups that require synchronization
     *
     * @return array Array of field groups that require synchronization
     */
    private function get_field_groups_requiring_sync() {
        if (!function_exists('acf_get_field_group')) {
            return array();
        }

        $sync_required = array();
        $json_groups = $this->get_acf_json_files();

        if (!is_array($json_groups)) {
            if (WP_DEBUG) {
                error_log('JSON groups is not an array: ' . print_r($json_groups, true));
            }
            return array();
        }

        foreach ($json_groups as $json_group) {
            // Verify json_group is an array and has the required key
            if (!is_array($json_group) || !isset($json_group['key'])) {
                if (WP_DEBUG) {
                    error_log('Invalid JSON group format: ' . print_r($json_group, true));
                }
                continue;
            }

            // Get database version
            $db_group = acf_get_field_group($json_group['key']);

            // If DB version doesn't exist or has a different modified time, it needs sync
            if (!$db_group) {
                $sync_required[] = $json_group;
            } else if (isset($json_group['modified']) && isset($db_group['modified']) && $db_group['modified'] != $json_group['modified']) {
                $sync_required[] = $json_group;
            }
        }

        return $sync_required;
    }

    /**
     * Handle the synchronization action
     */
    public function handle_sync_action() {
        // Security check - use a more inclusive approach for capabilities
        if (!current_user_can('manage_acf') && !current_user_can('edit_posts') && !current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'certificates-plugin'));
        }

        // Verify nonce for security
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'certificates_sync_acf')) {
            wp_die(__('Security check failed.', 'certificates-plugin'));
        }

        $json_groups = $this->get_acf_json_files();
        $count = 0;

        if (is_array($json_groups)) {
            foreach ($json_groups as $json_group) {
                if (is_array($json_group) && isset($json_group['key'])) {
                    acf_import_field_group($json_group);
                    $count++;
                }
            }
        }

        // Redirect to the main ACF field groups list
        wp_redirect(add_query_arg(array(
            'post_type' => 'acf-field-group',
            'sync' => 'complete',
            'count' => $count
        ), admin_url('edit.php')));

        exit;
    }

    /**
     * Register certificate field groups programmatically if needed
     *
     * This is a fallback in case the JSON synchronization doesn't work
     * or if you prefer to register fields via PHP.
     */
    public function register_field_groups() {
        // If no field groups exist in ACF, register them programmatically
        if (!$this->field_groups_exist()) {
            $this->register_certificates_fields();

            if (WP_DEBUG) {
                error_log('Registered certificate field groups programmatically');
            }
        }
    }

    /**
     * Check if certificate field groups already exist
     *
     * @return bool True if field groups exist, false otherwise.
     */
    private function field_groups_exist() {
        // Check if ACF function exists
        if (!function_exists('acf_get_field_groups')) {
            return false;
        }

        // Get field groups
        $field_groups = acf_get_field_groups(array(
            'post_type' => 'certificate',
        ));

        // Return true if certificate field groups exist and it's an array with elements
        return is_array($field_groups) && !empty($field_groups);
    }

    /**
     * Register certificate field groups
     */
    private function register_certificates_fields() {
        // Only proceed if ACF function exists
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        // Register Certificates Field Group
        acf_add_local_field_group(array(
            'key' => 'group_certificates_fields',
            'title' => 'Certificates Field Group',
            'fields' => array(
                array(
                    'key' => 'field_card_description',
                    'label' => 'Card Description',
                    'name' => 'card_description',
                    'aria-label' => '',
                    'type' => 'textarea',
                    'instructions' => 'Enter a short description that will appear on the certificate card (25-30 words maximum)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'rows' => 4,
                    'placeholder' => '',
                    'new_lines' => '',
                ),
                array(
                    'key' => 'field_overview',
                    'label' => 'Overview',
                    'name' => 'overview',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_intro',
                    'label' => 'Intro',
                    'name' => 'intro',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_apply_button_url',
                    'label' => 'Apply Now Button URL',
                    'name' => 'apply_button_url',
                    'aria-label' => '',
                    'type' => 'url',
                    'instructions' => 'Enter the URL for the Apply Now button',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_prepare_apply',
                    'label' => 'Prepare & Apply',
                    'name' => 'prepare_apply',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_earn_certificate',
                    'label' => 'Earn Your Certificate',
                    'name' => 'earn_certificate',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_next_steps',
                    'label' => 'Next Steps',
                    'name' => 'next_steps',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_documents',
                    'label' => 'Documents',
                    'name' => 'documents',
                    'aria-label' => '',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'certificate',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ));
    }
}