<div id="logs" class="tab-content">
    <h3><?php esc_html_e('Logs', 'wpdispatchforge'); ?></h3>
    <p><?php esc_html_e('View all synchronization, connection, and error logs.', 'wpdispatchforge'); ?></p>

    <?php
    $log_file = WPDF_PLUGIN_DIR . 'logs/wpdispatchforge.log';
    if (file_exists($log_file)) {
        $logs = file_get_contents($log_file);
        echo '<pre style="background: #f7f7f7; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow: auto;">' . esc_html($logs) . '</pre>';
    } else {
        echo '<p>' . esc_html__('No logs found.', 'wpdispatchforge') . '</p>';
    }
    ?>

    <!-- Clear Logs Button -->
    <form method="post" action="">
        <input type="hidden" name="clear_logs" value="1">
        <button type="submit" class="button button-primary"><?php esc_html_e('Clear Logs', 'wpdispatchforge'); ?></button>
    </form>

    <?php
    // Handle log clearing
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_logs'])) {
        file_put_contents($log_file, '');
        wp_redirect(admin_url('admin.php?page=wpdispatchforge-settings&tab=logs'));
        exit;
    }
    ?>
</div>
