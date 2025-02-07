<?php
/*
Plugin Name: WooCommerce Kanban
Description: Display WooCommerce orders in a Kanban board view.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin URL and path
define('WC_KANBAN_ORDERS_URL', plugin_dir_url(__FILE__));
define('WC_KANBAN_ORDERS_PATH', plugin_dir_path(__FILE__));

// Include required class files
require_once WC_KANBAN_ORDERS_PATH . 'includes/class-wc-kanban-orders.php';
require_once WC_KANBAN_ORDERS_PATH . 'includes/admin/class-wc-kanban-admin.php';

// Initialize the classes as singletons
function wc_kanban_init() {
    WC_Kanban_Orders::get_instance();
    WC_Kanban_Admin::get_instance();
}

wc_kanban_init();
?>
