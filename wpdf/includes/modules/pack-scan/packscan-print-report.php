<?php
/**
 * PackScan Print Report
 * This file is used to generate a clean, printable order report.
 */

// Load WordPress environment
require_once('../../../wp-load.php');

// Ensure the user has the right permissions to view the order report
if (!current_user_can('manage_woocommerce')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

// Include the print report template directly
include plugin_dir_path(__FILE__) . 'templates/packing-report-print.php';

add_filter('show_admin_bar', '__return_false');

// Load only necessary scripts and styles
function packscan_print_report_enqueue() {
    // Dequeue all admin styles and scripts
    wp_dequeue_script('jquery');
    wp_dequeue_script('admin-bar');
    wp_dequeue_style('admin-bar');
    // Enqueue only necessary styles/scripts for printing if any
}
add_action('wp_enqueue_scripts', 'packscan_print_report_enqueue', 100);
