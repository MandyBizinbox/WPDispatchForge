<?php

namespace DispatchForge\Classes;

/**
 * Class BreakApart
 */
class BreakApart {
    public function __construct() {

        // Hooks to manage parent and child product stock fields
        add_action('woocommerce_product_options_inventory_product_data', [$this, 'add_parent_product_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_parent_product_fields']);
        add_action('woocommerce_product_after_variable_attributes', [$this, 'add_child_product_fields'], 10, 3);
        add_action('woocommerce_save_product_variation', [$this, 'save_child_product_fields'], 10, 2);
        add_action('woocommerce_reduce_order_stock', [$this, 'manage_stock_on_order']);
        add_action('woocommerce_recalculate_child_stock', [$this, 'recalculate_child_stock']);
    
    }

    /**
     * Add fields for Parent Products
     */
    public function add_parent_product_fields() {
        global $product_object;

        if ($product_object && $product_object->get_type() === 'variable') {
            // Enable Break-Apart Functionality Checkbox
            woocommerce_wp_checkbox([
                'id' => '_enable_break_apart',
                'label' => __('Enable Break-Apart Functionality', 'dispatchforge'),
                'description' => __('Check this box to enable break-apart functionality for this product.', 'dispatchforge'),
                'desc_tip' => true,
            ]);

            // Check if Break-Apart is enabled
            $enable_break_apart = get_post_meta($product_object->get_id(), '_enable_break_apart', true) === 'yes';

            if ($enable_break_apart) {
                // Total Units in Parent Product
                woocommerce_wp_text_input([
                    'id' => '_parent_units',
                    'label' => __('Total Units in Parent Product', 'dispatchforge'),
                    'description' => __('Number of units in one full parent product.', 'dispatchforge'),
                    'desc_tip' => true,
                    'type' => 'number',
                ]);

                // Total Full Parent Products in Stock
                woocommerce_wp_text_input([
                    'id' => '_total_parents',
                    'label' => __('Total Full Parent Products in Stock', 'dispatchforge'),
                    'description' => __('Number of complete parent products available.', 'dispatchforge'),
                    'desc_tip' => true,
                    'type' => 'number',
                ]);

                // Total Parent Units in Stock
                woocommerce_wp_text_input([
                    'id' => '_total_parent_units',
                    'label' => __('Total Parent Units in Stock', 'dispatchforge'),
                    'description' => __('Remaining individual units from partially used parent products.', 'dispatchforge'),
                    'desc_tip' => true,
                    'type' => 'number',
                ]);
            }
        }
    }

    /**
     * Save fields for Parent Products
     */
    public function save_parent_product_fields($post_id) {
        $enable_break_apart = isset($_POST['_enable_break_apart']) ? 'yes' : 'no';
        update_post_meta($post_id, '_enable_break_apart', $enable_break_apart);

        if ($enable_break_apart === 'yes') {
            $parent_units = isset($_POST['_parent_units']) ? intval($_POST['_parent_units']) : 0;
            $total_parents = isset($_POST['_total_parents']) ? intval($_POST['_total_parents']) : 0;
            $total_parent_units = isset($_POST['_total_parent_units']) ? intval($_POST['_total_parent_units']) : 0;

            update_post_meta($post_id, '_parent_units', $parent_units);
            update_post_meta($post_id, '_total_parents', $total_parents);
            update_post_meta($post_id, '_total_parent_units', $total_parent_units);

            // Calculate and update Total Units
            $total_units = ($parent_units * $total_parents) + $total_parent_units;
            update_post_meta($post_id, '_total_units', $total_units);
        }
    }

    /**
     * Add fields for Child Products
     */
    public function add_child_product_fields($loop, $variation_data, $variation) {
        $parent_id = wp_get_post_parent_id($variation->ID);
        $enable_break_apart = get_post_meta($parent_id, '_enable_break_apart', true) === 'yes';

        if ($enable_break_apart) {
            // Force Manage Stock for Child Products
            echo '<input type="hidden" name="variable_manage_stock[' . $loop . ']" value="yes">';

            // Total Units Consumed
            woocommerce_wp_text_input([
                'id' => "_child_units_{$loop}",
                'name' => "_child_units[{$loop}]",
                'value' => get_post_meta($variation->ID, '_child_units', true),
                'label' => __('Total Units Consumed', 'dispatchforge'),
                'description' => __('Units from parent product needed to create one child product.', 'dispatchforge'),
                'desc_tip' => true,
                'type' => 'number',
            ]);

            // Total Stock on Shelf
            woocommerce_wp_text_input([
                'id' => "_child_shelf_stock_{$loop}",
                'name' => "_child_shelf_stock[{$loop}]",
                'value' => get_post_meta($variation->ID, '_child_shelf_stock', true),
                'label' => __('Total Stock on Shelf', 'dispatchforge'),
                'description' => __('Pre-made child products available on shelves.', 'dispatchforge'),
                'desc_tip' => true,
                'type' => 'number',
            ]);

            // Total Possible Stock from Parent (Locked)
            $total_units = intval(get_post_meta($parent_id, '_total_units', true));
            $child_units = intval(get_post_meta($variation->ID, '_child_units', true));
            $child_parent_stock = ($total_units > 0 && $child_units > 0) ? floor($total_units / $child_units) : 0;

            woocommerce_wp_text_input([
                'id' => "_child_parent_stock_{$loop}",
                'name' => "_child_parent_stock[{$loop}]",
                'value' => $child_parent_stock,
                'label' => __('Total Possible Stock from Parent', 'dispatchforge'),
                'description' => __('Stock that can be created from parent product.', 'dispatchforge'),
                'desc_tip' => true,
                'type' => 'number',
                'custom_attributes' => ['readonly' => 'readonly'],
            ]);
        }
    }

    /**
     * Save fields for Child Products
     */
    public function save_child_product_fields($variation_id, $loop) {
        $child_units = isset($_POST['_child_units'][$loop]) ? intval($_POST['_child_units'][$loop]) : 0;
        $child_shelf_stock = isset($_POST['_child_shelf_stock'][$loop]) ? intval($_POST['_child_shelf_stock'][$loop]) : 0;

        update_post_meta($variation_id, '_child_units', $child_units);
        update_post_meta($variation_id, '_child_shelf_stock', $child_shelf_stock);

        // Recalculate Total Possible Stock and WooCommerce stock field
        $parent_id = wp_get_post_parent_id($variation_id);
        $total_units = intval(get_post_meta($parent_id, '_total_units', true));
        $child_parent_stock = ($total_units > 0 && $child_units > 0) ? floor($total_units / $child_units) : 0;
        $final_stock = $child_parent_stock + $child_shelf_stock;

        update_post_meta($variation_id, '_child_parent_stock', $child_parent_stock);
        update_post_meta($variation_id, '_stock', $final_stock);
    }


/**
 * Manage stock on order placement
 */
public function manage_stock_on_order($order) {
    error_log("manage_stock_on_order called for Order ID = " . $order->get_id());

    foreach ($order->get_items() as $item) {
        // Get the variation ID or fallback to the product ID
        $variation_id = $item->get_variation_id();
        if (!$variation_id) {
            $variation_id = $item->get_product_id();
        }

        $product = wc_get_product($variation_id);

        if (!$product || !$product->is_type('variation')) {
            error_log("Invalid or non-variation product. Product ID: $variation_id");
            continue;
        }

        // Retrieve the parent product ID
        $parent_id = wp_get_post_parent_id($variation_id);
        if (!$parent_id) {
            error_log("No parent product found for Variation ID = $variation_id");
            continue;
        }

        // Adjust parent product fields
        $this->adjust_parent_stock($parent_id, $variation_id, $item);
    }
}



/**
 * Process stock adjustments for child products (variations)
 */
private function process_child_product($variation_id, $item) {
    $parent_id = wp_get_post_parent_id($variation_id);
    if (!$parent_id) {
        error_log("Child product has no parent. Variation ID: $variation_id");
        return;
    }

    // Check if Break-Apart is enabled
    $enable_break_apart = get_post_meta($parent_id, '_enable_break_apart', true) === 'yes';
    if (!$enable_break_apart) {
        error_log("Parent does not have 'Enable Break-Apart Functions' enabled. Parent ID: $parent_id");
        return;
    }

    error_log("Processing Break-Apart Child Product: Variation ID = $variation_id, Parent ID = $parent_id");

    // Fetch fields
    $child_units = intval(get_post_meta($variation_id, '_child_units', true));
    $child_shelf_stock = intval(get_post_meta($variation_id, '_child_shelf_stock', true));
    $parent_units = intval(get_post_meta($parent_id, '_parent_units', true));
    $total_parent_units = intval(get_post_meta($parent_id, '_total_parent_units', true));
    $total_parents = intval(get_post_meta($parent_id, '_total_parents', true));

    error_log("Initial Values: Child Units = $child_units, Child Shelf Stock = $child_shelf_stock, Total Parent Units = $total_parent_units, Total Parents = $total_parents, Parent Units = $parent_units");

    $quantity_sold = $item->get_quantity();
    $units_required = $quantity_sold * $child_units;

    // Deduct from shelf stock first
    if ($child_shelf_stock >= $quantity_sold) {
        update_post_meta($variation_id, '_child_shelf_stock', $child_shelf_stock - $quantity_sold);
        error_log("Deducted from shelf stock. Remaining Shelf Stock = " . ($child_shelf_stock - $quantity_sold));
    } else {
        $remaining_quantity = $quantity_sold - $child_shelf_stock;
        update_post_meta($variation_id, '_child_shelf_stock', 0);
        error_log("Shelf stock depleted. Remaining Quantity to Deduct = $remaining_quantity");

        // Deduct from total parent units
        if ($total_parent_units >= $units_required) {
            $new_total_parent_units = $total_parent_units - $units_required;
            update_post_meta($parent_id, '_total_parent_units', $new_total_parent_units);
            error_log("Deducted from Total Parent Units. New Total Parent Units = $new_total_parent_units");
        } else {
            // Break down a full parent product
            $remaining_units_needed = $units_required - $total_parent_units;
            $parent_products_to_break = ceil($remaining_units_needed / $parent_units);

            // Adjust total parents and parent units
            $new_total_parents = max($total_parents - $parent_products_to_break, 0);
            $new_total_parent_units = ($parent_products_to_break * $parent_units) - $remaining_units_needed;

            update_post_meta($parent_id, '_total_parents', $new_total_parents);
            update_post_meta($parent_id, '_total_parent_units', max($new_total_parent_units, 0));

            error_log("Broke down a full parent product. New Total Parents = $new_total_parents, New Total Parent Units = $new_total_parent_units");
        }

        // Recalculate total units
        $new_total_units = ($total_parents * $parent_units) + $total_parent_units;
        update_post_meta($parent_id, '_total_units', $new_total_units);
        error_log("Recalculated Total Units: $new_total_units");
    }

    // Recalculate child product stock
    $this->recalculate_child_stock($variation_id);
}


/**
 * Process parent product stock adjustments based on the order
 */
/**
 * Adjust parent product stock based on child product consumption
 */
/**
 * Adjust parent product stock based on child product consumption
 */
private function adjust_parent_stock($parent_id, $variation_id, $item) {
    error_log("Adjusting Parent Stock: Parent ID = $parent_id, Variation ID = $variation_id");

    // Fetch parent product fields
    $total_parents = intval(get_post_meta($parent_id, '_total_parents', true));
    $total_parent_units = intval(get_post_meta($parent_id, '_total_parent_units', true));
    $parent_units = intval(get_post_meta($parent_id, '_parent_units', true));

    // Fetch child product fields
    $child_units = intval(get_post_meta($variation_id, '_child_units', true));
    $child_shelf_stock = intval(get_post_meta($variation_id, '_child_shelf_stock', true));
    $quantity_sold = $item->get_quantity();

    // Calculate total units required
    $units_required = $child_units * $quantity_sold;

    // Deduct from Child Shelf Stock first
    if ($child_shelf_stock >= $quantity_sold) {
        // Update Shelf Stock
        $new_shelf_stock = $child_shelf_stock - $quantity_sold;
        update_post_meta($variation_id, '_child_shelf_stock', $new_shelf_stock);

        // Update WooCommerce stock field for child product
        $this->update_child_stock($variation_id, $new_shelf_stock, $total_parents, $total_parent_units, $parent_units);
        error_log("Deducted from Shelf Stock: Variation ID = $variation_id, Remaining Shelf Stock = $new_shelf_stock");
        return; // No need to proceed to parent product deduction
    }

    // Calculate remaining units after deducting shelf stock
    $remaining_quantity = $quantity_sold - $child_shelf_stock;
    $remaining_units = $remaining_quantity * $child_units;

    // Reset Shelf Stock
    update_post_meta($variation_id, '_child_shelf_stock', 0);
    error_log("Depleted Shelf Stock for Variation ID = $variation_id. Remaining Units to Deduct = $remaining_units");

    // Deduct from Parent Product
    if ($total_parent_units >= $remaining_units) {
        $total_parent_units -= $remaining_units;
    } else {
        $remaining_units_needed = $remaining_units - $total_parent_units;
        $total_parent_units = 0;

        // Consume full parent products
        $full_parents_consumed = ceil($remaining_units_needed / $parent_units);
        $leftover_units = ($full_parents_consumed * $parent_units) - $remaining_units_needed;

        $total_parents = max($total_parents - $full_parents_consumed, 0);
        $total_parent_units += $leftover_units;
    }

    // Recalculate Total Units
    $new_total_units = ($total_parents * $parent_units) + $total_parent_units;

    // Update parent product fields
    update_post_meta($parent_id, '_total_parents', $total_parents);
    update_post_meta($parent_id, '_total_parent_units', $total_parent_units);
    update_post_meta($parent_id, '_total_units', $new_total_units);

    // Update WooCommerce stock field for parent product
    wc_update_product_stock($parent_id, $new_total_units);

    // Recalculate child product stock dynamically
    $this->update_child_stock($variation_id, 0, $total_parents, $total_parent_units, $parent_units);

    // Log updates
    error_log("Updated Parent Product Fields: Total Parents = $total_parents, Total Parent Units = $total_parent_units, Total Units = $new_total_units");
}

/**
 * Update WooCommerce stock for child product
 */
private function update_child_stock($variation_id, $child_shelf_stock, $total_parents, $total_parent_units, $parent_units) {
    // Calculate possible stock from parent product
    $total_units = ($total_parents * $parent_units) + $total_parent_units;
    $child_units = intval(get_post_meta($variation_id, '_child_units', true));
    $child_parent_stock = ($total_units > 0 && $child_units > 0) ? floor($total_units / $child_units) : 0;

    // Calculate final stock for the child product
    $final_stock = $child_shelf_stock + $child_parent_stock;

    // Update WooCommerce stock field for child product
    wc_update_product_stock($variation_id, $final_stock);

    // Update child product metadata
    update_post_meta($variation_id, '_child_parent_stock', $child_parent_stock);

    // Log updates
    error_log("Updated WooCommerce Stock for Child Product: Variation ID = $variation_id, Final Stock = $final_stock, Shelf Stock = $child_shelf_stock, Parent Stock = $child_parent_stock");
}
}