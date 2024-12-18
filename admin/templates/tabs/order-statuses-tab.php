<div id="order-statuses" class="tab-content">
    <h3><?php esc_html_e('Order Statuses', 'wpdispatchforge'); ?></h3>

    <!-- Display Existing WooCommerce Statuses -->
    <h4><?php esc_html_e('Existing WooCommerce Order Statuses', 'wpdispatchforge'); ?></h4>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Status Key', 'wpdispatchforge'); ?></th>
                <th><?php esc_html_e('Status Name', 'wpdispatchforge'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Load WooCommerce order statuses
            if (function_exists('wc_get_order_statuses')) {
                $statuses = wc_get_order_statuses();
                foreach ($statuses as $key => $name) {
                    echo '<tr>';
                    echo '<td>' . esc_html($key) . '</td>';
                    echo '<td>' . esc_html($name) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="2">' . esc_html__('WooCommerce is not active.', 'wpdispatchforge') . '</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <!-- Add New Order Status -->
    <h4><?php esc_html_e('Add New Order Status', 'wpdispatchforge'); ?></h4>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="status_key"><?php esc_html_e('Status Key', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <input type="text" id="status_key" name="status_key" class="regular-text" required>
                    <p class="description"><?php esc_html_e('Enter a unique status key (e.g., wc-custom-status).', 'wpdispatchforge'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="status_name"><?php esc_html_e('Status Name', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <input type="text" id="status_name" name="status_name" class="regular-text" required>
                    <p class="description"><?php esc_html_e('Enter the name for the status (e.g., Custom Status).', 'wpdispatchforge'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="icon"><?php esc_html_e('Action Icon', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <input type="text" id="icon" name="icon" class="regular-text">
                    <p class="description"><?php esc_html_e('Enter a Dashicon class for the status icon (e.g., dashicons-yes).', 'wpdispatchforge'); ?></p>
                </td>
            </tr>
        </table>
        <button type="submit" class="button button-primary"><?php esc_html_e('Add Status', 'wpdispatchforge'); ?></button>
    </form>

    <?php
    // Handle form submission for adding new order status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_key'], $_POST['status_name'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpdf_settings';

        // Prepare the status data
        $status_data = [
            'key' => sanitize_text_field($_POST['status_key']),
            'name' => sanitize_text_field($_POST['status_name']),
            'icon' => sanitize_text_field($_POST['icon']),
        ];

        // Save to the database
        $wpdb->replace(
            $table_name,
            ['setting_key' => 'custom_order_status_' . $status_data['key'], 'setting_value' => maybe_serialize($status_data)],
            ['%s', '%s']
        );

        // Redirect to avoid resubmission
        wp_redirect(admin_url('admin.php?page=wpdispatchforge-settings&tab=order-statuses'));
        exit;
    }

    // Load custom statuses
    $custom_statuses = $wpdb->get_results("SELECT * FROM $table_name WHERE setting_key LIKE 'custom_order_status_%'");

    if ($custom_statuses) :
    ?>
        <!-- Display Custom Order Statuses -->
        <h4><?php esc_html_e('Custom Order Statuses', 'wpdispatchforge'); ?></h4>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Status Key', 'wpdispatchforge'); ?></th>
                    <th><?php esc_html_e('Status Name', 'wpdispatchforge'); ?></th>
                    <th><?php esc_html_e('Icon', 'wpdispatchforge'); ?></th>
                    <th><?php esc_html_e('Actions', 'wpdispatchforge'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($custom_statuses as $status) {
                    $status_data = maybe_unserialize($status->setting_value);
                    echo '<tr>';
                    echo '<td>' . esc_html($status_data['key']) . '</td>';
                    echo '<td>' . esc_html($status_data['name']) . '</td>';
                    echo '<td><span class="' . esc_attr($status_data['icon']) . '"></span></td>';
                    echo '<td><a href="#" class="button button-secondary">' . esc_html__('Remove', 'wpdispatchforge') . '</a></td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
