<?php

class WC_Kanban_Admin {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    public function add_settings_page() {
        add_submenu_page(
            'wc-kanban-orders',  // Parent slug
            'Kanban Order Settings',
            'Settings',
            'manage_options',
            'wc-kanban-settings',
            array($this, 'settings_page_content')
        );
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=wc-kanban-settings">' . __('Settings', 'textdomain') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    public function register_settings() {
        register_setting('wc_kanban_settings', 'wc_kanban_statuses');

        $roles = get_editable_roles();
        foreach ($roles as $role_key => $role) {
            add_settings_section(
                'wc_kanban_section_' . $role_key,
                __('Order Statuses for ', 'textdomain') . $role['name'],
                null,
                'wc-kanban-settings'
            );

            add_settings_field(
                'wc_kanban_statuses_' . $role_key,
                __('Select Statuses', 'textdomain'),
                array($this, 'statuses_checkbox_callback'),
                'wc-kanban-settings',
                'wc_kanban_section_' . $role_key,
                array('role' => $role_key)
            );
        }
    }

    public function statuses_checkbox_callback($args) {
        $role = $args['role'];
        $options = get_option('wc_kanban_statuses');
        $selected_statuses = isset($options[$role]) ? $options[$role] : array();

        $statuses = wc_get_order_statuses();
        foreach ($statuses as $status_key => $status_name) {
            echo '<label>';
            echo '<input type="checkbox" name="wc_kanban_statuses[' . $role . '][]" value="' . $status_key . '"' . checked(in_array($status_key, $selected_statuses), true, false) . '/>';
            echo $status_name;
            echo '</label><br>';
        }
    }

    public function settings_page_content() {
        ?>
        <div class="wrap">
            <h1><?php _e('Kanban Order Settings', 'textdomain'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wc_kanban_settings');
                do_settings_sections('wc-kanban-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

WC_Kanban_Admin::get_instance();
?>
