<?php
// File: templates/packing-report-page.php

if (!defined('ABSPATH')) exit;

?>
<div class="wrap packscan container" style="border: 1px solid darkblue; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); padding: 20px;">
    <h1 style="text-align: center; font-size: 55px; color: darkblue;"><?php _e('Order Report for Order #', 'packscan'); ?><span id="order-number"><?php echo ($order_id) ? esc_html($order_id) : ''; ?></span></h1>

    <!-- Input to fetch specific order report -->
    <div class="form-group" id="fetch-order-report" style="text-align: center; margin-bottom: 20px;">
        <label for="report-order-number" style="font-size: 30px; font-weight: bold; color: black;"><?php _e('Enter Order Number:', 'packscan'); ?></label>
        <input type="text" id="report-order-number" class="form-control" style="width: auto; display: inline-block; font-size: 20px; border: 1px solid grey; border-radius: 5px; padding: 5px;" placeholder="<?php _e('Order Number', 'packscan'); ?>">
        <button id="fetch-report-details" class="primary-action"><?php _e('Fetch Report', 'packscan'); ?></button>
    </div>

    <!-- Order details section -->
    <div id="order-details" style="display: none;">
        <h2 style="text-align: center; font-size: 30px; color: darkblue;"><?php _e('Order Details', 'packscan'); ?></h2>
        <p class="customer-details" style="font-size: 25px; font-weight: bold; color: black;"><strong><?php _e('Customer Name:', 'packscan'); ?></strong> <span id="customer-name"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Shipping Address:', 'packscan'); ?></strong> <span id="shipping-address"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Shipping Method:', 'packscan'); ?></strong> <span id="shipping-method"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Billing Details:', 'packscan'); ?></strong> <span id="billing-details"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Payment Method:', 'packscan'); ?></strong> <span id="payment-method"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Picking User:', 'packscan'); ?></strong> <span id="picking-user"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Packing User:', 'packscan'); ?></strong> <span id="packing-user"></span></p>
    </div>

    <!-- Order items table -->
    <table class="table table-striped" id="order-items" style="width: 100%; margin-top: 20px; border-collapse: collapse; text-align: left; display: none;">
        <thead>
            <tr style="background-color: lightblue;">
                <th style="font-size: 20x; font-weight: bold; border: 1px solid black;"><?php _e('SKU', 'packscan'); ?></th>
                <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Item Name', 'packscan'); ?></th>
                <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Qty Ordered', 'packscan'); ?></th>
                <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Qty Picked', 'packscan'); ?></th>
                <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Qty Packed', 'packscan'); ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Order items will be dynamically inserted here -->
        </tbody>
    </table>

    <!-- Action buttons -->
    <div class="button-group" style="text-align: center; margin-top: 20px; display: none;">
        <button id="hold-order" class="secondary-action"><?php _e('Place Order On Hold', 'packscan'); ?></button>
        <button id="split-order" class="secondary-action"><?php _e('Split Order', 'packscan'); ?></button>
        <button id="complete-order" class="primary-action"><?php _e('Complete Order', 'packscan'); ?></button>
        <button id="print-report" class="secondary-action"><?php _e('Print Report', 'packscan'); ?></button>
    </div>
</div>

<style>
    .discrepancy-row {
        background-color: #f8d7da; /* Light red background for discrepancies */
    }
</style>
