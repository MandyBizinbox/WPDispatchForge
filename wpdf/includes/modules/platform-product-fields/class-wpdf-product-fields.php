<?php

namespace DispatchForge\Classes;

class WPDF_Product_Fields {
    public function __construct() {
    add_action('add_meta_boxes', [$this, 'add_product_meta_box']);
    add_action('save_post', [$this, 'save_meta_box_data']);

    // Fix: Add Supplier Details Tab
    add_filter('woocommerce_product_data_tabs', [$this, 'add_custom_inventory_tab']);
    add_action('woocommerce_product_data_panels', [$this, 'add_custom_inventory_fields']);
    add_action('woocommerce_process_product_meta', [$this, 'save_custom_inventory_fields']);
}


    public function add_product_meta_box() {
        add_meta_box(
            'wpdf_product_fields',
            __('Connected Platform Product Fields', 'wpdispatchforge'),
            [$this, 'render_meta_box'],
            'product',
            'normal',
            'high'
        );
        add_meta_box(
            'wpdf_connected_platforms',
            __('Sync to Connected WooCommerce Platforms', 'wpdispatchforge'),
            [$this, 'render_connected_platforms_meta_box'],
            'product',
            'side'
        );
    }

 

    public function render_meta_box($post) {
        global $wpdb;

        // Define table name
        $connected_platforms_table = $wpdb->prefix . 'wpdf_connected_platforms';

        // Fetch all connected platforms
        $connected_platforms = $wpdb->get_results("SELECT * FROM $connected_platforms_table", ARRAY_A);

        if (empty($connected_platforms)) {
            echo '<p>' . esc_html__('No connected platforms found. Please configure platforms in the settings.', 'wpdispatchforge') . '</p>';
            return;
        }

        echo '<div id="wpdf-platform-tabs">';

        // Render tab headers
        echo '<ul class="wpdf-tab-headers">';
        foreach ($connected_platforms as $platform) {
            $status_color = ($platform['status'] === 'pending') ? 'red' : 'inherit';
            echo '<li><a href="#wpdf-tab-' . esc_attr($platform['id']) . '" style="color: ' . esc_attr($status_color) . ';">' . esc_html($platform['store_name']) . '</a></li>';
        }
        echo '</ul>';

        // Render tab content
        foreach ($connected_platforms as $platform) {
            echo '<div id="wpdf-tab-' . esc_attr($platform['id']) . '" class="wpdf-tab-content">';
            echo '<h4>' . esc_html($platform['store_name']) . '</h4>';
            echo '<table class="form-table">';
            if ($platform['platform'] === 'woocommerce') {
                $this->render_woocommerce_fields($post, $platform);
            } elseif ($platform['platform'] === 'takealot') {
                $this->render_takealot_fields($post, $platform);
            }
            echo '</table>';
            echo '</div>';
        }

        echo '</div>';

        // Add nonce for security
        wp_nonce_field('wpdf_save_product_fields', 'wpdf_product_fields_nonce');
    }

    private function render_woocommerce_fields($post, $platform) {
    $product_name = get_post_meta($post->ID, '_woocommerce_product_name_' . $platform['id'], true);
    $description = get_post_meta($post->ID, '_woocommerce_description_' . $platform['id'], true);
    $short_description = get_post_meta($post->ID, '_woocommerce_short_description_' . $platform['id'], true);
    $regular_price = get_post_meta($post->ID, '_woocommerce_regular_price_' . $platform['id'], true);
    $sale_price = get_post_meta($post->ID, '_woocommerce_sale_price_' . $platform['id'], true);
    $sale_date_start = get_post_meta($post->ID, '_woocommerce_sale_date_start_' . $platform['id'], true);
    $sale_date_end = get_post_meta($post->ID, '_woocommerce_sale_date_end_' . $platform['id'], true);

    echo '<tr>
            <th><label for="woocommerce_product_name_' . esc_attr($platform['id']) . '">' . __('Product Name', 'wpdispatchforge') . '</label></th>
            <td><input type="text" id="woocommerce_product_name_' . esc_attr($platform['id']) . '" name="woocommerce_product_name[' . esc_attr($platform['id']) . ']" value="' . esc_attr($product_name) . '" class="widefat" /></td>
          </tr>';
    echo '<tr>
            <th><label for="woocommerce_description_' . esc_attr($platform['id']) . '">' . __('Description', 'wpdispatchforge') . '</label></th>
            <td>' . wp_editor($description, 'woocommerce_description_' . esc_attr($platform['id']), ['textarea_name' => 'woocommerce_description[' . esc_attr($platform['id']) . ']']) . '</td>
          </tr>';
    echo '<tr>
            <th><label for="woocommerce_short_description_' . esc_attr($platform['id']) . '">' . __('Short Description', 'wpdispatchforge') . '</label></th>
            <td>' . wp_editor($short_description, 'woocommerce_short_description_' . esc_attr($platform['id']), ['textarea_name' => 'woocommerce_short_description[' . esc_attr($platform['id']) . ']']) . '</td>
          </tr>';
    echo '<tr>
            <th><label for="woocommerce_regular_price_' . esc_attr($platform['id']) . '">' . __('Regular Price', 'wpdispatchforge') . '</label></th>
            <td><input type="number" id="woocommerce_regular_price_' . esc_attr($platform['id']) . '" name="woocommerce_regular_price[' . esc_attr($platform['id']) . ']" value="' . esc_attr($regular_price) . '" class="widefat" step="0.01" /></td>
          </tr>';
    echo '<tr>
            <th><label for="woocommerce_sale_price_' . esc_attr($platform['id']) . '">' . __('Sale Price', 'wpdispatchforge') . '</label></th>
            <td><input type="number" id="woocommerce_sale_price_' . esc_attr($platform['id']) . '" name="woocommerce_sale_price[' . esc_attr($platform['id']) . ']" value="' . esc_attr($sale_price) . '" class="widefat" step="0.01" /></td>
          </tr>';
    echo '<tr>
            <th><label for="woocommerce_sale_date_start_' . esc_attr($platform['id']) . '">' . __('Sale Date Start', 'wpdispatchforge') . '</label></th>
            <td><input type="date" id="woocommerce_sale_date_start_' . esc_attr($platform['id']) . '" name="woocommerce_sale_date_start[' . esc_attr($platform['id']) . ']" value="' . esc_attr($sale_date_start) . '" class="widefat" /></td>
          </tr>';
    echo '<tr>
            <th><label for="woocommerce_sale_date_end_' . esc_attr($platform['id']) . '">' . __('Sale Date End', 'wpdispatchforge') . '</label></th>
            <td><input type="date" id="woocommerce_sale_date_end_' . esc_attr($platform['id']) . '" name="woocommerce_sale_date_end[' . esc_attr($platform['id']) . ']" value="' . esc_attr($sale_date_end) . '" class="widefat" /></td>
          </tr>';
}

    private function render_takealot_fields($post, $platform) {
    $status = get_post_meta($post->ID, '_takealot_status_' . $platform['id'], true);
    $tsin = get_post_meta($post->ID, '_takealot_tsin_' . $platform['id'], true);
    $offer_id = get_post_meta($post->ID, '_takealot_offer_id_' . $platform['id'], true);
    $barcode = get_post_meta($post->ID, '_takealot_barcode_' . $platform['id'], true);
    $product_label = get_post_meta($post->ID, '_takealot_product_label_' . $platform['id'], true);
    $lead_time_days = get_post_meta($post->ID, '_takealot_lead_time_days_' . $platform['id'], true);
    $selling_price = get_post_meta($post->ID, '_takealot_selling_price_' . $platform['id'], true);
    $rrp = get_post_meta($post->ID, '_takealot_rrp_' . $platform['id'], true);

    echo '<tr>
            <th><label>' . __('Status', 'wpdispatchforge') . '</label></th>
            <td><input type="text" value="' . esc_attr($status) . '" class="widefat" readonly /></td>
          </tr>';
    echo '<tr>
            <th><label>' . __('TSIN', 'wpdispatchforge') . '</label></th>
            <td><input type="text" value="' . esc_attr($tsin) . '" class="widefat" readonly /></td>
          </tr>';
    echo '<tr>
            <th><label>' . __('Offer ID', 'wpdispatchforge') . '</label></th>
            <td><input type="text" value="' . esc_attr($offer_id) . '" class="widefat" readonly /></td>
          </tr>';
    echo '<tr>
            <th><label>' . __('Barcode', 'wpdispatchforge') . '</label></th>
            <td><input type="text" value="' . esc_attr($barcode) . '" class="widefat" readonly /></td>
          </tr>';
    echo '<tr>
            <th><label>' . __('Product Label Number', 'wpdispatchforge') . '</label></th>
            <td><input type="text" value="' . esc_attr($product_label) . '" class="widefat" readonly /></td>
          </tr>';
    echo '<tr>
            <th><label>' . __('Lead Time Days', 'wpdispatchforge') . '</label></th>
            <td>
                <select name="takealot_lead_time_days[' . esc_attr($platform['id']) . ']" class="widefat">
                    <option value="3" ' . selected($lead_time_days, '3', false) . '>3</option>
                    <option value="4" ' . selected($lead_time_days, '4', false) . '>4</option>
                    <option value="5" ' . selected($lead_time_days, '5', false) . '>5</option>
                    <option value="6" ' . selected($lead_time_days, '6', false) . '>6</option>
                </select>
            </td>
          </tr>';
    echo '<tr>
            <th><label>' . __('Selling Price', 'wpdispatchforge') . '</label></th>
            <td><input type="number" step="0.01" name="takealot_selling_price[' . esc_attr($platform['id']) . ']" value="' . esc_attr($selling_price) . '" class="widefat" /></td>
          </tr>';
    echo '<tr>
            <th><label>' . __('RRP', 'wpdispatchforge') . '</label></th>
            <td><input type="number" step="0.01" name="takealot_rrp[' . esc_attr($platform['id']) . ']" value="' . esc_attr($rrp) . '" class="widefat" /></td>
          </tr>';

    // Inventory Table
    echo '<tr>
            <th><label>' . __('Takealot Inventory', 'wpdispatchforge') . '</label></th>
            <td>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th>' . __('Warehouse', 'wpdispatchforge') . '</th>
                            <th>' . __('Stock Level', 'wpdispatchforge') . '</th>
                        </tr>
                    </thead>
                    <tbody id="takealot-inventory-table-' . esc_attr($platform['id']) . '">
                        <tr><td colspan="2">' . __('Loading...', 'wpdispatchforge') . '</td></tr>
                    </tbody>
                </table>
            </td>
          </tr>';
}
public function render_connected_platforms_meta_box($post) {
    global $wpdb;

    // Define table name
    $connected_platforms_table = $wpdb->prefix . 'wpdf_connected_platforms';

    // Fetch all connected WooCommerce platforms
    $woocommerce_platforms = $wpdb->get_results("SELECT * FROM $connected_platforms_table WHERE platform = 'woocommerce'", ARRAY_A);

    if (empty($woocommerce_platforms)) {
        echo '<p>' . esc_html__('No WooCommerce platforms connected.', 'wpdispatchforge') . '</p>';
        return;
    }

    echo '<ul class="wpdf-connected-platforms">';
    foreach ($woocommerce_platforms as $platform) {
        $is_checked = get_post_meta($post->ID, '_wpdf_sync_to_' . $platform['id'], true);
        echo '<li>
                <label>
                    <input type="checkbox" name="wpdf_sync_to[' . esc_attr($platform['id']) . ']" ' . checked($is_checked, 'yes', false) . ' />
                    ' . esc_html($platform['store_name']) . '
                </label>
              </li>';
    }
    echo '</ul>';
}

    public function save_meta_box_data($post_id) {
        // Check nonce
        if (!isset($_POST['wpdf_product_fields_nonce']) || !wp_verify_nonce($_POST['wpdf_product_fields_nonce'], 'wpdf_save_product_fields')) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save WooCommerce fields
        if (isset($_POST['woocommerce_title'])) {
            foreach ($_POST['woocommerce_title'] as $platform_id => $title) {
                update_post_meta($post_id, '_woocommerce_title_' . $platform_id, sanitize_text_field($title));
            }
        }

        if (isset($_POST['woocommerce_description'])) {
            foreach ($_POST['woocommerce_description'] as $platform_id => $description) {
                update_post_meta($post_id, '_woocommerce_description_' . $platform_id, sanitize_textarea_field($description));
            }
        }

        if (isset($_POST['woocommerce_price'])) {
            foreach ($_POST['woocommerce_price'] as $platform_id => $price) {
                update_post_meta($post_id, '_woocommerce_price_' . $platform_id, floatval($price));
            }
        }

        // Save Takealot fields
        if (isset($_POST['takealot_tsin'])) {
            foreach ($_POST['takealot_tsin'] as $platform_id => $tsin) {
                update_post_meta($post_id, '_takealot_tsin_' . $platform_id, sanitize_text_field($tsin));
            }
        }
        
        if (isset($_POST['woocommerce_product_name'])) {
    foreach ($_POST['woocommerce_product_name'] as $platform_id => $product_name) {
        update_post_meta($post_id, '_woocommerce_product_name_' . $platform_id, sanitize_text_field($product_name));
    }
}

if (isset($_POST['woocommerce_regular_price'])) {
    foreach ($_POST['woocommerce_regular_price'] as $platform_id => $regular_price) {
        update_post_meta($post_id, '_woocommerce_regular_price_' . $platform_id, floatval($regular_price));
    }
}

if (isset($_POST['woocommerce_sale_date_start'])) {
    foreach ($_POST['woocommerce_sale_date_start'] as $platform_id => $sale_date_start) {
        update_post_meta($post_id, '_woocommerce_sale_date_start_' . $platform_id, sanitize_text_field($sale_date_start));
    }
}


        if (isset($_POST['takealot_offer_id'])) {
            foreach ($_POST['takealot_offer_id'] as $platform_id => $offer_id) {
                update_post_meta($post_id, '_takealot_offer_id_' . $platform_id, sanitize_text_field($offer_id));
            }
        }

        if (isset($_POST['takealot_selling_price'])) {
            foreach ($_POST['takealot_selling_price'] as $platform_id => $selling_price) {
                update_post_meta($post_id, '_takealot_selling_price_' . $platform_id, floatval($selling_price));
            }
        }
        
        if (isset($_POST['takealot_lead_time_days'])) {
    foreach ($_POST['takealot_lead_time_days'] as $platform_id => $lead_time_days) {
        update_post_meta($post_id, '_takealot_lead_time_days_' . $platform_id, sanitize_text_field($lead_time_days));
    }
}

if (isset($_POST['takealot_selling_price'])) {
    foreach ($_POST['takealot_selling_price'] as $platform_id => $selling_price) {
        update_post_meta($post_id, '_takealot_selling_price_' . $platform_id, floatval($selling_price));
    }
}

if (isset($_POST['takealot_rrp'])) {
    foreach ($_POST['takealot_rrp'] as $platform_id => $rrp) {
        update_post_meta($post_id, '_takealot_rrp_' . $platform_id, floatval($rrp));
    }
}

    }


public function add_custom_inventory_tab($tabs) {
    // Ensure we preserve existing tabs
    if (!isset($tabs['general'])) {
        $tabs['general'] = [
            'label'    => __('General', 'woocommerce'),
            'target'   => 'general_product_data',
            'class'    => ['general_tab'],
            'priority' => 10,
        ];
    }

    $tabs['supplier_details'] = [
        'label'    => __('Supplier Details', 'wpdispatchforge'),
        'target'   => 'supplier_details_data',
        'class'    => [],
        'priority' => 21, // Append after default tabs
    ];

    return $tabs;
}


public function add_custom_inventory_fields() {
    ?>
    <div id="supplier_details_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <?php
            woocommerce_wp_text_input([
                'id'          => '_preferred_supplier',
                'label'       => __('Preferred Supplier', 'wpdispatchforge'),
                'desc_tip'    => 'true',
                'description' => __('Enter the name of the preferred supplier.', 'wpdispatchforge'),
                'type'        => 'text',
            ]);
            woocommerce_wp_text_input([
                'id'                => '_cost_price',
                'label'             => __('Cost Price', 'wpdispatchforge'),
                'desc_tip'          => 'true',
                'description'       => __('Enter the cost price (decimal allowed).', 'wpdispatchforge'),
                'type'              => 'number',
                'custom_attributes' => [
                    'step' => '0.01',
                    'min'  => '0',
                ],
            ]);
            ?>
        </div>
    </div>
    <?php
}

public function save_custom_inventory_fields($post_id) {
    if (isset($_POST['_preferred_supplier'])) {
        update_post_meta($post_id, '_preferred_supplier', sanitize_text_field($_POST['_preferred_supplier']));
    }
    if (isset($_POST['_cost_price'])) {
        update_post_meta($post_id, '_cost_price', floatval($_POST['_cost_price']));
    }
}

}
new WPDF_Product_Fields();

