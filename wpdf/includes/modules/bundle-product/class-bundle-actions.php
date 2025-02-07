<?php

namespace DispatchForge\Modules;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class BundleProductActions
{
    public function __construct()
    {
        add_action('woocommerce_order_status_processing', [$this, 'reduce_bundle_stock']);
        add_action('wp_ajax_custom_bundle_product_search', [$this, 'custom_bundle_product_search_handler']);
        add_action('wp_ajax_nopriv_custom_bundle_product_search', [$this, 'custom_bundle_product_search_handler']);
    }

    public function custom_bundle_product_search_handler()
    {
        check_ajax_referer('search-products', 'security');

        if (!isset($_POST['term']) || empty($_POST['term'])) {
            wp_send_json_error(['message' => 'Search term missing']);
        }

        $term = sanitize_text_field($_POST['term']);

        $args = [
            'post_type'      => 'product',
            'posts_per_page' => 10,
            's'              => $term,
        ];

        $query = new \WP_Query($args);
        $results = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);

                $results[] = [
                    'id'    => $product_id,
                    'text'  => get_the_title(),
                    'stock' => $product->get_stock_quantity() !== null ? $product->get_stock_quantity() : 'N/A',
                    'price' => wc_price($product->get_regular_price()),
                ];
            }
        }

        wp_reset_postdata();
        wp_send_json($results);
    }

    public function reduce_bundle_stock($order_id)
    {
        $order = wc_get_order($order_id);
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product && $product->get_type() === 'bundle') {
                $components = json_decode(get_post_meta($product->get_id(), '_bundle_components', true), true);
                $quantity   = $item->get_quantity();

                foreach ($components as $component_id => $component_data) {
                    $current_stock = get_post_meta($component_id, '_stock', true);
                    $new_stock = max(0, $current_stock - ($component_data['qty'] * $quantity));
                    update_post_meta($component_id, '_stock', $new_stock);
                }
            }
        }
    }
}

new BundleProductActions();
