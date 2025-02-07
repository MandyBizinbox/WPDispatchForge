<?php
/**
 * Connected Platforms Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include the platform functions
require_once WPDF_PLUGIN_DIR . 'includes/helpers/platform-functions.php';

// Fetch connected platforms from the database
global $wpdb;
$connected_platforms_table = $wpdb->prefix . 'wpdf_connected_platforms';
$connected_platforms = $wpdb->get_results("SELECT * FROM $connected_platforms_table");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_platform'])) {
    $nonce_action = 'wpdf_save_platform';
    $nonce_valid = isset($_POST['_wpnonce']) && wpdf_verify_token($_POST['_wpnonce'], $nonce_action);

    if ($nonce_valid) {
        $platform = sanitize_text_field($_POST['platform']);
        $store_name = sanitize_text_field($_POST['store_name']);
        $url = esc_url_raw($_POST['url']);
        $api_key = sanitize_textarea_field($_POST['api_key']);
        $api_secret = sanitize_textarea_field($_POST['api_secret']);
        $store_id = sanitize_text_field($_POST['store_id']);
        $warehouse_id = sanitize_text_field($_POST['warehouse_id']);
        $webhook_url = esc_url_raw($_POST['webhook_url']);
        $webhook_secret = sanitize_text_field($_POST['webhook_secret']);

        $wpdb->replace(
            $connected_platforms_table,
            [
                'platform' => $platform,
                'store_name' => $store_name,
                'url' => $url,
                'api_key' => $api_key,
                'api_secret' => $api_secret,
                'store_id' => $store_id,
                'warehouse_id' => $warehouse_id,
                'webhook_url' => $webhook_url,
                'webhook_secret' => $webhook_secret,
                'status' => 'pending',
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        echo '<div class="updated"><p>' . esc_html__('Platform saved successfully.', 'wpdf') . '</p></div>';
    } else {
        echo '<div class="error"><p>' . esc_html__('Invalid token.', 'wpdf') . '</p></div>';
    }

    // Refresh the list of connected platforms
    $connected_platforms = $wpdb->get_results("SELECT * FROM $connected_platforms_table");
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Connected Platforms', 'wpdf'); ?></h1>

    <form method="post">
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr(wpdf_generate_token('wpdf_save_platform')); ?>">

        <table class="form-table">
            <tr>
                <th scope="row"><label for="platform"><?php esc_html_e('Platform', 'wpdf'); ?></label></th>
                <td>
                    <select id="platform" name="platform" required>
                        <option value="woocommerce">WooCommerce</option>
                        <option value="shopify">Shopify</option>
                        <option value="takealot">Takealot</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="store_name"><?php esc_html_e('Store Name', 'wpdf'); ?></label></th>
                <td><input type="text" id="store_name" name="store_name" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="url"><?php esc_html_e('API URL', 'wpdf'); ?></label></th>
                <td><input type="url" id="url" name="url" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="api_key"><?php esc_html_e('API Key', 'wpdf'); ?></label></th>
                <td><textarea id="api_key" name="api_key"></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="api_secret"><?php esc_html_e('API Secret', 'wpdf'); ?></label></th>
                <td><textarea id="api_secret" name="api_secret"></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="store_id"><?php esc_html_e('Store ID', 'wpdf'); ?></label></th>
                <td><input type="text" id="store_id" name="store_id"></td>
            </tr>
            <tr>
                <th scope="row"><label for="warehouse_id"><?php esc_html_e('Warehouse ID', 'wpdf'); ?></label></th>
                <td><input type="text" id="warehouse_id" name="warehouse_id"></td>
            </tr>
            <tr>
                <th scope="row"><label for="webhook_url"><?php esc_html_e('Webhook URL', 'wpdf'); ?></label></th>
                <td><input type="url" id="webhook_url" name="webhook_url"></td>
            </tr>
            <tr>
                <th scope="row"><label for="webhook_secret"><?php esc_html_e('Webhook Secret', 'wpdf'); ?></label></th>
                <td><input type="text" id="webhook_secret" name="webhook_secret"></td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="save_platform" class="button-primary">
                <?php esc_html_e('Save Platform', 'wpdf'); ?>
            </button>
        </p>
    </form>

    <h2><?php esc_html_e('Connected Platforms', 'wpdf'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Platform', 'wpdf'); ?></th>
                <th><?php esc_html_e('Store Name', 'wpdf'); ?></th>
                <th><?php esc_html_e('API URL', 'wpdf'); ?></th>
                <th><?php esc_html_e('Status', 'wpdf'); ?></th>
                <th><?php esc_html_e('Actions', 'wpdf'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($connected_platforms) : ?>
                <?php foreach ($connected_platforms as $platform) : ?>
                    <tr>
                        <td><?php echo esc_html($platform->platform); ?></td>
                        <td><?php echo esc_html($platform->store_name); ?></td>
                        <td><?php echo esc_url($platform->url); ?></td>
                        <td><?php echo esc_html($platform->status); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'id' => $platform->id], admin_url('admin.php?page=dispatchforge-settings'))); ?>">
                                <?php esc_html_e('Edit', 'wpdf'); ?>
                            </a>
                            |
                            <a href="<?php echo esc_url(add_query_arg(['action' => 'delete', 'id' => $platform->id], admin_url('admin.php?page=dispatchforge-settings'))); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this platform?', 'wpdf'); ?>');">
                                <?php esc_html_e('Delete', 'wpdf'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e('No platforms connected.', 'wpdf'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
