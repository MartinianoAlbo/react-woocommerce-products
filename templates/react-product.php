<?php
/**
 * Template personalizado para productos React
 */

if (!defined('ABSPATH')) {
    exit;
}

// Asegurar que las funciones del plugin estén disponibles
if (!function_exists('rwp_get_product_template_id')) {
    require_once WP_PLUGIN_DIR . '/react-woocommerce-products/includes/rwp-functions.php';
}

error_log('RWP Debug: Template React cargado');

get_header('shop');

global $product;

// Asegurar que tenemos un producto válido
if (!is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
    if (!$product) {
        error_log('RWP Debug: No se encontró el producto');
        wp_redirect(wc_get_page_permalink('shop'));
        exit;
    }
}

$product_id = $product->get_id();
$template_id = rwp_get_product_template_id($product_id);

error_log('RWP Debug: Product ID: ' . $product_id);
error_log('RWP Debug: Template ID: ' . $template_id);

// Verificar si React está habilitado
if (!rwp_is_react_enabled_for_product($product_id)) {
    error_log('RWP Debug: React no está habilitado para este producto');
    include(WC()->plugin_path() . '/templates/single-product.php');
    exit;
}

?>
<div class="woocommerce">
    <div id="product-<?php echo esc_attr($product_id); ?>" <?php wc_product_class('', $product); ?>>
        <div class="rwp-product-wrapper">
            <!-- Contenedor React Principal -->
            <div id="rwp-product-container" 
                 data-product-id="<?php echo esc_attr($product_id); ?>" 
                 data-template-id="<?php echo esc_attr($template_id); ?>"
                 class="rwp-template-<?php echo esc_attr($template_id); ?>">
            </div>
        </div>
    </div>
</div>

<?php
// Cargar los assets necesarios
do_action('rwp_enqueue_template_assets', $template_id);

get_footer('shop');
?> 