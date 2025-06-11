<?php
/**
 * Sistema de reemplazo de templates para WooCommerce React Product
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_React_Product_Template_Override {
    
    private static $instance = null;
    
    /**
     * Singleton instance
     */
    public static function get_instance() {
        error_log('RWP Debug: Template Override - get_instance called');
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        error_log('RWP Debug: Template Override - Constructor called');
        // Prioridad alta para asegurar que se ejecute después de WooCommerce
        add_action('init', array($this, 'init_template_hooks'), 999);
    }
    
    /**
     * Inicializar hooks de template
     */
    public function init_template_hooks() {
        error_log('RWP Debug: Template Override - init_template_hooks called');
        
        // Método 1: Filtro principal de WordPress (prioridad muy alta)
        add_filter('template_include', array($this, 'override_single_product_template'), 999);
        
        // Método 2: Filtro específico de single template
        add_filter('single_template', array($this, 'override_single_template'), 999);
        
        // Método 3: Sobrescribir el template de WooCommerce directamente
        add_filter('wc_get_template', array($this, 'override_wc_template'), 999, 5);
        
        // Método 4: Hook en template_redirect para forzar el template
        add_action('template_redirect', array($this, 'force_template_redirect'), 999);
        
        // Método 5: Remover todas las acciones de WooCommerce en el single product
        add_action('wp', array($this, 'remove_wc_single_product_hooks'), 999);
        
        // Método 6: Sobrescribir locate_template
        add_filter('woocommerce_locate_template', array($this, 'locate_wc_template'), 999, 3);
    }
    
    /**
     * Método 1: Override usando template_include
     */
    public function override_single_product_template($template) {
        error_log('RWP Debug: Template Override - override_single_product_template called');
        error_log('RWP Debug: Original template: ' . $template);
        
        if (!is_product()) {
            return $template;
        }
        
        global $post;
        error_log('RWP Debug: Product ID: ' . $post->ID);
        
        if (!$this->should_use_react_template($post->ID)) {
            error_log('RWP Debug: Not using React template, returning original');
            return $template;
        }
        
        $react_template = $this->get_react_template_path();
        error_log('RWP Debug: Using React template: ' . $react_template);
        
        if (file_exists($react_template)) {
            return $react_template;
        }
        
        return $template;
    }
    
    /**
     * Método 2: Override usando single_template
     */
    public function override_single_template($template) {
        global $post;
        
        if ($post && $post->post_type === 'product' && $this->should_use_react_template($post->ID)) {
            $react_template = $this->get_react_template_path();
            
            if (file_exists($react_template)) {
                return $react_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Método 3: Override de templates específicos de WooCommerce
     */
    public function override_wc_template($template, $template_name, $args, $template_path, $default_path) {
        // Lista de templates que queremos sobrescribir
        $templates_to_override = array(
            'single-product.php',
            'content-single-product.php',
            'single-product/product-image.php',
            'single-product/product-thumbnails.php',
            'single-product/tabs/tabs.php',
            'single-product/add-to-cart/simple.php',
            'single-product/add-to-cart/variable.php',
            'single-product/add-to-cart/grouped.php',
            'single-product/add-to-cart/external.php'
        );
        
        if (!in_array($template_name, $templates_to_override)) {
            return $template;
        }
        
        // Verificar si es un producto con React habilitado
        if (!is_product()) {
            return $template;
        }
        
        global $post;
        
        if (!$this->should_use_react_template($post->ID)) {
            return $template;
        }
        
        // Para single-product.php, usar nuestro template
        if ($template_name === 'single-product.php') {
            return $this->get_react_template_path();
        }
        
        // Para otros templates, retornar un template vacío o personalizado
        $empty_template = RWP_PLUGIN_FILE . 'templates/empty.php';
        if (file_exists($empty_template)) {
            return $empty_template;
        }
        
        return $template;
    }
    
    /**
     * Método 4: Forzar template redirect
     */
    public function force_template_redirect() {
        if (!is_product()) {
            return;
        }
        
        global $post;
        
        if (!$this->should_use_react_template($post->ID)) {
            return;
        }
        
        // Remover el template actual y cargar el nuestro
        add_filter('template_include', function($template) {
            return $this->get_react_template_path();
        }, PHP_INT_MAX);
    }
    
    /**
     * Método 5: Remover hooks de WooCommerce
     */
    public function remove_wc_single_product_hooks() {
        if (!is_product()) {
            return;
        }
        
        global $post;
        
        if (!$this->should_use_react_template($post->ID)) {
            return;
        }
        
        // Remover TODOS los hooks de WooCommerce del single product
        remove_all_actions('woocommerce_before_single_product');
        remove_all_actions('woocommerce_before_single_product_summary');
        remove_all_actions('woocommerce_single_product_summary');
        remove_all_actions('woocommerce_after_single_product_summary');
        remove_all_actions('woocommerce_after_single_product');
        
        // Remover hooks específicos que podrían interferir
        remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
        remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
        
        // Agregar nuestro propio contenido
        add_action('woocommerce_before_single_product', array($this, 'output_react_container'), 10);
    }
    
    /**
     * Método 6: Override locate_template de WooCommerce
     */
    public function locate_wc_template($template, $template_name, $template_path) {
        if ($template_name !== 'single-product.php') {
            return $template;
        }
        
        if (!is_product()) {
            return $template;
        }
        
        global $post;
        
        if (!$this->should_use_react_template($post->ID)) {
            return $template;
        }
        
        return $this->get_react_template_path();
    }
    
    /**
     * Verificar si debe usar React template
     */
    private function should_use_react_template($product_id) {
        error_log('RWP Debug: Template Override - should_use_react_template called for product ' . $product_id);
        $result = rwp_is_react_enabled_for_product($product_id);
        error_log('RWP Debug: Template Override - React enabled result: ' . ($result ? 'true' : 'false'));
        return $result;
    }
    
    /**
     * Obtener path del template React
     */
    private function get_react_template_path() {
        error_log('RWP Debug: Getting React template path');
        
        // Permitir que los temas sobrescriban el template
        $theme_template = get_stylesheet_directory() . '/react-woo-templates/react-product.php';
        if (file_exists($theme_template)) {
            error_log('RWP Debug: Using theme template: ' . $theme_template);
            return $theme_template;
        }
        
        // Template del plugin
        $plugin_template = RWP_PLUGIN_PATH . 'templates/react-product.php';
        error_log('RWP Debug: Using plugin template: ' . $plugin_template);
        return $plugin_template;
    }
    
    /**
     * Output del contenedor React
     */
    public function output_react_container() {
        global $post;
        
        $template = get_post_meta($post->ID, '_wc_react_product_template', true);
        if (empty($template)) {
            $template = get_option('wc_react_product_default_template', 'template-1');
        }
        
        echo '<div id="wc-react-product-root" data-product-id="' . esc_attr($post->ID) . '" data-template="' . esc_attr($template) . '"></div>';
    }
}

// Hook para inicializar la clase
// add_action('plugins_loaded', function() {
//     if (class_exists('WooCommerce')) {
//         WC_React_Product_Template_Override::get_instance();
//     }
// }, 20);