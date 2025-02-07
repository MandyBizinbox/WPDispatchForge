<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

global $product;
echo '<p>Custom bundle template loaded for product ID: ' . esc_html($product->get_id()) . '</p>';

if ($product->get_type() !== 'bundle') {
    return;
}

do_action('woocommerce_before_add_to_cart_form');
?>

<form class="cart" method="post" enctype='multipart/form-data'>
    <div class="bundle-product-wrapper">
        <h2><?php esc_html_e('Bundle Contents', 'woocommerce'); ?></h2>
        <ul>
            <?php
            $bundle_components = get_post_meta($product->get_id(), '_bundle_components', true);
            if (is_string($bundle_components)) {
                $bundle_components = json_decode($bundle_components, true);
            }

            if (!empty($bundle_components)) {
                foreach ($bundle_components as $component_id => $component) {
                    $component_product = wc_get_product($component_id);
                    if ($component_product) {
                        echo '<li>' . esc_html($component_product->get_name()) . ' x ' . esc_html($component['qty']) . '</li>';
                    }
                }
            }
            ?>
        </ul>
    </div>

    <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>">
    <button type="submit" class="single_add_to_cart_button button alt"><?php esc_html_e('Add Bundle to Cart', 'woocommerce'); ?></button>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>
