<div id="sync" class="tab-content">
    <h3><?php esc_html_e('Synchronization Settings', 'wpdispatchforge'); ?></h3>
    <form method="post" action="">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="sync_frequency"><?php esc_html_e('Sync Frequency', 'wpdispatchforge'); ?></label>
                </th>
                <td>
                    <select id="sync_frequency" name="sync_frequency">
                        <option value="real_time"><?php esc_html_e('Real-Time', 'wpdispatchforge'); ?></option>
                        <option value="daily"><?php esc_html_e('Daily', 'wpdispatchforge'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'wpdispatchforge'); ?></button>
    </form>
</div>
