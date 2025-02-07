<?php
/**
 * Plugin Name: PackScan
 * Description: An order packing plugin for WooCommerce to prevent shipping errors.
 * Version: 1.0.0
 * Author: ForgeWorks
 * Text Domain: packscan
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// Define plugin paths.
define('PACKSCAN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PACKSCAN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files.
require_once PACKSCAN_PLUGIN_DIR . 'includes/class-packscan.php';
require_once PACKSCAN_PLUGIN_DIR . 'includes/class-ps-picking-handler.php';
require_once PACKSCAN_PLUGIN_DIR . 'includes/class-ps-packing-handler.php';
require_once PACKSCAN_PLUGIN_DIR . 'includes/class-ps-report-handler.php';
require_once PACKSCAN_PLUGIN_DIR . 'includes/class-packscan-utils.php';

// Initialize the main plugin class.
add_action('plugins_loaded', 'packscan_init');
function packscan_init() {
    error_log('PackScan Debug: Initializing PackScan plugin');
    $packscan = new PackScan();
    $packscan->init();
}

// Add custom capabilities during initialization
add_action('admin_init', 'packscan_add_custom_capabilities');
function packscan_add_custom_capabilities() {
    $capabilities = [
        'manage_packscan_orders',
        'view_packscan_reports'
    ];

    $allowed_roles_process = get_option('packscan_allowed_roles_process', array('administrator'));
    foreach ($allowed_roles_process as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            foreach ($capabilities as $capability) {
                if (!$role->has_cap($capability)) {
                    $role->add_cap($capability);
                }
            }
        }
    }

    // Log the roles and capabilities for debugging
    packscan_debug_roles_capabilities();
}

// Debugging capabilities assignment
function packscan_debug_roles_capabilities() {
    $roles = get_option('packscan_allowed_roles_process', array('administrator'));
    error_log('PackScan Debug: Allowed Roles - ' . implode(', ', $roles));

    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            error_log('PackScan Debug: Capabilities for ' . $role_name . ' - ' . implode(', ', array_keys($role->capabilities)));
        } else {
            error_log('PackScan Debug: Role ' . $role_name . ' not found.');
        }
    }
}
// Enqueue scripts and styles
function packscan_enqueue_scripts($hook) {
    // Only load scripts on the PackScan pages
    if (!in_array($hook, ['packscan_page_packscan-picking', 'packscan_page_packscan-packing', 'packscan_page_packscan-report'])) {
        return;
    }

    // Enqueue the shared styles for all PackScan pages
    wp_enqueue_style('packscan-styles', PACKSCAN_PLUGIN_URL . 'css/packscan-style.css');

    // Define common localization data
    $localization_data = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('packscan_nonce'),
        'print_report_url' => plugins_url('packscan-print-report.php', __FILE__), // Correct print URL
        'report_screen_url' => admin_url('admin.php?page=packscan-report&order_number=') // Correct report screen URL
    ];

    // Enqueue and localize scripts for Picking Page
    if ($hook === 'packscan_page_packscan-picking') {
        wp_enqueue_script('ps-picking-scripts', PACKSCAN_PLUGIN_URL . 'js/ps-picking-scripts.js', ['jquery'], '1.0', true);
        wp_localize_script('ps-picking-scripts', 'packscan_vars', array_merge($localization_data, [
            'packing_screen_url' => admin_url('admin.php?page=packscan-packing&order_number=') // Correct packing screen URL
        ]));
    }

    // Enqueue and localize scripts for Packing Page
    if ($hook === 'packscan_page_packscan-packing') {
        wp_enqueue_script('ps-packing-scripts', PACKSCAN_PLUGIN_URL . 'js/ps-packing-scripts.js', ['jquery'], '1.0', true);
        wp_localize_script('ps-packing-scripts', 'packscan_vars', array_merge($localization_data, [
            'picking_screen_url' => admin_url('admin.php?page=packscan-picking&order_number='), // Correct picking screen URL
            'report_screen_url' => admin_url('admin.php?page=packscan-report&order_number=') // Ensure report URL is passed
        ]));
    }

    // Enqueue and localize scripts for Report Page
    if ($hook === 'packscan_page_packscan-report') {
        wp_enqueue_script('ps-report-scripts', PACKSCAN_PLUGIN_URL . 'js/ps-report-scripts.js', ['jquery'], '1.0', true);
        wp_localize_script('ps-report-scripts', 'packscan_vars', $localization_data);
    }
}
add_action('admin_enqueue_scripts', 'packscan_enqueue_scripts');


// Add admin menu for settings page and other submenus
add_action('admin_menu', 'packscan_add_admin_menu');
function packscan_add_admin_menu() {
    error_log('PackScan Debug: Adding PackScan menu items');

    add_menu_page(
        'PackScan',
        'PackScan',
        'manage_woocommerce',
        'packscan-settings',
        'packscan_render_settings_page',
        'dashicons-code-standards',
        6
    );

    add_submenu_page(
        'packscan-settings',
        'Settings',
        'Settings',
        'manage_woocommerce',
        'packscan-settings',
        'packscan_render_settings_page'
    );

    add_submenu_page(
        'packscan-settings',
        'Start Picking',
        'Start Picking',
        'manage_packscan_orders',
        'packscan-picking',
        function() { packscan_order_process_page_callback('picking'); }
    );

    add_submenu_page(
        'packscan-settings',
        'Start Packing',
        'Start Packing',
        'manage_packscan_orders',
        'packscan-packing',
        function() { packscan_order_process_page_callback('packing'); }
    );

    add_submenu_page(
        'packscan-settings',
        'View Report',
        'View Report',
        'manage_packscan_orders',
        'packscan-report',
        function() { packscan_order_process_page_callback('report'); }
    );
}

// Add a submenu page for the print report (no need to display in menu)
add_action('admin_menu', function () {
    add_submenu_page(null, 'Print Report', 'Print Report', 'manage_woocommerce', 'packscan-report-print', function () {
        include PACKSCAN_PLUGIN_DIR . 'templates/packing-report-print.php';
    });
});

// Load settings page from template file
function packscan_render_settings_page() {
    error_log('PackScan Debug: Accessing settings page');
    include PACKSCAN_PLUGIN_DIR . 'templates/settings-page.php';
}

// Process the order processing pages
function packscan_order_process_page_callback($stage = '') {
    if (!in_array($stage, ['picking', 'packing', 'report'])) {
        error_log('PackScan Debug: Invalid stage accessed - ' . $stage);
        wp_die(__('Invalid stage accessed.', 'packscan'));
    }

    switch ($stage) {
        case 'picking':
            include PACKSCAN_PLUGIN_DIR . 'templates/order-picking-page.php';
            break;
        case 'packing':
            include PACKSCAN_PLUGIN_DIR . 'templates/order-packing-page.php';
            break;
        case 'report':
            include PACKSCAN_PLUGIN_DIR . 'templates/packing-report-page.php';
            break;
    }
}

// Fetch and return the custom order number or default order ID
function get_packscan_custom_order_number($order_id) {
    $custom_order_number_meta_key = get_option('packscan_custom_order_number', '_order_number'); // Fallback to '_order_number'
    $custom_order_number = get_post_meta($order_id, $custom_order_number_meta_key, true);

    // If no custom order number is found, use the order ID as fallback
    return $custom_order_number ? $custom_order_number : $order_id;
}

