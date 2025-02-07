<?php
// File: pack-scan/templates/order-process-page.php

// Start session for security and tracking
if (!session_id()) {
    session_start();
}

// Security check: Ensure the user has the right capability
if (!current_user_can('manage_packscan_orders')) {
    wp_die(__('You do not have sufficient permissions to access this page.', 'packscan'));
}

// Get the order ID and stage from the URL parameters
$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
$stage = isset($_GET['stage']) ? sanitize_text_field($_GET['stage']) : '';

// Validate the order ID and stage
if ($order_id <= 0 || !in_array($stage, ['picking', 'packing', 'report'])) {
    echo '<p>' . __('Invalid order ID or stage.', 'packscan') . '</p>';
    exit;
}

// Fetch the order object using the order ID
$order = wc_get_order($order_id);

if (!$order) {
    echo '<p>' . __('Order not found.', 'packscan') . '</p>';
    exit;
}

// Fetch the custom order number based on user settings or default meta field
$custom_order_number = get_post_meta($order_id, get_option('packscan_custom_order_number_field', '_order_number_with_prefix'), true);

// Fetch order details
$client_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
$shipping_address = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();
$customer_notes = $order->get_customer_note();
$shipping_method = $order->get_shipping_method();
$payment_method = $order->get_payment_method_title();
$order_items = $order->get_items();

// Display order details
echo '<div id="order-details">';
echo '<h2>' . sprintf(__('Order Details for Order #%s', 'packscan'), esc_html($custom_order_number)) . '</h2>';
echo '<p><strong>' . __('Client:', 'packscan') . '</strong> ' . esc_html($client_name) . '</p>';
echo '<p><strong>' . __('Shipping Address:', 'packscan') . '</strong> ' . esc_html($shipping_address) . '</p>';
echo '<p><strong>' . __('Customer Notes:', 'packscan') . '</strong> ' . esc_html($customer_notes) . '</p>';
echo '<p><strong>' . __('Shipping Method:', 'packscan') . '</strong> ' . esc_html($shipping_method) . '</p>';
echo '<p><strong>' . __('Payment Method:', 'packscan') . '</strong> ' . esc_html($payment_method) . '</p>';
echo '</div>';

// Display order items table
echo '<table class="widefat fixed">';
echo '<thead>';
echo '<tr>';
echo '<th>' . __('Item Name', 'packscan') . '</th>';
echo '<th>' . __('SKU', 'packscan') . '</th>';
echo '<th>' . __('Quantity Ordered', 'packscan') . '</th>';
echo '<th>' . __('Quantity Picked', 'packscan') . '</th>';
echo '<th>' . __('Quantity Packed', 'packscan') . '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($order_items as $item_id => $item) {
    $product = $item->get_product();
    $sku = $product ? $product->get_sku() : '';
    $quantity_ordered = $item->get_quantity();
    $quantity_picked = get_post_meta($order_id, '_picked_qty_' . $item_id, true) ?: 0;
    $quantity_packed = get_post_meta($order_id, '_packed_qty_' . $item_id, true) ?: 0;

    echo '<tr>';
    echo '<td>' . esc_html($item->get_name()) . '</td>';
    echo '<td>' . esc_html($sku) . '</td>';
    echo '<td>' . esc_html($quantity_ordered) . '</td>';
    echo '<td>' . esc_html($quantity_picked) . '</td>';
    echo '<td>' . esc_html($quantity_packed) . '</td>';
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

// Display action buttons based on the stage
echo '<div id="order-actions">';

switch ($stage) {
    case 'picking':
        echo '<h3>' . __('Start Picking', 'packscan') . '</h3>';
        echo '<input type="text" id="sku-input" placeholder="' . __('Enter SKU to pick', 'packscan') . '">';
        echo '<button id="submit-picking">' . __('Submit Picking', 'packscan') . '</button>';
        break;
    
    case 'packing':
        echo '<h3>' . __('Start Packing', 'packscan') . '</h3>';
        echo '<input type="text" id="sku-input" placeholder="' . __('Enter SKU to pack', 'packscan') . '">';
        echo '<button id="submit-packing">' . __('Submit Packing', 'packscan') . '</button>';
        break;
    
    case 'report':
        echo '<h3>' . __('Order Report', 'packscan') . '</h3>';
        // Add logic to show discrepancies or summary
        break;
    
    default:
        echo '<p>' . __('Invalid stage.', 'packscan') . '</p>';
        break;
}

echo '</div>';
?>

<script>
    jQuery(document).ready(function($) {
        $('#submit-picking').on('click', function() {
            // AJAX call to submit picking data
            // Logic to handle response and errors
        });

        $('#submit-packing').on('click', function() {
            // AJAX call to submit packing data
            // Logic to handle response and errors
        });
    });
</script>
