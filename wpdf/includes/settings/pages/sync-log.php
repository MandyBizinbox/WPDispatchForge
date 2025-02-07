<?php
if (!defined('ABSPATH') || !isset($_GET['page']) || $_GET['page'] !== 'wpdispatchforge-settings') {
    return; // Exit if not accessed via the settings page
}

$logs_dir = plugin_dir_path(__FILE__) . '../../../logs';
$log_file = $logs_dir . '/wpdispatchforge.log';

// Ensure logs directory exists
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0755, true);
}

$logs = [];
if (file_exists($log_file)) {
    $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
?>

<div id="sync-log" class="tab-content">
    <h3><?php esc_html_e('Sync Log', 'wpdispatchforge'); ?></h3>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Log Entry', 'wpdispatchforge'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)) : ?>
                <?php foreach ($logs as $log) : ?>
                    <tr>
                        <td><?php echo esc_html($log); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td><?php esc_html_e('No log entries found.', 'wpdispatchforge'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
