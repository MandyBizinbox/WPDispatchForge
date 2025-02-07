<?php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

// Content for PackScan Settings Tab
echo '<h3>' . esc_html__('PackScan Settings', 'wpdispatchforge') . '</h3>';
echo '<p>' . esc_html__('Configure your PackScan settings here.', 'wpdispatchforge') . '</p>';

// Example Form
echo '<form method="post" action="">';
echo '<label for="custom_field">' . esc_html__('Custom Field', 'wpdispatchforge') . '</label>';
echo '<input type="text" id="custom_field" name="custom_field" value="" />';
echo '<button type="submit" class="button button-primary">' . esc_html__('Save', 'wpdispatchforge') . '</button>';
echo '</form>';
