<?php
namespace DispatchForge\Admin;

class WPDF_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
    }

    /**
     * Register the main menu and submenus
     */
    public function register_admin_menu() {
        // Main menu
        add_menu_page(
            __('DispatchForge Settings', 'wpdf'),
            __('DispatchForge Settings', 'wpdf'),
            'manage_options',
            'dispatchforge-settings',
            [$this, 'render_connected_platforms_page'],
            'dashicons-admin-tools',
            26
        );

        // Submenus
        add_submenu_page(
            'dispatchforge-settings',
            __('Connected Platforms', 'wpdf'),
            __('Connected Platforms', 'wpdf'),
            'manage_options',
            'connected-platforms',
            [$this, 'render_connected_platforms_page']
        );

        add_submenu_page(
            'dispatchforge-settings',
            __('Platform Sync', 'wpdf'),
            __('Platform Sync', 'wpdf'),
            'manage_options',
            'sync-tab',
            [$this, 'render_sync_page']
        );

        add_submenu_page(
            'dispatchforge-settings',
            __('Order Statuses', 'wpdf'),
            __('Order Statuses', 'wpdf'),
            'manage_options',
            'order-statuses-tab',
            [$this, 'render_order_statuses_page']
        );

        add_submenu_page(
            'dispatchforge-settings',
            __('PackScan Settings', 'wpdf'),
            __('PackScan Settings', 'wpdf'),
            'manage_options',
            'packscan-settings-tab',
            [$this, 'render_packscan_settings_page']
        );

        add_submenu_page(
            'dispatchforge-settings',
            __('Error Log', 'wpdf'),
            __('Error Log', 'wpdf'),
            'manage_options',
            'sync-log-tab',
            [$this, 'render_sync_log_page']
        );
    }

    /**
     * Render the Connected Platforms page
     */
    public function render_connected_platforms_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Connected Platforms', 'wpdf') . '</h1>';
        include plugin_dir_path(__FILE__) . 'templates/tabs/connected-platforms-tab.php';
        echo '</div>';
    }

    /**
     * Render the Platform Sync page
     */
    public function render_sync_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Platform Sync', 'wpdf') . '</h1>';
        include plugin_dir_path(__FILE__) . 'templates/tabs/sync-tab.php';
        echo '</div>';
    }

    /**
     * Render the Order Statuses page
     */
    public function render_order_statuses_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Order Statuses', 'wpdf') . '</h1>';
        include plugin_dir_path(__FILE__) . 'templates/tabs/order-statuses-tab.php';
        echo '</div>';
    }

    /**
     * Render the PackScan Settings page
     */
    public function render_packscan_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('PackScan Settings', 'wpdf') . '</h1>';
        include plugin_dir_path(__FILE__) . 'templates/tabs/packscan-settings-tab.php';
        echo '</div>';
    }

    /**
     * Render the Error Log page
     */
    public function render_sync_log_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Error Log', 'wpdf') . '</h1>';
        include plugin_dir_path(__FILE__) . 'templates/tabs/sync-log-tab.php';
        echo '</div>';
    }
}

// Initialize the admin class
