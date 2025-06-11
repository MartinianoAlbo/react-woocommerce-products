<?php
/**
 * Clase para manejar el frontend del plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class RWP_Frontend {
    
    private $template_to_load;
    private $rest_api;
    
    public function __construct() {
        
        // Hooks    
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_footer', array($this, 'add_product_data_to_footer'));

    }
    
    /**
     * Obtener instancia de REST API (lazy loading)
     */
    private function get_rest_api() {
        if (!$this->rest_api) {
            $this->rest_api = new RWP_REST_API();
        }
        return $this->rest_api;
    }
    
    /**
     * Cargar assets del frontend
     */
    public function enqueue_frontend_assets() {
        if (!is_product()) {
            return;
        }
        
        // Verificar si React está habilitado para este producto
        if (!rwp_is_react_enabled_for_product(get_the_ID())) {
            return;
        }
        
        // Registrar y encolar estilos
        wp_enqueue_style(
            'rwp-frontend',
            RWP_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            RWP_VERSION
        );
        
        // Registrar y encolar scripts
        wp_enqueue_script(
            'rwp-frontend',
            RWP_PLUGIN_URL . 'assets/js/frontend.js',
            array('wp-element'),
            RWP_VERSION,
            true
        );
        
        // Localizar script
        wp_localize_script('rwp-frontend', 'rwpSettings', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => RWP_PLUGIN_URL,
            'nonce' => wp_create_nonce('rwp-nonce')
        ));
    }
    
    /**
     * Agregar datos del producto al footer para React
     */
    public function add_product_data_to_footer() {
        if (!is_product()) {
            return;
        }
        
        // Verificar si React está habilitado para este producto
        if (!rwp_is_react_enabled_for_product(get_the_ID())) {
            return;
        }
        
        global $product;
        
        if (!is_a($product, 'WC_Product')) {
            return;
        }
        
        $product_data = $this->get_rest_api()->format_product_data($product);
        
        if ($product_data) {
            ?>
            <script type="text/javascript">
                window.rwpProductData = <?php echo wp_json_encode($product_data); ?>;
            </script>
            <?php
        }
    }
    

    

    
} 