<?php

namespace DispatchForge\Modules;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Product_Bundle extends \WC_Product_Simple
{
    public function __construct($product)
    {
        error_log('Initializing WC_Product_Bundle for product ID: ' . $product);
        $this->product_type = 'bundle';
        parent::__construct($product);
    }

    public function get_type()
    {
        return 'bundle';
    }

    public function is_sold_individually()
    {
        return false;
    }
}


class BundleProduct
{
    public function __construct()
    {
        add_filter('woocommerce_product_class', [$this, 'load_bundle_product_class'], 10, 2);
        add_filter('product_type_selector', [$this, 'add_bundle_product_type']);
        add_filter('woocommerce_product_data_tabs', [$this, 'modify_product_data_tabs']);
        add_action('woocommerce_product_data_panels', [$this, 'add_bundle_product_tab_content']);
        add_action('woocommerce_process_product_meta', [$this, 'save_bundle_product_fields']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_bundle_product_scripts']);
        add_action('wp', [$this, 'validate_product_type_registration']);
    }

public function load_bundle_product_class($classname, $product_type)
{
    if ('bundle' === $product_type) {
        error_log('Loading WC_Product_Bundle class for product type: bundle');
        return WC_Product_Bundle::class;
    }

    error_log('Returning default class for product type: ' . $product_type);
    return $classname;
}



    public function add_bundle_product_type($types)
    {
        $types['bundle'] = __('Bundle', 'dispatchforge');
        return $types;
    }

    public function modify_product_data_tabs($tabs)
    {
        if ('product' === get_post_type()) {
            ?>
                <script type='text/javascript'>
                    document.addEventListener('DOMContentLoaded', () => {
                        let optionGroupPricing = document.querySelector('.options_group.pricing');
                        !!optionGroupPricing && optionGroupPricing.classList.add('show_if_bundle');

                        let stockManagement = document.querySelector('._manage_stock_field');
                        !!stockManagement && stockManagement.classList.add('show_if_bundle');

                        let soldIndividuallyDiv = document.querySelector('.inventory_sold_individually');
                        let soldIndividually = document.querySelector('._sold_individually_field');
                        !!soldIndividuallyDiv && soldIndividuallyDiv.classList.add('show_if_bundle');
                        !!soldIndividually && soldIndividually.classList.add('show_if_bundle');

                        let generalProductData = document.querySelectorAll('#general_product_data > .options_group');
                        let taxDiv = !!generalProductData && Array.from(generalProductData).at(-1);
                        !!taxDiv && taxDiv.classList.add('show_if_bundle');
                    });
                </script>
            <?php
        }

        $tabs['general']['class'][] = 'show_if_bundle';
        $tabs['inventory']['class'][] = 'show_if_bundle';

        $tabs['bundle'] = [
            'label'    => __('Bundle Components', 'dispatchforge'),
            'target'   => 'bundle_product_data',
            'class'    => ['show_if_bundle'],
            'priority' => 21,
        ];

        return $tabs;
    }

    public function add_bundle_product_tab_content()
    {
        global $post;
        
        if (!$post || !is_a($post, 'WP_Post')) {
        error_log('Invalid global $post object.');
        return;
    }
        
        $bundle_components = get_post_meta($post->ID, '_bundle_components', true);
        
        if (is_string($bundle_components)) {
            $bundle_components = json_decode($bundle_components, true);
        }

        $bundle_components = is_array($bundle_components) ? $bundle_components : [];

        echo '<div id="bundle_product_data" class="panel woocommerce_options_panel">';
        echo '<div class="options_group">';

        echo '<label for="bundle_component_search">' . __('Add Product:', 'dispatchforge') . '</label>';
        echo '<input type="text" id="bundle_component_search" name="bundle_component_search" placeholder="Search for products..." autocomplete="off">';
        echo '<div id="bundle_search_results"></div>';
        echo '<input type="hidden" id="bundle_components_input" name="bundle_components" value="' . esc_attr(json_encode($bundle_components)) . '">';

        echo '<table id="bundle_components_table" class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>SKU</th><th>Name</th><th>Stock</th><th>Price</th><th>Quantity</th><th>Actions</th></tr></thead>';
        echo '<tbody id="bundle_components_body">';

        if (!empty($bundle_components)) {
            foreach ($bundle_components as $component_id => $component) {
                $product = wc_get_product($component_id);
                if ($product) {
                    $sku = $product->get_sku();
                    $name = $product->get_name();
                    $stock = $product->get_stock_quantity() ?? 'N/A';
                    $price = wc_get_price_to_display($product); // Get raw numeric price

                    echo '<tr>';
                    echo '<td>' . esc_html($sku ?: $component_id) . '</td>';
                    echo '<td>' . esc_html($name) . '</td>';
                    echo '<td>' . esc_html($stock) . '</td>';
                    echo '<td>' . esc_html(number_format($price, 2)) . '</td>';
                    echo '<td><input type="number" name="bundle_components[' . esc_attr($component_id) . '][qty]" value="' . esc_attr($component['qty']) . '" min="1"></td>';
                    echo '<td><button type="button" class="remove-item button">' . __('Remove', 'dispatchforge') . '</button></td>';
                    echo '</tr>';
                }
            }
        }

        echo '</tbody></table>';
        echo '</div></div>';
    }

    public function save_bundle_product_fields($post_id)
    {
        if (isset($_POST['bundle_components']) && is_array($_POST['bundle_components'])) {
            $sanitized_components = [];

            foreach ($_POST['bundle_components'] as $key => $component) {
                $sanitized_components[$key] = [
                    'id'    => sanitize_text_field($key),
                    'name'  => sanitize_text_field($component['name'] ?? ''),
                    'stock' => sanitize_text_field($component['stock'] ?? ''),
                    'price' => sanitize_text_field($component['price'] ?? ''),
                    'qty'   => intval($component['qty'] ?? 1),
                ];
            }

            update_post_meta($post_id, '_bundle_components', json_encode($sanitized_components));
        } else {
            delete_post_meta($post_id, '_bundle_components');
        }
    }

   public function enqueue_bundle_product_scripts()
{
    wp_enqueue_script('selectWoo');
    wp_enqueue_script('woocommerce_admin');
    wp_enqueue_script('bundle-product-scripts', plugin_dir_url(__FILE__) . 'assets/js/bundle-product-scripts.js', ['jquery', 'selectWoo', 'woocommerce_admin'], '1.0.0', true);

    // Use wp_add_inline_script instead of wp_localize_script
    $script_data = [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('search-products'),
    ];
    wp_add_inline_script(
        'bundle-product-scripts',
        'var bundleProductData = ' . wp_json_encode($script_data) . ';',
        'before'
    );
}


    public function validate_product_type_registration()
    {
        global $post;

        if (!is_a($post, 'WP_Post')) {
            error_log('validate_product_type_registration: Invalid global post object.');
            return;
        }

        $product = wc_get_product($post->ID);

        if (!$product || !is_a($product, 'WC_Product')) {
            error_log('validate_product_type_registration: Invalid product object.');
            return;
        }

        if ($product->get_type() === 'bundle') {
            if (!has_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart')) {
                add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
            }

            if (!$product->is_purchasable()) {
                $product->set_props(['purchasable' => true]);
            }

            if (!$product->is_in_stock()) {
                $product->set_stock_status('instock');
            }
        }
    }
}

new BundleProduct();
