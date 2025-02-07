<?php

class WC_Kanban_Orders {
    private static $instance = null;

    // Singleton pattern to get an instance of the class
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('wc_kanban', array($this, 'display_kanban_board'));
        add_action('wp_ajax_fetch_order_details', array($this, 'fetch_order_details_callback'));
        add_action('wp_ajax_update_order_status', array($this, 'update_order_status_callback'));
    }

    // Add the Kanban board menu to the admin dashboard
    public function add_admin_menu() {
        add_menu_page(
            'WooCommerce Kanban Orders',
            'Kanban View',
            'manage_options',
            'wc-kanban-orders',
            array($this, 'kanban_page_content'),
            'dashicons-schedule',
            6
        );
    }

    // Enqueue necessary scripts and styles for the Kanban board
    public function enqueue_scripts() {
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_script('wc-kanban-js', WC_KANBAN_ORDERS_URL . 'assets/js/wc-kanban.js', array('jquery'), '1.0.0', true);
        wp_localize_script('wc-kanban-js', 'ajaxurl', admin_url('admin-ajax.php')); // Make sure ajaxurl is available
        wp_enqueue_style('wc-kanban-css', WC_KANBAN_ORDERS_URL . 'assets/css/wc-kanban.css', array(), '1.0.0');
    }

    // Display the content for the Kanban board page
    public function kanban_page_content() {
        echo '<div class="wrap"><h1>Kanban Board for WooCommerce Orders</h1>';
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Display the Kanban board
        echo $this->display_kanban_board();

        // Add modal HTML for displaying order details
        echo '<div id="order-details-modal" style="display:none;">
                <div class="modal-content">
                    <button class="close-modal">Close</button>
                    <div class="order-details-content"></div>
                </div>
              </div>';
        
        echo '</div>';
    }

    // Function to display the Kanban board with orders grouped by status
    public function display_kanban_board() {
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        $visible_statuses = array();

        $settings = get_option('wc_kanban_statuses');
        foreach ($user_roles as $role) {
            if (isset($settings[$role])) {
                $visible_statuses = array_merge($visible_statuses, $settings[$role]);
            }
        }

        $visible_statuses = array_unique($visible_statuses);
        $normalized_statuses = array_map(function($status) {
            return str_replace('wc-', '', $status); // Remove 'wc-' prefix if present
        }, $visible_statuses);

        // Fetch orders matching these statuses
        $orders = wc_get_orders(array('limit' => -1, 'status' => $normalized_statuses));

        // Group orders by status
        $orders_by_status = array();
        foreach ($orders as $order) {
            $status = 'wc-' . $order->get_status(); // Prefix the status with 'wc-' to match column headers
            if (!isset($orders_by_status[$status])) {
                $orders_by_status[$status] = array();
            }
            $orders_by_status[$status][] = $order;
        }

        echo '<div id="kanban-board" class="kanban-container">';

        // Create a column for each status, even if empty
        foreach ($visible_statuses as $status) {
            echo '<div class="kanban-column" data-status="' . esc_attr($status) . '">';
            echo '<h2>' . wc_get_order_status_name($status) . '</h2>';

            // Display orders if they exist for the status
            if (isset($orders_by_status[$status]) && !empty($orders_by_status[$status])) {
                foreach ($orders_by_status[$status] as $order) {
                    $order_number = $order->get_order_number();
                    $client_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                    $order_date = $order->get_date_created()->date('Y-m-d');
                    $order_origin = get_post_meta($order->get_id(), '_order_origin', true);

                    if (class_exists('MultiSync_Pro')) {
                        $custom_order_number = get_post_meta($order->get_id(), 'custom_order_number_with_prefix', true);
                        if ($custom_order_number) {
                            $order_number = $custom_order_number;
                        }
                    }

                    echo '<div class="kanban-card" data-order-id="' . $order->get_id() . '">';
                    echo '<h3>' . $order_number . '</h3>';
                    echo '<p>Client: ' . $client_name . '</p>';
                    echo '<p>Date: ' . $order_date . '</p>';
                    echo '<p>Origin: ' . $order_origin . '</p>';
                    echo '</div>';
                }
            } else {
                // Debug: Log empty statuses
                error_log('No orders for status: ' . $status);
            }

            echo '</div>';
        }

        echo '</div>';
    }

    // AJAX callback to fetch order details and display in modal
    public function fetch_order_details_callback() {
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Order not found.');
        }

        ob_start();
        ?>
        <div class="modal-order-header">
            <h2>Order #<?php echo $order->get_order_number(); ?></h2>
        </div>
        <div class="modal-order-details">
            <div class="modal-column">
                <p><strong>Customer Details:</strong><br>
                <?php echo $order->get_formatted_billing_full_name(); ?><br>
                <?php echo $order->get_billing_email(); ?><br>
                <?php echo $order->get_billing_phone(); ?></p>

                <p><strong>Payment Method:</strong><br>
                <?php echo $order->get_payment_method_title(); ?></p>
            </div>
            <div class="modal-column">
                <p><strong>Order Date:</strong><br>
                <?php echo $order->get_date_created()->date('Y-m-d H:i'); ?></p>

                <p><strong>Shipping Details:</strong><br>
                <?php echo $order->get_formatted_shipping_full_name(); ?><br>
                <?php echo $order->get_shipping_address_1(); ?><br>
                <?php echo $order->get_shipping_address_2(); ?><br>
                <?php echo $order->get_shipping_city(); ?>, <?php echo $order->get_shipping_postcode(); ?></p>

                <p><strong>Shipping Method:</strong><br>
                <?php echo $order->get_shipping_method(); ?></p>

                <p><strong>Order Status:</strong><br>
                <span class="order-status"><?php echo wc_get_order_status_name($order->get_status()); ?></span></p>
            </div>
        </div>
        <div class="modal-order-items">
            <h3>Items on Order:</h3>
            <ul>
                <?php foreach ($order->get_items() as $item_id => $item): ?>
                    <li>
                        <?php echo $item->get_name(); ?> x <?php echo $item->get_quantity(); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <button class="close-modal">Close</button>
        <?php
        $content = ob_get_clean();
        wp_send_json_success($content);
    }

    // AJAX callback to update the order status
    public function update_order_status_callback() {
        $order_id = intval($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['new_status']);

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Order not found.');
        }

        $order->update_status($new_status);
        wp_send_json_success();
    }
}

// Initialize the class as a singleton
WC_Kanban_Orders::get_instance();
?>
