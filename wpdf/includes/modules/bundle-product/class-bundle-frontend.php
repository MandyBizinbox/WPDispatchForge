<?php

namespace DispatchForge\Modules;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BundleProductFrontend
{
    public function __construct()
    {
        add_action('init', function() {
            if (!class_exists('WooCommerce')) {
                return;
            }
        });
        add_filter('woocommerce_product_get_stock_quantity', [$this, 'set_bundle_stock'], 10, 2);
        add_filter('woocommerce_short_description', [$this, 'append_bundle_to_short_description'], 10, 2);
        add_action('woocommerce_single_product_summary', [$this, 'ensure_add_to_cart_button_display'], 20);
        add_filter('woocommerce_product_has_attributes', [$this, 'enable_bundle_attributes'], 10, 2);
        add_action('woocommerce_single_product_summary', [$this, 'display_bundle_components'], 25);
        add_action('woocommerce_single_product_summary', [$this, 'debug_hooks'], 5);

        $this->debug_log("BundleProductFrontend initialized");

        add_filter('woocommerce_is_purchasable', function ($purchasable, $product) {
            if ($product->get_type() === 'bundle') {
                return true;
            }
            return $purchasable;
        }, 10, 2);

        add_filter('woocommerce_locate_template', [$this, 'override_bundle_template'], 10, 3);
    }

    private function debug_log($message)
    {
        if (WP_DEBUG === true) {
            error_log(date('Y-m-d H:i:s') . ' - ' . $message . "\n", 3, WP_CONTENT_DIR . '/debug.log');
        }
    }

    public function debug_hooks()
    {
        $this->debug_log("woocommerce_single_product_summary hook triggered");
    }

    public function ensure_add_to_cart_button_display() {
        global $product;
        
        if (!$product || !is_a($product, 'WC_Product')) {
            return;
        }

        if ($product->get_type() === 'bundle') {
            if ($product->is_purchasable() && $product->is_in_stock()) {
                woocommerce_template_single_add_to_cart();
            } else {
                echo '<p class="stock out-of-stock">' . esc_html__('This product is currently out of stock.', 'wpdf') . '</p>';
            }
        }
    }

    public function override_bundle_template($template, $template_name, $template_path)
    {
        global $product;
        if ($template_name === 'single-product/add-to-cart/simple.php' && $product->get_type() === 'bundle') {
            $this->debug_log("Overriding bundle template for product ID: " . $product->get_id());
            return plugin_dir_path(__FILE__) . 'assets/templates/bundle-product-template.php';
        }
        return $template;
    }
    
public function display_bundle_components()
{
    global $product;
    $this->debug_log("Entering display_bundle_components for product ID: " . ($product ? $product->get_id() : 'N/A'));

    if (!$product || !$product->get_id()) {
        $this->debug_log("Product is not valid.");
        return;
    }

    $bundle_components = get_post_meta($product->get_id(), '_bundle_components', true);

    // Ensure $bundle_components is a valid array
    if (is_string($bundle_components)) {
        $bundle_components = json_decode($bundle_components, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->debug_log("Invalid JSON in _bundle_components for product ID: " . $product->get_id() . " - " . json_last_error_msg());
            $bundle_components = [];
        }
    }

    if (!is_array($bundle_components)) {
        $this->debug_log("bundle_components is not a valid array for product ID: " . $product->get_id());
        return;
    }

    if (!empty($bundle_components)) {
        echo '<div class="bundle-components-list"><strong>Included in this bundle:</strong><ul>';
        foreach ($bundle_components as $component_id => $component) {
            $product_component = wc_get_product($component_id);
            if ($product_component) {
                echo '<li>' . esc_html($product_component->get_name()) . ' x ' . esc_html($component['qty'] ?? 1) . '</li>';
            }
        }
        echo '</ul></div>';
    }
}



    public function append_bundle_to_short_description($description, $product = null)
    {
        if (!$product || !$product->get_id()) {
            $this->debug_log("Product object is not valid.");
            return $description;
        }

        $this->debug_log("Appending bundle description for product ID: " . $product->get_id());

        if ($product->get_type() === 'bundle') {
            $bundle_components = get_post_meta($product->get_id(), '_bundle_components', true);
            if (is_string($bundle_components)) {
                $bundle_components = json_decode($bundle_components, true);
            }

            if (!empty($bundle_components)) {
                $description .= '<div class="bundle-details"><strong>Bundle includes:</strong><ul>';
                foreach ($bundle_components as $component_id => $component) {
                    $product_component = wc_get_product($component_id);
                    if ($product_component) {
                        $description .= '<li>' . esc_html($product_component->get_name()) . ' x ' . esc_html($component['qty']) . '</li>';
                    }
                }
                $description .= '</ul></div>';
            }
        }

        return $description;
    }
    public function set_bundle_stock($stock, $product)
{
    if ($product->get_type() === 'bundle') {
        $bundle_components = get_post_meta($product->get_id(), '_bundle_components', true);

        // Ensure $bundle_components is a valid array
        if (is_string($bundle_components)) {
            $bundle_components = json_decode($bundle_components, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->debug_log("Invalid JSON in _bundle_components for product ID: " . $product->get_id() . " - " . json_last_error_msg());
                return 0; // Return stock as 0 if JSON is invalid
            }
        }

        if (!is_array($bundle_components)) {
            $this->debug_log("bundle_components is not a valid array for product ID: " . $product->get_id());
            return 0; // Return stock as 0 if not a valid array
        }

        $stock_levels = [];
        foreach ($bundle_components as $component_id => $component) {
            $component_stock = get_post_meta($component_id, '_stock', true);
            if ($component_stock !== '') {
                $stock_levels[] = floor($component_stock / max(1, $component['qty'] ?? 1));
            }
        }

        return !empty($stock_levels) ? min($stock_levels) : 0;
    }
    return $stock;
}

}

new BundleProductFrontend();
