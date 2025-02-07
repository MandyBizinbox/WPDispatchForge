<?php

if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Ensure correct page and tab are loaded
if (!isset($_GET['page']) || $_GET['page'] !== 'wpdispatchforge-settings' || !isset($_GET['tab']) || $_GET['tab'] !== 'order-statuses') {
    return;
}

global $wpdb;
$table_name = $wpdb->prefix . 'wpdf_settings';

// Fetch custom order statuses
$order_statuses = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE setting_key LIKE %s",
        'custom_order_status_%'
    )
);

?>

<div id="order-statuses" class="tab-content">
    <h3><?php esc_html_e('Order Statuses', 'wpdispatchforge'); ?></h3>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Status Key', 'wpdispatchforge'); ?></th>
                <th><?php esc_html_e('Status Name', 'wpdispatchforge'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($order_statuses)) : ?>
                <?php foreach ($order_statuses as $status) : ?>
                    <tr>
                        <td><?php echo esc_html($status->setting_key); ?></td>
                        <td><?php echo esc_html($status->setting_value); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="2"><?php esc_html_e('No custom order statuses found.', 'wpdispatchforge'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
