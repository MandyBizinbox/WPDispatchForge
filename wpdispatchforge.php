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
 * Include necessary files.
 */
require_once WPDF_PLUGIN_DIR . 'admin/class-wpdf-admin.php';

/**
 * Function to create settings table in the database.
 */
function wpdf_create_settings_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'wpdf_settings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        setting_key VARCHAR(191) NOT NULL,
        setting_value LONGTEXT NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY setting_key (setting_key)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Log table creation for debugging
    error_log("Settings table created or already exists: $table_name");
}

/**
 * Register activation hook to create database table.
 */
register_activation_hook(__FILE__, 'wpdf_create_settings_table');

/**
 * Initialize the admin functionality.
 */
WPDF_Admin::init();