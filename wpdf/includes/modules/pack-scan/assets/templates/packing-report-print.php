<?php
// File: templates/packing-report-print.php

// Bypass WordPress admin bar and any other admin elements
define('IFRAME_REQUEST', true);

if (!defined('ABSPATH')) exit;

// Fetch the order number from the URL parameter
$order_number = isset($_GET['order_number']) ? sanitize_text_field($_GET['order_number']) : '';

// Use utility function to fetch order details based on the custom order number
$order = PackScanUtils::get_order_by_custom_number($order_number);

if (!$order) {
    echo '<p>Order not found.</p>';
    exit;
}

// Fetch the picking and packing user information from post meta using public method
$report_handler = new PSReportHandler();
$picking_user = $report_handler->get_user_by_item_meta($order, '_picked_by_user_');
$packing_user = $report_handler->get_user_by_item_meta($order, '_packed_by_user_');

// Prepare order details
$order_details = [
    'custom_order_number' => get_post_meta($order->get_id(), '_order_number_with_prefix', true),
    'client_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
    'shipping_address' => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() . ', ' . $order->get_shipping_city() . ', ' . $order->get_shipping_postcode(),
    'billing_address' => $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ', ' . $order->get_billing_city() . ', ' . $order->get_billing_postcode(),
    'shipping_method' => $order->get_shipping_method(),
    'payment_method' => $order->get_payment_method_title(),
    'picking_user' => $picking_user ?: 'N/A',
    'packing_user' => $packing_user ?: 'N/A',
];

// Retrieve order items
$items = $order->get_items();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Report #<?php echo esc_html($order_number); ?></title>
    <style>
        /* Basic styles for printing */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            margin: 0;
            padding: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Order Report #<?php echo esc_html($order_details['custom_order_number']); ?></h1>

<!-- Display order details -->
<p><strong>Customer Name:</strong> <?php echo esc_html($order_details['client_name']); ?></p>
<p><strong>Shipping Address:</strong> <?php echo esc_html($order_details['shipping_address']); ?></p>
<p><strong>Billing Address:</strong> <?php echo esc_html($order_details['billing_address']); ?></p>
<p><strong>Shipping Method:</strong> <?php echo esc_html($order_details['shipping_method']); ?></p>
<p><strong>Payment Method:</strong> <?php echo esc_html($order_details['payment_method']); ?></p>
<p><strong>Picked by:</strong> <?php echo esc_html($order_details['picking_user']); ?></p>
<p><strong>Packed by:</strong> <?php echo esc_html($order_details['packing_user']); ?></p>

<!-- Display order items table -->
<table>
    <thead>
        <tr>
            <th>SKU</th>
            <th>Item Name</th>
            <th>Qty Ordered</th>
            <th>Qty Picked</th>
            <th>Qty Packed</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item_id => $item) : 
            $sku = $item->get_product()->get_sku();
            $name = $item->get_name();
            $ordered_qty = $item->get_quantity();
            $picked_qty = get_post_meta($order->get_id(), '_picked_qty_' . $item_id, true) ?: 0;
            $packed_qty = get_post_meta($order->get_id(), '_packed_qty_' . $item_id, true) ?: 0;
        ?>
        <tr>
            <td><?php echo esc_html($sku); ?></td>
            <td><?php echo esc_html($name); ?></td>
            <td><?php echo esc_html($ordered_qty); ?></td>
            <td><?php echo esc_html($picked_qty); ?></td>
            <td><?php echo esc_html($packed_qty); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    // Automatically open the print dialog
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>
