<?php
/**
 * Plugin Name: WPDispatchForge
 * Plugin URI: https://github.com/MandyBizinbox/WPDispatchForge
 * Description: Centralized order and inventory management plugin for WordPress.
 * Version: 1.0.0
 * Author: Mandy Bizinbox
 * Author URI: https://bizinbox.co.za
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wpdispatchforge
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define plugin constants.
 */
define('WPDF_VERSION', '1.0.0');
define('WPDF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPDF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPDF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Autoload all PHP files from the specified directory.
 */

function wpdf_autoload_files($directory) {
    $path = WPDF_PLUGIN_DIR . $directory;
    if (!is_dir($path)) {
        error_log("Autoloader Error: Directory not found: $path");
        return;
    }

    foreach (glob($path . "/*.php") as $file) {
        if (!file_exists($file)) {
            error_log("Autoloader Error: File not found: $file");
        } else {
            require_once $file;
            }
    }
}


/**
 * Include required files.
 */
// Autoload core files
wpdf_autoload_files('includes/settings');

// Autoload module-specific files
wpdf_autoload_files('includes/modules/break-apart-product');
wpdf_autoload_files('includes/modules/bundle-product');
wpdf_autoload_files('includes/modules/order-kanban');
wpdf_autoload_files('includes/modules/pack-scan');
wpdf_autoload_files('includes/modules/platform-product-fields');
wpdf_autoload_files('includes/modules/supplier-product-fields');

// Load platform integration files
wpdf_autoload_files('includes/platforms');

// Load admin classes
require_once WPDF_PLUGIN_DIR . 'includes/settings/class-wpdf-settings.php';

/**
 * Create database tables on activation.
 */
function wpdf_create_database_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [
        $wpdb->prefix . 'wpdf_connected_platforms' => "CREATE TABLE {$wpdb->prefix}wpdf_connected_platforms (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            platform VARCHAR(255) NOT NULL,
            store_name VARCHAR(255) NOT NULL,
            url VARCHAR(255) NOT NULL,
            api_key TEXT NULL,
            api_secret TEXT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;",
        // Add additional table creation SQL here
    ];
    $tables
        [$wpdb->prefix . 'wpdf_break_apart'] = "CREATE TABLE {$wpdb->prefix}wpdf_break_apart (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_product_id BIGINT(20) UNSIGNED NOT NULL,
            child_product_id BIGINT(20) UNSIGNED NOT NULL,
            total_units INT(11) NOT NULL,
            units_per_child INT(11) NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (parent_product_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
            FOREIGN KEY (child_product_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
        ) $charset_collate;";

    foreach ($tables as $table => $sql) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'wpdf_create_database_tables');

/**
 * Initialize plugin hooks and actions.
 */
function wpdf_init() {
    // Load text domain
    load_plugin_textdomain('wpdispatchforge', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Initialize modules or core features
    do_action('wpdf_modules_init');
}
add_action('plugins_loaded', 'wpdf_init');

//Instantiated Classes:
new \DispatchForge\Modules\BreakApart();

