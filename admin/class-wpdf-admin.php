<?php
/**
 * Class WPDF_Admin
 * Handles the admin panel functionality for WPDispatchForge, including settings.
 */
class WPDF_Admin {

    /**
     * Initialize the admin panel functionality.
     */
    public static function init() {
        // Add admin menu item.
        add_action('admin_menu', [self::class, 'add_admin_menu']);

        // Enqueue admin scripts and styles.
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);

        // Register settings.
        add_action('admin_init', [self::class, 'register_settings']);

        // Database table creation on activation.
        register_activation_hook(__FILE__, [self::class, 'create_settings_table']);
    }

    /**
     * Add admin menu and submenu pages.
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('WPDispatchForge', 'wpdispatchforge'),
            __('DispatchForge', 'wpdispatchforge'),
            'manage_options',
            'wpdispatchforge',
            [self::class, 'render_dashboard_page'],
            'dashicons-clipboard',
            6
        );

        add_submenu_page(
            'wpdispatchforge',
            __('Settings', 'wpdispatchforge'),
            __('Settings', 'wpdispatchforge'),
            'manage_options',
            'wpdispatchforge-settings',
            [self::class, 'render_settings_page']
        );
    }

    /**
     * Enqueue admin panel scripts and styles.
     */
public static function enqueue_admin_assets($hook) {
    // Load assets only on plugin-related pages.
    if (strpos($hook, 'wpdispatchforge') === false) {
        return;
    }

    wp_enqueue_style(
        'wpdispatchforge-admin-css',
        plugin_dir_url(__FILE__) . 'admin/css/admin-style.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script(
        'wpdispatchforge-admin-js',
        plugin_dir_url(__FILE__) . 'admin/js/admin-script.js',
        ['jquery'],
        '1.0.0',
        true
    );
}




    /**
     * Render the Dashboard page.
     */
    public static function render_dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Welcome to DispatchForge', 'wpdispatchforge'); ?></h1>
            <p><?php esc_html_e('This is your dashboard for managing centralized orders and inventory.', 'wpdispatchforge'); ?></p>
        </div>
        <?php
    }

    /**
     * Render the Settings page with Multi-Tab Layout.
     */
    public static function render_settings_page() {
        $tabs = [
            'connected-platforms' => __('Connected Platforms', 'wpdispatchforge'),
            'packscan-settings' => __('PackScan Settings', 'wpdispatchforge'),
            'order-statuses' => __('Order Statuses', 'wpdispatchforge'),
            'sync-log' => __('Sync Log', 'wpdispatchforge'),
        ];

        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'connected-platforms';

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('DispatchForge Settings', 'wpdispatchforge') . '</h1>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $label) {
            $class = ($current_tab === $tab) ? 'nav-tab nav-tab-active' : 'nav-tab';
            echo '<a href="?page=wpdispatchforge-settings&tab=' . esc_attr($tab) . '" class="' . esc_attr($class) . '">' . esc_html($label) . '</a>';
        }
        echo '</h2>';

        $tab_file = plugin_dir_path(__FILE__) . 'templates/tabs/' . $current_tab . '-tab.php';
        if (file_exists($tab_file)) {
            include $tab_file;
        } else {
            echo '<p>' . esc_html__('Invalid tab selected.', 'wpdispatchforge') . '</p>';
        }
        echo '</div>';
    }

    /**
     * Register settings for the plugin.
     */
    public static function register_settings() {
        // General Settings Section.
        add_settings_section(
            'wpdispatchforge_general_section',
            __('General Settings', 'wpdispatchforge'),
            [self::class, 'general_section_callback'],
            'wpdispatchforge-settings'
        );

        add_settings_field(
            'wpdispatchforge_enable_plugin',
            __('Enable Plugin', 'wpdispatchforge'),
            [self::class, 'enable_plugin_field_callback'],
            'wpdispatchforge-settings',
            'wpdispatchforge_general_section'
        );

        register_setting('wpdispatchforge_options', 'wpdispatchforge_enable_plugin');

        // API Integration Section.
        add_settings_section(
            'wpdispatchforge_api_section',
            __('API Integration', 'wpdispatchforge'),
            [self::class, 'api_section_callback'],
            'wpdispatchforge-settings'
        );

        add_settings_field(
            'wpdispatchforge_api_key',
            __('API Key', 'wpdispatchforge'),
            [self::class, 'api_key_field_callback'],
            'wpdispatchforge-settings',
            'wpdispatchforge_api_section'
        );

        add_settings_field(
            'wpdispatchforge_api_secret',
            __('API Secret', 'wpdispatchforge'),
            [self::class, 'api_secret_field_callback'],
            'wpdispatchforge-settings',
            'wpdispatchforge_api_section'
        );

        register_setting('wpdispatchforge_options', 'wpdispatchforge_api_key');
        register_setting('wpdispatchforge_options', 'wpdispatchforge_api_secret');

        // Synchronization Section.
        add_settings_section(
            'wpdispatchforge_sync_section',
            __('Synchronization Settings', 'wpdispatchforge'),
            [self::class, 'sync_section_callback'],
            'wpdispatchforge-settings'
        );

        add_settings_field(
            'wpdispatchforge_sync_frequency',
            __('Sync Frequency', 'wpdispatchforge'),
            [self::class, 'sync_frequency_field_callback'],
            'wpdispatchforge-settings',
            'wpdispatchforge_sync_section'
        );

        register_setting('wpdispatchforge_options', 'wpdispatchforge_sync_frequency');
    }

    /**
     * Callbacks for sections and fields.
     */

    public static function general_section_callback() {
        echo '<p>' . esc_html__('Configure general plugin settings.', 'wpdispatchforge') . '</p>';
    }

    public static function enable_plugin_field_callback() {
        $value = get_option('wpdispatchforge_enable_plugin', '');
        echo '<input type="checkbox" name="wpdispatchforge_enable_plugin" value="1" ' . checked(1, $value, false) . '> ' . esc_html__('Enable WPDispatchForge', 'wpdispatchforge');
    }

    public static function api_section_callback() {
        echo '<p>' . esc_html__('Enter API credentials for platform integrations.', 'wpdispatchforge') . '</p>';
    }

    public static function api_key_field_callback() {
        $value = get_option('wpdispatchforge_api_key', '');
        echo '<input type="text" name="wpdispatchforge_api_key" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public static function api_secret_field_callback() {
        $value = get_option('wpdispatchforge_api_secret', '');
        echo '<input type="password" name="wpdispatchforge_api_secret" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public static function sync_section_callback() {
        echo '<p>' . esc_html__('Configure synchronization settings.', 'wpdispatchforge') . '</p>';
    }

    public static function sync_frequency_field_callback() {
        $value = get_option('wpdispatchforge_sync_frequency', 'real_time');
        echo '<select name="wpdispatchforge_sync_frequency">
                <option value="real_time" ' . selected($value, 'real_time', false) . '>' . esc_html__('Real-Time', 'wpdispatchforge') . '</option>
                <option value="daily" ' . selected($value, 'daily', false) . '>' . esc_html__('Daily', 'wpdispatchforge') . '</option>
              </select>';
    }

    /**
     * Create settings table in the database.
     */
    public static function create_settings_table() {
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
    }
}

// Initialize the admin functionality.
WPDF_Admin::init();
