<div id="connected-platforms" class="tab-content">
    <h3><?php esc_html_e('Connected Platforms', 'wpdispatchforge'); ?></h3>
    <p><?php esc_html_e('Manage and monitor all connected platforms.', 'wpdispatchforge'); ?></p>

    <!-- Add New Platform -->
    <button id="add-platform-btn" class="button button-primary"><?php esc_html_e('Add New Platform', 'wpdispatchforge'); ?></button>

    <!-- Platform Form Modal -->
    <div id="platform-form-modal" style="display: none;">
        <h4><?php esc_html_e('Add New Platform', 'wpdispatchforge'); ?></h4>
        <form id="platform-form" method="post" action="">
            <table class="form-table">
                <tr>
                    <th>
                        <label for="platform_type"><?php esc_html_e('Platform', 'wpdispatchforge'); ?></label>
                    </th>
                    <td>
                        <select id="platform_type" name="platform_type">
                            <option value="woocommerce"><?php esc_html_e('WooCommerce', 'wpdispatchforge'); ?></option>
                            <option value="takealot"><?php esc_html_e('Takealot.com', 'wpdispatchforge'); ?></option>
                            <option value="amazon"><?php esc_html_e('Amazon', 'wpdispatchforge'); ?></option>
                            <option value="etsy"><?php esc_html_e('Etsy', 'wpdispatchforge'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="platform-form-fields"></tr>
            </table>
            <button type="submit" class="button button-primary"><?php esc_html_e('Save', 'wpdispatchforge'); ?></button>
        </form>
    </div>

    <!-- Table for Connected Platforms -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Platform', 'wpdispatchforge'); ?></th>
                <th><?php esc_html_e('Site Name', 'wpdispatchforge'); ?></th>
                <th><?php esc_html_e('URL', 'wpdispatchforge'); ?></th>
                <th><?php esc_html_e('Status', 'wpdispatchforge'); ?></th>
                <th><?php esc_html_e('Actions', 'wpdispatchforge'); ?></th>
            </tr>
        </thead>
        <tbody id="connected-platforms-list">
            <!-- Dynamically populated -->
        </tbody>
    </table>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['platform_type'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpdf_settings';

    $platform_data = [
        'platform' => sanitize_text_field($_POST['platform_type']),
        'name' => sanitize_text_field($_POST['site_name'] ?? $_POST['store_name']),
        'url' => sanitize_url($_POST['site_url'] ?? ''),
        'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
        'api_secret' => sanitize_text_field($_POST['api_secret'] ?? ''),
        'status' => 'pending',
    ];

    $wpdb->insert($table_name, [
        'setting_key' => 'platform_' . uniqid(),
        'setting_value' => maybe_serialize($platform_data),
    ]);

    // Make API Test Call Here (Pseudo-code)
    // $status = test_api_connection($platform_data);
    // Update status in $wpdb based on API test result

    wp_redirect(admin_url('admin.php?page=wpdispatchforge-settings&tab=connected-platforms'));
    exit;
}
?>
