<?php
/**
 * Custom Post Type Registration
 *
 * @package Certificates_Plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class to handle registration of the Certificates custom post type.
 */
class Certificates_CPT {

    /**
     * Constructor.
     */
    public function __construct() {
        // Register the custom post type.
        add_action( 'init', array( $this, 'register_post_type' ) );

        // Add meta boxes
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_certificate', array( $this, 'save_meta_box_data' ) );

        // Add custom admin columns
        add_filter( 'manage_certificate_posts_columns', array( $this, 'add_admin_columns' ) );
        add_action( 'manage_certificate_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
        add_filter( 'manage_edit-certificate_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'sort_by_display_order' ) );

        // Add template filters
        add_filter( 'single_template', array( $this, 'single_template' ) );
        add_filter( 'archive_template', array( $this, 'archive_template' ) );

        // Add logging for debugging.
        if ( WP_DEBUG ) {
            error_log( 'Certificates_CPT initialized' );
        }
    }

    /**
     * Register Certificates custom post type.
     */
    public function register_post_type() {
        register_post_type( 'certificate', $this->get_post_type_args() );

        // Log registration for debugging
        if ( WP_DEBUG ) {
            error_log( 'Certificate post type registered' );
        }
    }

    /**
     * Get post type arguments
     */
    private function get_post_type_args() {
        return array(
            'labels'              => $this->get_post_type_labels(),
            'description'         => __( 'Certificates custom post type', 'certificates-plugin' ),
            'public'              => true,
            'hierarchical'        => false,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_admin_bar'   => true,
            'show_in_nav_menus'   => true,
            'show_in_rest'        => true,
            'menu_position'       => null,
            'menu_icon'           => 'dashicons-media-document',
            'capability_type'     => 'post',
            'supports'            => array(
                'title',
                'editor',
                'page-attributes',
                'thumbnail',
                'custom-fields',
            ),
            'taxonomies'          => array(
                'category',
                'post_tag',
            ),
            'has_archive'         => false,
            'rewrite' => array(
                'slug' => 'credentials/certificate',
                'with_front' => false,
                'feeds' => false,
                'pages' => true,
            ),
            'query_var'           => true,
            'can_export'          => true,
        );
    }

    /**
     * Get post type labels
     */
    private function get_post_type_labels() {
        return array(
            'name'                  => _x( 'Certificates', 'Post type general name', 'certificates-plugin' ),
            'singular_name'         => _x( 'Certificate', 'Post type singular name', 'certificates-plugin' ),
            'menu_name'             => _x( 'Certificates', 'Admin Menu text', 'certificates-plugin' ),
            'all_items'             => __( 'All Certificates', 'certificates-plugin' ),
            'edit_item'             => __( 'Edit Certificate', 'certificates-plugin' ),
            'view_item'             => __( 'View Certificate', 'certificates-plugin' ),
            'view_items'            => __( 'View Certificates', 'certificates-plugin' ),
            'add_new_item'          => __( 'Add New Certificate', 'certificates-plugin' ),
            'add_new'               => __( 'Add New Certificate', 'certificates-plugin' ),
            'new_item'              => __( 'New Certificate', 'certificates-plugin' ),
            'parent_item_colon'     => __( 'Parent Certificate:', 'certificates-plugin' ),
            'search_items'          => __( 'Search Certificates', 'certificates-plugin' ),
            'not_found'             => __( 'No certificates found', 'certificates-plugin' ),
            'not_found_in_trash'    => __( 'No certificates found in Trash', 'certificates-plugin' ),
            'archives'              => __( 'Certificate Archives', 'certificates-plugin' ),
            'attributes'            => __( 'Certificate Attributes', 'certificates-plugin' ),
            'insert_into_item'      => __( 'Insert into certificate', 'certificates-plugin' ),
            'uploaded_to_this_item' => __( 'Uploaded to this certificate', 'certificates-plugin' ),
            'filter_items_list'     => __( 'Filter certificates list', 'certificates-plugin' ),
            'filter_by_date'        => __( 'Filter certificates by date', 'certificates-plugin' ),
            'items_list_navigation' => __( 'Certificates list navigation', 'certificates-plugin' ),
            'items_list'            => __( 'Certificates list', 'certificates-plugin' ),
            'item_published'        => __( 'Certificate published.', 'certificates-plugin' ),
            'item_published_privately' => __( 'Certificate published privately.', 'certificates-plugin' ),
            'item_reverted_to_draft' => __( 'Certificate reverted to draft.', 'certificates-plugin' ),
            'item_scheduled'        => __( 'Certificate scheduled.', 'certificates-plugin' ),
            'item_updated'          => __( 'Certificate updated.', 'certificates-plugin' ),
            'item_link'             => __( 'Certificate Link', 'certificates-plugin' ),
            'item_link_description' => __( 'A link to a certificate.', 'certificates-plugin' ),
        );
    }

    /**
     * Add meta boxes for certificate post type
     */
    public function add_meta_boxes() {
        add_meta_box(
            'certificate_display_order',
            __( 'Display Order', 'certificates-plugin' ),
            array( $this, 'display_order_meta_box' ),
            'certificate',
            'side',
            'high'
        );
    }

    /**
     * Display order meta box callback
     */
    public function display_order_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'certificate_display_order_nonce', 'certificate_display_order_nonce' );

        // Get current value
        $value = get_post_meta( $post->ID, '_certificate_display_order', true );

        echo '<label for="certificate_display_order">';
        echo __( 'Enter display order (lower numbers appear first):', 'certificates-plugin' );
        echo '</label> ';
        echo '<input type="number" id="certificate_display_order" name="certificate_display_order" value="' . esc_attr( $value ) . '" min="1" step="1" style="width: 100%">';
    }

    /**
     * Save meta box data
     */
    public function save_meta_box_data( $post_id ) {
        // Check if our nonce is set and verify it
        if ( ! isset( $_POST['certificate_display_order_nonce'] ) ||
            ! wp_verify_nonce( $_POST['certificate_display_order_nonce'], 'certificate_display_order_nonce' ) ) {
            return;
        }

        // Check user permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Don't save on autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Save the display order
        if ( isset( $_POST['certificate_display_order'] ) ) {
            $display_order = sanitize_text_field( $_POST['certificate_display_order'] );
            update_post_meta( $post_id, '_certificate_display_order', $display_order );
        }
    }

    /**
     * Add custom columns to admin list
     */
    public function add_admin_columns( $columns ) {
        $new_columns = array();

        // Insert display order after checkbox but before title
        foreach( $columns as $key => $value ) {
            if ( $key === 'cb' ) {
                $new_columns[$key] = $value;
                $new_columns['display_order'] = __( 'Order', 'certificates-plugin' );
            } else {
                $new_columns[$key] = $value;
            }
        }

        return $new_columns;
    }

    /**
     * Display content for custom columns
     */
    public function custom_column_content( $column, $post_id ) {
        if ( 'display_order' === $column ) {
            $order = get_post_meta( $post_id, '_certificate_display_order', true );
            echo esc_html( $order ?: '-' );
        }
    }

    /**
     * Make custom columns sortable
     */
    public function sortable_columns( $columns ) {
        $columns['display_order'] = 'display_order';
        return $columns;
    }

    /**
     * Sort by display order in admin
     */
    public function sort_by_display_order( $query ) {
        if ( ! is_admin() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        if ( 'display_order' === $orderby ) {
            $query->set( 'meta_key', '_certificate_display_order' );
            $query->set( 'orderby', 'meta_value_num' );
        }
    }

    /**
     * Use custom template for single certificate
     *
     * @param string $template The path of the template to include.
     * @return string The path of the template to include.
     */
    public function single_template( $template ) {
        if ( is_singular( 'certificate' ) ) {
            // Check if a custom template exists in the theme
            $theme_template = locate_template( array( 'single-certificate.php' ) );

            // If a theme template exists, use that
            if ( $theme_template ) {
                return apply_filters( 'certificates_plugin_theme_single_template', $theme_template );
            }

            // Otherwise use the plugin template
            $plugin_template = CERTIFICATES_PLUGIN_PATH . 'templates/single-certificate.php';

            if ( file_exists( $plugin_template ) ) {
                return apply_filters( 'certificates_plugin_single_template', $plugin_template );
            }
        }

        return $template;
    }

    /**
     * Use custom template for certificate archives
     *
     * @param string $template The path of the template to include.
     * @return string The path of the template to include.
     */
    public function archive_template( $template ) {
        if ( is_post_type_archive( 'certificate' ) ) {
            // Check if a custom template exists in the theme
            $theme_template = locate_template( array( 'archive-certificate.php' ) );

            // If a theme template exists, use that
            if ( $theme_template ) {
                return apply_filters( 'certificates_plugin_theme_archive_template', $theme_template );
            }

            // Otherwise use the plugin template
            $plugin_template = CERTIFICATES_PLUGIN_PATH . 'templates/archive-certificate.php';

            if ( file_exists( $plugin_template ) ) {
                return apply_filters( 'certificates_plugin_archive_template', $plugin_template );
            }
        }

        return $template;
    }
}