<?php
// File: pack-scan/templates/settings-page.php

// Check if the user has the required capability
if (!current_user_can('manage_woocommerce')) {
    return;
}

// Load the necessary function to check if plugins are active
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// Check if MultiSync Pro is installed and active
$multiSyncProInstalled = is_plugin_active('multi-sync-pro-master/wc-sync-master.php'); // Adjust the path if different
$multiSyncProConnectorInstalled = is_plugin_active('multi-sync-pro-connector/connector-plugin.php'); // Adjust the path if different

// Save settings if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('packscan_save_settings')) {
    update_option('packscan_custom_order_number_field', sanitize_text_field($_POST['packscan_custom_order_number_field']));
    update_option('packscan_allowed_roles_process', $_POST['packscan_allowed_roles_process']);
    update_option('packscan_allowed_roles_settings', $_POST['packscan_allowed_roles_settings']);
    update_option('packscan_order_statuses_process', $_POST['packscan_order_statuses_process']);
    update_option('packscan_on_hold_status', sanitize_text_field($_POST['packscan_on_hold_status']));
    update_option('packscan_complete_status', sanitize_text_field($_POST['packscan_complete_status']));
    echo '<div class="updated"><p>Settings saved.</p></div>';
}

// Retrieve current settings
$custom_order_number_field = get_option('packscan_custom_order_number_field', '');
$allowed_roles_process = get_option('packscan_allowed_roles_process', array('administrator'));
$allowed_roles_settings = get_option('packscan_allowed_roles_settings', array('administrator'));
$order_statuses_process = get_option('packscan_order_statuses_process', array('wc-processing'));
$on_hold_status = get_option('packscan_on_hold_status', 'wc-on-hold');
$complete_status = get_option('packscan_complete_status', 'wc-completed');

// Get potential custom order number fields
$custom_order_fields = PackScanUtils::find_custom_order_number_fields(); // Using utility function

// Add default WooCommerce Order ID to the options
$custom_order_fields[] = [
    'field' => '_default_order_id',
    'label' => 'Default Order ID'
];

// Get all user roles
global $wp_roles;
$all_roles = $wp_roles->roles;
$all_roles = apply_filters('editable_roles', $all_roles);

// Get all WooCommerce order statuses
$order_statuses = wc_get_order_statuses();
$on_hold_status = get_option('packscan_on_hold_status', 'wc-on-hold');
$completed_status = get_option('packscan_completed_status', 'wc-completed');
?>

<div class="wrap">
    <h1>PackScan Settings</h1>
    
     <?php if ($multiSyncProInstalled): ?>
        <div class="notice notice-success">
            <p><strong>MultiSync Pro is installed.</strong></p>
        </div>
    <?php endif; ?>
    
    <?php if ($multiSyncProConnectorInstalled): ?>
        <div class="notice notice-success">
            <p><strong>MultiSync Pro Connector is installed.</strong></p>
        </div>
    <?php endif; ?>
    
    
    <form method="post" action="">
        <?php wp_nonce_field('packscan_save_settings'); ?>
        
        <!-- Custom Order Number Field Selection -->
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Custom Order Number Field</th>
                <td>
                    <select name="packscan_custom_order_number_field">
                        <?php foreach ($custom_order_fields as $field): ?>
                            <option value="<?php echo esc_attr($field['field']); ?>" <?php selected($custom_order_number_field, $field['field']); ?>>
                                <?php echo esc_html($field['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select the custom field that contains the custom order number, or use the default order ID.</p>
                </td>
            </tr>

            <!-- User Roles Allowed to Process Orders -->
            <tr valign="top">
                <th scope="row">User Roles Allowed to Process Orders</th>
                <td>
                    <select name="packscan_allowed_roles_process[]" multiple="multiple">
                        <?php foreach ($all_roles as $role_slug => $role_details) : ?>
                            <option value="<?php echo esc_attr($role_slug); ?>" <?php echo in_array($role_slug, $allowed_roles_process) ? 'selected="selected"' : ''; ?>>
                                <?php echo esc_html($role_details['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select the roles that can process orders using PackScan.</p>
                </td>
            </tr>

            <!-- User Roles Allowed to Change Settings -->
            <tr valign="top">
                <th scope="row">User Roles Allowed to Change Settings</th>
                <td>
                    <select name="packscan_allowed_roles_settings[]" multiple="multiple">
                        <?php foreach ($all_roles as $role_slug => $role_details) : ?>
                            <option value="<?php echo esc_attr($role_slug); ?>" <?php echo in_array($role_slug, $allowed_roles_settings) ? 'selected="selected"' : ''; ?>>
                                <?php echo esc_html($role_details['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select the roles that can change PackScan settings. Administrators are always allowed.</p>
                </td>
            </tr>

            <!-- Order Statuses to Process -->
            <tr valign="top">
                <th scope="row">Order Statuses to Process</th>
                <td>
                    <select name="packscan_order_statuses_process[]" multiple="multiple">
                        <?php foreach ($order_statuses as $status_slug => $status_name) : ?>
                            <option value="<?php echo esc_attr($status_slug); ?>" <?php echo in_array($status_slug, $order_statuses_process) ? 'selected="selected"' : ''; ?>>
                                <?php echo esc_html($status_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select the order statuses that can be processed with PackScan.</p>
                </td>
            </tr>

            <!-- On-Hold Status -->
            <tr valign="top">
                <th scope="row">On-Hold Status</th>
                <td>
                    <select name="packscan_on_hold_status">
                        <?php foreach ($order_statuses as $status_slug => $status_name) : ?>
                            <option value="<?php echo esc_attr($status_slug); ?>" <?php selected($on_hold_status, $status_slug); ?>>
                                <?php echo esc_html($status_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select the status to use when placing an order on hold from PackScan.</p>
                </td>
            </tr>

            <!-- Complete Status -->
            <tr valign="top">
                <th scope="row">Complete Status</th>
                <td>
                    <select name="packscan_complete_status">
                        <?php foreach ($order_statuses as $status_slug => $status_name) : ?>
                            <option value="<?php echo esc_attr($status_slug); ?>" <?php selected($complete_status, $status_slug); ?>>
                                <?php echo esc_html($status_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Select the status to use when completing an order from PackScan.</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Save Settings'); ?>
    </form>
</div>
