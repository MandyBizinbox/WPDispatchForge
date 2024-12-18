<div id="packscan" class="tab-content">
    <h3><?php esc_html_e('PackScan Settings', 'wpdispatchforge'); ?></h3>
    <form method="post" action="">
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpdf_settings';

        // Retrieve existing settings
        $custom_order_field = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name WHERE setting_key = %s", 'packscan_custom_order_number_field')) ?: '';
        $allowed_roles_process = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name WHERE setting_key = %s", 'packscan_allowed_roles_process'))) ?: ['administrator'];
        $allowed_roles_settings = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name WHERE setting_key = %s", 'packscan_allowed_roles_settings'))) ?: ['administrator'];
        $order_statuses_process = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name WHERE setting_key = %s", 'packscan_order_statuses_process'))) ?: ['wc-processing'];
        $on_hold_status = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name WHERE setting_key = %s", 'packscan_on_hold_status')) ?: 'wc-on-hold';
        $complete_status = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM $table_name WHERE setting_key = %s", 'packscan_complete_status')) ?: 'wc-completed';
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="custom_order_field"><?php esc_html_e('Custom Order Number Field', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <input type="text" id="custom_order_field" name="custom_order_field" class="regular-text" value="<?php echo esc_attr($custom_order_field); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="allowed_roles_process"><?php esc_html_e('Allowed Roles for Processing', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <textarea id="allowed_roles_process" name="allowed_roles_process" class="regular-text"><?php echo esc_textarea(implode(',', $allowed_roles_process)); ?></textarea>
                    <p class="description"><?php esc_html_e('Enter comma-separated user roles (e.g., administrator, shop_manager).', 'wpdispatchforge'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="allowed_roles_settings"><?php esc_html_e('Allowed Roles for Settings', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <textarea id="allowed_roles_settings" name="allowed_roles_settings" class="regular-text"><?php echo esc_textarea(implode(',', $allowed_roles_settings)); ?></textarea>
                    <p class="description"><?php esc_html_e('Enter comma-separated user roles (e.g., administrator, shop_manager).', 'wpdispatchforge'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="order_statuses_process"><?php esc_html_e('Order Statuses for Processing', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <textarea id="order_statuses_process" name="order_statuses_process" class="regular-text"><?php echo esc_textarea(implode(',', $order_statuses_process)); ?></textarea>
                    <p class="description"><?php esc_html_e('Enter comma-separated order statuses (e.g., wc-processing, wc-pending).', 'wpdispatchforge'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="on_hold_status"><?php esc_html_e('On Hold Status', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <input type="text" id="on_hold_status" name="on_hold_status" class="regular-text" value="<?php echo esc_attr($on_hold_status); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="complete_status"><?php esc_html_e('Complete Status', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <input type="text" id="complete_status" name="complete_status" class="regular-text" value="<?php echo esc_attr($complete_status); ?>">
                </td>
            </tr>
        </table>

        <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'wpdispatchforge'); ?></button>
    </form>

    <?php
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $wpdb->replace($table_name, ['setting_key' => 'packscan_custom_order_number_field', 'setting_value' => sanitize_text_field($_POST['custom_order_field'])], ['%s', '%s']);
        $wpdb->replace($table_name, ['setting_key' => 'packscan_allowed_roles_process', 'setting_value' => maybe_serialize(array_map('sanitize_text_field', explode(',', $_POST['allowed_roles_process'])))], ['%s', '%s']);
        $wpdb->replace($table_name, ['setting_key' => 'packscan_allowed_roles_settings', 'setting_value' => maybe_serialize(array_map('sanitize_text_field', explode(',', $_POST['allowed_roles_settings'])))], ['%s', '%s']);
        $wpdb->replace($table_name, ['setting_key' => 'packscan_order_statuses_process', 'setting_value' => maybe_serialize(array_map('sanitize_text_field', explode(',', $_POST['order_statuses_process'])))], ['%s', '%s']);
        $wpdb->replace($table_name, ['setting_key' => 'packscan_on_hold_status', 'setting_value' => sanitize_text_field($_POST['on_hold_status'])], ['%s', '%s']);
        $wpdb->replace($table_name, ['setting_key' => 'packscan_complete_status', 'setting_value' => sanitize_text_field($_POST['complete_status'])], ['%s', '%s']);

        // Redirect to avoid resubmission
        wp_redirect(admin_url('admin.php?page=wpdispatchforge-settings&tab=packscan'));
        exit;
    }
    ?>
</div>
