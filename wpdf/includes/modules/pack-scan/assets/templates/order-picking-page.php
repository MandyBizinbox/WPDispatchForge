<?php
// File: templates/order-picking-page.php

// Ensure this file is accessed through WordPress
if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap packscan container" style="border: 1px solid darkblue; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); padding: 20px;">
    <h1 style="text-align: center; font-size: 55px; color: darkblue;"><?php _e('Start Picking', 'packscan'); ?></h1>

    <!-- Input field to enter the order number -->
    <div class="form-group" style="text-align: center; margin-bottom: 20px;">
        <label for="order-number-input" style="font-size: 30px; font-weight: bold; color: black;"><?php _e('Enter Order Number:', 'packscan'); ?></label>
        <input type="text" id="order-number-input" class="form-control" style="width: auto; display: inline-block; font-size: 20px; border: 1px solid grey; border-radius: 5px; padding: 5px;" placeholder="<?php _e('Order Number', 'packscan'); ?>" />
        <button id="fetch-order-details" class="primary-action"><?php _e('Fetch Order Details', 'packscan'); ?></button>
    </div>

    <!-- Order Details Section -->
    <div id="order-details" style="display:none;">
        <h2 style="text-align: center; font-size: 30px; color: darkblue;"><?php _e('Order Details', 'packscan'); ?></h2>
        <p class="customer-details" style="font-size: 25px; font-weight: bold; color: black;"><strong><?php _e('Order Number:', 'packscan'); ?></strong> <span id="order-number"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Customer Name:', 'packscan'); ?></strong> <span id="customer-name"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Shipping Address:', 'packscan'); ?></strong> <span id="shipping-address"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Shipping Method:', 'packscan'); ?></strong> <span id="shipping-method"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Payment Method:', 'packscan'); ?></strong> <span id="payment-method"></span></p>
        <p class="customer-details" style="font-size: 15px; font-weight: bold; color: black;"><strong><?php _e('Customer Notes:', 'packscan'); ?></strong> <span id="customer-notes"></span></p>

        <!-- SKU input and order items table -->
        <div class="form-group" style="text-align: center; margin-bottom: 20px;">
            <label for="sku-input" style="font-size: 30px; font-weight: bold; color: black;"><?php _e('Enter SKU:', 'packscan'); ?></label>
            <input type="text" id="sku-input" class="form-control" style="width: auto; display: inline-block; font-size: 20px; border: 1px solid grey; border-radius: 5px; padding: 5px;" placeholder="<?php _e('SKU', 'packscan'); ?>" />
            <button id="process-sku" class="secondary-action"><?php _e('Process SKU', 'packscan'); ?></button>
        </div>
        <div id="sku-error-message" class="alert alert-danger" style="display:none; font-size: 20px; color: red;"><?php _e('SKU not found.', 'packscan'); ?></div>

        <table id="order-items" class="table table-striped" style="width: 100%; margin-top: 20px; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: lightblue;">
                    <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('SKU', 'packscan'); ?></th>
                    <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Item Name', 'packscan'); ?></th>
                    <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Quantity Ordered', 'packscan'); ?></th>
                    <th style="font-size: 20px; font-weight: bold; border: 1px solid black;"><?php _e('Quantity Picked', 'packscan'); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be dynamically populated here -->
            </tbody>
        </table>

        <!-- Action Buttons -->
        <div style="text-align: center; margin-top: 20px;">
            <button id="clear-picked-qty" class="third-action"><?php _e('Clear Picked Quantities', 'packscan'); ?></button>
            <button id="continue-to-packing" class="primary-action"><?php _e('Continue to Packing', 'packscan'); ?></button>
        </div>

        <!-- Modal for excess quantity warning -->
        <div id="excess-qty-modal" class="modal">
            <div class="modal-content" style="background-color: lightred; width: 60%; padding: 20px; border-radius: 5px;">
                <span class="close-modal" id="close-warning-modal" style="font-size: 30px; font-weight: bold;">&times;</span>
                <p style="font-size: 30px; font-weight: bold; color: black;"><?php _e('Excess quantity picked. Please check the order.', 'packscan'); ?></p>
            </div>
        </div>
    </div>
</div>
